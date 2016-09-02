<?php
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/config.php";

// Make sure config.php sets $key
if (empty($key)) {
    echo 'Please set <code>$key</code> in <code>examples/config.php</code>';
    exit(1);
}

use GoogleCloudVisionPHP\GoogleCloudVision;
?>

<h1>Face Detection</h1>
<img src="images/brady.png" alt="" height="200">
<?php
$gcv = new GoogleCloudVision();
$gcv->setKey($key);
$gcv->setImage("images/brady.png");
$gcv->addFeatureFaceDetection(1);
echo "<pre style='max-height:300px; overflow:scroll;'>";
var_dump($gcv->request());
echo "</pre>";
?>

<h1>Landmark Detection</h1>
<img src="images/eiffel-tower.jpg" alt="" height="200">
<?php
$gcv = new GoogleCloudVision();
$gcv->setKey($key);
$gcv->setImage("images/eiffel-tower.jpg");
$gcv->addFeatureLandmarkDetection(1);
echo "<pre style='max-height:300px; overflow:scroll;'>";
var_dump($gcv->request());
echo "</pre>";
?>

<h1>Logo Detection</h1>
<img src="images/facebook.png" alt="" height="200">
<?php
$gcv = new GoogleCloudVision();
$gcv->setKey($key);
$gcv->setImage("images/facebook.png");
$gcv->addFeatureLogoDetection(1);
echo "<pre style='max-height:300px; overflow:scroll;'>";
var_dump($gcv->request());
echo "</pre>";
?>

<h1>label detection</h1>
<img src="images/faulkner.jpg" alt="" height="200">
<?php
$gcv = new GoogleCloudVision();
$gcv->setKey($key);
$gcv->setImage("images/faulkner.jpg");
$gcv->addFeatureLabelDetection(1);
echo "<pre style='max-height:300px; overflow:scroll;'>";
var_dump($gcv->request());
echo "</pre>";
?>

<h1>OCR</h1>
<img src="images/NeutraText.gif" alt="" height="200">
<?php
$gcv = new GoogleCloudVision();
$gcv->setKey($key);
$gcv->setImage("images/NeutraText.gif");
$gcv->addFeatureOCR(1);
echo "<pre style='max-height:300px; overflow:scroll;'>";
var_dump($gcv->request());
echo "</pre>";
?>

<h1>OCR in Other language</h1>
<img src="images/thai.gif" alt="" height="200">
<?php
$gcv = new GoogleCloudVision();
$gcv->setKey($key);
$gcv->setImage("images/thai.gif");
$gcv->addFeatureOCR(1);
$gcv->setImageContext(array("languageHints"=>array("th")));
echo "<pre style='max-height:300px; overflow:scroll;'>";
var_dump($gcv->request());
echo "</pre>";
?>
