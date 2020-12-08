<?
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// this file is located outside of repository!
include_once("../../lib/db.php");  // connects to MySQL database; it also defines "_local" const

define("_root", "");
include_once("lib/var.php");
include_once("lib/lib.php");
include_once("lib/tpl.php");

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

include_once("parts/backstage.php");

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$page->bread[] = ["GL_SERVICE", "gl_service.php"];
$page->zabst = "Service scripts for database operations.";
$page->zpubd = "2020-06-08";
$page->noindex = true;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$_tm0 = microtime(true);

$AQ = new AQs(_local==="1" ? true : false);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($_GET['upd_orgasum']) {  // recalc glifetriruns.orgasum, see comments with "undeservingly too large" in Analysis
  $page->bread[] = ["upd_orgasum", "?upd_orgasum=1"];
  $res = mysql_query("SELECT SQL_CALC_FOUND_ROWS * FROM rr_glifetriruns WHERE orgasum>0 ORDER BY id LIMIT $AQ->LP,$AQ->PP");
  $AQ->shwn = mysql_num_rows($res);
  $AQ->nttl = mysql_r("SELECT FOUND_ROWS()");
  while($r = mysql_fetch_object($res)) {
    $json = json_decode($r->records);  if(!$json) continue;
    $orgasums = $json->orga_sum ?: [];
    $max = 0;
    foreach($orgasums as $z=>$orgasum) {
      if($z==0) continue;
      if($max<$orgasum) $max = $orgasum;
    }
    if($max<>$r->orgasum) $AQ->AQs[] = "UPDATE rr_glifetriruns SET orgasum='$max' WHERE id='$r->id'";
  }
  $AQ->RunAQs();
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif($_GET['recalc_ratings']) {
  $page->bread[] = ["recalc_ratings", "?recalc_ratings=1"];
  $res = mysql_query("SELECT SQL_CALC_FOUND_ROWS * FROM rr_glifetriruns WHERE ver='$_ENV->anver' ORDER BY id LIMIT $AQ->LP,$AQ->PP");
  $AQ->shwn = mysql_num_rows($res);
  $AQ->nttl = mysql_r("SELECT FOUND_ROWS()");
  while($r = mysql_fetch_object($res)) {
    $rating0 = $r->rating;
    glRecords::EnrichOrgaRatings($r);
    if(property_exists($r, 'rating') && $r->rating<>$rating0) {
      $AQ->AQs[] = "UPDATE rr_glifetriruns SET rating='$r->rating' WHERE id='$r->id'";
    }
  }
  $AQ->RunAQs();
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif($_GET['rerun_new_orga']) {
  $page->bread[] = ["rerun_new_orga", "?rerun_new_orga=1"];
  
      if($_GET['order']=='asc')  $order = "ORDER BY gr.id";
  elseif($_GET['order']=='desc') $order = "ORDER BY gr.id DESC";
  else                           $order = "";
  
  if($_GET['minusone']) $wh = "gr.orgasum<0 AND stopped_nturn>=2000";
  else                  $wh = "gr.orgasum>0";
  
  $send2js = '';
  $res = mysql_query(
   "SELECT gr.*, gl.family_id, gl.notaset, gl.mutaset
    FROM rr_glifetriruns gr
    JOIN rr_glifetris gl ON gl.id=gr.gl_id
    WHERE $wh AND family_id=3 AND ver<$_ENV->anver AND stopped_at!='x'
    $order
    LIMIT 100
  ");
  while($r = mysql_fetch_object($res)) {
    $send2js .= "[$r->id, '$r->notaset', '".str_replace("\n", "\\n", $r->mutaset)."', $r->fseed],\n";
  }
  $send2js = "
    gl_reruns = [
      $send2js
    ];
  ";
  $page->z .= GLifeJS('rerun', [], $send2js);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$page->z .= "<div>time: " . round(microtime(true) - $_tm0, 3) . "s</div>";

$page->z .= "
  <style>
    DIV.stxt {font:normal 11px/11px Lucida Console, Monaco, Monospace; margin-top:5px; white-space:nowrap;}
    #glifeStatTB {border:solid 2px #ddd; margin-top:3px;}
    #glifeStatTB TD, #glifeStatTB TH {padding:2px 4px; text-align:right;}
    #glifeStatTB TH {background:#f4f4f4; border-bottom:solid 1px #ddd;}
  </style>
";

$page->z = "
  <div style='font:normal 11px/11px Lucida Console, Monaco, Monospace;'>
    <div id=aux1></div>
    
    $AQ->paginat
    
    $AQ->SQ
    
    $page->z
    
    $AQ->nav
    
    $AQ->paginat
  </div>
";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

MakePage();

?>