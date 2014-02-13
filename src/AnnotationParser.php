<?php

namespace TwentyFifth\Serializing;

/**
 * Parses the Annotation of an annotation serializable class and its super classes
 */
class AnnotationParser {
	const METHOD_REGEX = '#@method [\[\]a-zA-Z\\\\]+ ([a-zA-Z]+).*?\n#s';
	const PROPERTY_REGEX = '#@method [\[\]a-zA-Z\\\\]+ ((get|is)([a-zA-Z]+)).*?\n#s';
	const IGNORE_REGEX   = '#@noSerialize ([a-zA-Z]+)\n#s';

	/**
	 * @param AnnotationSerializable $object
	 *
	 * @return string[]
	 */
	public static function getMethods(AnnotationSerializable $object)
	{
		$methods = array();

		for ($class = new \ReflectionClass(get_class($object)); $class != null; $class = $class->getParentClass()) {
			preg_match_all(self::METHOD_REGEX, $class->getDocComment(), $matches);
			$methods = array_merge($methods, $matches[1]);
		}

		return $methods;
	}

	/**
	 * @param AnnotationSerializable $object
	 *
	 * @return string[]
	 */
	public static function getProperties(AnnotationSerializable $object)
	{
		$properties = array();

		for ($class = new \ReflectionClass(get_class($object)); $class != null; $class = $class->getParentClass()) {
			preg_match_all(self::PROPERTY_REGEX, $class->getDocComment(), $matches);
			foreach ($matches[3] as $index => $property) {
				$properties[lcfirst($property)] = $matches[1][$index];
			}
		}

		return $properties;
	}

	/**
	 * @param AnnotationSerializable $object
	 *
	 * @return string[]
	 */
	public static function getIgnores(AnnotationSerializable $object)
	{
		$ignores = array();

		for ($class = new \ReflectionClass(get_class($object)); $class != null; $class = $class->getParentClass()) {
			preg_match_all(self::IGNORE_REGEX, $class->getDocComment(), $matches);
			$ignores = array_merge($ignores, $matches[1]);
		}

		return array_map('lcfirst', $ignores);
	}
}
