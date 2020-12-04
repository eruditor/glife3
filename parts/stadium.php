<?

$H1 = "Stadium";

$zabst .= "
  “Stadium” is where glifes are sorted by orga-rating.<br>
  And orga-rating is an automatically produced number that (we hope) shows how likely this glife contains any signs of artificial life.<br>
";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$families = GetFamilies();
$famnames = GetFamilies(true);

$famname = $_GET['family'];
$family = $famnames[$famname];
$famQplus = $family ? "AND family_id='$family->id'" : "";
$famUplus = $family ? "&family=$family->name" : "";

$s = "| ";
foreach($famnames as $fam) {
  $t = $fam->id==$family->id ? "<u>$fam->name</u>" : "<a href='$_self?view=stadium&family=$fam->name'>$fam->name</a>";
  $s .= "$t | ";
}
$zzt .= "<h3>Family filter: $s</h3><hr>";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$PP = 100;  $LL = intval($_GET['ll']);  $LP = $LL * $PP;
  
$clean = [];  // non-mutated glife list
$s = '';
$where = "ver=4 AND rating>0 $famQplus";
$res = mysql_query(
 "SELECT gr.*, family_id, named, typed, notaset
 FROM rr_glifetriruns gr
 JOIN rr_glifetris gl ON gl.id=gr.gl_id
 WHERE $where
 ORDER BY rating, orgasum DESC
 LIMIT $LP,$PP
");
$shwn = mysql_num_rows($res);
$nttl = mysql_r("SELECT COUNT(*) FROM rr_glifetriruns WHERE $where");
while($r = mysql_fetch_object($res)) {
  if(!isset($clean[$r->notaset])) $clean[$r->notaset] = mysql_o("SELECT * FROM rr_glifetris WHERE family_id='".MRES($r->family_id)."' AND notaset='".MRES($r->notaset)."' AND mutaset=''");
  
  glRecords::EnrichOrgaRatings($r);
  
  $gllink = SPCQA($r->named) ?: $r->gl_id;
  
  $s .= "
    <tr>
      <td><a href='$_self?gl_run=$r->id&maxfps=300&pauseat=5000'>$r->id</a></td>
      <td><a href='$_self?glife=$gllink'>$gllink</a></td>
      <td>".($clean[$r->notaset]->named ?: $clean[$r->notaset]->notaset)."</td>
      <td>".$families[$r->family_id]->name."</td>
      <td class=tar>".glRecords::RecordsSpan($r, 'stopped_nturn')."</td>
      <td class=tar>".glRecords::RecordsSpan($r, 'orga_sum'     )."</td>
      <td class=tar>".glRecords::RecordsSpan($r, 'orga_avg'     )."</td>
      <td class=tar>".glRecords::RecordsSpan($r, 'orga_z'       )."</td>
      <td>$r->stopped_at</td>
      <td class=tar>".glRecords::RecordsSpan($r, 'rating'       )."</td>
    </tr>
  ";
}
$zzt .= "
  <table cellspacing=0 id='SavedListTB' style='border:solid 2px #ddd'>
  <tr><th>run_id</th><th>glife</th><th>clean</th><th>family</th><th>nturn</th><th>orga&Sigma;</th><th>orgaAVG</th><th>z</th><th>stopped</th><th>rating</th></tr>
  $s
  </table>
";

if($nttl>$PP) {  // pagination
  $q = '';  foreach($_GET as $k=>$v) if($k<>'ll') $q .= ($q?"&":"") . "$k=$v";
  $s = "
    <td width=160>" . ($LL>0 ? "<a href='$_self?$q".($LL>1?"&ll=".($LL-1):"")."'>&larr; prev $PP</a>" : "") . "</td>
    <td width=240>shown $shwn / $nttl</td>
    <td width=160>" . ($LP+$shwn<$nttl ? "<a href='$_self?$q&ll=".($LL+1)."'>next $PP &rarr;</a>" : "") . "</td>
  ";
  $zzt .= "<br><div align=center><table><tr>$s</tr></table></div>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>