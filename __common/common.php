<?

// DEBUG ////////////////////////////////////////////////////////////////////////////////////////////////

function print_var($var, $prms=[], $lvl=0) {
  if(_dev!==true && !in_array('release', $prms)) return "";
  $s = '';
      if(is_null($var))    { $s .= "<i style='color:#777;'>null</i>"; }
  elseif(is_bool($var))    { $s .= "<i style='color:#000;'>".($var?"true":"false")."</i>"; }
  elseif(is_numeric($var)) { $s .= "<span style='color:#119;'>$var</span>"; }
  elseif(is_string($var))  { $s .= "<span style='color:#070;'>".SPCQA($var)."</span>"; }
  elseif(is_array($var) || is_object($var)) {
        if(is_array($var))  { $c1 = "[";  $c2 = "]";  $clr = "910"; }
    elseif(is_object($var)) { $c1 = "{";  $c2 = "}";  $clr = "604"; }
    $s .= "$c1\n<div style='padding-left:".(in_array('narrow', $prms)?"8":"12")."px'>";
    foreach($var as $k=>$v) $s .= "<span style='color:#$clr;'>".SPCQA($k)."</span> = " . print_var($v, $prms, $lvl+1) . "<br>\n";
    $s .= "</div>$c2";
  }
  if($lvl==0) {
    if(in_array('narrow', $prms)) $font = "font:normal 11px/12px Arial narrow, Arial; font-stretch:condensed;";
    else $font = "font:normal 11px/12px Lucida Console, Monaco, Monospace;";
    $s = "<div style='text-align:left; $font background:#fff;'>$s</div>\n";
  }
  return $s;
}

function print_pre($var) { echo print_var($var); }

// EXITING ////////////////////////////////////////////////////////////////////////////////////////////////

function dierr($s, $code=0) {
  if(!$code) {
    if($_POST) $code = 500;
    else       $code = 404;
  }
      if($code==500) { $header = "500 Internal Server Error";            $msg = "Ошибка обработки данных ($code)";  }
  elseif($code==404) { $header = "404 Not Found";                        $msg = "Ошибка $code: нет такой страницы"; }
  elseif($code==429) { $header = "429 Too Many Requests";                $msg = "Сервис временно недоступен ($code)"; }
  elseif($code==503) { $header = "503 Service Temporarily Unavailable";  $msg = "Сервис временно недоступен ($code)"; }
  elseif($code==401) { $header = "401 Unauthorized";                     $msg = "Требуется авторизация ($code)"; }
  else               { $header = "";  $msg = ""; }
  
  IUD(
   "INSERT INTO rr_dierrs SET ".mysql_set([
      'msg'  => $s,
      'uri'  => $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
      'ref'  => $_SERVER['HTTP_REFERER'],
      'ip'   => $_ENV->ip,
      'post' => $_POST ? json_encode($_POST, JSON_UNESCAPED_UNICODE) : "",
    ])
  );
  
  if($header) header($_SERVER["SERVER_PROTOCOL"]." $header", true, $code);
  if($code==429 || $code==503) header("Retry-After: ".(12*60*60));
  
  if($_ENV->is_json) {
    echo json_encode((object)['error'=>SPCQA($s)], JSON_UNESCAPED_UNICODE);
  }
  else {
    echo $s;
  }
  
  die();
}

function redir($url, $code=301) {
  header("Location: $url", true, $code);
  exit();
}

// INCLUDE ////////////////////////////////////////////////////////////////////////////////////////////////

function include_var($inc) {
  ob_start();
  include($inc);
  return ob_get_clean();
}

// STRING ////////////////////////////////////////////////////////////////////////////////////////////////

function SPCQ($s) { return htmlspecialchars($s, ENT_QUOTES); }

function SPCQA($s) { return str_replace(["&amp;", "&quot;"], ["&", "\""], SPCQ($s)); }

function RN($s)  { return str_replace(["\r", "\n"], ["", "<br>"], trim($s)); }
function RNZ($s) { return str_replace(["\r", "\n"], ["", ","   ], trim($s)); }
function RNN($s) { return str_replace(["\r", ">\n", "\n"], ["", "> ", "<br>"], trim($s)); }

function Cap1($s) {
  if(is_numeric($s)) return $s;
  if(mb_strlen($s)==0) return "";
  if(mb_strlen($s)==1) return mb_strtoupper($s);
  $s1 = mb_substr($s, 1, 1);
  if($s1<>mb_strtolower($s1)) return $s;
  return mb_strtoupper(mb_substr($s, 0, 1)) . mb_substr($s, 1);
}

function Uncap1($s) {
  if(mb_strlen($s)<=1) return mb_strtolower($s);
  $s1 = mb_substr($s, 1, 1);
  if($s1==mb_strtolower($s1)) return mb_strtolower(mb_substr($s, 0, 1)).mb_substr($s,1);
  return $s;
}

function rus_plural($n, $forms) {  // [otvet, otveta, otvetov]
  return $n%10==1 && $n%100!=11 ? $forms[0] : ($n%10>=2 && $n%10<=4 && ($n%100<10 || $n%100>=20) ? $forms[1] : $forms[2]);
}

function Round2($x) {
  $x = intval($x);  $nd = mb_strlen((string)$x);  if($nd<=2) return $x;  else return intval( floor($x/pow(10, $nd-2)) * pow(10, $nd-2) );
}

function PercentDiff($x, $y) {
  $x = floatval($x);  $y = floatval($y);
  if(!$x && !$y) return 0;
  if(!($x + $y)) return 1;
  return 2 * abs($x-$y) / ($x+$y);
}

function FarDifferent($x, $y, $d=0.01) {
  if(!$x && !$y) return false;
  return PercentDiff($x,$y) > $d ? true : false;
}

function SpaceNum($x) {
  $s = (string)$x;
  $ret = '';  $l = mb_strlen($x);
  for($i=0; $i<$l; $i++) {
    $ret = $s[$l-$i-1] . ($i>0 && $i%3==0 ? "&thinsp;" : "") . $ret;
  }
  return $ret;
}

function ShortenNumber($num) {
  static $letters = [1000000000=>"G", 1000000=>"M", 1000=>"k"];
  $ret = $num;
  foreach($letters as $d=>$l) {
    if($num>=$d) {
      $v = round($num/$d);  if($v<10) $v = sprintf("%.1lf", $num/$d);
      $ret = $v . "&#8239;" . $l;
      break;
    }
  }
  return $ret;
}

function ar_str_ireplace($repls=[], $s) {
  $ar1 = $ar2 = [];
  foreach($repls as $k=>$v) { $ar1[] = $k;  $ar2[] = $v; }
  return str_ireplace($ar1, $ar2, $s);
}

function ar_str_replace($repls=[], $s) {
  $ar1 = $ar2 = [];
  foreach($repls as $k=>$v) { $ar1[] = $k;  $ar2[] = $v; }
  return str_replace($ar1, $ar2, $s);
}

function isdt($d) { return $d && $d<>'0000-00-00' && $d<>'0000-00-00 00:00:00' ? true : false; }

function sgn($x) { if($x>0) return 1;  elseif($x<0) return -1;  else return 0; }

function object_diff($new, $old) {  // returns array!
  $d = [];
  foreach($new as $k=>$v1) {
    if(!isset($old->$k) || $old->$k<>$v1) $d[$k] = $v1;
  }
  return $d;
}

function sort_by_field(&$objs, $key) {
  usort($objs, function($x,$y) use ($key) { return $x->$key>$y->$key ? 1 : ($x->$key<$y->$key ? -1 : 0); });
}

function rsort_by_field(&$objs, $key) {
  usort($objs, function($x,$y) use ($key) { return $x->$key<$y->$key ? 1 : ($x->$key>$y->$key ? -1 : 0); });
}

function sum_by_field(&$objs, $key) {
  $sum = 0;  foreach($objs as $i=>$o) $sum += $o->$key;
  return $sum;
}

function numdot($x) {
  return str_replace(",", ".", $x);
}

function num2key($x) {
  return "N".str_replace("-", "_", $x);
}

function arr2obj($arr) {
  $obj = (object)[];
  foreach($arr as $k=>$v) {
    $key = is_numeric($k) ? num2key($k) : $k;
    $obj->$key = $v;
  }
  return $obj;
}

function find_minmax($objs, $k='id') {
  $min_id = $max_id = 0;
  foreach($objs as $r) {
    $rk = $k ? $r->$k : $r;
    if(!$min_id || $min_id>$rk) $min_id = $rk;
    if(!$max_id || $max_id<$rk) $max_id = $rk;
  }
  return [$min_id, $max_id];
}

function url_get($ar) {
  return $ar ? '?'.http_build_query($ar) : '';
}

////////////////////////////////////////////////////////////////////////////////////////////////
