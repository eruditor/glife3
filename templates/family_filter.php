<?

class FamilyFilter {
  
  static function View($view) {
    $family = glDicts::GetFamily($_GET['family']);
    $famQplus = $family ? "AND family_id='$family->id'" : "";
    $famUplus = $family ? ['family'=>$family->name] : [];
    if($family) Page::$bread[] = [$family->name, "?family=$family->name"];
    
    $s = "<span class=gr>|</span> ";
    foreach(glDicts::GetFamilies() as $fam) {
      $t = $family && $fam->id==$family->id ? "<u>$fam->name</u>" : "<a href='/$view/?family=$fam->name'>$fam->name</a>";
      $s .= "$t <span class=gr>|</span> ";
    }
    echo "<div style='font:normal 15px/21px Arial narrow, Arial;'>$s</div><hr>";
    
    return [$famQplus, $famUplus];
  }
    
}
