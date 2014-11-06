<?php

namespace TwentyFifth\Serializing;

use TwentyFifth\Serializing\Annotations\AnnotationAdapterInterface;
use TwentyFifth\Serializing\Annotations\AnnotationSerializable;

/**
 * Serializes the data for the web interface (f.e. rest) and also handles Object of AbstractModel types
 */
class Serializer
{
    /** @var  AnnotationAdapterInterface */
    private $adapter;

    /**
     * //todo add default adapter for fallback?
     *
     * @return AnnotationAdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param mixed $adapter
     *
     * @return Serializer
     */
    public function setAdapter(AnnotationAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @param mixed $data
     * @param int   $steps
     *
     * @return array|null
     */
    public function serialize($data, $steps)
    {
        if ($steps < 0) {
            return $this->serializeBasic($data, $steps);
        }

        if ($data instanceof Serializable) {
            return $this->serializeSerializable($data, $steps);
        }

        if (is_array($data)) {
            return $this->serializeArray($data, $steps);
        }

        if ($data instanceof \Iterator) {
            return $this->serializeIterator($data, $steps);
        }

        if ($data instanceof \IteratorAggregate) {
            return $this->serializeIterator($data->getIterator(), $steps);
        }

        return $this->serializeBasic($data, $steps);
    }

    /**
     * Returns all properties with its callable for a Serializable
     *
     * todo: remove hardcoded check how to get the data, this should be a configuration and the serializer should
     *       have a list of adapters for them
     *
     * @param Serializable $serializable
     *
     * @return Callable[]
     * @throws SerializingException
     */
    public function getSerializeMethods(Serializable $serializable)
    {
        if ($serializable instanceof AnnotationSerializable) {
            return $this->getSerializeMethodsForAnnotationSerializeable($serializable);
        } elseif ($serializable instanceof MethodSerializable) {
            return $this->getSerializeMethodsForMethodSerializeable($serializable);
        } else {
            throw new SerializingException(__CLASS__ . ' is not able to serialize ' . get_class($serializable));
        }
    }

    /**
     * @param Serializable $object
     * @param int          $steps
     *
     * @return array
     */
    protected function serializeSerializable(Serializable $object, $steps)
    {
        $serialized = [];

        foreach ($this->getSerializeMethods($object) as $property => $getter) {
            $value = $getter();
            $serialized[$property] = $this->serialize($value, $steps - 1);
        }

        return $serialized;
    }

    private static $tree = [];

    /**
     * @param AnnotationSerializable $serializable
     *
     * @return array
     */
    protected function getSerializeMethodsForAnnotationSerializeable(AnnotationSerializable $serializable)
    {
        self::$tree[] = get_class($serializable);
        return $this->getAdapter()->getSerializableMethods($serializable);
    }

    /**
     * @param MethodSerializable $serializable
     *
     * @return array
     */
    protected function getSerializeMethodsForMethodSerializeable(MethodSerializable $serializable)
    {
        return $serializable->getSerializeMethods();
    }

    /**
     * Serializes an array keeping its associative-ness
     *
     * @param array $array
     * @param int   $steps
     *
     * @return array
     */
    protected function serializeArray(array $array, $steps)
    {
        $serialized = array_map(
            function ($element) use ($steps) {
                return $this->serialize($element, $steps);
            },
            $array
        );

        $associative = array_sum(array_map('is_string', array_keys($array))) > 0;

        return $associative ? $serialized : array_values($serialized);
    }

    /**
     * Serializes an iterator to an array
     *
     * @param \Iterator $data
     * @param           $steps
     *
     * @return array
     */
    protected function serializeIterator(\Iterator $data, $steps)
    {
        return $this->serializeArray(iterator_to_array($data, true), $steps);
    }

    /**
     * @param mixed $data
     * @param int   $steps
     *
     * @return mixed
     */
    protected function serializeBasic($data, $steps)
    {
        if ($data instanceof \DateTime) {
            $arr = (array)$data;
            $arr['date_iso8601'] = $data->format(\DateTime::ISO8601);
            $arr['date_rfc2822'] = $data->format(\DateTime::RFC2822);
            return $arr;
        }

        if (is_object($data) || is_array($data)) {
            return null;
        }

        return $data;
    }
}
