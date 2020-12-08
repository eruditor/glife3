<?

$page->bread[] = ["Gallery", "$_self?view=gallery"];

$page->zabst .= "
  “Gallery” is a list of the most interesting glifes.<br>
  They are chosen by human and have a personal name.<br>
  
  
";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$family = glDicts::GetFamily($_GET['family']);
$famQplus = $family ? "AND family_id='$family->id'" : "";
$famUplus = $family ? "&family=$family->name" : "";
if($family) $page->bread[] = [$family->name, "&family=$family->name"];

$s = "| ";
foreach(glDicts::GetFamilies() as $fam) {
  $t = $fam->id==$family->id ? "<u>$fam->name</u>" : "<a href='$_self?view=gallery&family=$fam->name'>$fam->name</a>";
  $s .= "$t | ";
}
$page->z .= "<h3>Family filter: $s</h3><hr>";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$page->z .= GlifeBigInfo("named<>''$famQplus");

?>