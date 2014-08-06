<?php

namespace TwentyFifth\Serializing;

interface MethodSerializable
	extends Serializable
{
	/**
	 * Returns a list of properties with getters as Callable
	 *
	 * @return Callable[]
	 */
	function getSerializeMethods();
}