<?php
/**
 * This file contains only the GoogleCloudVisionTest class.
 */

namespace Wikisource\GoogleCloudVisionPHP\Tests;

use Exception;
use Wikisource\GoogleCloudVisionPHP\GoogleCloudVision;
use Wikisource\GoogleCloudVisionPHP\LimitExceededException;

/**
 * Test all aspects of the GoogleCloudVision.
 */
class GoogleCloudVisionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * The GCV object used by all the tests.
     *
     * @var GoogleCloudVision
     */
    protected $gcv;

    /**
     * The full filesystem path to the dog.jpg test image.
     *
     * @var string
     */
    protected $testImageDog;

    /**
     * The full filesystem path to the Munich_subway_station_Hasenbergl_2.JPG test image.
     *
     * @var string
     */
    protected $testImageMunich;

    /**
     * Set up a couple of test images and a GCV object to work with for all the tests.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->testImageDog = realpath(__DIR__.'/dog.jpg');
        $this->testImageMunich = realpath(__DIR__.'/Munich_subway_station_Hasenbergl_2.JPG');
        $this->gcv = new GoogleCloudVision();
    }

    /**
     * Get the API key from the config file in this directory. This is used by every test in the requires-key group.
     *
     * @return string The API key.
     * @throws Exception If the $key variable is not set in the config file.
     */
    protected function getApiKeyFromConfigFile()
    {
        $testConfigFile = __DIR__.'/config.php';
        require_once $testConfigFile;
        if (!isset($key)) {
            throw new Exception('Unable to load key from '.$testConfigFile.' (please set $key)');
        }
        return $key;
    }

    /**
     * Test that converting a test image to base65 results in the expected output.
     *
     * @return void
     */
    public function testConvertImgtoBased64()
    {
        $countbase64 = strlen($this->gcv->convertImgtoBased64($this->testImageDog));
        $this->assertEquals($countbase64, 30420);
    }

    /**
     * Test that the image can be set from a filename.
     *
     * @return void
     */
    public function testSetImageWithFile()
    {
        $request = $this->gcv->setImage($this->testImageDog);
        $this->assertNotNull($request['requests'][0]['image']['content']);
    }

    /**
     * Test that the image can be set with raw data.
     *
     * @return void
     */
    public function testSetRawImage()
    {
        $request = $this->gcv->setImage(file_get_contents($this->testImageDog), GoogleCloudVision::IMAGE_TYPE_RAW);
        $this->assertEquals(30420, strlen($request['requests'][0]['image']['content']));
    }

    /**
     * Test that the image can be set as a GCS URI.
     *
     * @return void
     */
    public function testSetImageWithGsc()
    {
        $request = $this->gcv->setImage('gs://bucket_name/object_name', GoogleCloudVision::IMAGE_TYPE_GCS);
        $this->assertNotNull($request['requests'][0]['image']['source']['gcsImageUri']);
    }

    /**
     * Test that adding a feature adds the correct type to the request parameters.
     *
     * @return void
     */
    public function testAddType()
    {
        $request = $this->gcv->addFeature("LABEL_DETECTION");
        $this->assertEquals($request['requests'][0]['features'][0]['type'], "LABEL_DETECTION");
    }

    /**
     * Test that setting the Image Context correctly sets the relevant request parameters.
     *
     * @return void
     */
    public function testSetImageContext()
    {
        $request = $this->gcv->setImageContext(array("languageHints" => array("th", "en")));
        $this->assertEquals($request['requests'][0]['imageContext']['languageHints'][0], "th");
        $this->assertEquals($request['requests'][0]['imageContext']['languageHints'][1], "en");
    }

    /**
     * Test that not setting the image contect to an array throws an exception.
     *
     * @expectedException Exception
     * @return void
     */
    public function testSetImageContextException()
    {
        $request = $this->gcv->setImageContext("dddd");
    }

    /**
     * Test that a request without setting the key throws an exception.
     *
     * @expectedException Exception
     * @return void
     */
    public function testRequestWithoutKey()
    {
        $this->gcv->setImage($this->testImageDog);
        $this->gcv->addFeature("LABEL_DETECTION", 1);
        $response = $this->gcv->request();
    }

    /**
     * Test that a request without data throws an exception.
     *
     * @expectedException Exception
     * @return void
     */
    public function testRequestWithoutData()
    {
        $this->gcv->setKey(getenv('GCV_KEY'));
        $response = $this->gcv->request();
    }

    /**
     * Test that the request can be sent.
     *
     * @group requires-key
     * @return void
     */
    public function testRequest()
    {
        $this->gcv->setKey($this->getApiKeyFromConfigFile());
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
     *
     * @return void
     */
    public function testImageSizeLimit()
    {
        // Test a small image.
        $this->assertEquals(22815, filesize($this->testImageDog));
        $this->gcv->setImage($this->testImageDog);
        $this->gcv->setImage(file_get_contents($this->testImageDog), GoogleCloudVision::IMAGE_TYPE_RAW);
    }

    /**
     * Test image size limits for large files when passed as a file name.
     *
     * @return void
     */
    public function testImageSizeLimitLargeFile()
    {
        // Test a large image by filename.
        $this->assertEquals(8413646, filesize($this->testImageMunich));
        $this->expectException(LimitExceededException::class);
        $this->gcv->setImage($this->testImageMunich);
    }

    /**
     * Test image size limits for large files when passed as raw data.
     *
     * @return void
     */
    public function testImageSizeLimitLargeRaw()
    {
        // Test a large image by raw data.
        $this->expectException(LimitExceededException::class);
        $this->gcv->setImage(file_get_contents($this->testImageMunich), GoogleCloudVision::IMAGE_TYPE_RAW);
    }

    /**
     * An image can be passed in as a URL.
     *
     * @return void
     */
    public function testImageFilenameCanBeUrl()
    {
        $url = 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/53/';
        $url .= 'Munich_subway_station_Hasenbergl_2.JPG/800px-Munich_subway_station_Hasenbergl_2.JPG';
        $this->gcv->setImage($url);
    }
}
