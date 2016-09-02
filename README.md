Google Cloud Vision PHP
=======================

This project hosts the PHP library for the various RESTful based Google Cloud Vision API.
[Read about Google Cloud Vision API] (https://cloud.google.com/vision/)

[![Build Status](https://travis-ci.org/thangman22/google-cloud-vision-php.svg?branch=master)](https://travis-ci.org/thangman22/google-cloud-vision-php)

##Features
*   Support almost feature of Google Cloud Vision API (Version 1)
*   Auto encode images to based64

##how to get service key
[Google Cloud Vision API Document](https://cloud.google.com/vision/docs/getting-started)

##Requirements
*   PHP >= 5.4 with cURL extension

##Installation
Add this to your composer.json

```json
"require": {
        "thangman22/google-cloud-vision-php": "*"
    }
```

##Example
```php
use GoogleCloudVisionPHP\GoogleCloudVision;

$gcv = new GoogleCloudVision();

// Follow instruction from Google Cloud Vision Document
$gcv->setKey("[Key from Google]");

$gcv->setImage("[File path]");

// 1 is Max result
$gcv->addFeature("LABEL_DETECTION", 1);

$gcv->addFeatureUnspecified(1);
$gcv->addFeatureFaceDetection(1);
$gcv->addFeatureLandmarkDetection(1);
$gcv->addFeatureLogoDetection(1);
$gcv->addFeatureLabelDetection(1);
$gcv->addFeatureOCR(1);
$gcv->addFeatureSafeSeachDetection(1);
$gcv->addFeatureImageProperty(1);

//Optinal
$gcv->setImageContext(array("languageHints"=>array("th")));

$response = $gcv->request();

```

