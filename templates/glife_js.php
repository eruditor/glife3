<?

class GLifeJS {
  
  static function View($notaset='', $prms=[], $send2js = '') {
    if(!$notaset) {
      $notaset = $_GET['notaset'] ?: "Aphrodite";
    }
    
    $fm = null;  // family is required to run
    
    if($notaset=='random') {
      $prms['randrules'] = 1;
    }
    elseif($notaset=='rerun') {
      $prms['rerun'] = 1;
    }
    elseif($notaset=='repair') {
      $prms['repair'] = 1;
    }
    elseif(substr($notaset,0,8)=="anyrand_") {
      $prms['anyrand'] = 1;
      $fm = glDicts::GetFamily(substr($notaset,8));
      $send = '';  $FD = 0;
      $res = MQ("SELECT * FROM rr_glifetris WHERE family_id='$fm->id' AND named<>'' AND mutamd5=''");
      while($r = mysqli_fetch_object($res)) {
        if(!$FD) $FD = glDicts::GetFD($r);
        $send .= "['".SPCQA($r->named)."', '$r->notaset'],\n";
      }
      $send2js .= "
        gl_cleannamed = [
          $send
        ];
      ";
      $prms['FD'] = $FD;
    }
    elseif($_GET['fm'] && $_GET['notaset']) {
      $fm_id = intval($_GET['fm']);  if(!$fm_id) die("#87234852");
      $fm = glDicts::GetFamily($fm_id);  if(!$fm) die("#87234853");
      $prms['FD'] = $fm->FD ?: 3;
      $prms['notaset'] = $notaset;
    }
    else {
      $fm_id = intval($prms['fm_id'] ?: $_GET['fm']);
      $gl = glDicts::GetGL4Notaset($notaset, $fm_id);  if(!$gl) dierr("incorrect notaset ($notaset, $fm_id)");
      $fm = glDicts::GetFamily($gl->family_id);
      $prms['FD'] = glDicts::GetFD($gl);
      $prms['notaset'] = $gl->notaset;
      //$prms['mutaset'] = $gl->mutaset;
      if($gl->named && !$gl->mutaset) $prms['named'] = $gl->named;
      if(GLifeInfo::is_jsoned($gl)) {
        $send2js .= "Ruleset = `" . $gl->mutaset . "`;\n";
      }
      else {
        $send2js .= "Mutaset = '".str_replace("\n", "\\n", $gl->mutaset)."';\n";
      }
    }
    
    if(!$fm) $fm = glDicts::GetFamily($prms['family'] ?: $_GET['family']);  if(!$fm) dierr("#48379230");
    $prms['family'] = $fm->name;
    $send2js .= "glFamily = JSON.parse(`" . json_encode($fm) . "`);\n";
    
    $rseed = $prms['rseed'] ?: intval($_GET['rseed']) ?: rand(1,getrandmax());
    $fseed = $prms['fseed'] ?: intval($_GET['fseed']) ?: rand(1,getrandmax());
    
    $jsget = "?v=$_ENV->ver";
    if($_ENV->dev) $jsget .= "&rnd=".rand(1,getrandmax());  // to refresh cached scripts every run
    
    $plus = '';  foreach($prms as $k=>$v) if($v) $plus .= "&".urlencode($k)."=".urlencode($v);
    
    $send2js .= "gl_bgc4records = JSON.parse(`" . json_encode(glRecords::$bgc4records) . "`);\n";
    
    $spacetime = $physics = $dynamics = $visualisation = '';
    
    $named = $gl->named;
    $prefnm = ($t=stripos($named,"-")) ? substr($named,0,$t) : '';
    $named4 = substr($named, 0, 4);
    $named6 = substr($named, 0, 6);
    
         if($fm->mode=='MVM')  $spacetime = "<script src='/js/MVM/spacetime.js$jsget'></script>";
    else if($fm->mode=='BND')  $spacetime = "<script src='/js/BND/spacetime.js$jsget'></script>";
    else if($fm->mode=='FLD')  $spacetime = "<script src='/js/FLD/spacetime.js$jsget'></script>";
    else if($fm->mode=='XCH')  $spacetime = "<script src='/js/XCH/spacetime.js$jsget'></script>";
    else if($fm->mode=='FHP')  $spacetime = "<script src='/js/FHP/spacetime.js$jsget'></script>";
    else if($fm->mode=='LFL')  $spacetime = "<script src='/js/LFL/spacetime.js$jsget'></script>";
    
         if($prefnm=='LTX')    $physics = "<script src='/js/LFL/LTX/physics.js$jsget'></script>";
    else if($fm->mode=='LFL')  $physics = "<script src='/js/LFL/physics.js$jsget'></script>";
    else                       $physics = "<script src='/js/!default/physics.js$jsget'></script>";
    
         if($fm->mode=='PRT')  $dynamics = "<script src='/js/PRT/dynamics.js$jsget'></script>";
    else if($fm->mode=='MVM')  $dynamics = "<script src='/js/MVM/dynamics.js$jsget'></script>";
    else if($named=='Bond4C')  $dynamics = "<script src='/js/BND/Bond4C/dynamics.js$jsget'></script>";
    else if($named=='Bond4C2') $dynamics = "<script src='/js/BND/Bond4C2/dynamics.js$jsget'></script>";
    else if($fm->mode=='FLD')  $dynamics = "<script src='/js/FLD/dynamics.js$jsget'></script>";
    else if($fm->mode=='XCH')  $dynamics = "<script src='/js/XCH/dynamics.js$jsget'></script>";
    else if($fm->mode=='FHP')  $dynamics = "<script src='/js/FHP/dynamics.js$jsget'></script>";
    else if($named6=='LeniaQ') $dynamics = "<script src='/js/LFL/QM/dynamics.js$jsget'></script>";
    else if($named4=='Leia')   $dynamics = "<script src='/js/LFL/CNSRV/dynamics.js$jsget'></script>";
    else if($prefnm=='LTX')    $dynamics = "<script src='/js/LFL/LTX/dynamics.js$jsget'></script>";
    else if($fm->mode=='LFL')  $dynamics = "<script src='/js/LFL/dynamics.js$jsget'></script>";
    else                       $dynamics = "<script src='/js/!default/dynamics.js$jsget'></script>";
    
         if($fm->mode=='PRT')  $visualisation = "<script src='/js/PRT/visualisation.js$jsget'></script>";
    else if($fm->mode=='MVM')  $visualisation = "<script src='/js/MVM/visualisation.js$jsget'></script>";
    else if($named=='Bond4C')  $visualisation = "<script src='/js/BND/Bond4C/visualisation.js$jsget'></script>";
    else if($named=='Bond4C2') $visualisation = "<script src='/js/BND/Bond4C2/visualisation.js$jsget'></script>";
    else if($fm->mode=='FLD')  $visualisation = "<script src='/js/FLD/visualisation.js$jsget'></script>";
    else if($fm->mode=='XCH')  $visualisation = "<script src='/js/XCH/visualisation.js$jsget'></script>";
    else if($fm->mode=='FHP')  $visualisation = "<script src='/js/FHP/visualisation.js$jsget'></script>";
    else if($named6=='LeniaQ') $visualisation = "<script src='/js/LFL/QM/visualisation.js$jsget'></script>";
    else if($named4=='Leia')   $visualisation = "<script src='/js/LFL/CNSRV/visualisation.js$jsget'></script>";
    else if($fm->mode=='LFL')  $visualisation = "<script src='/js/LFL/visualisation.js$jsget'></script>";
    
    return "
      <a name='cont'></a>
      <div id=GLifeCont></div>
      
      <script src='/_vendor/jquery-fft/jquery.fft.js'></script>
      <script src='//cdn.plot.ly/plotly-latest.min.js'></script>
      
      <script>$send2js</script>
      
      <script src='/js/0.params.js$jsget&rseed=$rseed&fseed=$fseed$plus'></script>
      <script src='/js/1.math.js$jsget'></script>
      <script src='/js/2.hardware.js$jsget'></script>
      <script src='/js/3.spacetime.js$jsget'></script>$spacetime
      <script src='/js/4.physics_laws.js$jsget'></script>$physics
      <script src='/js/5.dynamics.js$jsget'></script>$dynamics$visualisation
      <script src='/js/6.visualisation.js$jsget'></script>
      <script src='/js/7.analysis.js$jsget'></script>
      <script src='/js/8.interface.js$jsget'></script>
      <script src='/js/9.divine_forces.js$jsget'></script>
      <script src='/main.js$jsget'></script>
    ";
  }
  
}