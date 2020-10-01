<? define("_root","../../");
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
include(_root."page.php");
$page->type = "page";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$p = mysql_o("SELECT * FROM rr_pages WHERE typ='k' AND url='alife'");

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$otitle = "GLife Service";
$h1 = "";
$zabst = "";
$zzt = "";
$zpubd = "2020-06-08";

$_tm0 = microtime(true);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($_GET['resetup']) {
  exit();  // single-time run to convert to new data format
  $stitle = "resetup";
  $glids = [];
  $res = mysql_query("
    SELECT gl1.id gl1_id, gl2.*
    FROM rr_glifes gl1
    JOIN rr_glifes gl2 ON gl2.rules=gl1.rules AND gl2.id>gl1.id
  ");
  while($r = mysql_fetch_object($res)) {
    if(!$glids[$r->id] || $r->gl1_id<$glids[$r->id]) {
      $glids[$r->id] = $r->gl1_id;
    }
  }
  foreach($glids as $id=>$glid) {
    mysql_query("UPDATE rr_gliferuns SET gl_id='$glid' WHERE id='$id' LIMIT 1");
  }
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif($_GET['avgruns']) {  // take all runs for this glife and calc their average to put to glife's params
  $stitle = "avgruns";
  
  ini_set("memory_limit", "4000M");
  set_time_limit(900);
  
  include_once("lib/service.php");
  
  $PP = intval($_GET['pp']) ?: 10000;  $LL = intval($_GET['ll']);  $LP = $LL * $PP;
  
  $resg = mysql_query("SELECT * FROM rr_glifes WHERE 1 ORDER BY id LIMIT $LP, $PP");
  while($gl = mysql_fetch_object($resg)) {
    $gls = gl_AvgRuns($gl, $_GET['inx']);
    if($_GET['verbose']) $zzt .= "<div class=stxt>" . print_pre($gls, 1, -1) . "</div>";
  }
  
  $zzt .= "<div>started=$LP | <a href='$_self?avgruns=1&ll=".($LL+1)."'>next $PP</a></div>";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$page->title = "Alife: $otitle: $stitle Ц ERUDITOR.RU";
$h1 = "<a href='/k/?alife'>Alife</a> &rarr; <a href='/alife/glife3/'>$otitle</a> &rarr; $stitle";

$zzt .= "<div>time: " . round(microtime(true) - $_tm0, 3) . "s</div>";

$page->z .= "
  <style>
    DIV.stxt {font:normal 11px/11px Lucida Console, Monaco, Monospace; margin-top:5px; white-space:nowrap;}
    #glifeStatTB {border:solid 2px #ddd; margin-top:3px;}
    #glifeStatTB TD, #glifeStatTB TH {padding:2px 4px; text-align:right;}
    #glifeStatTB TH {background:#f4f4f4; border-bottom:solid 1px #ddd;}
  </style>
";

$page->z .= "
  <h1>$h1</h1>
  
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

MakePage();

?>