<?php

namespace TwentyFifth\Serializing;

use TwentyFifth\Serializing\Annotations\Simple\SimpleAnnotationParser;
use TwentyFifth\Serializing\TestModel\TestAnnotatedModel;

class AnnotationParserTest
	extends \PHPUnit_Framework_TestCase
{
	public function testGetMethods()
	{
		$serializable = new TestAnnotatedModel();
		$expected = [
			'getIdWorks',
			'getNameWorks',
			'getModelWorks',
			'getWithNoParenthesisWorks',
			'spaceWorks',
			'setSetWorks',
			'getNonExistentTypeWorks',
			'withTypeHintWorks',
			'getDoublesWorks',
			'getDoublesWorks',
			'getDoublesWorks',
			'isIsWorks'
		];

		$result = SimpleAnnotationParser::getMethods($serializable);
		$this->assertSame($expected, $result);
	}

	public function testGetProperties()
	{
		$serializable = new TestAnnotatedModel();
		$expected = [
			'idWorks'                =>'getIdWorks',
			'nameWorks'              => 'getNameWorks',
			'modelWorks'             => 'getModelWorks',
			'withNoParenthesisWorks' => 'getWithNoParenthesisWorks',
			'nonExistentTypeWorks'   => 'getNonExistentTypeWorks',
			'doublesWorks'           => 'getDoublesWorks',
			'isWorks'                => 'isIsWorks'
		];

		$result = SimpleAnnotationParser::getProperties($serializable);
		$this->assertSame($expected, $result);
	}

	public function testGetIgnores()
	{
		$serializable = new TestAnnotatedModel();
		$expected = [
			'simpleWorks',
			'getDoublesWorks',
			'getDoublesWorks',
			'getthisalllowercaseWorks',
			'gETTHISALLUPPERCASEWORKS'
		];

		$result = SimpleAnnotationParser::getIgnores($serializable);
		$this->assertSame($expected, $result);
	}

}
