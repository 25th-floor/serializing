<?php

namespace TwentyFifth\Serializing\TestModel;
use TwentyFifth\Serializing\Annotations\AnnotationSerializable;

/**
 * @method int getId()
 * @method string getName()
 * @method TestModel getModel()
 */
class TestModel
	implements AnnotationSerializable
{
	private $data;

	public function __construct($data)
	{
		$this->data = $data;
	}

	public function __call($method, $parameters)
	{
		switch ($method) {
			case 'getId':
				return $this->data['id'];
			case 'getName':
				return $this->data['name'];
			case 'getModel':
				return $this->data['model'];
			default:
				throw new \Exception("Test method not found");
		}
	}
}
