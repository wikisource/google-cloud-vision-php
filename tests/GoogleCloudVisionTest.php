<?php

namespace Wikisource\GoogleCloudVisionPHP\Tests;

use Wikisource\GoogleCloudVisionPHP\GoogleCloudVision;
use Wikisource\GoogleCloudVisionPHP\LimitExceededException;

class GoogleCloudVisionTest extends \PHPUnit_Framework_TestCase
{

    /** @var GoogleCloudVision */
    protected $gcv;

    /** @var string The full filesystem path to the dog.jpg test image. */
    protected $testImageDog;

    /** @var string The full filesystem path to the Munich_subway_station_Hasenbergl_2.JPG test image. */
    protected $testImageMunich;

    protected function setUp()
    {
        $this->testImageDog = realpath(__DIR__ . '/dog.jpg');
        $this->testImageMunich = realpath(__DIR__ . '/Munich_subway_station_Hasenbergl_2.JPG');
        $this->gcv = new GoogleCloudVision();
    }

    public function testConvertImgtoBased64()
    {
        $countbase64 = strlen($this->gcv->convertImgtoBased64($this->testImageDog));
        $this->assertEquals($countbase64, 30420);
    }

    public function testSetImageWithFile()
    {
        $request = $this->gcv->setImage($this->testImageDog);
        $this->assertNotNull($request['requests'][0]['image']['content']);
    }

    public function testSetRawImage()
    {
        $request = $this->gcv->setImage(file_get_contents($this->testImageDog), GoogleCloudVision::IMAGE_TYPE_RAW);
        $this->assertEquals(30420, strlen($request['requests'][0]['image']['content']));
    }

    public function testSetImageWithGsc()
    {
        $request = $this->gcv->setImage($this->testImageDog, GoogleCloudVision::IMAGE_TYPE_GCS);
        $this->assertNotNull($request['requests'][0]['image']['source']['gcsImageUri']);
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
        $this->gcv->setImage($this->testImageDog);
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
        $this->gcv->setImage($this->testImageDog);
        $this->gcv->addFeature("LABEL_DETECTION", 1);

        $response = $this->gcv->request();
        $this->assertNotNull($response['responses']);
    }

    /**
     * The Vision API limits image sizes to 4 MB: https://cloud.google.com/vision/limits
     * so this library shouldn't permit larger requests.
     * There are four tests for this, 2 sizes of file multiplied by 2 ways of passing the file to the GCV class.
     * Only the large ones need to be in their own test method,
     * becuase it's only possible to test for a single Exception at a time.
     */
    public function testImageSizeLimit()
    {
        // Test a small image.
        $this->assertEquals(22815, filesize($this->testImageDog));
        $this->gcv->setImage($this->testImageDog);
        $this->gcv->setImage(file_get_contents($this->testImageDog), GoogleCloudVision::IMAGE_TYPE_RAW);
    }

    public function testImageSizeLimitLargeFile()
    {
        // Test a large image by filename.
        $this->assertEquals(8413646, filesize($this->testImageMunich));
        $this->expectException(LimitExceededException::class);
        $this->gcv->setImage($this->testImageMunich);
    }

    public function testImageSizeLimitLargeRaw()
    {
        // Test a large image by raw data.
        $this->expectException(LimitExceededException::class);
        $this->gcv->setImage(file_get_contents($this->testImageMunich), GoogleCloudVision::IMAGE_TYPE_RAW);
    }
}
