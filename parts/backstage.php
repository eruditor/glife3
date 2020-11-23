<?

function GetFamilies() {
  static $families = [];
  if(!$families) {
    $res = mysql_query("SELECT * FROM rr_glfamilies");
    while($r = mysql_fetch_object($res)) $families[$r->id] = $r;
  }
  return $families;
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


function GlifeBigInfo($gls) {
  $families = GetFamilies();
  
  $single = false;
  
  if(!$gls) {
    return "";
  }
  elseif(is_string($gls)) {
    $res = mysql_query("SELECT * FROM rr_glifetris WHERE $gls");
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
            $v = round($json->$k[$z]);
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
        <h3>$nm (".($gl->typed?:"-").")</h3>
        ".$families[$gl->family_id]->name.": $gl->notaset<br>
        <i>".RN($gl->mutaset)."</i>
      </td>
      <td><table><tr>$srun</tr></table><br></td>
      </tr>
    ";
  }
  
  return "<table id='SavedListTB'>$s</table>";
}

?>