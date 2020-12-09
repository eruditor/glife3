<?

setlocale(LC_CTYPE,"ru_RU.CP1251");

set_error_handler(function($errno, $errstr) { return $errstr == 'Creating default object from empty value'; }, E_WARNING);

if(!defined("_local")) define("_local", 0);

$_ENV = (object)[];

$page = new stdClass();
$page->z = '';  // main content block
$page->bread = [];  // breadcrumbs for <H1> and <title>

$_ENV->startT = microtime(true);

////////////////////////////////////////////////////////////////////////////////////////////////

$_ENV->isMobile = false;  // canvas is anyway bigger than mobile screen

////////////////////////////////////////////////////////////////////////////////////////////////

global $_self;
$_self = str_replace("index.php", "", $_SERVER['PHP_SELF']);

////////////////////////////////////////////////////////////////////////////////////////////////

include_once("ver.php");

////////////////////////////////////////////////////////////////////////////////////////////////

?>