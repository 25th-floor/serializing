<?php

namespace TwentyFifth\Serializing\Annotations\Doctrine;

use TwentyFifth\Serializing\Annotations\AnnotationAdapterInterface;
use TwentyFifth\Serializing\Annotations\AnnotationSerializable;
use TwentyFifth\Serializing\Annotations\Doctrine\Annotation\ReferenceValue;
use TwentyFifth\Serializing\Annotations\Doctrine\Annotation\SerializableMethod;
use TwentyFifth\Serializing\ReferenceObject;

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

            $pos = $this->getPropertyStartPosition($annotation);

            // only getter are allowed!
            if (!$pos) {
                continue;
            }

            $property = lcfirst(substr($annotation->getName(), $pos));

            $getter = array($serializable, $annotation->getName());
            $methods[$property] = $getter;

            if ($annotation->isReference()) {
                $getter =$annotation->getName();
                $value = $serializable->$getter();

                $data = $this->handleReference($value);
                if (!empty($data)) {
                    // return closure so the Serializer does not need to change
                    $methods[$property] = function() use ($data) {
                        return $data;
                    };
                }
            }
        }

        return $methods;
    }

    protected function handleReference($object)
    {
        if (!is_object($object)) {
            return $object;
        }

        if (!$object instanceof AnnotationSerializable) {
            return $object;
        }

        $className = get_class($object);

        $definition = $this->getParser()->getDefinition($className);

        $data = array();
        foreach ($definition as $annotation) {
            if (!$annotation instanceof ReferenceValue) {
                continue;
            }

            $pos = $this->getPropertyStartPosition($annotation);

            // only getter are allowed!
            if (!$pos) {
                continue;
            }

            $property = lcfirst(substr($annotation->getName(), $pos));
            $getter = $annotation->getName();

            $data[$property] = $object->$getter();

        }
        if (!empty($data)) {
            $data['__ReferenceObject'] = $className;
        }

        return $data;
    }

    /**
     * @param $annotation
     *
     * @return bool|int
     */
    protected function getPropertyStartPosition($annotation)
    {
        $pos = false;
        if (strpos(strtolower($annotation->getName()), 'get') === 0) {
            $pos = 3;
        }

        if (strpos(strtolower($annotation->getName()), 'has') === 0) {
            $pos = 3;
        }

        if (strpos(strtolower($annotation->getName()), 'is') === 0) {
            $pos = 2;
            return $pos;
        }
        return $pos;
    }
}
