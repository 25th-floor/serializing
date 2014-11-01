<?php

namespace TwentyFifth\Serializing\Annotations;

/**
 * Interface AnnotationAdapterInterface
 *
 * @package TwentyFifth\Serializing\Annotations
 */
interface AnnotationAdapterInterface
{
    /**
     * @param AnnotationSerializable $serializable
     *
     * @return array
     */
    public function getSerializableMethods(AnnotationSerializable $serializable);
}
