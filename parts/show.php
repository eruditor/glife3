<?

$families = GetFamilies();

$glife = $_GET['glife'];

    if(is_numeric($glife)) { $q = "id='".intval($glife)."'";   $isnamed = false; }
elseif($glife)             { $q = "named='".MRES($glife)."'";  $isnamed = true;  }
else die("nothing to show");

$gl = mysql_o("SELECT * FROM rr_glifetris WHERE $q");  if(!$gl) die("no gl found");

$page->bread[] = [SPCQA($gl->named ?: $gl->notaset)];

$page->z .= GlifeBigInfo($gl);

$page->z .= GLifeJS($glife);

?>