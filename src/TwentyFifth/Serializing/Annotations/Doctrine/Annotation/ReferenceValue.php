<?php

namespace TwentyFifth\Serializing\Annotations\Doctrine\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * @package TwentyFifth\Serializing\Annotation
 */
final class ReferenceValue
{
    /** @var string name of the method */
    private $name;

    function __construct($values)
    {
        if (!isset($values['name'])) {
            throw new \RuntimeException('Name is missing!');
        }

        if (strpos($values['name'], ' ') !== false) {
            throw new \RuntimeException(sprintf('Name "%s" has a space within', $values['name']));
        }

        $this->name = $values['name'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
