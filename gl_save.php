<? define("_root","../../");
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
include(_root . "lib/var.php");
include(_root . "lib/db.php");
include(_root . "lib/lib.php");
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($_POST['rules']) {
  $gl = mysql_o("SELECT * FROM rr_glifes WHERE rules='".MRES($_POST['rules'])."'");
  if(!$gl) {
    $q = '';
    $post = ['rules'=>'', 'failed_at'=>'', 'failed_nturn'=>0, 'records'=>''];
    foreach($post as $k=>$v) {
      $post[$k] = MRES($_POST[$k]);
      $q .= ($q?", ":"") . "$k='".$post[$k]."'";
    }
    mysql_query("INSERT INTO rr_glifes SET found_dt=NOW(), $q");
    $glid = mysql_insert_id();
  }
  else {
    $glid = $gl->id;
  }
  
  $q = '';
  $post = ['seed'=>'', 'failed_at'=>'', 'failed_nturn'=>0, 'records'=>'', 'context'=>''];
  foreach($post as $k=>$v) {
    $post[$k] = MRES($_POST[$k]);
    $q .= ($q?", ":"") . "$k='".$post[$k]."'";
  }
  mysql_query("INSERT INTO rr_gliferuns SET gl_id='$glid', found_dt=NOW(), $q");
  $id = mysql_insert_id();
  mysql_query("INSERT INTO rr_glogs SET glife_id='$glid', usr_id=0, dt=NOW(), val0='', val1='".MRES($q)."'");
  
  if($gl) {
    include_once("lib/service.php");
    $gls = gl_AvgRuns($gl, true);
  }
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
else if(_local==="1" && $_POST['named']) {
  $id = intval($_POST['id']);
  $old = mysql_o("SELECT * FROM rr_glifes WHERE id='$id'");  if(!$old) die("glife3 $id not found");
  $named = $_POST['named'];
  if(strpos($named, ":")!==false) {
    list($named, $typed) = explode(":", $named);
  }
  $q = '';
  $q .= ($q?", ":"") . "named='".MRES($named)."'";
  $q .= ($q?", ":"") . "typed='".MRES($typed)."'";
  if($q) {
    mysql_query("UPDATE rr_glifes SET $q WHERE id='$id' LIMIT 1");
    $val0 = "named='".MRES($old->named)."', typed='".MRES($old->typed)."'";
    mysql_query("INSERT INTO rr_glogs SET glife_id='$id', usr_id=0, dt=NOW(), val0='".MRES($val0)."', val1='".MRES($q)."'");
  }
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>