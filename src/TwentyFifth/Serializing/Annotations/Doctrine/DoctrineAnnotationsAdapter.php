<?php

namespace TwentyFifth\Serializing\Annotations\Doctrine;

use TwentyFifth\Serializing\Annotations\AnnotationAdapterInterface;
use TwentyFifth\Serializing\Annotations\AnnotationSerializable;
use TwentyFifth\Serializing\Annotations\Doctrine\Annotation\CallableMethod;

/**
 * Class DoctrineAnnotationsAdapter
 *
 * @package TwentyFifth\Serializing\Annotations\Doctrine
 */
class DoctrineAnnotationsAdapter implements AnnotationAdapterInterface
{
    /** @var  DoctrineAnnotationParser */
    private $parser;

    function __construct($parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return DoctrineAnnotationParser
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * @param AnnotationSerializable $serializable
     *
     * @return array
     */
    public function getSerializableMethods(AnnotationSerializable $serializable)
    {
        $className = get_class($serializable);

        $availableMethods = $this->getParser()->getDefinition($className);

        $methods = array();
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

            $methods[$property] = array($serializable, $annotation->getName());
        }

        return $methods;
    }
}