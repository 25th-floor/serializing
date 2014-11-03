<?php

namespace TwentyFifth\Serializing;

use TwentyFifth\Serializing\Simple\SimpleAnnotationAdapter;
use TwentyFifth\Serializing\TestModel\IteratorAggregateImpl;
use TwentyFifth\Serializing\TestModel\TestInvalidSerializeable;
use TwentyFifth\Serializing\TestModel\TestMethodSerializeable;
use TwentyFifth\Serializing\TestModel\TestModel;

/**
 * Class ModelSerializerTest
 * @package MoreThanChecks\Test\Serializing
 */
class ModelSerializerTest
	extends \PHPUnit_Framework_TestCase
{
	/** @var Serializer */
	private $serializer;

	protected function setUp()
	{
		parent::setUp();

		$this->serializer = new Serializer();
		$this->serializer->setAdapter(new SimpleAnnotationAdapter());
	}

	/**
	 * @return Serializer
	 */
	protected function getSerializer()
	{
		return $this->serializer;
	}

	/**
	 * @dataProvider getTestData
	 */
	public function testSerializing($input, $steps, $expected)
	{
		$result = $this->getSerializer()->serialize($input, $steps);
		$this->assertSame($expected, $result);
	}

	/**
	 * @expectedException \TwentyFifth\Serializing\SerializingException
	 */
	public function testInvalidSerializing()
	{
		$this->getSerializer()->serialize(new TestInvalidSerializeable(), 1);
	}

	public function getTestData()
	{
		$empty_strings = array(
			'empty string, -1' => array('', -1, ''),
			'empty string, 0'  => array('', 0, ''),
			'empty string, 1'  => array('', 1, ''),
		);

		$dateTime = new \DateTime('1.1.2014 12:00');
		$expectedDateTime = (array) $dateTime;
		$expectedDateTime['date_iso8601'] = $dateTime->format(\DateTime::ISO8601);
		$expectedDateTime['date_rfc2822'] = $dateTime->format(\DateTime::RFC2822);

		$listArray = [1,2,3];
		unset($listArray[1]);

		$primitive_types = array(
			'string'        => array('FooBar', 1, 'FooBar'),
			'integer'       => array(55, 1, 55),
			'float'         => array(55.42, 1, 55.42),
			'boolean true'  => array(true, 1, true),
			'boolean false' => array(false, 1, false),
			'stdClass'      => array(new \StdClass(), 1, null),
			'DateTime'      => array($dateTime, 1, $expectedDateTime),
			'list array'    => [$listArray, 1, [1,3]],
		);

		$primitive_types_in_simple_array = array(
			'array of string'        => array(array('FooBar'), 1, array('FooBar')),
			'array of integer'       => array(array(55), 1, array(55)),
			'array of float'         => array(array(55.42), 1, array(55.42)),
			'array of boolean true'  => array(array(true), 1, array(true)),
			'array of boolean false' => array(array(false), 1, array(false)),
			'array of dateTime'      => array([$dateTime], 1, [$expectedDateTime]),
		);

		$array_with_no_steps_left = array(
			'array with no steps' => array(array(4212), -1, null),
			'array-array with no steps' => array(array(array(4212)), 0, array(array(4212))),
		);

		/** @var TestModel $m1 */
		$m1_data = array(
			'id' => 4212,
			'name' => 'FooBar',
			'model' => null,
		);
		$m1_expected = $m1_data;
		$m1 = $this->getModelMock($m1_data);

		/** @var TestModel $m2 */
		$m2_data = array(
			'id' => 55,
			'name' => 'XX',
			'model' => $m1,
		);
		$m2_expected = $m2_data;
		$m2_expected['model'] = $m1_data;
		$m2_nosteps_expected = $m2_data;
		$m2_nosteps_expected['model'] = null;
		$m2 = $this->getModelMock($m2_data);

		/** @var TestModel $m3 */
		$m3_data = array(
			'id' => 555,
			'name' => array('a' => 'b', 5 => array('c' => false)),
			'model' => null,
		);
		$m3_expected = $m3_data;
		$m3_nosteps_expected = $m3_data;
		$m3_nosteps_expected['name'] = null;
		$m3 = $this->getModelMock($m3_data);

		// test a model carrying another model carrying another model
		$m4_data = array(
			'id' => 70,
			'name' => 'testdrive',
			'model' => $m2,
		);

		$m4_expected_one_step = $m4_data;
		$m4_expected_two_steps = $m4_data;

		// define expected data for model key with only single step
		$m2_data_one_step = $m2_data;
		$m2_data_one_step['model'] = null;

		// define expected data for model key with two steps left
		$m2_data_two_steps = $m2_data;
		$m2_data_two_steps['model'] = $m1_data;

		$m4_expected_one_step['model'] = $m2_data_one_step;
		$m4_expected_two_steps['model'] = $m2_data_two_steps;
		$m4 = $this->getModelMock($m4_data);

		$entityModels = array(
			'plain model' => array($m1, 2, $m1_expected),
			'one model in another' => array($m2, 1, $m2_expected),
			'one model in another, no steps left' => array($m2, 0, $m2_nosteps_expected),
			'plain model with array' => array($m3, 2, $m3_expected),
			'one model in another in another 1 step' => array($m4, 1, $m4_expected_one_step),
			'one model in another in another 2 steps' => array($m4, 2, $m4_expected_two_steps),
			'one model in another in another 3 steps' => array($m4, 3, $m4_expected_two_steps),
			'plain model with negative steps' => array($m2, -1, null),
		);

		$entityModelCollections = array(
			'm1, m2 and m3' => array(array($m1, $m2, $m3), 1, array($m1_expected, $m2_expected, $m3_expected)),
			'm1, m2 and m3 no depth' => array(
				array($m1, $m2, $m3),
				0,
				array($m1_expected, $m2_nosteps_expected, $m3_nosteps_expected)
			),
			'm1, m2 and m3 with negative depth' => array(
				array($m1, $m2, $m3),
				-100,
				null
			),
		);

		$methodSerializeables = array(
			'method serializeable' => array(new TestMethodSerializeable('Bär', 'Foo'), 1, ['foo'=>'Foo', 'bar'=>'Bär']),
			'method serializeable no depth' => array(new TestMethodSerializeable('Bär', 'Foo'), -1, null),
		);

		$iterator = [
			'iterator non-associative' => [new \ArrayIterator([1,2,3]), 2, [1,2,3]],
			'iterator non-associative with gap' => [new \ArrayIterator($listArray), 2, [1,3]],
			'iterator associative' => [new \ArrayIterator(['f'=>1, 'g'=>2, 'h'=>3]), 2, ['f'=>1, 'g'=>2, 'h'=>3]],
		];

		$iteratorAggregate = array_map(function($iteratorTest) {
			return [new IteratorAggregateImpl($iteratorTest[0]), $iteratorTest[1], $iteratorTest[2]];
		}, $iterator);

		return array_merge(
			$empty_strings,
			$primitive_types,
			$primitive_types_in_simple_array,
			$array_with_no_steps_left,
			$entityModels,
			$entityModelCollections,
			$methodSerializeables,
			$iterator,
			$iteratorAggregate
		);
	}

	protected function getModelMock($data)
	{
		$mock = new TestModel($data);
		return $mock;
	}
}
