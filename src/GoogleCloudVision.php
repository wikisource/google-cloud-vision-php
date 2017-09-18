<?php
/**
 * This file contains only the GoogleCloudVision class.
 */

namespace Wikisource\GoogleCloudVisionPHP;

use Exception;
use GuzzleHttp\Client;

class GoogleCloudVision
{

    protected $features = array();
    protected $imageContext = array();
    protected $image = array();
    protected $requestBody = array();
    protected $version = "v1";
    protected $endpoint = "https://vision.googleapis.com/";
    protected $key;

    /** @var int The maximum size allowed for the image, in bytes. */
    protected $imageMaxSize;

    /** @var string Image type: Google Cloud Storage URI. Note the typo. */
    const IMAGE_TYPE_GCS = 'GSC';

    /** @var string Image type: file path or URL. */
    const IMAGE_TYPE_FILE = 'FILE';

    /** @var string Image type: raw data. */
    const IMAGE_TYPE_RAW = 'RAW';

    /**
     * Create a new GCV API object.
     */
    public function __construct()
    {
        $this->imageMaxSize = 1024 * 1024 * 4;
    }

    /**
     * Change the URL for the API endpoint. Defaults to https://vision.googleapis.com/ but may need to be changed for
     * various reasons (e.g. if routing through a proxy server).
     *
     * @param string $newEndpoint The new URL of the API endpoint.
     */
    public function setEndpoint($newEndpoint)
    {
        $this->endpoint = $newEndpoint;
    }

    /**
     * Set the permitted maximum size of images.
     * This defaults to 4 MB as per the Google Cloud Vision API limits documentation.
     *
     * @param int $newSize
     * @throws Exception
     */
    public function setImageMaxSize($newSize)
    {
        if (!is_int($newSize)) {
            throw new Exception("Image size must be specified in integer bytes, '$newSize' given");
        }
        $this->imageMaxSize = $newSize;
    }

    /**
     * Set the image that will be sent to the API.
     *
     * An image can be set from a filename or URL, raw data, or a Google Cloud Storage item.
     *
     * A Google Cloud Storage image URI must be in the following form: gs://bucket_name/object_name.
     * Object versioning is not supported.
     * Read more: https://cloud.google.com/vision/reference/rest/v1/images/annotate#imagesource
     *
     * @param mixed $input The filename, URL, data, etc.
     * @param string $type The type that $input should be treated as.
     * @return string[] The request body.
     * @throws LimitExceededException When the image size is over the maximum permitted.
     */
    public function setImage($input, $type = self::IMAGE_TYPE_FILE)
    {
        if ($type === self::IMAGE_TYPE_GCS) {
            $this->image['source']['gcsImageUri'] = $input;
        } elseif ($type === self::IMAGE_TYPE_FILE) {
            $this->image['content'] = $this->convertImgtoBased64($input);
        } elseif ($type === self::IMAGE_TYPE_RAW) {
            $size = strlen($input);
            if ($size > $this->imageMaxSize) {
                throw new LimitExceededException("Image size ($size) exceeds permitted size ($this->imageMaxSize)", 1);
            }
            $this->image['content'] = base64_encode($input);
        }
        return $this->setRequestBody();
    }

    /**
     * Fetch base64-encoded data of the specified image.
     *
     * @param string $path Path to the image file. Anything supported by file_get_contents is suitable.
     * @return string The encoded data as a string or FALSE on failure.
     * @throws LimitExceededException When the image size is over the maximum permitted.
     */
    public function convertImgtoBased64($path)
    {
        // Get the data.
        $data = file_get_contents($path);
        // Check the size.
        $size = strlen($data);
        if ($size > $this->imageMaxSize) {
            $msg = "Image size of %s (%s) exceeds permitted size (%s)";
            throw new LimitExceededException(sprintf($msg, $path, $size, $this->imageMaxSize), 2);
        }
        // Return encoded data.
        return base64_encode($data);
    }

    /**
     * Set the request body, based on the image, features, and imageContext.
     *
     * @return string[]
     */
    protected function setRequestBody()
    {
        if (!empty($this->image)) {
            $this->requestBody['requests'][0]['image'] = $this->image;
        }
        if (!empty($this->features)) {
            $this->requestBody['requests'][0]['features'] = $this->features;
        }
        if (!empty($this->imageContext)) {
            $this->requestBody['requests'][0]['imageContext'] = $this->imageContext;
        }
        return $this->requestBody;
    }

    /**
     * Add a feature request.
     * @link https://cloud.google.com/vision/docs/reference/rest/v1/images/annotate#Type
     * @deprecated Use one of the explicit addFeature* methods instead.
     * @todo Make this protected visibility.
     * @param string $type One of the type values.
     * @param int $maxResults
     * @return string[]
     * @throws Exception
     */
    public function addFeature($type, $maxResults = 1)
    {

        if (!is_numeric($maxResults)) {
            throw new Exception("maxResults variable is not valid it should be Integer.", 1);
        }

        $this->features[] = array("type" => $type, "maxResults" => $maxResults);
        return $this->setRequestBody();
    }

    public function setImageContext($imageContext)
    {
        if (!is_array($imageContext)) {
            throw new Exception("imageContext variable is not valid it should be Array.", 1);
        }
        $this->imageContext = $imageContext;
        return $this->setRequestBody();
    }

    /**
     * Unspecified feature type.
     * @param int $maxResults
     * @return string[]
     */
    public function addFeatureUnspecified($maxResults = 1)
    {
        return $this->addFeature("TYPE_UNSPECIFIED", $maxResults);
    }

    /**
     * Run face detection.
     * @param int $maxResults
     * @return string[]
     */
    public function addFeatureFaceDetection($maxResults = 1)
    {
        return $this->addFeature("FACE_DETECTION", $maxResults);
    }

    /**
     * Run landmark detection.
     * @param int $maxResults
     * @return string[]
     */
    public function addFeatureLandmarkDetection($maxResults = 1)
    {
        return $this->addFeature("LANDMARK_DETECTION", $maxResults);
    }

    /**
     * Run logo detection.
     * @param int $maxResults
     * @return string[]
     */
    public function addFeatureLogoDetection($maxResults = 1)
    {
        return $this->addFeature("LOGO_DETECTION", $maxResults);
    }

    /**
     * Run label detection.
     * @param int $maxResults
     * @return string[]
     */
    public function addFeatureLabelDetection($maxResults = 1)
    {
        return $this->addFeature("LABEL_DETECTION", $maxResults);
    }

    /**
     * Run OCR.
     * @param int $maxResults
     * @return string[]
     */
    public function addFeatureTextDetection($maxResults = 1)
    {
        return $this->addFeature("TEXT_DETECTION", $maxResults);
    }

    /**
     * Run OCR.
     * @deprecated Use self::addFeatureTextDetection() instead.
     * @param int $maxResults
     * @return string[]
     */
    public function addFeatureOCR($maxResults = 1)
    {
        return $this->addFeatureTextDetection($maxResults);
    }

    /**
     * Run dense text document OCR. Takes precedence when both DOCUMENT_TEXT_DETECTION and TEXT_DETECTION are present.
     * @param int $maxResults
     * @return string[]
     */
    public function addFeatureDocumentTextDetection($maxResults = 1)
    {
        return $this->addFeature("DOCUMENT_TEXT_DETECTION", $maxResults);
    }

    /**
     * Run computer vision models to compute image safe-search properties.
     * @param int $maxResults
     * @return string[]
     */
    public function addFeatureSafeSeachDetection($maxResults = 1)
    {
        return $this->addFeature("SAFE_SEARCH_DETECTION", $maxResults);
    }

    /**
     * Compute a set of image properties, such as the image's dominant colors.
     * @param int $maxResults
     * @return string[]
     */
    public function addFeatureImageProperty($maxResults = 1)
    {
        return $this->addFeature("IMAGE_PROPERTIES", $maxResults);
    }

    /**
     * Run crop hints.
     * @param int $maxResults
     * @return string[]
     */
    public function addFeatureCropHints($maxResults = 1)
    {
        return $this->addFeature("CROP_HINTS", $maxResults);
    }

    /**
     * Run web detection.
     * @param int $maxResults
     * @return string[]
     */
    public function addFeatureWebDetection($maxResults = 1)
    {
        return $this->addFeature("WEB_DETECTION", $maxResults);
    }

    /**
     * Send the request to Google and get the results.
     *
     * @param string $apiMethod Which API method to use. Currently can only be 'annotate'.
     *
     * @return string[] The results of the request.
     * @throws Exception If any of the key, features, or image have not been set yet.
     */
    public function request($apiMethod = "annotate")
    {
        if (empty($this->key) === true) {
            $msg = "API Key is empty, please grant from https://console.cloud.google.com/apis/credentials";
            throw new Exception($msg);
        }

        if (empty($this->features) === true) {
            throw new Exception("Features is can't empty.", 1);
        }

        if (empty($this->image) === true) {
            throw new Exception("Images is can't empty.", 1);
        }

        $url = $this->endpoint.$this->version."/images:$apiMethod?key=".$this->key;
        return $this->requestServer($url, $this->requestBody);
    }

    /**
     * Set the API key.
     *
     * @param string $key The API key.
     *
     * @return void
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Execute the request and return the result.
     *
     * @param string $url The full URL to query.
     * @param string[] $data The data to send.
     *
     * @return string[] The resulting information about the image.
     */
    protected function requestServer($url, $data)
    {
        $client = new Client();
        $result = $client->post($url, ['json' => $data]);
        return \GuzzleHttp\json_decode($result->getBody(), true);
    }
}
