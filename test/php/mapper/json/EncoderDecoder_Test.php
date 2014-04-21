<?php

use models\UserModel;

use models\mapper\JsonEncoder;
use models\mapper\JsonDecoder;

require_once(dirname(__FILE__) . '/../../TestConfig.php');
require_once(SimpleTestPath . 'autorun.php');


class PropertiesTest extends UserModel {
	public function __construct($id = '') {
		parent::__construct($id);
		$this->setPrivateProp('shouldBePrivate');
		$this->setReadOnlyProp('shouldBeReadOnly');
		$this->shouldBePrivate = 'default';
		$this->shouldBeReadOnly = 'default';
	}
	
	public $shouldBeReadOnly;
	
	public $shouldBePrivate;
	
}

class TestJsonEncoderDecoder extends UnitTestCase {

	function __construct() {
	}
	
	function testEncode_privateProperties_notVisible() {
		
		$test = new PropertiesTest();
		$test->name = 'test';
		$test->shouldBePrivate = 'this is private';
		
		$data = JsonEncoder::encode($test);
		
		$this->assertTrue(array_key_exists('name', $data));
		$this->assertFalse(array_key_exists('shouldBePrivate', $data));
		
		// change some data
		$data['name'] = 'different'; // this can be changed
		$data['shouldBePrivate'] = 'hacked'; // this prop is private and cannot be set
		
		$test2 = new PropertiesTest();
		JsonDecoder::decode($test2, $data);

		$this->assertEqual($test2->name, 'different');
		$this->assertEqual($test2->shouldBePrivate, 'default');
		
	}
	
	function testDecode_readOnlyProperties_propertiesNotChanged() {
		
		$test = new PropertiesTest();
		$test->name = 'test';
		$test->shouldBeReadOnly = 'cannot change this';
		
		$data = JsonEncoder::encode($test);
		
		$this->assertTrue(array_key_exists('name', $data));
		$this->assertTrue(array_key_exists('shouldBeReadOnly', $data));
		$this->assertEqual($test->shouldBeReadOnly, $data['shouldBeReadOnly']);
		
		// change some data
		$data['name'] = 'different'; // this can be changed
		$data['shouldBeReadOnly'] = 'changed'; // this prop is read-only
		
		$test2 = new PropertiesTest();
		JsonDecoder::decode($test2, $data);

		$this->assertEqual($test2->name, 'different');
		$this->assertEqual($test2->shouldBeReadOnly, 'default');
		
	}
}

?>