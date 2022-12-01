<?

class FamilyFilter {
  
  static function View($view) {
    $family = glDicts::GetFamily($_GET['family']);
    $famQplus = $family ? "AND family_id='$family->id'" : "";
    $famUplus = $family ? ['family'=>$family->name] : [];
    if($family) Page::$bread[] = [$family->name, "?family=$family->name"];
    
    $s = "| ";
    foreach(glDicts::GetFamilies() as $fam) {
      $t = $family && $fam->id==$family->id ? "<u>$fam->name</u>" : "<a href='/$view/?family=$fam->name'>$fam->name</a>";
      $s .= "$t | ";
    }
    echo "<h3>Family filter: $s</h3><hr>";
    
    return [$famQplus, $famUplus];
  }
    
}
