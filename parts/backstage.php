<?

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


function GlifeBigInfo($gls) {
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
    $res2 = mysql_query("SELECT * FROM rr_glifetriruns WHERE gl_id='$gl->id' ORDER BY id DESC LIMIT 3");
    while($r = mysql_fetch_object($res2)) {
      $srun .= "<td>";
      $srun .= "
        <b>Run #$r->id</b>: $r->stopped_nturn / $r->orgasum<br>
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
            $t .= "<td><span style='background:#".gl_Bgc4Records($k, $v).";'>$v%</span></td>";
          }
          $t .= "<td>".($ar_stopped[$z]?:"-")."</td>";
          $stb .= "<tr><td>$z</td>$t</tr>";
        }
        $srun .= "
          <table cellspacing=0 id='glifeStatTB'>
            <tr><th>z</th><th>fillin</th><th>spread</th><th>variat</th><th>stopped</th></tr>
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
        <span class='nrrw gr'>".RN($gl->mutaset)."</span>
      </td>
      <td><table><tr>$srun</tr></table><br></td>
      </tr>
    ";
  }
  
  return "<table id='SavedListTB'>$s</table>";
}

?>