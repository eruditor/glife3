<?

$page->bread[] = ["Gallery", "?view=gallery"];

$page->zabst .= "
  “Gallery” is a list of the most interesting glifes.<br>
  They are chosen by human and have a personal name.<br>
";

list($famQplus, $famUplus) = AddFamilyFilter();

$page->z .= GlifeBigInfo("named<>''$famQplus");

?>