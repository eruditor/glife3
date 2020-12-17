<?

$page->bread[] = ["Catalog", "?view=catalog"];

$page->zabst .= "
  “Catalog” is a categorized list of glifes.<br>
  The most interesting glifes are “named” and presented in the “<a href='?view=gallery'>Gallery</a>”.<br>
  Those that are manually processed and categorized are “typed”, they are here on the left.<br>
  All others are devided into 3 categories (good, so-so and bad) by machine.<br>
";

list($famQplus, $famUplus) = AddFamilyFilter('catalog');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$goodnesses = [
  0 => ['id'=>0, 'clr'=>"4ff", 'nam'=>"manually processed", 'wh'=>""],
  1 => ['id'=>1, 'clr'=>"8f8", 'nam'=>"good",  'wh'=>"stopped_nturn>=5000"],
  2 => ['id'=>2, 'clr'=>"ff8", 'nam'=>"so-so", 'wh'=>"stopped_nturn>=1000 AND stopped_nturn<5000"],
  3 => ['id'=>3, 'clr'=>"f88", 'nam'=>"bad",   'wh'=>"stopped_nturn<1000"],
];

$typeds = [
  "foam"=>"efe", "brain"=>"efe", "vores"=>"efe", "cyclic"=>"efe", "train"=>"efe", ""=>"efe",
  "amoeba"=>"ffd", "holey"=>"ffd", "shoal"=>"ffd", "vapor"=>"ffd", "exglider"=>"ffd", "pool"=>"ffd", "?"=>"ffd",
  "boil"=>"fee", "extin"=>"fee", "conway"=>"fee", "blink"=>"fee", "gas"=>"fee", "kia"=>"fee",
];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($_GET['typed']) {
  $typed = $_GET['typed'];  if(!isCorrectID($typed) && $typed<>"?") dierr("#179236742");
  $otyped = $typeds[$typed];  if(!$otyped) dierr("#9187543243");
  
  $page->bread[] = ["<i style='background:#".$otyped."'>$typed</i>", "&typed=$typed"];
  
  $PG = new Pagination(100);
  $s = '';
  $res = mysql_query(
   "SELECT SQL_CALC_FOUND_ROWS *
    FROM rr_glifetris
    WHERE typed='$typed' $famQplus
    ORDER BY id DESC
    LIMIT $PG->LP,$PG->PP
  ");
  $PG->Count($res);
  while($r = mysql_fetch_object($res)) {
    $mutalnk = !$r->mutaset
      ? ""
      : (strlen($r->mutaset)<200 ? "<a href='?notaset=$r->notaset&mutaset=$r->mutaset&maxfps=300'>".RN($r->mutaset)."</a>"
      : ProcrustMutaset($r->mutaset));
    $s .= "
      <tr>
        <td align=right><a href='?glife=$r->id'>$r->id</a></td>
        <td>".glDicts::GetFamily($r->family_id)->name."</td>
        <td><a href='?notaset=$r->notaset&maxfps=300'>$r->notaset</a></td>
        <td class=nrrw>$mutalnk</td>
        <td><a href='?glife=".urlencode($r->named)."&maxfps=300'><i>$r->named</i></a></td>
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
  $PG->AddPagination();
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif(isset($_GET['stopped'])) {
  $stopped  = $_GET['stopped'];  if($stopped && !isCorrectVar($stopped)) dierr("#47592374");
  $goodness = intval($_GET['goodness']);  if(!$goodness) dierr("#74428766");
  $ogoodness = $goodnesses[$goodness];  if(!$ogoodness) dierr("#109743282");
  $nonmutated = intval($_GET['nonmutated']);
  
  $page->bread[] = [($stopped?:"---") . " (".$ogoodness['nam'].")", "&stopped=$stopped&goodness=".$ogoodness['id']];
  
  $PG = new Pagination(100);
  $s = '';
  $res = mysql_query(
   "SELECT SQL_CALC_FOUND_ROWS gr.*, gl.*, gr.id gr_id
    FROM rr_glifetriruns gr
    JOIN rr_glifetris gl ON gl.id=gr.gl_id
    WHERE stopped_at='".MRES($stopped)."' AND stopped_nturn<10000 AND ".$ogoodness['wh']." $famQplus
      " . ($nonmutated ? "AND gl.mutaset=''" : "") . "
    ORDER BY gr.id DESC
    LIMIT $PG->LP,$PG->PP
  ");
  $PG->Count($res);
  while($r = mysql_fetch_object($res)) {
    $a_notaset = explode(",",  $r->notaset);
    $a_mutaset = explode("\n", ProcrustMutaset($r->mutaset,200,"\n"));
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
    
    $FD = glDicts::GetFD($r);
    $tr = '';
    for($z=0; $z<$FD; $z++) {
      $tr .= "<tr>";
      if($z==0) {
        $tr .= "
          <td rowspan=$FD style='line-height:1.5em;'>
            <a href='?glife=$r->gl_id&maxfps=300#show'>$r->gl_id</a><br>
            <a href='?gl_run=$r->gr_id&maxfps=300#cont'>$r->gr_id</a>
          </td>
          <td rowspan=$FD class=nrrw>$r->dt</td>
          <td rowspan=$FD>".glDicts::GetFamily($r->family_id)->name."</td>
          <td rowspan=$FD>".($r->named ? "<a href='?glife=".urlencode($r->named)."&maxfps=300'><i>$r->named</i></a>" : "")."</td>
          <td rowspan=$FD>$r->typed</td>
        ";
      }
      $tr .= "
          <td class='pd0'>{$a_notaset[$z]}</td>
          <td class='pd0 nrrw'>{$a_mutaset[$z]}</td>
          <td class='pd0'>".($z==0?"$r->stopped_nturn":"")."</td>
          <td class='pd0'>{$a_stopped[$z]}</td>
          <td class='pd0 tar'>{$a_records['fillin'][$z]}</td>
          <td class='pd0 tar'>{$a_records['spread'][$z]}</td>
          <td class='pd0 tar'>{$a_records['variat'][$z]}</td>
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
  
  $PG->AddPagination();
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
      <td><a href='?view=catalog$famUplus&typed=$r->typed'>".($r->typed?:"-?-")."</a></td>
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
        <td><a href='?view=catalog$famUplus&stopped=$r->stopped_at&goodness=$r->goodness'>".($r->stopped_at?:"---")."</a></td>
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