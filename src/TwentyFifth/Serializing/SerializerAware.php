<?php

namespace TwentyFifth\Serializing;

/**
 * Interface SerializerAware
 *
 * @package TwentyFifth\Serializing
 */
interface SerializerAware
{
    /**
     * @return Serializer
     */
    public function getSerializer();

    /**
     * @param Serializer $serializer
     */
    public function setSerializer(Serializer $serializer);
}
