Google Cloud Vision PHP
=======================

This is a simple PHP interface to the [Google Cloud Vision API](https://cloud.google.com/vision/).

[![Build Status](https://travis-ci.org/wikisource/google-cloud-vision-php.svg?branch=master)](https://travis-ci.org/wikisource/google-cloud-vision-php)

Features:

* Supports almost all features of the Cloud Vision API (version 1).
* Loads images from files, URLs, raw data, or Google Cloud Storage.

## Installation

Requirements:

* PHP >= 5.6
* PHP cURL extension
* API key (see the [Getting Started](https://cloud.google.com/vision/docs/getting-started) documentation)

To install, first add this to your `composer.json`:

```json
    "require": {
        "wikisource/google-cloud-vision-php": "*"
    }
```

...and run `composer update`.

Then copy `config.php.dist` to `config.php` and add your API key to that file.

## Usage

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

## Kudos

This is a fork of [thangman22's original library](https://github.com/thangman22/google-cloud-vision-php), and all credit goes to them.
