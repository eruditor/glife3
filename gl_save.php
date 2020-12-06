<?
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

include_once("../../lib/db.php");  // connects to MySQL database, this file is located outside of repository

define("_root", "");
include_once("lib/var.php");
include_once("lib/lib.php");

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($_POST['rerun'])) {
  $rerun_gr_id = intval($_POST['rerun']);  if(!$rerun_gr_id) die("no rerun_gr_id");
  $old = mysql_o("SELECT * FROM rr_glifetriruns WHERE id='$rerun_gr_id'");  if(!$old) die("rerun_gr not found");
  if($old->fseed<>$_POST['fseed']) die("#4y1788372");
  
  $new = clone $old;
  foreach(['stopped_at', 'stopped_nturn', 'orgasum', 'records'] as $k) {
    $new->$k = $_POST[$k];
  }
  glRecords::EnrichOrgaRatings($new);
  
  $q = $val0 = $val1 = '';
  foreach(['stopped_at', 'stopped_nturn', 'orgasum', 'records', 'rating'] as $k) {
    if($old->$k==$new->$k) continue;
    $q .= ($q?",":"") . "$k='".MRES($new->$k)."'";
    $val0 .= "$k:|".MRES($old->$k)."|";
    $val1 .= "$k:|".MRES($new->$k)."|";
  }
  if($q) {
    mysql_query("UPDATE rr_glifetriruns SET ver='$_ENV->anver', $q WHERE id='$old->id' LIMIT 1");
    mysql_query("INSERT INTO rr_glogs SET glife_id='$old->id', usr_id=0, dt=NOW(), val0='$val0', val1='$val1'");
  }
  else {
    mysql_query("UPDATE rr_glifetriruns SET ver='$_ENV->anver' WHERE id='$old->id' LIMIT 1");
  }
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif($_POST['family_id']) {
  // insert/select glifetri
  $gl = mysql_o("SELECT * FROM rr_glifetris WHERE 
                        family_id='".intval($_POST['family_id'])."'
                    AND notaset  ='".MRES(  $_POST['notaset'  ])."'
                    AND mutaset  ='".MRES(  $_POST['mutaset'  ])."'
                ");
  if(!$gl) {
    $q = '';
    $post = ['family_id'=>'', 'notaset'=>'', 'mutaset'=>0, 'named'=>'', 'typed'=>''];
    foreach($post as $k=>$v) {
      $post[$k] = MRES($_POST[$k]);
      $q .= ($q?", ":"") . "$k='".$post[$k]."'";
    }
    mysql_query("INSERT INTO rr_glifetris SET found_dt=NOW(), $q");
    $glid = mysql_insert_id();
  }
  else {
    $glid = $gl->id;
  }
  
  // insert glifetrirun
  $q = '';
  $post = ['rseed'=>'', 'fseed'=>'', 'stopped_at'=>0, 'stopped_nturn'=>0, 'orgasum'=>0, 'records'=>'', 'context'=>''];
  foreach($post as $k=>$v) {
    $post[$k] = MRES($_POST[$k]);
    $q .= ($q?", ":"") . "$k='".$post[$k]."'";
  }
  mysql_query("INSERT INTO rr_glifetriruns SET gl_id='$glid', dt=NOW(), ver='$_ENV->anver', $q");
  $id = mysql_insert_id();
  
  // calc rating
  $r = mysql_o("SELECT * FROM rr_glifetriruns WHERE id='$id'");
  glRecords::EnrichOrgaRatings($r);
  if(property_exists($r, 'rating')) mysql_query("UPDATE rr_glifetriruns SET rating='$r->rating' WHERE id='$r->id'");
  
  // insert qlog
  mysql_query("INSERT INTO rr_glogs SET glife_id='$glid', usr_id=0, dt=NOW(), val0='', val1='tri:".MRES($q)."'");
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
else if(_local==="1" && $_POST['named']) {
  $id = intval($_POST['id']);
  $old = mysql_o("SELECT * FROM rr_glifetris WHERE id='$id'");  if(!$old) die("glife3 $id not found");
  $named = $_POST['named'];
  if(strpos($named, ":")!==false) {
    list($named, $typed) = explode(":", $named);
  }
  $q = '';
  $q .= ($q?", ":"") . "named='".MRES($named)."'";
  $q .= ($q?", ":"") . "typed='".MRES($typed)."'";
  if($q) {
    mysql_query("UPDATE rr_glifetris SET $q WHERE id='$id' LIMIT 1");
    $val0 = "named='".MRES($old->named)."', typed='".MRES($old->typed)."'";
    mysql_query("INSERT INTO rr_glogs SET glife_id='$id', usr_id=0, dt=NOW(), val0='".MRES($val0)."', val1='tri:".MRES($q)."'");
  }
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// legacy, but might be used some day
function gl3_AvgRuns($gl, $inx=false) {
  $gls = [];
  
  $res = mysql_query("SELECT * FROM rr_glifetriruns WHERE gl_id='$gl->id' AND stopped_at!='x'");
  while($r = mysql_fetch_object($res)) {
    $gl_id = $r->gl_id;
    $gls[$gl_id]->stopped_at[$r->stopped_at] ++;
    $gls[$gl_id]->stopped_nturn += $r->stopped_nturn;
    $gls[$gl_id]->nn ++;
    if($r->records) {
      $records = json_decode($r->records);
      if(!$gls[$gl_id]->records) $gls[$gl_id]->records = new stdClass();
      foreach($records as $k=>$v) {
        if(is_array($v)) {
          foreach($v as $kv=>$vv) {
            $gls[$gl_id]->records->$k[$kv] += $vv;
          }
        }
        else {
          $gls[$gl_id]->records->$k += $v;
        }
      }
      $gls[$gl_id]->nrec ++;
    }
  }
  
  foreach($gls as $gl_id=>$r) {
    arsort($r->stopped_at);  reset($r->stopped_at);  $gls[$gl_id]->stopped_at = key($r->stopped_at);
    $gls[$gl_id]->stopped_nturn = round($r->stopped_nturn / $r->nn);
    if($r->records) {
      foreach($r->records as $k=>$v) {
        if(is_array($v)) {
          foreach($v as $kv=>$vv) {
            $gls[$gl_id]->records->$k[$kv] /= $r->nrec;
            if(!in_array($k,['fillin','spread','variat'])) $gls[$gl_id]->records->$k[$kv] = round($gls[$gl_id]->records->$k[$kv]);
          }
        }
        else {
          $gls[$gl_id]->records->$k = round($gls[$gl_id]->records->$k / $r->nrec);
        }
      }
    }
    if($inx) mysql_query(
    "UPDATE rr_glifetris
      SET
        stopped_at='".MRES($gls[$gl_id]->stopped_at)."',
        stopped_nturn='".($gls[$gl_id]->stopped_nturn)."',
        records='".($gls[$gl_id]->records ? json_encode($gls[$gl_id]->records) : "")."'
      WHERE id='$gl_id'
    ");
  }
  
  return $gls;
}

?>