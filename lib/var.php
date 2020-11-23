<?

setlocale(LC_CTYPE,"ru_RU.CP1251");

set_error_handler(function($errno, $errstr) { return $errstr == 'Creating default object from empty value'; }, E_WARNING);

$_ENV = new stdClass();
$page = new stdClass();

$_ENV->startT = microtime(true);

////////////////////////////////////////////////////////////////////////////////////////////////

function getMobDetect($ua = null) {
  require_once("/var/www/libs/vendor/autoload.php");

  $ua = isset($ua) ? $ua : $_SERVER['HTTP_USER_AGENT'];
  $id = md5($ua);

  static $cache;
  if(isset($cache[$id])) return $cache[$id];

  $detect = new Mobile_Detect(null, $ua);
  $b = new StdClass();
  $b->isMobileDevice = $detect->isMobile();
  $b->isTablet = $detect->isTablet();

  $cache[$id] = $b;

  return $b;
}

function ua_contains_mobile() {
  return stripos($_SERVER['HTTP_USER_AGENT'], "mobile") !== false;
}

function isMobile($ua = null) {
  $b = getMobDetect($ua);
  return (($b->isMobileDevice || ua_contains_mobile()) && !$b->isTablet);
}
function isTablet($ua = null) {
  $b = getMobDetect($ua);
  return (boolean)$b->isTablet;
}
function isIE($ua = null) {
  $ua = isset($ua) ? $ua : $_SERVER['HTTP_USER_AGENT'];
  if(preg_match('/MSIE (\w+\.\w+);/', $ua, $ms)) {
    return $ms[1];
  }
  return false;
}
$_ENV->isMobile = isMobile();

////////////////////////////////////////////////////////////////////////////////////////////////

function isCorrectID($t_id) { if(!$t_id) return false;  return preg_match("`[^a-zA-Z0-9_\.\-]`", $t_id) ? false : true; }

////////////////////////////////////////////////////////////////////////////////////////////////

$_self = str_replace("index.php","",$_SERVER['PHP_SELF']);

////////////////////////////////////////////////////////////////////////////////////////////////

?>