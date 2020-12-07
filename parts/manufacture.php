<?

$H1 = "Manufacture";

$zabst .= "
  “Manufacture” is where glifes are automatically produced.<br>
  Setting random physics rules for a newborn universe, running it's dynamics calculations and then analyzing if the result is somehow interesting.<br>
  (At ~1000 fps it takes about 1 second to process one set of rules.)<br>
";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$families = GetFamilies();
$famnames = GetFamilies(true);
$named    = GetCleanNamed();

$aRgeoms = [18=>"Moore", 182=>"3D Moore", 142=>"3D von Neumann"];
$aRsymms = [85=>"8-rotation+parity", 47=>"4-rotation-vector"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($_GET['autore'])) {
  if($_GET['family']) {
    $fm = $famnames[$_GET['family']];  if(!$fm) die("incorrect family");
    $FD = intval($fm->FD || $_GET['FD']);
    $H1 .= " &rarr; Family=" . $fm->name . " (FD=$FD)";
    $zzt .= GLifeJS('random', ['FD'=>$FD]);
  }
  elseif($_GET['nota']) {
    // nmuta already passed through GET
    $nota = SPCQA($_GET['nota']);
    $H1 .= " &rarr; $nota";
    $zzt .= GLifeJS($nota);
  }
  elseif($_GET['glid']) {
    $glid = SPCQA($_GET['glid']);  if(!$glid) die("#78234655");
    $H1 .= " &rarr; gl_id=$glid";
    $zzt .= GLifeJS($glid);
  }
  else {
    die("incorrect parameters");
  }
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
else {
  $famopts = "<option value=''>";
  foreach($families as $r) $famopts .= "<option value='$r->name' data-fd='$r->FD'>$r->name";
  
  $namopts = "<option value=''>";
  foreach($families as $r) $namopts .= "<option value='anyrand_$r->name'>Any random from $r->name";
  foreach($named as $r) $namopts .= "<option value='".SPCQA($r->named)."'>".$families[$r->family_id]->name.": ".SPCQA($r->named);
  
  $famsel = "onchange='var fd=this.options[this.selectedIndex].getAttribute(`data-fd`); var fdinp=document.getElementById(`glfdinp`); fdinp.value=fd>0?fd:3; fdinp.disabled=fd>0?true:false;'";
  
  $speeds = [1001, 300, 60];
  $speedsel = '';  foreach($speeds as $speed) $speedsel .= "<option value='$speed'>$speed";
  $speedsel = "<select name='maxfps'>$speedsel</select>";
  
  $zzt .= "
    <h2>Random search for extra-universial life</h2>
    <table><tr>
    <td valign=top>
      <form method=GET action='$_self'>
        <input type=hidden name='view' value='manufacture'>
        <input type=hidden name='autore' value='1'>
        <table cellspacing=0 cellpadding=5 style='border:solid 2px #ddd;' id=glifeStatTB>
        <tr><th colspan=2 class=tal>Iterating family</th></tr>
        <tr><td>family</td><td class=tal><select name='family' style='width:140px' $famsel>$famopts</select></td></tr>
        <tr><td>FD    </td><td class=tal><input type=number name='FD' value='3' min='1' max='8' id='glfdinp'></td></tr>
        <tr><td>speed </td><td class=tal>$speedsel <input type=submit value=' OK ' style='float:right;'></td></tr>
        </table>
      </form>
    </td><td width=30>&nbsp;</td>
    <td valign=top>
      <form method=GET action='$_self'>
        <input type=hidden name='view' value='manufacture'>
        <input type=hidden name='autore' value='1'>
        <table cellspacing=0 cellpadding=5 style='border:solid 2px #ddd;' id=glifeStatTB>
        <tr><th colspan=2 class=tal>Mutating known glife</th></tr>
        <tr><td>named </td><td class=tal><select name='nota'>$namopts</select></td></tr>
        <tr><td>nmuta </td><td class=tal><input type=text name='nmuta' value='100' size=8></td></tr>
        <tr><td>speed </td><td class=tal>$speedsel <input type=submit value=' OK ' style='float:right;'></td></tr>
        </table>
      </form>
    </td><td width=30>&nbsp;</td>
    <td valign=top>
      <form method=GET action='$_self'>
        <input type=hidden name='view' value='manufacture'>
        <input type=hidden name='autore' value='1'>
        <table cellspacing=0 cellpadding=5 style='border:solid 2px #ddd;' id=glifeStatTB>
        <tr><th colspan=2 class=tal>Refielding initial layout</th></tr>
        <tr><td>gl_id </td><td class=tal><input type=text name='glid' value='' size=16></td></tr>
        <tr><td>      </td><td class=tal><br></td></tr>
        <tr><td>speed </td><td class=tal>$speedsel <input type=submit value=' OK ' style='float:right;'></td></tr>
        </table>
      </form>
    </td>
    </tr></table><hr>
    
    <table><tr>
    <td valign=top>
      <form method=GET action='$_self'>
        <input type=hidden name='view' value='manufacture'>
        <input type=hidden name='autore' value='1'>
        <table cellspacing=0 cellpadding=5 style='border:solid 2px #ddd;' id=glifeStatTB>
        <tr><th colspan=2 class=tal><span class=gr>Unclassified</span> (not working yet)</th></tr>
        <tr><td><span title='' class=hlp>FD</span></td><td class=tal><input type=text name='FD' value='3' size=2></td></tr>
        <tr><td><span title='' class=hlp>RB</span></td><td class=tal><input type=text name='RB' value='2' size=2></td></tr>
        <tr><td>nmuta </td><td class=tal><input type=text name='nmuta' value='100' size=8></td></tr>
        <tr><td>      </td><td><input type=submit value=' OK ' disabled></td></tr>
        </table>
      </form>
    </td>
    </tr></table>
  ";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>