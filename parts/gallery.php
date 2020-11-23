<?

$H1 = "Gallery";

////////////////////////////////////////////////////////////////

$families = GetFamilies();
$famnames = GetFamilies(true);

$famname = $_GET['family'];
$family = $famnames[$famname];
$famQplus = $family ? "AND family_id='$family->id'" : "";

$s = "| ";
foreach($famnames as $fam) {
  $t = $fam->id==$family->id ? "<u>$fam->name</u>" : "<a href='$_self?view=gallery&family=$fam->name'>$fam->name</a>";
  $s .= "$t | ";
}
$zzt .= "<h2>Filter by Family: $s</h2><hr>";

////////////////////////////////////////////////////////////////

$zzt .= GlifeBigInfo("named<>''$famQplus");

?>