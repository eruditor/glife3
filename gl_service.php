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
  exit("single time use");
  
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
elseif($_GET['import_glifetris']) {
  exit("single time use");
  
  $stitle = "import_glifetris";
  
  ini_set("memory_limit", "4000M");
  set_time_limit(900);
  
  $PP = intval($_GET['pp']) ?: 10000;  $LL = intval($_GET['ll']);  $LP = $LL * $PP;
  
  $nupd = 0; 
  $resg = mysql_query("SELECT * FROM rr_glifes WHERE gl3_id=0 ORDER BY id LIMIT $LP, $PP");
  while($gl = mysql_fetch_object($resg)) {
    $gl3 = new stdClass;
    preg_match_all("`\[(\d+:\d+)\],`", $gl->rules, $arules);  $arules = $arules[1];
    if(!$arules) continue;
    
    $gl3->id = $gl->id;
    $gl3->family_id = 1;
    $gl3->notaset = implode(",", $arules);
    $gl3->mutaset = "";
    $gl3->named = $gl->named;
    $gl3->typed = $gl->typed;
    $gl3->found_dt = $gl->found_dt;
    
    $q = '';
    foreach($gl3 as $k=>$v) $q .= ($q?",":"") . "$k='".MRES($v)."'";
    mysql_query("INSERT INTO rr_glifetris SET $q");
    $id = mysql_insert_id();
    mysql_query("UPDATE rr_glifes SET gl3_id='$id' WHERE id='$gl->id' LIMIT 1");
    $nupd ++;
  }
  
  $zzt .= "updated=$nupd<br>";
  $zzt .= "remaining=" . mysql_r("SELECT COUNT(*) FROM rr_glifes WHERE gl3_id=0") . "<br>";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif($_GET['import_glifetriruns']) {
  exit("single time use");
  
  $stitle = "import_glifetriruns";
  
  ini_set("memory_limit", "4000M");
  set_time_limit(900);
  
  $PP = intval($_GET['pp']) ?: 10;  $LL = intval($_GET['ll']);  $LP = $LL * $PP;
  
  $nupd = 0; 
  $resg = mysql_query(
   "SELECT glrun.*
   FROM rr_gliferuns glrun
   JOIN rr_glifes gl ON gl.id=glrun.gl_id
   WHERE gl3run_id=0 AND gl.gl3_id!=0
   ORDER BY glrun.id
   LIMIT $LP, $PP
  ");
  while($gl = mysql_fetch_object($resg)) {
    $gl3 = new stdClass;
    
    $gl3->id = $gl->id;
    $gl3->gl_id = $gl->gl_id;
    $gl3->dt = $gl->found_dt;
    $gl3->rseed = 0;
    $gl3->fseed = 0;
    $gl3->stopped_at = $gl->failed_at;
    $gl3->stopped_nturn = $gl->failed_nturn;
    $gl3->orgasum = -1;
    $gl3->records = $gl->records;
    $gl3->context = $gl->context;
    
    $q = '';
    foreach($gl3 as $k=>$v) $q .= ($q?",":"") . "$k='".MRES($v)."'";
    mysql_query("INSERT INTO rr_glifetriruns SET $q");
    $id = mysql_insert_id();
    mysql_query("UPDATE rr_gliferuns SET gl3run_id='$id' WHERE id='$gl->id' LIMIT 1");
    //$zzt .= "$q<br>";
    $nupd ++;
  }
  
  $zzt .= "updated=$nupd<br>";
  $zzt .= "remaining=" . mysql_r("SELECT COUNT(*) FROM rr_gliferuns WHERE gl3run_id=0") . "<br>";
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