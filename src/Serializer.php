<?php

namespace TwentyFifth\Serializing;

/**
 * Serializes the data for the web interface (f.e. rest) and also handles Object of AbstractModel types
 */
class Serializer
{
	/**
	 * @param mixed $data
	 * @param int   $steps
	 *
	 * @return array|null
	 */
	public static function serialize($data, $steps)
	{
		if ($steps < 0)
			return self::serializeBasic($data, $steps);

		if ($data instanceof Serializable) {
			return self::serializeSerializable($data, $steps);
		} else if (is_array($data)) {
			return self::serializeArray($data, $steps);
		} else {
			return self::serializeBasic($data, $steps);
		}
	}

	/**
	 * Returns all properties with its callable for a Serializable
	 *
	 * @param Serializable $serializable
	 * @return Callable[]
	 * @throws SerializingException
	 */
	public static function getSerializeMethods(Serializable $serializable)
	{
		if ($serializable instanceof AnnotationSerializable) {
			return self::getSerializeMethodsForAnnotationSerializeable($serializable);
		} elseif ($serializable instanceof MethodSerializable) {
			return self::getSerializeMethodsForMethodSerializeable($serializable);
		} else {
			throw new SerializingException(__CLASS__.' is not able to serialize '.get_class($serializable));
		}
	}

	/**
	 * @param Serializable $object
	 * @param int          $steps
	 * @return array
	 */
	protected static function serializeSerializable(Serializable $object, $steps)
	{
		$serialized = [];

		foreach (self::getSerializeMethods($object) as $property => $getter) {
			$value = $getter();
			$serialized[$property] = self::serialize($value, $steps - 1);
		}

		return $serialized;
	}

	/**
	 * @param AnnotationSerializable $serializable
	 * @return array
	 */
	protected static function getSerializeMethodsForAnnotationSerializeable(AnnotationSerializable $serializable)
	{
		$methods = array();

		$properties = AnnotationParser::getProperties($serializable);
		$ignores    = AnnotationParser::getIgnores($serializable);

		foreach ($properties as $property => $getter) {
			if (in_array($getter, $ignores))
				continue;

			$methods[$property] = array($serializable, $getter);
		}

		return $methods;
	}

	/**
	 * @param MethodSerializable $serializable
	 * @return array
	 */
	protected static function getSerializeMethodsForMethodSerializeable(MethodSerializable $serializable)
	{
		return $serializable->getSerializeMethods();
	}

	/**
	 * @param array $array
	 * @param int   $steps
	 *
	 * @return array
	 */
	protected static function serializeArray($array, $steps)
	{
		$serialized = array();
		// number of non-numeric indexes > 0?
		$serializeToList = !count(array_filter(array_keys($array), function($val){ return !is_numeric($val);}));

		foreach ($array as $key => $value) {
			if ($serializeToList) {
				$serialized[] = self::serialize($value, $steps);
			} else {
				$serialized[$key] = self::serialize($value, $steps);
			}
		}
		return $serialized;
	}

	/**
	 * @param mixed $data
	 * @param int   $steps
	 *
	 * @return mixed
	 */
	protected static function serializeBasic($data, $steps)
	{
		if ($data instanceof \DateTime) {
			return $data;
		}

		if (is_object($data) || is_array($data))
			return null;

		return $data;
	}

}
