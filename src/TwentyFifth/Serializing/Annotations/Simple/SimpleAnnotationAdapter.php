<?php

namespace TwentyFifth\Serializing\Annotations\Simple;

use TwentyFifth\Serializing\Annotations\AnnotationAdapterInterface;
use TwentyFifth\Serializing\Annotations\AnnotationSerializable;

/**
 * Class SimpleAnnotationAdapter
 *
 * @package TwentyFifth\Serializing\Simple
 */
class SimpleAnnotationAdapter implements AnnotationAdapterInterface
{
    public static $ignoreListCache = false;

    /**
     * @param AnnotationSerializable $serializable
     *
     * @return array
     */
    public function getSerializableMethods(AnnotationSerializable $serializable)
    {
        $methods = array();

        $properties = SimpleAnnotationParser::getProperties($serializable);
        $ignores = SimpleAnnotationParser::getIgnores($serializable);

        if (self::$ignoreListCache) {
            $ignoreList = SimpleAnnotationParser::getListIgnores($serializable);
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
}
