<?

ob_start();  // to prevent error-output on prod; look for ob_end_clean

// DEV ////////////////////////////////////////////////////////////////////////////////////////////////

define("_dev", CFG::init()->dev('dev') ? true : false);

// ERROR HANDLING ////////////////////////////////////////////////////////////////////////////////////////////////

if(defined('_dev') && _dev===true) {
  ini_set('display_errors', 'On');
  error_reporting(E_ALL & ~E_NOTICE);
  function localErrorHandler($errno, $errstr, $errfile, $errline) {
    $show = true;
        if(stripos($errstr, "Undefined index"   )!==false) $show = false;
    elseif(stripos($errstr, "Undefined offset"  )!==false) $show = false;
    elseif(stripos($errstr, "Undefined property")!==false) $show = false;
    elseif(stripos($errstr, "Only variables should be passed by reference")!==false) $show = false;
    if($show) {
      if(php_sapi_name()=='cli') echo "[$errno] $errstr: $errfile, $errline\n";
      elseif($_ENV->is_json) echo "[$errno] $errstr: $errfile, $errline\n";
      else {
        echo "<div style='text-align:left; font:normal 11px/13px lucida console,monaco,monospace; background:#f4f4f4; color:#000;'>[$errno] <b>$errstr</b>: $errfile, $errline</div>";
        //print_pre(debug_backtrace());
      }
    }
    return !$show;
  }
  set_error_handler("localErrorHandler");
}
else {
  error_reporting(0);
}

// SETTINGS ////////////////////////////////////////////////////////////////////////////////////////////////

setlocale(LC_ALL, 'ru_RU.UTF-8', 'ru_RU.utf8', 'Rus');

session_set_cookie_params(30*24*60*60, "/", null, false, true);

session_start();

// ENV ////////////////////////////////////////////////////////////////////////////////////////////////

$_ENV = (object)[];

$_ENV->startT = microtime(true);
$_ENV->dev = _dev===true ? true : false;
$_ENV->http = true || $_SERVER['HTTPS'] || $_SERVER['X-HTTPS'] || $_SERVER['HTTP_X_HTTPS'] || $_SERVER['REQUEST_SCHEME']=="https" ? "https" : "http";
$_ENV->host = $_SERVER['HTTP_HOST'];
$_ENV->httphost = "$_ENV->http://$_ENV->host";
$_ENV->ip = $_SERVER['HTTP_X_REAL_IP'] ?: $_SERVER['REMOTE_ADDR'];
$_ENV->path2includes = $_SERVER['DOCUMENT_ROOT'] . "/../includes/";

// defaults:
$_ENV->is_ajax  = false;
$_ENV->is_api   = false;

include_once($_ENV->path2includes."ver.php");

// FORMAT POST ////////////////////////////////////////////////////////////////////////////////////////////////

// reformatting POST data (if name-value or object is posted)
if($_SERVER['CONTENT_TYPE']=="application/json" && !$_POST) {
  header('Content-Type: application/json; charset=utf-8');
  
  $_ENV->posted_json = json_decode(file_get_contents('php://input'));
  
  if(is_array($_ENV->posted_json)) {
    foreach($_ENV->posted_json as $var) {
      if(!is_object($var) || !$var->name) continue;
      $_POST[$var->name] = $var->value;
    }
  }
  elseif(is_object($_ENV->posted_json)) {
    foreach($_ENV->posted_json as $k=>$v) {
      $_POST[$k] = $v;
    }
  }
}

// AWS ////////////////////////////////////////////////////////////////////////////////////////////////

// if we are in AWS environment
if(CFG::init()->aws('AWS_ACCESS_KEY_ID')) {
  $_SERVER['AWS_ACCESS_KEY_ID']     = CFG::init()->aws('AWS_ACCESS_KEY_ID');
  $_SERVER['AWS_SECRET_ACCESS_KEY'] = CFG::init()->aws('AWS_SECRET_ACCESS_KEY');
  putenv("AWS_ACCESS_KEY_ID="     . CFG::init()->aws('AWS_ACCESS_KEY_ID'));
  putenv("AWS_SECRET_ACCESS_KEY=" . CFG::init()->aws('AWS_SECRET_ACCESS_KEY'));
}

////////////////////////////////////////////////////////////////////////////////////////////////
