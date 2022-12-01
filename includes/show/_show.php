<?

////////////////////////////////////////////////////////////////////////////////////////////////
if($_GET['glife']) {
  $glife = $_GET['glife'];
  
      if(is_numeric($glife)) { $q = "id='".intval($glife)."'";  }
  elseif($glife)             { $q = "named='".MRES($glife)."'"; }
  
  $gl = mysql_o("SELECT * FROM rr_glifetris WHERE $q");  if(!$gl) die("no gl found");
  
  Page::$bread[] = [GLifeInfo::GLtitle($gl)];
  
  echo GLifeInfo::GlifeBigInfo($gl);
  
  echo GLifeJS::View($glife);
}
////////////////////////////////////////////////////////////////////////////////////////////////
elseif($_GET['gl_run']) {
  $gr_id = intval($_GET['gl_run']);  if(!$gr_id) die("#r84238237432");
  
  $gr = mysql_o("SELECT * FROM rr_glifetriruns WHERE id='$gr_id'");   if(!$gr) die("#r84238237433");
  $gl = mysql_o("SELECT * FROM rr_glifetris WHERE id='$gr->gl_id'");  if(!$gl) die("#r84238237434");
  
  Page::$bread[] = ["Run #$gr->id", "/show/?gl_run=$gr->id"];
  
  echo GLifeInfo::GlifeBigInfo($gl, " AND gr.id='$gr_id'");
  
  echo GLifeJS::View($gl->id, ['rseed'=>$gr->rseed, 'fseed'=>$gr->fseed, 'FW'=>$gr->FW, 'FH'=>$gr->FH, 'LF'=>$gr->LF]);
}
////////////////////////////////////////////////////////////////////////////////////////////////
else die("nothing to show");
////////////////////////////////////////////////////////////////////////////////////////////////
