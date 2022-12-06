<?

Page::$bread[] = ["Stadium", "stadium/"];

echo "
  <div class=zabst>
    «Stadium» is where glifes are sorted by orga-rating.<br>
    Orga-rating is an automatically produced number which (we hope) shows how likely this glife contains any signs of artificial life.<br>
  </div>
";

list($famQplus, $famUplus) = FamilyFilter::View('stadium');

////////////////////////////////////////////////////////////////////////////////////////////////

$PG = new Pagination(100);
$s = '';
$where = "ver='$_ENV->anver' AND rating>0 $famQplus";
$res = MQ(
 "SELECT gr.*, family_id, named, typed, notaset
  FROM rr_glifetriruns gr
  JOIN rr_glifetris gl ON gl.id=gr.gl_id
  WHERE $where
  ORDER BY rating, orgasum DESC
  LIMIT $PG->LP,$PG->PP
");
$PG->shwn = mysql_num($res);
$PG->nttl = mysql_r("SELECT COUNT(*) FROM rr_glifetriruns gr JOIN rr_glifetris gl ON gl.id=gr.gl_id WHERE $where");
while($r = mysqli_fetch_object($res)) {
  $nonmutated = glDicts::GetNonmutated($r);
  
  glRecords::EnrichOrgaRatings($r);
  
  $gllink = SPCQA($r->named) ?: $r->gl_id;
  
  $s .= "
    <tr>
      <td><a href='/show/?gl_run=$r->id&maxfps=1001&pauseat=$r->stopped_nturn'>$r->id</a></td>
      <td><a href='/show/?glife=$gllink'>$gllink</a>".($r->typed?":$r->typed":"")."</td>
      <td>".($nonmutated ? ($nonmutated->named ?: $nonmutated->notaset) : '')."</td>
      <td>".glDicts::GetFamily($r->family_id)->name."</td>
      <td class=tar>".glRecords::RecordsSpan($r, 'stopped_nturn')."</td>
      <td class=tar>".glRecords::RecordsSpan($r, 'orga_sum'     )."</td>
      <td class=tar>".glRecords::RecordsSpan($r, 'orga_avg'     )."</td>
      <td class=tar>".glRecords::RecordsSpan($r, 'orga_z'       )."</td>
      <td>$r->stopped_at</td>
      <td class=tar>".glRecords::RecordsSpan($r, 'rating'       )."</td>
    </tr>
  ";
}
echo "
  <table cellspacing=0 id='SavedListTB' style='border:solid 2px #ddd'>
  <tr><th>run_id</th><th>glife</th><th>nonmutated</th><th>family</th><th>nturn</th><th>orga&Sigma;</th><th>orgaAVG</th><th>z</th><th>stopped</th><th>rating</th></tr>
  $s
  </table>
";
//echo $PG->Draw();

////////////////////////////////////////////////////////////////////////////////////////////////
