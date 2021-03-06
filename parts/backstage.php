<?

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function AddFamilyFilter($view) {
  global $page;
  
  $family = glDicts::GetFamily($_GET['family']);
  $famQplus = $family ? "AND family_id='$family->id'" : "";
  $famUplus = $family ? "&family=$family->name" : "";
  if($family) $page->bread[] = [$family->name, "&family=$family->name"];
  
  $s = "| ";
  foreach(glDicts::GetFamilies() as $fam) {
    $t = $fam->id==$family->id ? "<u>$fam->name</u>" : "<a href='?view=$view&family=$fam->name'>$fam->name</a>";
    $s .= "$t | ";
  }
  $page->z .= "<h3>Family filter: $s</h3><hr>";
  
  return [$famQplus, $famUplus];
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Pagination {
  public $PP = 100;
  public $LL = 0;
  public $LP = 0;
  public $shwn = 0;
  public $nttl = 0;
  
  function __construct($pp=100) {
    global $page;
    $this->PP = intval($_GET['pp']) ?: $pp;
    $this->LL = intval($_GET['ll']);
    $this->LP = $this->LL * $this->PP;
    if($this->LL) $page->bread[] = ["page #$this->LL", "&ll=$this->LL"];
  }
  
  function Count($res) {
    $this->shwn = mysql_num_rows($res);
    $this->nttl = mysql_r("SELECT FOUND_ROWS()");
  }
  
  function AddPagination() {
    global $page;
    if($this->nttl<=$this->PP) return "";
    $q = '';  foreach($_GET as $k=>$v) if($k<>'ll') $q .= ($q?"&":"") . "$k=$v";
    $s = "
      <td width=160 style='text-align:right;' >" . ($this->LL>0 ? "<a href='?$q".($this->LL>1?"&ll=".($this->LL-1):"")."'>&larr; prev $this->PP</a>" : "") . "</td>
      <td width=240 style='text-align:center;'>shown ".($this->LP+1)." &ndash; ".($this->LP+$this->shwn)." / $this->nttl</td>
      <td width=160 style='text-align:left;'  >" . ($this->LP+$this->shwn<$this->nttl ? "<a href='?$q&ll=".($this->LL+1)."'>next $this->PP &rarr;</a>" : "") . "</td>
    ";
    $page->z .= "<br><div align=center><table><tr>$s</tr></table></div>";
  }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function GlifeBigInfo($gl, $q4runs='', $single=true) {
  if(!$gl) return "";
  
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
  
  $nm = GLtitle($gl);
  $nm = $single && !$q4runs ? "<u>$nm</u>" : "<a href='?glife=".($gl->named ? urlencode(SPCQA($gl->named)) : $gl->id)."'>$nm</a>";
  
  if($gl->mutaset) {
    $clean = glDicts::GetNonmutated($gl);
    $cleanstr = GLtitle($clean);
    $cleanstr = "mutated <a href='?glife=".($clean->named ?: $clean->id)."'>$cleanstr</a>";
  }
  else {
    $cleanstr = RN(str_replace(",", "\n", $gl->notaset));
  }
  
  $colspan = $tr = '';
  if(strlen($cleanstr)>80*$FD) {
    $colspan = " colspan=2";
    $tr = "</tr><tr>";
  }
  
  $s = "
    <tr><td$colspan>
      <h3 title='popularity=".round($gl->sumturns/1000)."'>$nm " . ($gl->typed?"<span class=gr>($gl->typed)</span>":"") . "</h3>
      " . glDicts::GetFamily($gl->family_id)->name . ":<br>
      <span class='nrrw'>$cleanstr</span><br>
      <small class='nrrw gr'>".ProcrustMutaset($gl->mutaset)."</small>
    </td>
    $tr
    <td><table><tr>$srun</tr></table><br></td>
    <td>".GlifeEditInput($gl)."</td>
    </tr>
  ";
  
  if($single) $s = "<a name='show'></a><table id='SavedListTB'>$s</table>";
  
  return $s;
}

function GlifeEditInput($r) {
  return _local==="1"
    ? "<span style='position:absolute;'>
         <input type=text id='glrule$r->id' value='".SPCQA($r->named . ($r->typed?":$r->typed":""))."' size=16><input type=button value=' Save ' onclick='XHRsave3(`id=$r->id&named=`+encodeURIComponent(document.getElementById(`glrule$r->id`).value));'><br>
         ".(!$r->named && !$r->typed ? "
           <a href='./gl_save.php?id=$r->id&typed=foam'>foam</a> &nbsp;
           <a href='./gl_save.php?id=$r->id&typed=amoeba'>amoeba</a> &nbsp;
           <a href='./gl_save.php?id=$r->id&typed=boil'>boil</a> &nbsp;
           <a href='./gl_save.php?id=$r->id&typed=train'>train</a>
         " : "")."
      </span>
      "
    : "";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function GLtitle($gl) {
  if($gl->named) return $gl->named;
  $s4nota = strlen($gl->notaset)>40 ? $gl->notamd5 : RNZ($gl->notaset);
  $s4muta = strlen($gl->mutaset)>40 ? $gl->mutamd5 : RNZ($gl->mutaset);
  $sfull = $s4nota . ($s4muta ? "+$s4muta" : "");
  return SPCQA($sfull);
}

function ProcrustMutaset($s, $max=0, $delim="<br>") {
  $nsymb = 8;
  if(!$max) $max = 2 * $nsymb;
  $ret = '';
  $ar = explode("\n", $s);
  foreach($ar as $a) {
    $t = strlen($a)<=$max ? $a : substr($a,0,$nsymb) . "&hellip;(" . strlen($a) . ")&hellip;" . substr($a,-$nsymb);
    $ret .= ($ret?$delim:"") . $t;
  }
  return $ret;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function GLifeJS($notaset='', $prms=[], $send2js = '') {
  if(!$notaset) {
    $notaset = $_GET['notaset'] ?: "Aphrodite";
  }
  
  $fm = null;  // family is required to run
  
  if($notaset=='random') {
    $prms['randrules'] = 1;
  }
  elseif($notaset=='rerun') {
    $prms['rerun'] = 1;
  }
  elseif($notaset=='repair') {
    $prms['repair'] = 1;
  }
  elseif(substr($notaset,0,8)=="anyrand_") {
    $prms['anyrand'] = 1;
    $fm = glDicts::GetFamily(substr($notaset,8));
    $send = '';  $FD = 0;
    $res = mysql_query("SELECT * FROM rr_glifetris WHERE family_id='$fm->id' AND named<>'' AND mutamd5=''");
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
  elseif($_GET['fm'] && $_GET['notaset']) {
    $fm_id = intval($_GET['fm']);  if(!$fm_id) die("#87234852");
    $fm = glDicts::GetFamily($fm_id);  if(!$fm) die("#87234853");
    $prms['FD'] = $fm->FD ?: 3;
    $prms['notaset'] = $notaset;
  }
  else {
    $fm_id = intval($prms['fm_id'] ?: $_GET['fm']);
    $gl = glDicts::GetGL4Notaset($notaset, $fm_id);  if(!$gl) dierr("incorrect notaset");
    $fm = glDicts::GetFamily($gl->family_id);
    $prms['FD'] = glDicts::GetFD($gl);
    $prms['notaset'] = $gl->notaset;
    //$prms['mutaset'] = $gl->mutaset;
    $send2js .= "Mutaset = '".str_replace("\n", "\\n", $gl->mutaset)."';\n";
  }
  
  if(!$fm) $fm = glDicts::GetFamily($prms['family'] ?: $_GET['family']);  if(!$fm) dierr("#48379230");
  $prms['family'] = $fm->name;
  $send2js .= "glFamily = JSON.parse(`" . json_encode($fm) . "`);\n";
  
  $rseed = $prms['rseed'] ?: intval($_GET['rseed']) ?: rand(1,getrandmax());
  $fseed = $prms['fseed'] ?: intval($_GET['fseed']) ?: rand(1,getrandmax());
  
  $jsget = "?v=$_ENV->ver";
  if(_local==="1") $jsget .= "&rnd=".rand(1,getrandmax());  // to refresh cached scripts every run
  
  $plus = '';  foreach($prms as $k=>$v) if($v) $plus .= "&".urlencode($k)."=".urlencode($v);
  
  $send2js .= "gl_bgc4records = JSON.parse(`" . json_encode(glRecords::$bgc4records) . "`);\n";
  
  return "
    <a name='cont'></a>
    <div id=GLifeCont></div>
    
    <script src='vendor/jquery-fft/jquery.fft.js'></script>
    <script src='//cdn.plot.ly/plotly-latest.min.js'></script>
    
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