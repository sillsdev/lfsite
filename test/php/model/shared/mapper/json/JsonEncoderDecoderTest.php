<?php

use Api\Model\Shared\Mapper\ArrayOf;
use Api\Model\Shared\Mapper\MapOf;
use Api\Model\Shared\Mapper\ObjectForEncoding;
use Api\Model\Shared\Mapper\JsonDecoder;
use Api\Model\Shared\Mapper\JsonEncoder;
use PHPUnit\Framework\TestCase;

class PropertyObject extends ObjectForEncoding
{
    public function __construct()
    {
        $this->setPrivateProp("shouldBePrivate");
        $this->setReadOnlyProp("shouldBeReadOnly");
    }

    public $name;

    public $shouldBeReadOnly;

    public $shouldBePrivate;
}

class PropertyObjectInArray
{
    public function __construct()
    {
        $this->data = new ArrayOf(function () {
            return new PropertyObject();
        });
    }

    public $name;

    /** @var ArrayOf<PropertyObject> */
    public $data;
}

class PropertyObjectInArray2
{
    public function __construct()
    {
        $this->data2 = new ArrayOf(function () {
            return new PropertyObjectInArray();
        });
    }

    public $name;

    /** @var ArrayOf<PropertyObjectInArray> */
    public $data2;
}

class PropertyObjectInMap
{
    public function __construct()
    {
        $this->data = new MapOf(function () {
            return new PropertyObject();
        });
    }

    public $name;

    /** @var MapOf<PropertyObject> */
    public $data;
}

class PropertyObjectInMap2
{
    public function __construct()
    {
        $this->data2 = new MapOf(function () {
            return new PropertyObjectInArray();
        });
    }

    public $name;

    /** @var MapOf<PropertyObjectInArray> */
    public $data2;
}

class JsonEncoderDecoderTest extends TestCase
{
    public function testEncode_PrivateProperties_NotVisible()
    {
        $object = new PropertyObject();
        $object->name = "can change name";
        $object->shouldBePrivate = "this is private";

        $params = json_decode(json_encode(JsonEncoder::encode($object)), true);

        $this->assertArrayHasKey("name", $params);
        $this->assertArrayNotHasKey("shouldBePrivate", $params);
        $this->assertEquals($object->name, $params["name"]);

        $params["name"] = "different name";
        $params["shouldBePrivate"] = "hacked";

        JsonDecoder::decode($object, $params);

        $this->assertEquals("different name", $object->name);
        $this->assertEquals("this is private", $object->shouldBePrivate);
    }

    public function testDecode_ReadOnlyProperties_PropertiesNotChanged()
    {
        $object = new PropertyObject();
        $object->name = "can change name";
        $object->shouldBeReadOnly = "cannot change this";

        $params = json_decode(json_encode(JsonEncoder::encode($object)), true);

        $this->assertArrayHasKey("name", $params);
        $this->assertArrayHasKey("shouldBeReadOnly", $params);
        $this->assertEquals($object->name, $params["name"]);
        $this->assertEquals($object->shouldBeReadOnly, $params["shouldBeReadOnly"]);

        $params["name"] = "different name";
        $params["shouldBeReadOnly"] = "changed";

        JsonDecoder::decode($object, $params);

        $this->assertEquals("different name", $object->name);
        $this->assertEquals("cannot change this", $object->shouldBeReadOnly);
    }

    public function testDecode_ReadOnlyPropertiesInArray_PropertiesNotChanged()
    {
        $objectData = new PropertyObject();
        $objectData->shouldBeReadOnly = "cannot change this";

        $object = new PropertyObjectInArray();
        $object->name = "can change name";
        $object->data[] = $objectData;

        $params = json_decode(json_encode(JsonEncoder::encode($object)), true);

        $this->assertArrayHasKey("name", $params);
        $this->assertArrayHasKey("shouldBeReadOnly", $params["data"][0]);
        $this->assertEquals($object->data[0]->shouldBeReadOnly, $params["data"][0]["shouldBeReadOnly"]);

        $params["name"] = "different name";
        $params["data"][0]["shouldBeReadOnly"] = "changed";

        JsonDecoder::decode($object, $params);

        $this->assertEquals("different name", $object->name);
        $this->assertEquals("cannot change this", $object->data[0]->shouldBeReadOnly);
    }

    public function testDecode_ReadOnlyPropertiesInTwoArrays_PropertiesNotChanged()
    {
        $objectData = new PropertyObject();
        $objectData->name = "can change name";
        $objectData->shouldBeReadOnly = "cannot change this";

        $object1 = new PropertyObjectInArray();
        $object1->name = "can change name";
        $object1->data[] = $objectData;

        $object = new PropertyObjectInArray2();
        $object->name = "can change name";
        $object->data2[] = $object1;

        $params = json_decode(json_encode(JsonEncoder::encode($object)), true);

        $this->assertArrayHasKey("name", $params);
        $this->assertArrayHasKey("shouldBeReadOnly", $params["data2"][0]["data"][0]);
        $this->assertEquals(
            $object->data2[0]->data[0]->shouldBeReadOnly,
            $params["data2"][0]["data"][0]["shouldBeReadOnly"]
        );

        $params["name"] = "different name2";
        $params["data2"][0]["name"] = "different name1";
        $params["data2"][0]["data"][0]["name"] = "different name";
        $params["data2"][0]["data"][0]["shouldBeReadOnly"] = "changed";

        JsonDecoder::decode($object, $params);

        $this->assertEquals("different name2", $object->name);
        $this->assertEquals("different name1", $object->data2[0]->name);
        $this->assertEquals("different name", $object->data2[0]->data[0]->name);
        $this->assertEquals("cannot change this", $object->data2[0]->data[0]->shouldBeReadOnly);
    }

    public function testDecode_ReadOnlyPropertiesInMap_PropertiesNotChanged()
    {
        $objectData = new PropertyObject();
        $objectData->shouldBeReadOnly = "cannot change this";

        $key = "key1";
        $object = new PropertyObjectInMap();
        $object->name = "can change name";
        $object->data[$key] = $objectData;

        $params = json_decode(json_encode(JsonEncoder::encode($object)), true);

        $this->assertArrayHasKey("name", $params);
        $this->assertArrayHasKey("shouldBeReadOnly", $params["data"][$key]);
        $this->assertEquals($object->data[$key]->shouldBeReadOnly, $params["data"][$key]["shouldBeReadOnly"]);

        $params["name"] = "different name";
        $params["data"][$key]["shouldBeReadOnly"] = "changed";

        JsonDecoder::decode($object, $params);

        $this->assertEquals("different name", $object->name);
        $this->assertEquals("cannot change this", $object->data[$key]->shouldBeReadOnly);
    }

    public function testDecode_ReadOnlyPropertiesInTwoMaps_PropertiesNotChanged()
    {
        $objectData = new PropertyObject();
        $objectData->name = "can change name";
        $objectData->shouldBeReadOnly = "cannot change this";

        $key = "key1";
        $object1 = new PropertyObjectInMap();
        $object1->name = "can change name";
        $object1->data[$key] = $objectData;

        $object = new PropertyObjectInMap2();
        $object->name = "can change name";
        $object->data2[$key] = $object1;

        $params = json_decode(json_encode(JsonEncoder::encode($object)), true);

        $this->assertArrayHasKey("name", $params);
        $this->assertArrayHasKey("shouldBeReadOnly", $params["data2"][$key]["data"][$key]);
        $this->assertEquals(
            $object->data2[$key]->data[$key]->shouldBeReadOnly,
            $params["data2"][$key]["data"][$key]["shouldBeReadOnly"]
        );

        $params["name"] = "different name2";
        $params["data2"][$key]["name"] = "different name1";
        $params["data2"][$key]["data"][$key]["name"] = "different name";
        $params["data2"][$key]["data"][$key]["shouldBeReadOnly"] = "changed";

        JsonDecoder::decode($object, $params);

        $this->assertEquals("different name2", $object->name);
        $this->assertEquals("different name1", $object->data2[$key]->name);
        $this->assertEquals("different name", $object->data2[$key]->data[$key]->name);
        $this->assertEquals("cannot change this", $object->data2[$key]->data[$key]->shouldBeReadOnly);
    }
}
