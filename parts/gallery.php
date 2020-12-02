<?

$H1 = "Gallery";

$zabst .= "
  “Gallery” is a list of the most interesting glifes.<br>
  They are chosen by human and have a personal name.<br>
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
  $t = $fam->id==$family->id ? "<u>$fam->name</u>" : "<a href='$_self?view=gallery&family=$fam->name'>$fam->name</a>";
  $s .= "$t | ";
}
$zzt .= "<h3>Family filter: $s</h3><hr>";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$zzt .= GlifeBigInfo("named<>''$famQplus");

?>