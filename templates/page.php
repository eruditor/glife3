<?

class Page {
  
  static $main = '';
  static $title = '';
  static $descr = '';
  static $canonical = '';
  static $meta = '';
  static $noindex = false;
  static $bread = [];
  
  
  static function Make() {
    ob_end_flush();
    
    ////////////////////////////////////////////////////////////////////////////////////////////////
    
    $tm = [
      "gallery"     => "Gallery",
      "catalog"     => "Catalog",
      "stadium"     => "Stadium",
      "manufacture" => "Manufacture",
    ];
    $topmenu = '';
    foreach($tm as $k=>$v) {
      $topmenu .= "<td><a href='/$k/'".(Router::$route->folder==$k?" class=chsn":"").">$v</a></td>";
    }
    $topmenu = "<table cellspacing=0><tr>$topmenu</tr></table>";
    
    ////////////////////////////////////////////////////////////////////////////////////////////////
    
    $H1 = $title = $url = '';
    foreach(self::$bread as $k=>$v) {
      $nm = $v[0];  $ah = $v[1];  $plus = $v[2] ?: '';
      if($ah) $url .= $ah;
      $crumb = $nm;
      if($k<count(self::$bread)-1) $crumb = "<a href='$url'>$crumb</a>";
      if($plus) $crumb .= $plus;
      $H1 .= ($H1?" → ":"") . $crumb;
      $title = strip_tags($nm) . ($title?" ← ":"") . $title;
    }
    if(self::$title) $title = self::$title;
    
    ////////////////////////////////////////////////////////////////////////////////////////////////
    
    $mt = sprintf("%.3lf", microtime(true)-$_ENV->startT);
    
    ////////////////////////////////////////////////////////////////////////////////////////////////
    
    if($_ENV->route->xget) self::$noindex = true;
    
    if(!self::$canonical) {
      $filter_get = ['maxfps', 'paused', 'pauseat'];
      $get = '';  $setcanon = false;
      foreach($_GET as $k=>$v) {
        if(in_array($k, $filter_get)) { $setcanon = true;  continue; }
        $get .= ($get ? "&" : "?") . urlencode($k) . "=" . urlencode($v);
      }
      if($setcanon) self::$canonical = $_ENV->route->path.$get;
    }
    
    ////////////////////////////////////////////////////////////////////////////////////////////////
    
    $rax = "<script type=\"text/javascript\">document.write('<img src=\"//counter.yadro.ru/hit?t14.1;r' + escape(document.referrer) + ((typeof(screen)=='undefined')?'':';s'+screen.width+'*'+screen.height+'*'+(screen.colorDepth?screen.colorDepth:screen.pixelDepth)) + ';u' + escape(document.URL) + ';' + Math.random() + '\" border=0 width=88 height=31 alt=\"\">')</script>";
    $yam = '
    <script type="text/javascript">
      (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
          try {
            w.yaCounter11332753 = new Ya.Metrika({
              id:11332753,
              clickmap:true,
              trackLinks:true,
              accurateTrackBounce:true,
              webvisor:true
            });
          } catch(e) { }
        });
        var n = d.getElementsByTagName("script")[0],
          s = d.createElement("script"),
          f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/watch.js";
        if (w.opera == "[object Opera]") {
          d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
      })(document, window, "yandex_metrika_callbacks");
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/11332753" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    ';
    
    if($_ENV->dev || self::$noindex) {
      $rax = "<img src='/i/rax31.gif' width=88 height=31>";
      $yam = "<!--".htmlspecialchars($yam)."-->";
    }
    
    if(self::$noindex) self::$meta .= "<meta name='robots' content='noindex'>\n  ";
    
    ////////////////////////////////////////////////////////////////////////////////////////////////
    
  echo "<!DOCTYPE html>
<HTML>
<head>
  <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1'>
  <title>$title</title>
  <meta name='description' content='".self::$descr."'>
  " . self::$meta . "
  " . (self::$canonical ? "<link rel='canonical' href='".self::$canonical."'>" : "") . "
  <link href='/style.css?v=".date("YmdHis", filemtime("style.css"))."' rel='stylesheet'>
  <script src='/js.js?v=".date("YmdHis", filemtime("js.js"))."'></script>
  ".trim($yam)."
</head>
<BODY align=center>
<DIV id=Wrap align=left>
  <DIV id=TopLine><a href='/' class=ttl>GLife</a></DIV>
  <DIV id=MenuLine>$topmenu</DIV>
  <DIV id=Main align=left>
    " . ($H1 ? "<h1>$H1</h1>" : "") . "
    <div class=zzt>".self::$main."</div>
  </DIV>
  <DIV id=BottomBuffer></DIV>
</DIV>
<DIV id=Bottom>
  <table id=BottomLine><tr>
  <td width=100% align=left>
    eruditor.ru &copy; 2019-".date("Y")."
  </td>
  <td style='padding-right:1px;'>$rax</td>
  </tr></table>
</DIV>
<!-- ".trim($mt)." test1 -->
</BODY>
</HTML>";
  
  }
  
}