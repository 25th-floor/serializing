<?php

namespace TwentyFifth\Serializing\Annotations\Doctrine;

use TwentyFifth\Serializing\Annotations\AnnotationAdapterInterface;
use TwentyFifth\Serializing\Annotations\AnnotationSerializable;
use TwentyFifth\Serializing\Annotations\Doctrine\Annotation\SerializableMethod;

/**
 * Class DoctrineAnnotationsAdapter
 *
 * @package TwentyFifth\Serializing\Annotations\Doctrine
 */
class DoctrineAnnotationsAdapter implements AnnotationAdapterInterface
{
    /** @var  DoctrineAnnotationParser */
    private $parser;

    private $serializeList = false;

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
     * @return boolean
     */
    public function isSerializeList()
    {
        return $this->serializeList;
    }

    /**
     * @param boolean $serializeList
     *
     * @return DoctrineAnnotationsAdapter
     */
    public function setSerializeList($serializeList)
    {
        $this->serializeList = $serializeList;
        return $this;
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
            if (!$annotation instanceof SerializableMethod) {
                continue;
            }

            if ($this->isSerializeList() && !$annotation->isInList()) {
                continue;
            }

            $pos = false;
            if (strpos(strtolower($annotation->getName()), 'get') === 0) {
                $pos = 3;
            }

            if (strpos(strtolower($annotation->getName()), 'has') === 0) {
                $pos = 3;
            }

            if (strpos(strtolower($annotation->getName()), 'is') === 0) {
                $pos = 2;
            }

            // only getter are allowed!
            if (!$pos) {
                continue;
            }

            $property = lcfirst(substr($annotation->getName(), $pos));

            $methods[$property] = array($serializable, $annotation->getName());
        }

        return $methods;
    }
}
