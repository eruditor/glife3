<?

$page->bread[] = ["Catalog", "?view=catalog"];

$page->zabst .= "
  “Catalog” is a categorized list of glifes.<br>
  The most interesting glifes are “named” and presented in the “<a href='$_self?view=gallery'>Gallery</a>”.<br>
  Those that are manually processed and categorized are “typed”, they are here on the left.<br>
  All others are devided into 3 categories (good, so-so and bad) by machine.<br>
";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$families = GetFamilies();
$famnames = GetFamilies(true);

$famname = $_GET['family'];
$family = $famnames[$famname];
$famQplus = $family ? "AND family_id='$family->id'" : "";
$famUplus = $family ? "&family=$family->name" : "";
if($family) $page->bread[] = [$family->name, "&family=$family->name"];

$s = "| ";
foreach($famnames as $fam) {
  $t = $fam->id==$family->id ? "<u>$fam->name</u>" : "<a href='$_self?view=catalog&family=$fam->name'>$fam->name</a>";
  $s .= "$t | ";
}
$page->z .= "<h3>Family filter: $s</h3><hr>";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$goodnesses = [
  0 => ['id'=>0, 'clr'=>"4ff", 'nam'=>"manually processed", 'wh'=>""],
  1 => ['id'=>1, 'clr'=>"8f8", 'nam'=>"good",  'wh'=>"stopped_nturn>=5000"],
  2 => ['id'=>2, 'clr'=>"ff8", 'nam'=>"so-so", 'wh'=>"stopped_nturn>=1000 AND stopped_nturn<5000"],
  3 => ['id'=>3, 'clr'=>"f88", 'nam'=>"bad",   'wh'=>"stopped_nturn<1000"],
];

$typeds = [
  "foam"=>"efe", "brain"=>"efe", "vores"=>"efe", "cyclic"=>"efe", "train"=>"efe", ""=>"efe",
  "amoeba"=>"ffd", "holey"=>"ffd", "shoal"=>"ffd", "vapor"=>"ffd", "exglider"=>"ffd", "?"=>"ffd",
  "boil"=>"fee", "extin"=>"fee", "conway"=>"fee", "blink"=>"fee", "gas"=>"fee", "kia"=>"fee",
];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($_GET['typed']) {
  $typed = $_GET['typed'];  if(!isCorrectID($typed)) dierr("#179236742");
  $otyped = $typeds[$typed];  if(!$otyped) dierr("#9187543243");
  
  $page->bread[] = ["<i style='background:#".$otyped."'>$typed</i>", "&typed=$typed"];
  
  $PP = 100;  $LL = intval($_GET['ll']);  $LP = $LL * $PP;
  
  $s = '';
  $res = mysql_query(
   "SELECT SQL_CALC_FOUND_ROWS *
    FROM rr_glifetris
    WHERE typed='$typed' $famQplus
    ORDER BY id DESC
    LIMIT $LP,$PP
  ");
  $shwn = mysql_num_rows($res);
  $nttl = mysql_r("SELECT FOUND_ROWS()");
  while($r = mysql_fetch_object($res)) {
    $s .= "
      <tr>
        <td align=right><a href='$_self?glife=$r->id'>$r->id</a></td>
        <td>".$families[$r->family_id]->name."</td>
        <td><a href='$_self?notaset=$r->notaset&maxfps=300'>$r->notaset</a></td>
        <td class=nrrw>".($r->mutaset?"<a href='$_self?notaset=$r->notaset&mutaset=$r->mutaset&maxfps=300'>".RN($r->mutaset)."</a>":"")."</td>
        <td><a href='$_self?glife=".urlencode($r->named)."&maxfps=300'><i>$r->named</i></a></td>
        <td>$r->typed</td>
        <td>$r->found_dt</td>
        <td>".GlifeEditInput($r)."</td>
      </tr>
    ";
  }
  $page->z .= "
    <table cellspacing=0 id='SavedListTB'>
      <tr>
        <th>id</th><th>family</th><th>notaset</th><th>mutaset</th><th>named</th><th>typed</th><th>datetime</th><th></th>
      </tr>
      $s
    </table>
  ";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif(isset($_GET['stopped'])) {
  $stopped  = $_GET['stopped'];  if(!isCorrectVar($stopped)) dierr("#47592374");
  $goodness = intval($_GET['goodness']);  if(!$goodness) dierr("#74428766");
  $ogoodness = $goodnesses[$goodness];  if(!$ogoodness) dierr("#109743282");
  
  $page->bread[] = [($stopped?:"---") . " (".$ogoodness['nam'].")", "&stopped=$stopped&goodness=".$ogoodness['id']];
  
  $PP = 100;  $LL = intval($_GET['ll']);  $LP = $LL * $PP;
  if($LL) $page->bread[] = ["page #$LL", "&ll=$LL"];
  
  $s = '';
  $res = mysql_query(
   "SELECT SQL_CALC_FOUND_ROWS gr.*, gl.*, gr.id gr_id
    FROM rr_glifetriruns gr
    JOIN rr_glifetris gl ON gl.id=gr.gl_id
    WHERE stopped_at='".MRES($stopped)."' AND stopped_nturn<10000 AND ".$ogoodness['wh']." $famQplus
    ORDER BY gr.id DESC
    LIMIT $LP,$PP
  ");
  $shwn = mysql_num_rows($res);
  $nttl = mysql_r("SELECT FOUND_ROWS()");
  while($r = mysql_fetch_object($res)) {
    $a_notaset = explode(",",  $r->notaset);
    $a_mutaset = explode("\n", $r->mutaset);
    $a_stopped = explode(";",  trim($r->stopped_at, ";"));
    $a_records = [];
    if($r->records) {
      $records = json_decode($r->records);
      foreach(['fillin','spread','variat'] as $k) {
        if(!$records->$k) continue;
        foreach($records->$k as $z=>$v) {
          $v = round($v);
          $bgc = glRecords::Bgc4Records($k, $v);
          $t = $v>=100 ? "AA" : sprintf("%'.02d", $v);
          $a_records[$k][$z] = "<span style='background:#$bgc'>$t</span>";
        }
      }
    }
    $FD = count($a_notaset);
    
    $tr = '';
    for($z=0; $z<$FD; $z++) {
      $tr .= "<tr>";
      if($z==0) {
        $tr .= "
          <td rowspan=$FD>
            <a href='$_self?glife=$r->gl_id'>$r->gl_id</a><br>
            $r->gr_id
          </td>
          <td rowspan=$FD class=nrrw>$r->dt</td>
          <td rowspan=$FD>".$families[$r->family_id]->name."</td>
          <td rowspan=$FD>".($r->named ? "<a href='$_self?glife=".urlencode($r->named)."&maxfps=300'><i>$r->named</i></a>" : "")."</td>
          <td rowspan=$FD>$r->typed</td>
        ";
      }
      $tr .= "
          <td>{$a_notaset[$z]}</td>
          <td class=nrrw>{$a_mutaset[$z]}</td>
          <td>".($z==0?"$r->stopped_nturn":"")."</td>
          <td>{$a_stopped[$z]}</td>
          <td class=tar>{$a_records['fillin'][$z]}</td>
          <td class=tar>{$a_records['spread'][$z]}</td>
          <td class=tar>{$a_records['variat'][$z]}</td>
      ";
      if($z==0 && _local==="1") {
        $tr .= "
          <td rowspan=$FD>
            <input type=text id='glrule$r->id' value='".SPCQA($r->named . ($r->typed?":$r->typed":""))."' size=24><input type=button value=' Save ' onclick='XHRsave3(`id=$r->id&named=`+encodeURIComponent(document.getElementById(`glrule$r->id`).value));'>
          </td>
        ";
      }
      $tr .= "</tr>";
    }
    
    $s .= $tr . "<tr><td><br></td></tr>";;
  }
  
  $page->z .= "
    <table cellspacing=0 id='SavedListTB'>
      <tr>
        <th>gl_id<br>run_id</th><th>datetime</th><th>family</th><th>named</th><th>typed</th>
        <th>notaset</th>
        <th>mutaset</th>
        <th>nturn</th>
        <th>stopped</th>
        <th>fil</th>
        <th>spr</th>
        <th>var</th>
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
    $page->z .= "<br><div align=center><table><tr>$s</tr></table></div>";
  }
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
else {
  $s = '';  $ss = [];
  
  $res = mysql_query(
  "SELECT typed, COUNT(*) nn
    FROM rr_glifetris
    WHERE (named<>'' OR typed<>'') $famQplus
    GROUP BY typed
    ORDER BY nn DESC
  ");
  while($r = mysql_fetch_object($res)) {
    $ss[0] .= "
      <tr style='background:#".$typeds[$r->typed]."'>
      <td><a href='$_self?view=catalog$famUplus&typed=$r->typed'>".($r->typed?:"-?-")."</a></td>
      <td align=right>$r->nn</td>
      </tr>
    ";
  }
  
  $res = mysql_query(
  "SELECT stopped_at, IF(stopped_nturn>=5000, 1, IF(stopped_nturn>=1000, 2, 3)) goodness, COUNT(*) nn
    FROM rr_glifetriruns gr
    JOIN rr_glifetris gl ON gl.id=gr.gl_id
    WHERE stopped_at!='x' $famQplus
    GROUP BY stopped_at, goodness
    ORDER BY goodness, nn DESC
  ");
  while($r = mysql_fetch_object($res)) {
    $ss[$r->goodness] .= "
      <tr>
        <td><a href='$_self?view=catalog$famUplus&stopped=$r->stopped_at&goodness=$r->goodness'>".($r->stopped_at?:"---")."</a></td>
        <td align=right>$r->nn</td>
      </tr>
    ";
  }
  
  foreach($goodnesses as $ogoodness) {
    $th1 = strtoupper($ogoodness['nam']);
    if($ogoodness['id']==0) { $th2 = "type";      $th3 = "genoms"; }
    else                    { $th2 = "category";  $th3 = "runs";   }
    $s .= "
      <td width=20%>
        <table cellspacing=0 width=100%>
          <tr><th colspan=2 style='background:#".$ogoodness['clr']."'>$th1</th></tr>
          <tr><th>$th2</th><th style='text-align:right'>$th3</th></tr>
          ".$ss[$ogoodness['id']]."
        </table>
      </td>
      <td width=5%>&nbsp;</td>
    ";
  }
  $page->z .= "
    <table cellspacing=0 id='SavedListTB'><tr>
      $s
    </tr></table>
  ";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>