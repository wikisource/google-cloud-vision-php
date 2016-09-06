<?php
/**
 * This file contains only the LimitExceededException class.
 */

namespace Wikisource\GoogleCloudVisionPHP;

/**
 * An exception to be used whenever any API limits are exceeded.
 * https://cloud.google.com/vision/limits
 */
class LimitExceededException extends \Exception
{
}
