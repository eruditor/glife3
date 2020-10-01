<?

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

global $gl_bgc4records;
$gl_bgc4records = [
  'fillin' => [
      2 => "faa",
      5 => "fda",
     10 => "ff8",
     30 => "afa",
    999 => "ccc",
  ],
  'spread' => [
      5 => "faa",
     10 => "fda",
     30 => "ff8",
     60 => "afa",
     80 => "8ff",
    100 => "eee",
    999 => "ccc",
  ],
  'variat' => [
    1 => "ccc",
    5 => "faa",
    10 => "fda",
    50 => "ff8",
    100 => "afa",
    999 => "8ff",
  ],
];

function gl_Bgc4Records($k, $v) {
  global $gl_bgc4records;
  $bgc = '';
  foreach($gl_bgc4records[$k] as $x=>$c) {
    $bgc = $c;
    if($v<$x) break;
  }
  return $bgc;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function gl_AvgRuns($gl, $inx=false) {
  $gls = [];
  
  $res = mysql_query("SELECT * FROM rr_gliferuns WHERE gl_id='$gl->id' AND failed_at!='x'");
  while($r = mysql_fetch_object($res)) {
    $gl_id = $r->gl_id;
    $gls[$gl_id]->failed_at[$r->failed_at] ++;
    $gls[$gl_id]->failed_nturn += $r->failed_nturn;
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
    arsort($r->failed_at);  reset($r->failed_at);  $gls[$gl_id]->failed_at = key($r->failed_at);
    $gls[$gl_id]->failed_nturn = round($r->failed_nturn / $r->nn);
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
    "UPDATE rr_glifes
      SET
        failed_at='".MRES($gls[$gl_id]->failed_at)."',
        failed_nturn='".($gls[$gl_id]->failed_nturn)."',
        records='".($gls[$gl_id]->records ? json_encode($gls[$gl_id]->records) : "")."'
      WHERE id='$gl_id'
    ");
  }
  
  return $gls;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>