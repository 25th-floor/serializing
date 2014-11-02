<?php

namespace TwentyFifth\Serializing;

/**
 * Class SerializerAwareTrait
 *
 * @package TwentyFifth\Serializing
 */
trait SerializerAwareTrait
{
    private $serializer;

    /**
     * @return Serializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param Serializer $serializer
     *
     * @return $this
     */
    public function setSerializer(Serializer $serializer)
    {
        $this->serializer = $serializer;
        return $this;
    }

}
