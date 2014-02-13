<?php

namespace TwentyFifth\Serializing\TestModel;

class IteratorAggregateImpl
	implements \IteratorAggregate
{
	/**
	 * @var \Iterator
	 */
	private $iterator;

	function __construct($iterator)
	{
		$this->iterator = $iterator;
	}

	public function getIterator()
	{
		return $this->iterator;
	}
} 