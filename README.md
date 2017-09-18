Google Cloud Vision PHP
=======================

This is a simple PHP interface to the [Google Cloud Vision API](https://cloud.google.com/vision/).

[![Build Status](https://travis-ci.org/wikisource/google-cloud-vision-php.svg?branch=master)](https://travis-ci.org/wikisource/google-cloud-vision-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wikisource/google-cloud-vision-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/wikisource/google-cloud-vision-php/?branch=master)

Features:

* Supports almost all features of the Cloud Vision API (version 1).
* Loads images from files, URLs, raw data, or Google Cloud Storage.

## Installation

Requirements:

* PHP ≥ 5.6
* API key (see Google's [Getting Started](https://cloud.google.com/vision/docs/getting-started) documentation)

To install, first add this to your `composer.json`:

```json
    "require": {
        "wikisource/google-cloud-vision-php": "^1.2"
    }
```

...and run `composer update`.

## Usage

```php
use GoogleCloudVisionPHP\GoogleCloudVision;

$gcv = new GoogleCloudVision();

// Get your API key from the Google Cloud Platform site.
$gcv->setKey("[Key from Google]");

// An image can be set from either a filename or URL (the default), raw data, or a Google Cloud Storage item:
$gcv->setImage("local/filesystem/file.png");
$gcv->setImage("https://example.org/url/to/file.png");
$gcv->setImage(file_get_contents('local/file.png'), GoogleCloudVision::IMAGE_TYPE_RAW);
$gcv->setImage("gs://bucket_name/object_name", GoogleCloudVision::IMAGE_TYPE_GCS);

// Set which features you want to retrieve:
$gcv->addFeatureUnspecified(1);
$gcv->addFeatureFaceDetection(1);
$gcv->addFeatureLandmarkDetection(1);
$gcv->addFeatureLogoDetection(1);
$gcv->addFeatureLabelDetection(1);
$gcv->addFeatureTextDetection(1);
$gcv->addFeatureDocumentTextDetection(1);
$gcv->addFeatureSafeSeachDetection(1);
$gcv->addFeatureImageProperty(1);

// Optional. The API will try to guess the language if you don't set this.
$gcv->setImageContext(['languageHints' => ['th']]);

$response = $gcv->request();
```

## Kudos

This is a fork of [thangman22's original library](https://github.com/thangman22/google-cloud-vision-php), and all credit goes to them.

Test images are from:

1. `Munich_subway_station_Hasenbergl_2.JPG` by Martin Falbisoner [CC BY-SA 4.0](http://creativecommons.org/licenses/by-sa/4.0)
   via [Wikimedia Commons](https://commons.wikimedia.org/wiki/File%3AMunich_subway_station_Hasenbergl_2.JPG)
