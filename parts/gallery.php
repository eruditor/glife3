<?

$page->bread[] = ["Gallery", "?view=gallery"];

$page->zabst .= "
  “Gallery” is a list of the most interesting glifes.<br>
  They are chosen by human and have a personal name.<br>
";

list($famQplus, $famUplus) = AddFamilyFilter('gallery');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$PG = new Pagination(50);
$s = '';
$res = mysql_query(
 "SELECT SQL_CALC_FOUND_ROWS gl.*, COUNT(*) nruns, SUM(stopped_nturn) sumturns
  FROM rr_glifetris gl
  LEFT JOIN rr_glifetriruns gr ON gr.gl_id=gl.id
  WHERE named<>'' $famQplus
  GROUP BY gl.id
  ORDER BY sumturns DESC, found_dt DESC
  LIMIT $PG->LP,$PG->PP
");
$PG->Count($res);
while($gl = mysql_fetch_object($res)) {
  $s .= GlifeBigInfo($gl, "", false);
}
$page->z .= "<table id='SavedListTB'>$s</table>";
$PG->AddPagination();

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>