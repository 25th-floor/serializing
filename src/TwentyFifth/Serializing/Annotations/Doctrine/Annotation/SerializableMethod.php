<?php

namespace TwentyFifth\Serializing\Annotations\Doctrine\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * @package TwentyFifth\Serializing\Annotation
 */
final class SerializableMethod
{
    /** @var string name of the method */
    private $name;

    /** @var bool serialize into list, defaults to true */
    private $inList = true;

    function __construct($values)
    {
        if (!isset($values['name'])) {
            throw new \RuntimeException('Name is missing!');
        }

        if (strpos($values['name'], ' ') !== false) {
            throw new \RuntimeException(sprintf('Name "%s" has a space within', $values['name']));
        }

        $this->name = $values['name'];

        if (isset($values['inList'])) {
            $this->inList = (bool) $values['inList'];

            // workaround for typical type using the annotation with ""
            if (strtolower($values['inList']) == "false") {
                $this->inList = false;
            }
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function isInList()
    {
        return $this->inList;
    }

}
