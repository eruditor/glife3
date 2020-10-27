<? define("_root","../../");
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
include(_root."page.php");
$page->type = "page";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$p = mysql_o("SELECT * FROM rr_pages WHERE typ='k' AND url='alife'");

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$ver = 302;

$otitle = "GLife3";
$h1 = "";
$zabst = "
  Life game on WebGL2.<br>
";
$zzt = "";
$zpubd = "2020-10-01";

$send2js = '';

include_once("lib/service.php");

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($_GET['savedcat']) || isset($_GET['typed']) || isset($_GET['named']) || isset($_GET['savedid'])) {
  if(isset($_GET['typed'])) {
    $typed = $_GET['typed'];
    if($typed) {
      $stitle = "Typed «".SPCQA($typed)."»";
      $Q = "typed='".MRES($typed)."'";
    }
    else {
      $stitle = "Named Un-Typed";
      $Q = "typed='' AND named<>''";
    }
  }
  elseif($_GET['named']=="all") {
    $stitle = "All Named";
    $Q = "named<>''";
  }
  elseif($_GET['savedid']) {
    $savedid = intval($_GET['savedid']);
    $stitle = "All Named";
    $Q = "id='$savedid'";
  }
  else {
    $savedcat = SPCQA($_GET['savedcat']);
    $goodness = intval($_GET['goodness']);
    $stitle = "Category «".SPCQA($savedcat)."»";
    if($_GET['ll']) $stitle = "<a href='$_self?savedcat=$savedcat".($goodness?"&goodness=$goodness":"")."'>$stitle</a>";
    $Q = "failed_at='".MRES($savedcat)."'";
        if($goodness==1) $Q .= " AND failed_nturn>=5000";
    elseif($goodness==2) $Q .= " AND failed_nturn>=1000 AND failed_nturn<5000";
    elseif($goodness==3) $Q .= " AND failed_nturn<1000";
  }
  
  $PP = 100;  $LL = intval($_GET['ll']);  $LP = $LL * $PP;
  
  $page->title = "Alife: $otitle: Saved Genoms: ".strip_tags($stitle)." – ERUDITOR.RU";
  $h1 = "<a href='/k/?alife'>Alife</a> &rarr; <a href='/alife/glife3/'>$otitle</a> &rarr; <a href='$_self?savedlist=1'>Saved Genoms</a> &rarr; $stitle";
  
  $s = '';
  $res = mysql_query(
   "SELECT SQL_CALC_FOUND_ROWS *
    FROM rr_glifes
    WHERE $Q
    ORDER BY id DESC
    LIMIT $LP,$PP
  ");
  $shwn = mysql_num_rows($res);
  $nttl = mysql_r("SELECT FOUND_ROWS()");
  while($r = mysql_fetch_object($res)) {
    $s4records = [];
    if($r->records) {
      $records = json_decode($r->records);
      foreach(['fillin','spread','variat'] as $k) {
        if(!$records->$k) continue;
        foreach($records->$k as $z=>$v) {
          $v = round($v);
          $bgc = gl_Bgc4Records($k, $v);
          $t = $v>=100 ? "AA" : sprintf("%'.02d", $v);
          $s4records[$k] .= "<span style='background:#$bgc;'>" . $t . "</span> ";
        }
      }
    }
    $s .= "
      <tr>
        <td>$r->id</td>
        <td><a href='$_self?ruleset=$r->rules&maxfps=300'>$r->rules</a></td>
        <td><a href='$_self?ruleset=$r->named&maxfps=300'><i>$r->named</i></a></td>
        <td>$r->typed</td>
        <td>$r->found_dt</td>
        <td>$r->failed_at</td>
        <td>$r->failed_nturn</td><td></td>
        <td class=tar>{$s4records['fillin']}</td><td></td>
        <td class=tar>{$s4records['spread']}</td><td></td>
        <td class=tar>{$s4records['variat']}</td><td></td>
        ".(_local==="1" ? "
          <td>
            <input type=text id='glrule$r->id' value='".SPCQA($r->named . ($r->typed?":$r->typed":""))."' size=24><input type=button value=' Save ' onclick='XHRsave(`id=$r->id&named=`+encodeURIComponent(document.getElementById(`glrule$r->id`).value));'>
          </td>
        " : "")."
      </tr>
    ";
  }
  $zzt = "
    <style>
      #SavedListTB TD, #SavedListTB TH {font:normal 11px/13px arial; padding:1px 3px; vertical-align:top;}
      #SavedListTB TH {text-align:left; font-weight:bold;}
      #SavedListTB TD INPUT {font:normal 11px/11px arial; padding:0;}
      #SavedListTB TD.tar {text-align:right; font:normal 11px/11px Lucida Console, Monaco, Monospace;}
    </style>
    <table cellspacing=0 id='SavedListTB'>
      <tr>
        <th>id</th><th>genom</th><th>named</th><th>typed</th><th>datetime</th><th>category</th><th>nturn</th><th></th>
        <th>fillin</th><th></th>
        <th>spread</th><th></th>
        <th>variat</th><th></th>
      </tr>
      $s
    </table>
  ";
  if($nttl>$PP) {  // pagination
    $q = '';
    foreach($_GET as $k=>$v) if($k<>'ll') $q .= ($q?"&":"") . "$k=$v";
    $s = '';
    $s .= "<td width=160>" . ($LL>0 ? "<a href='$_self?$q&ll=".($LL-1)."'>&larr; prev $PP</a>" : "") . "</td>";
    $s .= "<td width=240>shown $shwn / $nttl</td>";
    $s .= "<td width=160>" . ($LP+$shwn<$nttl ? "<a href='$_self?$q&ll=".($LL+1)."'>next $PP &rarr;</a>" : "") . "</td>";
    $zzt .= "<br><div align=center><table><tr>$s</tr></table></div>";
  }
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif($_GET['savedlist']) {
  $stitle = "Saved Genoms: Category List";
  $page->title = "Alife: $otitle: $stitle – ERUDITOR.RU";
  $h1 = "<a href='/k/?alife'>Alife</a> &rarr; <a href='/alife/glife3/'>$otitle</a> &rarr; $stitle";
  
  $s = '';  $ss = [];
  $clr4cat = [0=>"4ff", 1=>"8f8", 2=>"ff8", 3=>"f88"];
  $clr4nmd = [
    "foam"=>"efe", "brain"=>"efe", "vores"=>"efe", "cyclic"=>"efe", ""=>"efe",
    "amoeba"=>"ffd", "holey"=>"ffd", "shoal"=>"ffd", "vapor"=>"ffd", "?"=>"ffd",
    "boil"=>"fee", "extin"=>"fee", "conway"=>"fee", "blink"=>"fee", "gas"=>"fee", "kia"=>"fee",
  ];
  
  $named_nn = mysql_r("SELECT COUNT(*) FROM rr_glifes WHERE named<>''");
  $ss[0] .= "
      <tr style='background:#dff;'>
       <td><a href='$_self?named=all'><i>all named</i></a></td>
       <td align=right>$named_nn</td>
      </tr>
  ";
  
  $res = mysql_query(
   "SELECT typed, COUNT(*) nn
    FROM rr_glifes
    WHERE named<>'' OR typed<>''
    GROUP BY typed
    ORDER BY nn DESC
  ");
  while($r = mysql_fetch_object($res)) {
    $ss[0] .= "
      <tr style='background:#".$clr4nmd[$r->typed].";'>
       <td><a href='$_self?typed=$r->typed'>".($r->typed?:"-?-")."</a></td>
       <td align=right>$r->nn</td>
      </tr>
    ";
  }
  
  $res = mysql_query(
   "SELECT failed_at, IF(failed_nturn>=5000, 1, IF(failed_nturn>=1000, 2, 3)) goodness, COUNT(*) nn
    FROM rr_glifes
    WHERE 1
    GROUP BY failed_at, goodness
    ORDER BY goodness, nn DESC
  ");
  while($r = mysql_fetch_object($res)) {
    $ss[$r->goodness] .= "
      <tr>
        <td><a href='$_self?savedcat=$r->failed_at&goodness=$r->goodness'>".($r->failed_at?:"---")."</a></td>
        <td align=right>$r->nn</td>
      </tr>
    ";
  }
  
  for($goodness=0; $goodness<=3; $goodness++) {
    if($goodness==0) {
      $th1 = "manually selected";
      $th2 = "type";
    }
    else {
      $th1 = "goodness = $goodness";
      $th2 = "category";
    }
    $s .= "
      <td width=20%>
        <table cellspacing=0 width=100%>
          <tr><th colspan=2 style='background:#".$clr4cat[$goodness].";'>$th1</th></tr>
          <tr><th>$th2</th><th style='text-align:right;'>genoms</th></tr>
          ".$ss[$goodness]."
        </table>
      </td>
      <td width=5%>&nbsp;</td>
    ";
  }
  $zzt = "
    <style>
      #SavedListTB TD, #SavedListTB TH {font:normal 11px/13px arial; padding:1px 3px; vertical-align:top;}
      #SavedListTB TH {text-align:left; font-weight:bold;}
    </style>
    <table cellspacing=0 id='SavedListTB'><tr>
      $s
    </tr></table>
  ";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
else {
  $ruleset = '';
  
  ////////////////////////////////////////////////////////////////
  if($_GET['ruleset']) {
    $ruleset = $_GET['ruleset'];
    
    if(strpos($ruleset, "[")!==false) { $q = "rules='".MRES($ruleset)."'";  $isnamed = false; }
    else                              { $q = "named='".MRES($ruleset)."'";  $isnamed = true; }
    
    $gl = mysql_o("SELECT * FROM rr_glifes WHERE $q");
    
    $s = '';
    if($gl) {
      $r = $gl;
      $FD = count(explode("],[", $r->rules));
      $s .= "
        <b>GLife #$r->id ({$FD}D)</b><br>
        ".($r->named ? "named = ".SPCQA($r->named)."<br>" : "")."
        ".($r->typed ? "typed = ".SPCQA($r->typed)."<br>" : "")."
        ".($gl->named && $isnamed ? "genom = ".SPCQA($gl->rules)."<br>" : "")."
        ".($r->failed_at ?: "---")." / $r->failed_nturn<br>
      ";
      if($r->records) {
        $json = json_decode($r->records) ?: [];
        $stb = '';
        for($z=0; $z<$FD; $z++) {
          $t = '';
          foreach(['fillin','spread','variat'] as $k) {
            $v = round($json->$k[$z]);
            $t .= "<td><span style='background:#".gl_Bgc4Records($k, $v).";'>$v%</span></td>";
          }
          $stb .= "<tr><td>$z</td>$t</tr>";
        }
        $s .= "
          <table cellspacing=0 id='glifeStatTB'>
            <tr><th>z</th><th>fillin</th><th>spread</th><th>variat</th></tr>
            $stb
          </table>
        ";
      }
      $nruns = mysql_r("SELECT COUNT(*) FROM rr_gliferuns WHERE gl_id='$gl->id'");
      if($nruns>1) {
        if($_GET['allruns']) $s .= "<div><a href='$_self?ruleset=$ruleset&maxfps=300&paused=1'>Hide all runs</a></div>";
        else                 $s .= "<div><a href='$_self?ruleset=$ruleset&maxfps=300&paused=1&allruns=1'>Show last runs</a></div>";
      }
      $s .= "<br>";
      
      if($_GET['allruns']) {
        $res = mysql_query("SELECT * FROM rr_gliferuns WHERE gl_id='$gl->id' ORDER BY id DESC LIMIT 5");
        while($r = mysql_fetch_object($res)) {
          $s .= "
            <b>Run #$r->id</b><br>
            ".($r->named ? "named = ".SPCQA($r->named)."<br>" : "")."
            ".($r->typed ? "typed = ".SPCQA($r->typed)."<br>" : "")."
            ".($r->failed_at ?: "---")." / $r->failed_nturn<br>
          ";
          if($r->records) {
            $json = json_decode($r->records) ?: [];
            $stb = '';
            for($z=0; $z<$FD; $z++) {
              $t = '';
              foreach(['fillin','spread','variat'] as $k) {
                $v = round($json->$k[$z]);
                $t .= "<td><span style='background:#".gl_Bgc4Records($k, $v).";'>$v%</span></td>";
              }
              $stb .= "<tr><td>$z</td>$t</tr>";
            }
            $s .= "
              <table cellspacing=0 id='glifeStatTB'>
                <tr><th>z</th><th>fillin</th><th>spread</th><th>variat</th></tr>
                $stb
              </table>
            ";
          }
          $s .= "<br>";
        }
      }
      $zzt .= "<div class=stxt>$s</div>";
    }
    $stitle = SPCQA($ruleset);
  }
  ////////////////////////////////////////////////////////////////
  elseif($_GET['rerun']) { // rerun old runs to calc gl.records field
    usleep(200000);
    $q = '';
        if($_GET['rerun']=='typed') $q = "gl.typed<>''";
    elseif($_GET['rerun']=='5000')  $q = "gl.failed_nturn>=5000";
    else die("err. #478126332");
    $res = mysql_query("
      SELECT gl.*, gr.id
      FROM rr_glifes gl
      LEFT JOIN rr_gliferuns gr ON gr.gl_id=gl.id AND gr.records<>''
      WHERE $q
      GROUP BY gl.id
      HAVING gr.id IS NULL
      LIMIT 1
    ");
    while($r = mysql_fetch_object($res)) {
      $ruleset = $r->rules;
    }
    if(!$ruleset) exit("rerun finished");
    $stitle = "Rerun = " . SPCQA($ruleset);
  }
  ////////////////////////////////////////////////////////////////
  
  $send2js .= "gl_bgc4records = JSON.parse(`" . json_encode($gl_bgc4records) . "`);";
  
  $s = '';
  $res = mysql_query("SELECT * FROM rr_glifes WHERE named<>''");
  while($r = mysql_fetch_object($res)) {
    $s .= "'$r->named': '$r->rules',\n";
  }
  $send2js .= "
    NamedRules = {
      'Debug': '[[37:23],[36:125],[3:23],]',
      $s
    }
  ";
  
  ////////////////////////////////////////////////////////////////
  
  $page->title = "Alife: $otitle".($stitle ? ": ".$stitle : "")." – ERUDITOR.RU";
  
  $h1 = "<a href='/k/?alife'>Alife</a> &rarr; " . ($_GET ? "<a href='/alife/glife3/'>$otitle</a>" : $otitle . " <span>v".sprintf("%.2lf", $ver/100)."</span>") . ($stitle ? " &rarr; ".$stitle : "");
  
  $zabst .= "&rarr; <a href='$_self?savedlist=1'>Database of Genoms found in random-search</a><br>";
  
  ////////////////////////////////////////////////////////////////
  
  $fseed = intval($_GET['fseed']) ?: rand(1,getrandmax());
  $rseed = intval($_GET['rseed']) ?: rand(1,getrandmax());
  
  $jsget = "?v=$ver&fseed=$fseed&rseed=$rseed";
  
  $zzt .= "
    <style>
      CANVAS {vertical-align:top; background:#ccc; cursor:crosshair; width:400px; height:100px;}
      DIV#statcanvas {white-space:normal;}
      #topbar {width:400px; text-align:left; padding:0 0 0 0;}
      #pausebtn, #pausestatbtn {width:100px;}
      #topform {float:right; font:normal 14px/17px Arial;}
    </style>
    
    <div id='topbar'>
      <form method=GET action='$_self' id='topform'></form>
    </div>
    
    <canvas id='cnv'></canvas><br>
    
    <div id='stxtfps'    class='stxt' style='margin-bottom:10px;'></div>
    <div id='stxtstat'   class='stxt'></div>
    <div id='statcanvas' class='stxt'></div>
    <div id='stxtlog'    class='stxt'></div>
    
    <script>$send2js</script>
    
    <script src='lib/0.params.js$jsget".($ruleset?"&ruleset=".urlencode($ruleset):"")."'></script>
    <script src='lib/1.math.js$jsget'></script>
    <script src='lib/2.hardware.js$jsget'></script>
    <script src='lib/3.spacetime.js$jsget'></script>
    <script src='lib/4.physics_laws.js$jsget'></script>
    <script src='lib/5.dynamics.js$jsget'></script>
    <script src='lib/6.visualisation.js$jsget'></script>
    <script src='lib/7.analysis.js$jsget'></script>
    <script src='lib/8.interface.js$jsget'></script>
    <script src='lib/9.divine_forces.js$jsget'></script>
    <script src='main.js$jsget'></script>
  ";
}

$page->z .= "
  <style>
    DIV.stxt {font:normal 11px/11px Lucida Console, Monaco, Monospace; margin-top:5px; white-space:nowrap;}
    #glifeStatTB {border:solid 2px #ddd; margin:0 0 10px 0;}
    #glifeStatTB TD, #glifeStatTB TH {padding:2px 4px; text-align:right;}
    #glifeStatTB TH {background:#f4f4f4; border-bottom:solid 1px #ddd;}
  </style>
";

$page->z .= "
  <h1>$h1</h1>
  
  <div class=zabst>
    $zabst
  </div>
  
  <div class=zzt>
    $zzt
  </div>
  
  <div class=zauth><span title='Àâòîð'>&copy; </span>".GetAuthorName($p->author)."</div>
  
  <div class=zpubd>$zpubd</div>
";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

MakePage();

?>