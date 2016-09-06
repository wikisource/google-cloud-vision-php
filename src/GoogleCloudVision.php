<?php

namespace Wikisource\GoogleCloudVisionPHP;

use Exception;

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
    protected $imageMaxSize = 1024 * 1024 * 4;

    /** @var string Image type: Google Cloud Storage URI. Note the typo. */
    const IMAGE_TYPE_GCS = 'GSC';

    /** @var string Image type: file path or URL. */
    const IMAGE_TYPE_FILE = 'FILE';

    /** @var string Image type: raw data. */
    const IMAGE_TYPE_RAW = 'RAW';

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
     * This defaults to 4 MB as per the Google Clound Vision API limits documentation.
     *
     * @param ing $newSize
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
        $size = filesize($path);
        if ($size > $this->imageMaxSize) {
            $msg = "Image size of $path ($size) exceeds permitted size ($this->imageMaxSize)";
            throw new LimitExceededException($msg, 2);
        }
        $data = file_get_contents($path);
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

    public function addFeatureUnspecified($maxResults = 1)
    {
        return $this->addFeature("TYPE_UNSPECIFIED", $maxResults);
    }

    public function addFeatureFaceDetection($maxResults = 1)
    {
        return $this->addFeature("FACE_DETECTION", $maxResults);
    }

    public function addFeatureLandmarkDetection($maxResults = 1)
    {
        return $this->addFeature("LANDMARK_DETECTION", $maxResults);
    }

    public function addFeatureLogoDetection($maxResults = 1)
    {
        return $this->addFeature("LOGO_DETECTION", $maxResults);
    }

    public function addFeatureLabelDetection($maxResults = 1)
    {
        return $this->addFeature("LABEL_DETECTION", $maxResults);
    }

    public function addFeatureOCR($maxResults = 1)
    {
        return $this->addFeature("TEXT_DETECTION", $maxResults);
    }

    public function addFeatureSafeSeachDetection($maxResults = 1)
    {
        return $this->addFeature("SAFE_SEARCH_DETECTION", $maxResults);
    }

    public function addFeatureImageProperty($maxResults = 1)
    {
        return $this->addFeature("IMAGE_PROPERTIES", $maxResults);
    }

    /**
     * Send the request to Google and get the results.
     *
     * @param string $apiMethod Which API method to use. Currently can only be 'annotate'.
     * @return string[] The results of the request.
     * @throws Exception
     */
    public function request($apiMethod = "annotate")
    {
        if (empty($this->key)) {
            $msg = "API Key is empty, please grant from https://console.cloud.google.com/apis/credentials";
            throw new Exception($msg);
        }

        if (empty($this->features)) {
            throw new Exception("Features is can't empty.", 1);
        }

        if (empty($this->image)) {
            throw new Exception("Images is can't empty.", 1);
        }

        $url = $this->endpoint . $this->version . "/images:$apiMethod?key=" . $this->key;
        return $this->requestServer($url, $this->requestBody);
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    protected function requestServer($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $res = json_decode(curl_exec($ch), true);
        return $res;
    }
}
