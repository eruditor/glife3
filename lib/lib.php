<?
function RN($s) { return str_replace(array("\r","\n"),array("","<br>"),trim($s)); }
function RNP($s) { return "<p>".str_replace(array("\r","\n","^^"),array("","</p><p>","\r\n"),trim($s))."</p>"; }
function RNN($s) { return str_replace(array("\r", ">\n", "\n"),array("", "> ", "<br>"),trim($s)); }
function mysql_r($s) { $t=mysql_fetch_row(mysql_query($s));  return $t[0]; }
function mysql_o($s) { return mysql_fetch_object(mysql_query($s)); }
function z_addslashes($s) { if(!get_magic_quotes_gpc())$s=mysql_real_escape_string($s); return $s; }
function z_stripslashes($s) { if(!get_magic_quotes_gpc())$s=stripslashes(str_replace(array("\\r","\\n"),array(chr(13),chr(10)),$s)); return $s; }
function x_addslashes($s) { return z_addslashes(str_replace("'","`",$s)); }
function wml($s) { return "<script>wml('$s')</script>"; }

function convert2plaintext($s) {
  $ret = '';
  $s = strip_tags(str_replace(array("<li>","\r","   ","  "),array("ï ",""," "," "),trim($s)));
  $ar = explode("\n", $s);
  foreach($ar as $a) {
    $a = trim($a);  if(!$a) continue;
    $ret .= ($a?' ':'') . $a;
    if(strlen($ret)>300) break;
  }
  return trim(htmlSPC($ret));
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ‘”Õ ÷»» ¬–
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function MRES($s) { return mysql_real_escape_string($s); }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function print_pre($var, $return=false, $release=0) {
  if(_local || $release<0) $s="<pre style='white-space:pre-wrap;'>".print_r($var,1)."</pre>";
  if($return) return $s;  else echo $s;
}

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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function isdt($d) { if(!$d || $d=="0000-00-00" || $d=="0000-00-00 00:00:00") return false;  return true; }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  ŒÕ≈÷ ¡ÀŒ ¿ ‘”Õ ÷»… ¬–
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function dtCherez($t1,$t2) {
  $dt=strtotime($t1)-strtotime($t2);
  if($dt<60)   return $dt." ÒÂÍ.";
  if($dt<60*60) return round($dt/60)." ÏËÌ.";
  if($dt<24*60*60) return round($dt/(60*60))." ˜‡Ò.";
  return round($dt/(24*60*60))." ‰Ì.";
}

function dtNazad($t1,$t2="") {
  $dt=strtotime("now")-strtotime($t1);
  if($dt<60)   return $dt." ÒÂÍ.";
  if($dt<60*60) return round($dt/60)." ÏËÌ.";
  if($dt<24*60*60) return round($dt/(60*60))." ˜‡Ò.";
  return round($dt/(24*60*60))." ‰Ì.";
}

function StrCut1($s,$n) {
  if(strlen($s)<=$n) return $s;
  $s1=substr($s,0,$n);
  $ar=array(" ", ".", ",", "!", "?", "'", "\"", ")", "(", "[", "]");  $ii=0;
  foreach($ar as $a) {
    $i=strrpos($s1,$a);
    if($i!==false && $i>$ii)$ii=$i;
  }
  if($ii)$s1=substr($s1,0,$ii);
  return $s1."Ö";
}

function StrCut2($s,$n,$m) {
  if(strlen($s)<=$n) return $s;
  $ar=array(" ", ".", ",", "!", "?", "'", "\"", ")", "(", "[", "]");  $ii=strlen($s);
  foreach($ar as $a) {
    $i=strpos($s1,$a,$n);
    if($i!==false && $i<$ii)$ii=$i;
  }
  if($ii)$s1=substr($s,0,$ii); else $s1=substr($s,0,$m);
  return $s1."Ö";
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>