<? define("_root","../../");
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
include(_root."page.php");
$page->type = "page";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$p = mysql_o("SELECT * FROM rr_pages WHERE typ='k' AND url='alife'");

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$ver = 304;

$Title = "GLife3";
$H1 = "";
$zabst = "
  <b>Life game on WebGL2</b><br>
  &rarr; <a href='/alife/glife3/?paused=1&maxfps=300&nmuta=100&rseed=1936799038&LF=1&fseed=186356772'>First emergent appearance of Artificial Life in Cellular Automata</a><br>
";
$zzt = "";
$zpubd = "2020-10-01";

include_once("lib/service.php");
include_once("parts/backstage.php");

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!$_GET['view'] && (isset($_GET['savedcat']) || isset($_GET['typed']) || isset($_GET['named']))) {
  // redirect here
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif($_GET['savedlist']) {
  // redirect here
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif($_GET['ruleset']) {
  // redirect here
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif($_GET['view']=='manufacture') {
  include("parts/manufacture.php");
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif($_GET['view']=='library') {
  include("parts/library.php");
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif($_GET['view']=='gallery') {
  include("parts/gallery.php");
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif($_GET['gl_id'] || $_GET['gl_name']) {
  include("parts/show.php");
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
else {
  $zzt .= GLifeJS();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function GLifeJS($notaset='', $prms=[]) {
  $send2js = '';
  
  global $gl_bgc4records;
  $send2js .= "gl_bgc4records = JSON.parse(`" . json_encode($gl_bgc4records) . "`);";
  
  if($notaset=='random') {
    $gl->notaset = 'random';
  }
  else {
    $gl = GetGL4Notaset($notaset);
    $fl = GetFamilies()[$gl->family_id];
  }
  
  $rseed = intval($_GET['rseed']) ?: rand(1,getrandmax());
  $fseed = intval($_GET['fseed']) ?: rand(1,getrandmax());
  
  $jsget = "?v=$ver";
  if(_local==="1") $jsget .= "&rnd=".rand(1,getrandmax());  // to refresh all scripts every run
  
  $plus = '';  foreach($prms as $k=>$v) $plus .= "&".urlencode($k)."=".urlencode($v);
  
  return "
    <div id=GLifeCont></div>
    
    <script>$send2js</script>
    
    <script src='lib/0.params.js$jsget&rseed=$rseed&fseed=$fseed&notaset=".urlencode($gl->notaset)."$plus'></script>
    <script src='lib/1.math.js$jsget'></script>
    <script src='lib/2.hardware.js$jsget'></script>
    <script src='lib/3.spacetime.js$jsget'></script>
    <script src='lib/4.physics_laws.js$jsget'></script>
    <script src='lib/5.dynamics.js$jsget'></script>
    <script src='lib/6.visualisation.js$jsget'></script>
    <script src='lib/7.analysis.js$jsget'></script>
    <script src='lib/8.interface.js$jsget'></script>
    <script src='lib/9.divine_forces.js$jsget'></script>
    <script src='main.js$jsget'></script>
  ";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$gltitle = "GLife";

$page->title = "Alife: $gltitle".($Title?": ".strip_tags($Title):"")." Ц ERUDITOR.RU";

$H1 = "<a href='/k/?alife'>Alife</a> &rarr; "
      . ($_GET?"<a href='/alife/glife3/'>$gltitle</a><sup>".sprintf("%.2lf", $ver/100)."</sup>":"$gltitle <span>v".sprintf("%.2lf", $ver/100)."</span>")
      . ($H1?" &rarr; $H1":"");

$zabst .= "<br><b>
  | <a href='$_self?view=gallery'>Gallery</a>
  | <a href='$_self?view=library'>Library</a>
  | <a href='$_self?view=manufacture'>Manufacture</a>
  | </b><br>
";

$page->z .= "
  <h1>$H1</h1>
  
  <div class=zabst>
    $zabst
  </div>
  
  <div class=zzt>
    $zzt
  </div>
  
  <div class=zauth><span title='јвтор'>&copy; </span>".GetAuthorName($p->author)."</div>
  
  <div class=zpubd>$zpubd</div>
";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$page->z = "
  <style>
    HTML, BODY {min-width:1200px;}
    CANVAS {vertical-align:top; background:#ccc; cursor:crosshair;}
    DIV.stxt {font:normal 11px/11px Lucida Console, Monaco, Monospace; margin-top:5px; white-space:nowrap;}
    SUP {color:#aaa; font-weight:normal; vertical-align:middle; position:relative; font-size:50%; bottom:0.6em;}
    
    .valtop, .valtop TD {vertical-align:top;}
    .hlp {cursor:help;}
    
    #glifeStatTB {border:solid 2px #ddd; margin:0 0 10px 0;}
    #glifeStatTB TD, #glifeStatTB TH {padding:2px 4px; text-align:right; vertical-align:top;}
    #glifeStatTB TH {background:#f4f4f4; border-bottom:solid 1px #ddd;}
    #glifeStatTB .tal {text-align:left;}
    
    #SavedListTB TD, #SavedListTB TH {font:normal 11px/13px arial; padding:1px 3px; vertical-align:top;}
    #SavedListTB TH {text-align:left; font-weight:bold;}
    #SavedListTB TD INPUT {font:normal 11px/11px arial; padding:0;}
    #SavedListTB TD.tar {text-align:right; font:normal 11px/11px Lucida Console, Monaco, Monospace;}
    #SavedListTB TD.nrrw {font-family:arial narrow, arial; font-stretch:condensed;}
  </style>
" . $page->z;

$_ENV->isMobile = false;

MakePage();

?>