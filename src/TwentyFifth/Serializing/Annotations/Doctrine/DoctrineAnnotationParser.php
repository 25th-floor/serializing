<?php

namespace TwentyFifth\Serializing\Annotations\Doctrine;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Cache\Cache;
use ReflectionClass;
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
    const CACHE_PREFIX = 'TwentyFifth\\Serializing\\Definition\\';

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
     * @return Reader
     */
    public function getAnnotationReader()
    {
        if ($this->annotationReader == null) {
            AnnotationRegistry::registerAutoloadNamespace('TwentyFifth\Serializing\Annotations\Doctrine\Annotation', __DIR__ . '/../../../../');
            $this->annotationReader = new SimpleAnnotationReader();
            $this->annotationReader->addNamespace('TwentyFifth\Serializing\Annotations\Doctrine\Annotation');
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
