<?

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

spl_autoload_register(
  function ($class) {
    static $incs = [
      'glRecords' => 'lib/glrecords.php',
      'AQs'       => 'lib/aqs.php',
    ];
    
    $inc = $incs[$class];
    if($inc) include_once(_root.$inc);
  }
);

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function dierr($s) {
  header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
  die("error: $s");
}

function print_pre($var, $return=false, $release=0) {
  if(_local==="1" || $release<0) $s="<pre style='white-space:pre-wrap;'>".print_r($var,1)."</pre>";
  if($return) return $s;  else echo $s;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function MRES($s) { return mysql_real_escape_string($s); }
function mysql_r($s) { $t=mysql_fetch_row(mysql_query($s));  return $t[0]; }
function mysql_o($s) { return mysql_fetch_object(mysql_query($s)); }

function htmlSPC($string, $flags=null) {
  static $php53 = null;
  static $default_flags = null;
  if (is_null($php53)) {
    $php53 = version_compare(PHP_VERSION, '5.4.0') < 0;
    $default_flags = $php53 ? ENT_COMPAT : ENT_COMPAT | ENT_HTML401;
  }
  if (!is_int($flags)) {
    $flags = $default_flags;
  }
  return $php53
    ? htmlspecialchars($string, $flags)
    : htmlspecialchars($string, $flags, 'cp1251');
}

function SPCQ($s) { return htmlSPC($s, ENT_QUOTES); }

function SPCQA($s) { return str_replace(array("&amp;", "&quot;"), array("&", "\""), SPCQ($s)); }

function isCorrectID($t_id)  { if(!$t_id) return false;  return preg_match("`[^a-zA-Z0-9_\.\-]`",       $t_id) ? false : true; }
function isCorrectVar($t_id) { if(!$t_id) return false;  return preg_match("`[^a-zA-Z0-9_\.\-;,@$\n]`", $t_id) ? false : true; }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function iconver($c1,$c2,$x) {
      if(is_string($x)) { $ret=iconv($c1,$c2,$x); }
  elseif(is_array($x))  { $ret=array();        foreach($x as $n=>$v) $ret  [iconver($c1,$c2,$n)]=iconver($c1,$c2,$v); }
  elseif(is_object($x)) { $ret=new stdClass(); foreach($x as $n=>$v) $ret->{iconver($c1,$c2,$n)}=iconver($c1,$c2,$v); }
  else                  { $ret=$x; }
  return $ret;
}
function W2U($x) { return iconver("Windows-1251", "UTF-8//TRANSLIT", $x); }
function U2W($x) { return iconver("UTF-8", "Windows-1251//TRANSLIT", $x); }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function RN($s) { return str_replace(array("\r","\n"),array("","<br>"),trim($s)); }

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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>