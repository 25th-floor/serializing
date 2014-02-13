<?php

namespace TwentyFifth\Serializing\TestModel;

use TwentyFifth\Serializing\MethodSerializable;

class TestMethodSerializeable
	implements MethodSerializable
{

	private $foo;
	private $bar;

	function __construct($bar, $foo)
	{
		$this->bar = $bar;
		$this->foo = $foo;
	}

	public function getSerializeMethods()
	{
		$that = $this;
		return array(
			'foo' => array($this, 'getFoo'),
			'bar' => function() use ($that) { return $that->bar; }
		);
	}

	public function getFoo()
	{
		return $this->foo;
	}
}