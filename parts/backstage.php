<?

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function AddFamilyFilter() {
  global $page;
  
  $family = glDicts::GetFamily($_GET['family']);
  $famQplus = $family ? "AND family_id='$family->id'" : "";
  $famUplus = $family ? "&family=$family->name" : "";
  if($family) $page->bread[] = [$family->name, "&family=$family->name"];
  
  $s = "| ";
  foreach(glDicts::GetFamilies() as $fam) {
    $t = $fam->id==$family->id ? "<u>$fam->name</u>" : "<a href='?view=stadium&family=$fam->name'>$fam->name</a>";
    $s .= "$t | ";
  }
  $page->z .= "<h3>Family filter: $s</h3><hr>";
  
  return [$famQplus, $famUplus];
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function GlifeBigInfo($gls, $q4runs='') {
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
    $FD = glDicts::GetFD($gl);
    
    $srun = '';
    $res2 = mysql_query("SELECT *, IF(orgasum>=0,1,0) has_orgasum FROM rr_glifetriruns gr WHERE gl_id='$gl->id' $q4runs ORDER BY has_orgasum DESC, id DESC LIMIT 3");
    while($r = mysql_fetch_object($res2)) {
      $srun .= "<td>";
      $srun .= "
        <b><a href='?gl_run=$r->id'>Run #$r->id</a></b>:
        ".glRecords::RecordsSpan($r, 'stopped_nturn')." / 
        ".glRecords::RecordsSpan($r, 'orga_sum')."
        <br>
      ";
      $ar_stopped = $r->stopped_at=='x' ? array_fill(0, $FD, 'x') : explode(";", $r->stopped_at);
      if($r->records) {
        $json = json_decode($r->records) ?: (object)[];
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
    $nm = $single ? "<u>$nm</u>" : "<a href='?glife=".($gl->named ? urlencode(SPCQA($gl->named)) : $gl->id)."'>$nm</a>";
    
    if($gl->mutaset) {
      $clean = glDicts::GetNonmutated($gl);
      $cleanstr = $clean->named ?: $clean->notaset;
      $cleanstr = "mutated <a href='?glife=".($clean->named ?: $clean->id)."'>$cleanstr</a>";
    }
    else {
      $cleanstr = $gl->notaset;
    }
    
    $s .= "
      <tr><td>
        <h3 title='popularity=".round($gl->sumturns/1000)."'>$nm ".($gl->typed?"<span class=gr>($gl->typed)</span>":"")."</h3>
        ".glDicts::GetFamily($gl->family_id)->name.": $cleanstr<br>
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
  if(!$notaset) $notaset = "Aphrodite";
  
  if($notaset=='random') {
    $prms['randrules'] = 1;
  }
  elseif($notaset=='rerun') {
    $prms['rerun'] = 1;
    $prms['family'] = "Conway3D";
  }
  elseif(substr($notaset,0,8)=="anyrand_") {
    $prms['anyrand'] = 1;
    
    $fm = glDicts::GetFamily(substr($notaset, 8));  if(!$fm) die("#874289734");
    $prms['family'] = $fm->name;
    
    $send = '';  $FD = 0;
    $res = mysql_query("SELECT * FROM rr_glifetris WHERE family_id='$fm->id' AND named<>'' AND mutaset=''");
    while($r = mysql_fetch_object($res)) {
      if(!$FD) $FD = glDicts::GetFD($r);
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
    $gl = glDicts::GetGL4Notaset($notaset);  if(!$gl) die("incorrect notaset");
    $prms['notaset'] = $gl->notaset;
    $prms['mutaset'] = $gl->mutaset;
    $prms['FD'] = glDicts::GetFD($gl);
    $prms['family'] = glDicts::GetFamily($gl->family_id)->name;
  }
  
  $rseed = $prms['rseed'] ?: intval($_GET['rseed']) ?: rand(1,getrandmax());
  $fseed = $prms['fseed'] ?: intval($_GET['fseed']) ?: rand(1,getrandmax());
  
  $jsget = "?v=$_ENV->ver";
  if(_local==="1") $jsget .= "&rnd=".rand(1,getrandmax());  // to refresh cached scripts every run
  
  $plus = '';  foreach($prms as $k=>$v) if($v) $plus .= "&".urlencode($k)."=".urlencode($v);
  
  $send2js .= "gl_bgc4records = JSON.parse(`" . json_encode(glRecords::$bgc4records) . "`);";
  
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