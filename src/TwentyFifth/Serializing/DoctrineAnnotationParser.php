<?php

namespace TwentyFifth\Serializing;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use ReflectionClass;
use TwentyFifth\Serializing\Annotation\CallableMethod;
use UnexpectedValueException;

/**
 * Class DoctrineAnnotationParser
 *
 * @package TwentyFifth\Serializing
 */
class DoctrineAnnotationParser
{
    /**
     * Prefix for cache key, to avoid conflicts with other systems using the same cache
     * @var string
     */
    const CACHE_PREFIX = 'TwentyFifth\\Serializing\\Definition';

    /** @var  Reader */
    private $annotationReader;

    /** @var  Cache */
    private $cache;

    public function getDefinition($name)
    {
        $methods = $this->fetchFromCache($name);
        if ($methods !== false) {
            return $methods;
        }

        if (!class_exists($name) && !interface_exists($name)) {
            throw new \RuntimeException(sprintf('class %s does not exist', $name));
        }

        $methods = array();

        try {
            for ($class = new ReflectionClass($name); $class != null; $class = $class->getParentClass()) {
                $annotations = $this->getAnnotationReader()->getClassAnnotations($class);

                $methods = array_merge($methods, $annotations);
            }

        } catch (UnexpectedValueException $e) {
            throw new \RuntimeException(sprintf(
                    'Error while reading Annotations on %s: %s',
                    $class->getName(),
                    $e->getMessage()
                ), 0, $e);
        }

        $this->saveToCache($name, $methods);

        return $methods;
    }

    /**
     * Fetches a definition from the cache
     *
     * @param string $name Entry name
     * @return mixed The cached definition, null or false if the value is not already cached
     */
    private function fetchFromCache($name)
    {
        if ($this->getCache() === null) {
            return false;
        }

        $cacheKey = self::CACHE_PREFIX . $name;
        if (($data = $this->cache->fetch($cacheKey)) !== false) {
            return $data;
        }
        return false;
    }

    /**
     * Saves a definition to the cache
     *
     * @param string $name Entry name
     * @param array  $definition
     */
    private function saveToCache($name, $definition = null)
    {
        if ($this->getCache() === null) {
            return;
        }

        $cacheKey = self::CACHE_PREFIX . $name;
        $this->cache->save($cacheKey, $definition);
    }

    /**
     * @param string $class
     *
     * todo: move somewhere else
     *
     * @return array serializable properties
     */
    public function getSerializableProperties($class)
    {
        $availableMethods = $this->getDefinition($class);

        $properties = array();
        foreach ($availableMethods as $annotation) {
            if (!$annotation instanceof CallableMethod) {
                continue;
            }

            if (!$annotation->isSerializable()) {
                continue;
            }

            if ($annotation->getName() == 'getParent') {
                continue;
            }

            $pos = false;
            if (strpos(strtolower($annotation->getName()), 'get') === 0) {
                $pos = 3;
            }

            if (strpos(strtolower($annotation->getName()), 'is') === 0) {
                $pos = 2;
            }

            if (!$pos) {
                continue;
            }

            $property = lcfirst(substr($annotation->getName(), $pos));

            $properties[$property] = $annotation->getName();
        }

        return $properties;
    }

    /**
     * check if method is callable
     *
     * todo: this need to be moved elsewhere (to method specific)
     *
     * @param $method
     * @param $class
     *
     * @return bool
     */
    public function isCallable($method, $class)
    {
        $availableMethods = $this->getDefinition($class);

        foreach ($availableMethods as $annotation) {
            if (!$annotation instanceof CallableMethod) {
                continue;
            }

            if ($method == $annotation->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Reader
     */
    public function getAnnotationReader()
    {
        if ($this->annotationReader == null) {
            AnnotationRegistry::registerAutoloadNamespace('TwentyFifth\Serializing\Annotation', __DIR__ . '/../../');
            $this->annotationReader = new SimpleAnnotationReader();
            $this->annotationReader->addNamespace('TwentyFifth\Serializing\Annotation');
        }
        return $this->annotationReader;
    }

    /**
     * @param Reader $annotationReader
     *
     * @return $this
     */
    public function setAnnotationReader(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
        return $this;
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        if ($this->cache == null) {
            $this->cache = new FilesystemCache('/tmp');
        }

        return $this->cache;
    }

    /**
     * @param Cache $cache
     *
     * @return DoctrineAnnotationParser
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
        return $this;
    }

}
