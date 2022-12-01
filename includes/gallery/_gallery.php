<?

Page::$bread[] = ["Gallery", "gallery/"];

echo "
  <div class=zabst>
    «Gallery» is a list of the most interesting glifes.<br>
    They are chosen by human and have a personal name.<br>
  </div>
";

list($famQplus, $famUplus) = FamilyFilter::View('gallery');

////////////////////////////////////////////////////////////////////////////////////////////////

$PG = new Pagination(50);
$s = '';
$res = MQ(
 "SELECT SQL_CALC_FOUND_ROWS gl.*, COUNT(*) nruns, SUM(stopped_nturn) sumturns
  FROM rr_glifetris gl
  LEFT JOIN rr_glifetriruns gr ON gr.gl_id=gl.id
  WHERE named<>'' $famQplus
  GROUP BY gl.id
  ORDER BY sumturns DESC, found_dt DESC
  LIMIT $PG->LP,$PG->PP
");
$PG->Count($res);
while($gl = mysqli_fetch_object($res)) {
  $s .= GLifeInfo::GlifeBigInfo($gl, "", false);
}
echo "<table id='SavedListTB'>$s</table>";
echo $PG->Draw();

////////////////////////////////////////////////////////////////////////////////////////////////
