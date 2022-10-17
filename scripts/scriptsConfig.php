<?php
use Sil\PhpEnv\Env; // https://github.com/silinternational/php-env#class-env-summary-of-functions

$rootPath = dirname(__DIR__);

define("TestPath", $rootPath . "/test/");
define("APPPATH", $rootPath . "/html/");

require_once APPPATH . "vendor/autoload.php";

define("DATABASE", Env::requireEnv("DATABASE"));
define("MONGODB_CONN", Env::requireEnv("MONGODB_CONN"));
define("BCRYPT_COST", 7);
