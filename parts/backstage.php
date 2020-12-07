<?

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


function GetCleanNamed() {
  static $named = [];
  if(!$named) {
    $res = mysql_query("SELECT * FROM rr_glifetris WHERE mutaset='' AND named<>'' ORDER BY family_id, named");
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


function GetClean($gl) {  // non-mutated glife list
  static $clean = [];
  if(!isset($clean[$gl->family_id][$gl->notaset])) {
    $clean[$gl->family_id][$gl->notaset] = mysql_o("SELECT * FROM rr_glifetris WHERE family_id='".MRES($gl->family_id)."' AND notaset='".MRES($gl->notaset)."' AND mutaset=''");
  }
  return $clean[$gl->family_id][$gl->notaset];
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
        <b><a href='$_self?gl_run=$r->id'>Run #$r->id</a></b>:
        ".glRecords::RecordsSpan($r, 'stopped_nturn')." / 
        ".glRecords::RecordsSpan($r, 'orga_sum')."
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
            $t .= "<td><span style='background:#".glRecords::Bgc4Records($k, $v)."'>$v%</span></td>";
          }
          $t .= "<td>".($ar_stopped[$z]?:"-")."</td>";
          foreach(['orga_num','orga_sum'] as $k) {
            $vv = $json->$k;
            $v = round($vv[$z]);
            $st = '';
            if($r->orgasum<0) { $v = -1;  $st = "color:#aaa;"; }
            $t .= "<td><span style='background:#".glRecords::Bgc4Records($k, $v).";$st'>".ShortenNumber($v)."</span></td>";
          }
          $stb .= "<tr><td>$z</td>$t</tr>";
        }
        $srun .= "
          <table cellspacing=0 id='glifeStatTB' class='nrrw'>
            <tr><th>z</th><th>fillin</th><th>spread</th><th>variat</th><th>stopped</th><th>orgaN</th><th>orga&Sigma;</th></tr>
            $stb
          </table>
        ";
      }
      $srun .= "</td>";
    }
    
    $nm = $gl->named ?: $gl->notaset;
    $nm = $single ? "<u>$nm</u>" : "<a href='$_self?glife=".($gl->named ? urlencode(SPCQA($gl->named)) : $gl->id)."'>$nm</a>";
    
    if($gl->mutaset) {
      $clean = GetClean($gl);
      $cleanstr = $clean->named ?: $clean->notaset;
      $cleanstr = "mutated <a href='$_self?glife=".($clean->named ?: $clean->id)."'>$cleanstr</a>";
    }
    else {
      $cleanstr = $gl->notaset;
    }
    
    $s .= "
      <tr><td>
        <h3 title='popularity=".round($gl->sumturns/1000)."'>$nm ".($gl->typed?"<span class=gr>($gl->typed)</span>":"")."</h3>
        ".$families[$gl->family_id]->name.": $cleanstr<br>
        <small class='nrrw gr'>".RN($gl->mutaset)."</small>
      </td>
      <td><table><tr>$srun</tr></table><br></td>
      <td>".GlifeEditInput($gl)."</td>
      </tr>
    ";
  }
  
  return "<table id='SavedListTB'>$s</table>";
}

function GlifeEditInput($r) {
  return _local==="1"
    ? "<span style='position:absolute;'><input type=text id='glrule$r->id' value='".SPCQA($r->named . ($r->typed?":$r->typed":""))."' size=16><input type=button value=' Save ' onclick='XHRsave3(`id=$r->id&named=`+encodeURIComponent(document.getElementById(`glrule$r->id`).value));'></span>"
    : "";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function GLifeJS($notaset='', $prms=[], $send2js = '') {
  $send2js .= "gl_bgc4records = JSON.parse(`" . json_encode(glRecords::$bgc4records) . "`);";
  
  $families = GetFamilies();
  $famnames = GetFamilies(true);
  
  if($notaset=='random') {
    $prms['randrules'] = 1;
  }
  elseif($notaset=='rerun') {
    $prms['rerun'] = 1;
    $prms['family'] = "Conway3D";
  }
  elseif(substr($notaset,0,8)=="anyrand_") {
    $prms['anyrand'] = 1;
    
    $fm = $famnames[substr($notaset, 8)];  if(!$fm) die("#874289734");
    $prms['family'] = $fm->name;
    
    $send = '';  $FD = 0;
    $res = mysql_query("SELECT * FROM rr_glifetris WHERE family_id='$fm->id' AND named<>'' AND mutaset=''");
    while($r = mysql_fetch_object($res)) {
      if(!$FD) $FD = GetFD4GL($r);
      $send .= "['".SPCQA($r->named)."', '$r->notaset'],\n";
    }
    $send2js .= "
      gl_cleannamed = [
        $send
      ];
    ";
    $prms['FD'] = $FD;
  }
  else {
    $gl = GetGL4Notaset($notaset);
    $prms['notaset'] = $gl->notaset;
    $prms['mutaset'] = $gl->mutaset;
    
    $FD = GetFD4GL($gl);
    $prms['FD'] = $FD;
    
    $fm = $families[$gl->family_id];
    $prms['family'] = $fm->name;
  }
  
  $rseed = $prms['rseed'] ?: intval($_GET['rseed']) ?: rand(1,getrandmax());
  $fseed = $prms['fseed'] ?: intval($_GET['fseed']) ?: rand(1,getrandmax());
  
  $jsget = "?v=$_ENV->ver";
  if(_local==="1") $jsget .= "&rnd=".rand(1,getrandmax());  // to refresh cached scripts every run
  
  $plus = '';  foreach($prms as $k=>$v) if($v) $plus .= "&".urlencode($k)."=".urlencode($v);
  
  return "
    <div id=GLifeCont></div>
    
    <script>$send2js</script>
    
    <script src='js/0.params.js$jsget&rseed=$rseed&fseed=$fseed$plus'></script>
    <script src='js/1.math.js$jsget'></script>
    <script src='js/2.hardware.js$jsget'></script>
    <script src='js/3.spacetime.js$jsget'></script>
    <script src='js/4.physics_laws.js$jsget'></script>
    <script src='js/5.dynamics.js$jsget'></script>
    <script src='js/6.visualisation.js$jsget'></script>
    <script src='js/7.analysis.js$jsget'></script>
    <script src='js/8.interface.js$jsget'></script>
    <script src='js/9.divine_forces.js$jsget'></script>
    <script src='main.js$jsget'></script>
  ";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>