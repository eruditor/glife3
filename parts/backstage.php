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
  'stopped_nturn' => [
    1000 => "faa",
    4000 => "fda",
    5000 => "afa",
    10000 => "8ff",
    99999 => "eee",
  ],
  'orga_num' => [
    0 => "eee",
    50 => "faa",
    100 => "fda",
    200 => "afa",
    400 => "8ff",
    800 => "afa",
    9999999 => "ccc",
  ],
  'orga_sum' => [
    0 => "eee",
    5000 => "faa",
    10000 => "fda",
    20000 => "afa",
    40000 => "8ff",
    60000 => "afa",
    9999999 => "ccc",
  ],
  'orga_avg' => [
    1 => "eee",
    20 => "faa",
    40 => "fda",
    60 => "afa",
    100 => "8ff",
    140 => "afa",
    9999999 => "ccc",
  ],
  'orga_z' => [
    1 => "faa",
    2 => "8ff",
    3 => "fda",
    9 => "ccc",
  ],
];

function gl_Bgc4Records($k, $v) {
  global $gl_bgc4records;
  $bgc = '';
  if(!$gl_bgc4records[$k]) return "fff";
  foreach($gl_bgc4records[$k] as $x=>$c) {
    $bgc = $c;
    if($v<$x) break;
  }
  return $bgc;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function GetFamilies($byname=false) {
  static $families = [], $famnames = [];
  if(!$families) {
    $res = mysql_query("SELECT * FROM rr_glfamilies");
    while($r = mysql_fetch_object($res)) {
      $families[$r->id] = $r;
      $famnames[$r->name] = $r;
    }
  }
  return $byname ? $famnames : $families;
}


function GetNamed() {
  static $named = [];
  if(!$named) {
    $res = mysql_query("SELECT * FROM rr_glifetris WHERE named<>''");
    while($r = mysql_fetch_object($res)) $named[$r->id] = $r;
  }
  return $named;
}


function GetGL4Notaset($notaset) {
  if(!$notaset) $notaset = "Aphrodite";
  
  if(is_numeric($notaset))             $fld = "id";
  elseif(strpos($notaset,":")===false) $fld = "named";
  else                                 $fld = "notaset";
  
  $gl = mysql_o("SELECT * FROM rr_glifetris WHERE $fld='".MRES($notaset)."'");  if(!$gl) die("incorrect notaset");
  
  return $gl;
}


function GetFD4GL($gl) {
  $FD = 0;
  if($gl->notaset) {
    $FD = count(explode(",", $gl->notaset));
  }
  else {
    $families = GetFamilies();
    $fm = $families[$gl->family_id];
    if($fm->FD) $FD = $fm->FD;
  }
  return $FD;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function GlifeBigInfo($gls, $q4runs='') {
  $families = GetFamilies();
  
  $single = false;
  
  if(!$gls) {
    return "";
  }
  elseif(is_string($gls)) {
    $res = mysql_query(
     "SELECT gl.*, COUNT(*) nruns, SUM(stopped_nturn) sumturns
      FROM rr_glifetris gl
      LEFT JOIN rr_glifetriruns gr ON gr.gl_id=gl.id
      WHERE $gls
      GROUP BY gl.id
      ORDER BY sumturns DESC, found_dt DESC
    ");
    $gls = [];
    while($gl = mysql_fetch_object($res)) {
      $gls[$gl->id] = $gl;
    }
  }
  elseif(is_object($gls)) {
    $gls = [$gls->id => $gls];
    $single = true;
  }
  
  $s = '';
  foreach($gls as $gl) {
    $FD = count(explode(",", $gl->notaset));
    
    $srun = '';
    $res2 = mysql_query("SELECT *, IF(orgasum>=0,1,0) has_orgasum FROM rr_glifetriruns gr WHERE gl_id='$gl->id' $q4runs ORDER BY has_orgasum DESC, id DESC LIMIT 3");
    while($r = mysql_fetch_object($res2)) {
      $srun .= "<td>";
      $srun .= "
        <b>Run #$r->id</b>:
        <span style='background:#".gl_Bgc4Records('stopped_nturn', $r->stopped_nturn)."'>$r->stopped_nturn</span>
        ".($r->orgasum>=0 ? "/ <span style='background:#".gl_Bgc4Records('orga_sum', $r->orgasum)."'>$r->orgasum</span>" : "")."
        <br>
      ";
      $ar_stopped = $r->stopped_at=='x' ? array_fill(0, $FD, 'x') : explode(";", $r->stopped_at);
      if($r->records) {
        $json = json_decode($r->records) ?: [];
        $stb = '';
        for($z=0; $z<$FD; $z++) {
          $t = '';
          foreach(['fillin','spread','variat'] as $k) {
            $vv = $json->$k;
            $v = round($vv[$z]);
            $t .= "<td><span style='background:#".gl_Bgc4Records($k, $v)."'>$v%</span></td>";
          }
          $t .= "<td>".($ar_stopped[$z]?:"-")."</td>";
          foreach(['orga_num','orga_sum'] as $k) {
            $vv = $json->$k;
            $v = round($vv[$z]);
            $st = '';
            if($r->orgasum<0) { $v = -1;  $st = "color:#aaa;"; }
            $t .= "<td><span style='background:#".gl_Bgc4Records($k, $v).";$st'>$v</span></td>";
          }
          $stb .= "<tr><td>$z</td>$t</tr>";
        }
        $srun .= "
          <table cellspacing=0 id='glifeStatTB'>
            <tr><th>z</th><th>fillin</th><th>spread</th><th>variat</th><th>stopped</th><th>orgaN</th><th>orga&Sigma;</th></tr>
            $stb
          </table>
        ";
      }
      $srun .= "</td>";
    }
    
    $nm = $gl->named ?: $gl->notaset;
    $nm = $single ? "<u>$nm</u>" : "<a href='$_self?glife=".($gl->named ? urlencode(SPCQA($gl->named)) : $gl->id)."'>$nm</a>";
    
    $s .= "
      <tr><td>
        <h3 title='popularity=".round($gl->sumturns/1000)."'>$nm ".($gl->typed?"<span class=gr>($gl->typed)</span>":"")."</h3>
        ".$families[$gl->family_id]->name.": $gl->notaset<br>
        <small class='nrrw gr'>".RN($gl->mutaset)."</small>
      </td>
      <td><table><tr>$srun</tr></table><br></td>
      </tr>
    ";
  }
  
  return "<table id='SavedListTB'>$s</table>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>