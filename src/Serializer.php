<?php

namespace TwentyFifth\Serializing;

/**
 * Serializes the data for the web interface (f.e. rest) and also handles Object of AbstractModel types
 */
class Serializer
{
    static $ignoreListCache = false;

    /**
     * @param mixed $data
     * @param int   $steps
     *
     * @return array|null
     */
    public static function serialize($data, $steps)
    {
        if ($steps < 0) {
            return self::serializeBasic($data, $steps);
        }

        if ($data instanceof Serializable) {
            return self::serializeSerializable($data, $steps);
        } else {
            if (is_array($data)) {
                return self::serializeArray($data, $steps);
            } else {
                if ($data instanceof \Iterator) {
                    return self::serializeIterator($data, $steps);
                } else {
                    if ($data instanceof \IteratorAggregate) {
                        return self::serializeIterator($data->getIterator(), $steps);
                    } else {
                        return self::serializeBasic($data, $steps);
                    }
                }
            }
        }
    }

    /**
     * Returns all properties with its callable for a Serializable
     *
     * @param Serializable $serializable
     *
     * @return Callable[]
     * @throws SerializingException
     */
    public static function getSerializeMethods(Serializable $serializable)
    {
        if ($serializable instanceof AnnotationSerializable) {
            return self::getSerializeMethodsForAnnotationSerializeable($serializable);
        } elseif ($serializable instanceof MethodSerializable) {
            return self::getSerializeMethodsForMethodSerializeable($serializable);
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
    protected static function serializeSerializable(Serializable $object, $steps)
    {
        $serialized = [];

        foreach (self::getSerializeMethods($object) as $property => $getter) {
            $value = $getter();
            $serialized[$property] = self::serialize($value, $steps - 1);
        }

        return $serialized;
    }

    private static $serializeMethodCache = array();

    /**
     * @param AnnotationSerializable $serializable
     *
     * @return array
     */
    protected static function getSerializeMethodsForAnnotationSerializeable(AnnotationSerializable $serializable)
    {
//        $className = get_class($serializable);
//        if (isset(self::$serializeMethodCache[$className])) {
//            return self::$serializeMethodCache[$className];
//        }

        $methods = array();

        $properties = AnnotationParser::getProperties($serializable);
        $ignores = AnnotationParser::getIgnores($serializable);

        if (self::$ignoreListCache) {
            $ignoreList = AnnotationParser::getListIgnores($serializable);
            $ignores = array_merge($ignores, $ignoreList);
        }

        foreach ($properties as $property => $getter) {
            if (in_array($getter, $ignores)) {
                continue;
            }

            $methods[$property] = array($serializable, $getter);
        }

//        self::$serializeMethodCache[$className] = $methods;

        return $methods;
    }

    /**
     * @param MethodSerializable $serializable
     *
     * @return array
     */
    protected static function getSerializeMethodsForMethodSerializeable(MethodSerializable $serializable)
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
    protected static function serializeArray(array $array, $steps)
    {
        $serialized = array_map(
            function ($element) use ($steps) {
                return self::serialize($element, $steps);
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
    protected static function serializeIterator(\Iterator $data, $steps)
    {
        return self::serializeArray(iterator_to_array($data, true), $steps);
    }

    /**
     * @param mixed $data
     * @param int   $steps
     *
     * @return mixed
     */
    protected static function serializeBasic($data, $steps)
    {
        if ($data instanceof \DateTime) {
            $arr = (array) $data;
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
