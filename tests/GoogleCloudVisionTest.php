<?php

namespace Wikisource\GoogleCloudVisionPHP\Tests;

use Wikisource\GoogleCloudVisionPHP\GoogleCloudVision;

class GoogleCloudVisionTest extends \PHPUnit_Framework_TestCase
{

    protected $gcv;
    protected $filePath;

    protected function setUp()
    {
        $this->filePath = realpath(__DIR__ . '/dog.jpg');
        $this->gcv = new GoogleCloudVision();
    }

    public function testConvertImgtoBased64()
    {
        $countbase64 = strlen($this->gcv->convertImgtoBased64($this->filePath));
        $this->assertEquals($countbase64, 30420);
    }

    public function testSetImageWithFile()
    {
        $request = $this->gcv->setImage($this->filePath);
        $this->assertNotNull($request['requests'][0]['image']['content']);
    }

    public function testSetRawImage()
    {
        $request = $this->gcv->setImage(file_get_contents($this->filePath), 'RAW');
        $this->assertEquals(30420, strlen($request['requests'][0]['image']['content']));
    }

    public function testSetImageWithGsc()
    {
        $request = $this->gcv->setImage($this->filePath, "GSC");
        $this->assertNotNull($request['requests'][0]['image']['source']['gcs_image_uri']);
    }

    public function testAddType()
    {
        $request = $this->gcv->addFeature("LABEL_DETECTION", 1);
        $this->assertEquals($request['requests'][0]['features'][0]['type'], "LABEL_DETECTION");
    }

    public function testSetImageContext()
    {
        $request = $this->gcv->setImageContext(array("languageHints" => array("th", "en")));
        $this->assertEquals($request['requests'][0]['imageContext']['languageHints'][0], "th");
    }

    /**
     * @expectedException Exception
     */
    public function testSetImageException()
    {
        $request = $this->gcv->addFeature("dddd", "dddd");
    }

    /**
     * @expectedException Exception
     */
    public function testSetImageContextException()
    {
        $request = $this->gcv->setImageContext("dddd");
    }

    /**
     * @expectedException Exception
     */
    public function testRequestWithoutKey()
    {
        $this->gcv->setImage($this->filePath);
        $this->gcv->addFeature("LABEL_DETECTION", 1);
        $response = $this->gcv->request();
    }

    /**
     * @expectedException Exception
     */
    public function testRequestWithoutData()
    {
        $this->gcv->setKey(getenv('GCV_KEY'));
        $response = $this->gcv->request();
    }

    /**
     * @group integration
     */
    public function testRequest()
    {

        $this->gcv->setKey(getenv('GCV_KEY'));
        $this->gcv->setImage($this->filePath);
        $this->gcv->addFeature("LABEL_DETECTION", 1);

        $response = $this->gcv->request();
        $this->assertNotNull($response['responses']);
    }
}
