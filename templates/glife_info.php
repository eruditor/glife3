<?

class GLifeInfo {
  
  static function GlifeBigInfo($gl, $q4runs='', $single=true) {
    if(!$gl) return "";
    
    $FD = glDicts::GetFD($gl);
      
    $srun = '';
    $res2 = MQ("SELECT *, IF(orgasum>=0,1,0) has_orgasum FROM rr_glifetriruns gr WHERE gl_id='$gl->id' $q4runs ORDER BY has_orgasum DESC, id DESC LIMIT 3");
    while($r = mysqli_fetch_object($res2)) {
      $srun .= "<td>";
      $srun .= "
        <b><a href='/show/?gl_run=$r->id'>Run #$r->id</a></b>:
        ".glRecords::RecordsSpan($r, 'stopped_nturn')." / 
        ".glRecords::RecordsSpan($r, 'orga_sum')."
        <br>
      ";
      $ar_stopped = $r->stopped_at=='x' ? array_fill(0, $FD, 'x') : explode(";", $r->stopped_at);
      if($r->records) {
        $json = json_decode($r->records) ?: (object)[];
        $stb = '';
        for($z=0; $z<$FD; $z++) {
          $t = '';
          foreach(['fillin','spread','variat'] as $k) {
            $vv = $json->$k;
            $v = round($vv[$z]);
            $t .= "<td><span style='background:#".glRecords::Bgc4Records($k, $v)."'>$v%</span></td>";
          }
          $t .= "<td>".($ar_stopped[$z]?:"-")."</td>";
          foreach(['orga_num','orga_sum'] as $k) {
            $vv = $json->$k;
            if(!$vv) { $t .= "<td>???</td>";  continue; }
            $v = round($vv[$z]);
            $st = '';
            if($r->orgasum<0) { $v = -1;  $st = "color:#aaa;"; }
            $t .= "<td><span style='background:#".glRecords::Bgc4Records($k, $v).";$st'>".ShortenNumber($v)."</span></td>";
          }
          $stb .= "<tr><td>$z</td>$t</tr>";
        }
        $srun .= "
          <table cellspacing=0 id='glifeStatTB' class='nrrw'>
            <tr><th>z</th><th>fillin</th><th>spread</th><th>variat</th><th>stopped</th><th>orgaN</th><th>orga&Sigma;</th></tr>
            $stb
          </table>
        ";
      }
      $srun .= "</td>";
    }
    
    $nm = GLifeInfo::GLtitle($gl);
    $nm = $single && !$q4runs ? "<u>$nm</u>" : "<a href='/show/?glife=".($gl->named ? urlencode(SPCQA($gl->named)) : $gl->id)."'>$nm</a>";
    
    if($gl->mutaset) {
      $clean = glDicts::GetNonmutated($gl);
      $cleanstr = GLifeInfo::GLtitle($clean);
      if($clean) $cleanstr = "<a href='/show/?glife=".($clean->named ?: $clean->id)."'>$cleanstr</a>";
      $cleanstr = "mutated $cleanstr";
    }
    else {
      $cleanstr = RN(str_replace(",", "\n", $gl->notaset));
    }
    
    if(strlen($cleanstr)>80*$FD) {
      $cleanstr = GLifeInfo::ProcrustMutaset($cleanstr);
    }
    
    $s = "
      <tr><td>
        <h3 title='popularity=".round($gl->sumturns/1000)."'>$nm " . ($gl->typed?"<span class=gr>($gl->typed)</span>":"") . "</h3>
        " . glDicts::GetFamily($gl->family_id)->name . ":<br>
        <span class='nrrw'>$cleanstr</span><br>
        <small class='nrrw gr'>".GLifeInfo::ProcrustMutaset($gl->mutaset)."</small>
      </td>
      <td><table><tr>$srun</tr></table><br></td>
      <td>".GLifeInfo::GlifeEditInput($gl)."</td>
      </tr>
    ";
    
    if($single) $s = "<a name='show'></a><table id='SavedListTB'>$s</table>";
    
    return $s;
  }
  
  
  static function GlifeEditInput($r) {
    return $_ENV->dev
      ? "<span style='position:absolute;'>
           <input type=text id='glrule$r->id' value='".SPCQA($r->named . ($r->typed?":$r->typed":""))."' size=16><input type=button value=' Save ' onclick='XHRsave3(`id=$r->id&named=`+encodeURIComponent(document.getElementById(`glrule$r->id`).value));'><br>
           ".(!$r->named && !$r->typed ? "
             <a href='/gl_save/?id=$r->id&typed=foam'>foam</a> &nbsp;
             <a href='/gl_save/?id=$r->id&typed=amoeba'>amoeba</a> &nbsp;
             <a href='/gl_save/?id=$r->id&typed=boil'>boil</a> &nbsp;
             <a href='/gl_save/?id=$r->id&typed=train'>train</a>
           " : "")."
        </span>
        "
      : "";
  }
  
  
  static function GLtitle($gl) {
    if(!$gl) return "NOT_FOUND";
    if($gl->named) return $gl->named;
    $s4nota = strlen($gl->notaset)>40 ? $gl->notamd5 : RNZ($gl->notaset);
    $s4muta = strlen($gl->mutaset)>40 ? $gl->mutamd5 : RNZ($gl->mutaset);
    $sfull = $s4nota . ($s4muta ? "+$s4muta" : "");
    return SPCQA($sfull);
  }
  
  
  static function ProcrustMutaset($s, $max=0, $delim="<br>") {
    $nsymb = 8;
    if(!$max) $max = 2 * $nsymb;
    $ret = '';
    $ar = explode("\n", $s);
    foreach($ar as $a) {
      $t = strlen($a)<=$max ? $a : substr($a,0,$nsymb) . "&hellip;(" . strlen($a) . ")&hellip;" . substr($a,-$nsymb);
      $ret .= ($ret?$delim:"") . $t;
    }
    return $ret;
  }
  
}