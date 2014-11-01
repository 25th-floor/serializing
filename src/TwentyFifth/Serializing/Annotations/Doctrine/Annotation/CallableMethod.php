<?php

namespace TwentyFifth\Serializing\Annotations\Doctrine\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * @package TwentyFifth\Serializing\Annotation
 */
final class CallableMethod
{

    /** @var string */
    private $name;

    /** @var bool */
    private $serializable = false;

    function __construct($values)
    {
        if (!isset($values['name'])) {
            throw new \RuntimeException('Name is missing!');
        }

        if (strpos($values['name'], ' ') !== false) {
            throw new \RuntimeException(sprintf('Name "%s" has a space within', $values['name']));
        }

        $this->name = $values['name'];

        if (strpos(strtolower($this->name), 'get') === 0 || strpos(strtolower($this->name), 'is') === 0) {
            $this->serializable = true;
        } else {
            // only getter are allowed to be serialized!
            return;
        }

        if (isset($values['serializable'])) {
            $this->serializable = (bool) $values['serializable'];

            // workaround for typical type using the annotation with ""
            if (strtolower($values['serializable']) == "false") {
                $this->serializable = false;
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
    public function isSerializable()
    {
        return $this->serializable;
    }

}
