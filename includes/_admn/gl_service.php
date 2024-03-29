<?

////////////////////////////////////////////////////////////////////////////////////////////////

Page::$noindex = true;
Page::$bread[] = ["GL_SERVICE", "gl_service.php"];

echo "
  <div class=zabst>
    Service scripts for database operations.
  </div>
";

////////////////////////////////////////////////////////////////////////////////////////////////

$_tm0 = microtime(true);

$AQ = new AQs(glUser::$user ? true : false);

////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////
if($_GET['recalc_ratings']) {
  Page::$bread[] = ["recalc_ratings", "?recalc_ratings=1"];
  $res = MQ(
   "SELECT SQL_CALC_FOUND_ROWS *
    FROM rr_glifetriruns
    WHERE orgasum<=0 AND records!=''  # ver='$_ENV->anver'
    ORDER BY id
    LIMIT $AQ->LP,$AQ->PP
  ");
  $AQ->shwn = mysql_num($res);
  $AQ->nttl = mysql_r("SELECT FOUND_ROWS()");
  while($r = mysqli_fetch_object($res)) {
    $rating0 = $r->rating;  $orgasum0 = $r->orgasum;
    glRecords::EnrichOrgaRatings($r);
    $q = '';
    if($r->ver==$_ENV->anver && property_exists($r, 'rating') && $r->rating<>$rating0) $q .= ($q?",":"") . "rating ='".intval($r->rating)."'";
    if(property_exists($r, 'orgasum') && $r->orgasum<>$orgasum0) $q .= ($q?",":"") . "orgasum='".intval($r->orgasum)."'";
    if($q) {
      $AQ->AQs[] = "UPDATE rr_glifetriruns SET $q WHERE id='$r->id'";
    }
  }
  $AQ->RunAQs();
}
////////////////////////////////////////////////////////////////////////////////////////////////
elseif($_GET['rerun_new_orga']) {
  $fm_id = 3;
  
  Page::$bread[] = ["rerun_new_orga", "?rerun_new_orga=1"];
  
      if($_GET['order']=='asc')  $order = "ORDER BY gr.id";
  elseif($_GET['order']=='desc') $order = "ORDER BY gr.id DESC";
  else                           $order = "";
  
  if($_GET['minusone']) $wh = "gr.orgasum<0 AND stopped_nturn>=2000";
  else                  $wh = "gr.orgasum>0";
  
  $send2js = '';
  $res = MQ(
   "SELECT gr.*, gl.family_id, gl.notaset, gl.mutaset
    FROM rr_glifetriruns gr
    JOIN rr_glifetris gl ON gl.id=gr.gl_id
    WHERE $wh AND family_id=$fm_id AND ver<$_ENV->anver AND stopped_at!='x'
    $order
    LIMIT 100
  ");
  while($r = mysqli_fetch_object($res)) {
    $send2js .= "[$r->id, '$r->notaset', '".str_replace("\n", "\\n", $r->mutaset)."', $r->fseed],\n";
  }
  $send2js = "
    gl_reruns = [
      $send2js
    ];
  ";
  echo GLifeJS::View('rerun', ['family'=>$fm_id], $send2js);
}
////////////////////////////////////////////////////////////////////////////////////////////////
elseif($_GET['repair_mutaset']) {
  exit("already done");
  $fm_id = 6;
  
  Page::$bread[] = ["repair_mutaset", "?repair_mutaset=1"];
  
  $PP = intval($_GET['pp']) ?: 1000;
  
  $send2js = '';
  $res = MQ(
   "SELECT gl.*, gr.rseed, gr.context
    FROM rr_glifetris gl
    JOIN rr_glifetriruns gr ON gr.gl_id=gl.id
    WHERE family_id=$fm_id AND LENGTH(mutaset) BETWEEN 500 AND 15000
    LIMIT $PP
  ");
  while($r = mysqli_fetch_object($res)) {
    preg_match("`&nmuta=(\d+)`", $r->context, $nmuta);
    $send2js .= "[$r->id, $r->rseed, $nmuta[1]],\n";
  }
  $send2js = "
    gl_repair = [
      $send2js
    ];
  ";
  echo GLifeJS::View('repair', ['family'=>$fm_id], $send2js);
}
////////////////////////////////////////////////////////////////////////////////////////////////
elseif($_GET['notamd5']) {
  Page::$bread[] = ["notamd5", "?notamd5=1"];
  $res = MQ(
   "SELECT SQL_CALC_FOUND_ROWS *
    FROM rr_glifetris
    WHERE 1
    ORDER BY id
    LIMIT $AQ->LP,$AQ->PP
  ");
  $AQ->shwn = mysql_num($res);
  $AQ->nttl = mysql_r("SELECT FOUND_ROWS()");
  while($r = mysqli_fetch_object($res)) {
    if(!$r->notamd5 && $r->notaset) $AQ->AQs[] = "UPDATE rr_glifetris SET notamd5='".MRES(md5($r->notaset))."' WHERE id='$r->id' LIMIT 1";
  }
  $AQ->RunAQs();
}
////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////

echo "<div>time: " . round(microtime(true) - $_tm0, 3) . "s</div>";

echo "
  <style>
    DIV.stxt {font:normal 11px/11px Lucida Console, Monaco, Monospace; margin-top:5px; white-space:nowrap;}
    #glifeStatTB {border:solid 2px #ddd; margin-top:3px;}
    #glifeStatTB TD, #glifeStatTB TH {padding:2px 4px; text-align:right;}
    #glifeStatTB TH {background:#f4f4f4; border-bottom:solid 1px #ddd;}
  </style>
";

echo "
  <div style='font:normal 11px/11px Lucida Console, Monaco, Monospace;'>
    <div id=aux1></div>
    
    $AQ->paginat
    
    $AQ->SQ
    
    $AQ->nav
    
    $AQ->paginat
  </div>
";

////////////////////////////////////////////////////////////////////////////////////////////////
