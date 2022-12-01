<?

class Router {
  
  static $route = null;  // current global route
  
  static $routes = [  // used in RouteURL & Href
    // URL                                                  PHP
    ""                                                  => ["main.php"],
    "show"                                              => ["show/_show.php"],
    "gallery"                                           => ["gallery/_gallery.php"],
    "catalog"                                           => ["catalog/_catalog.php"],
    "stadium"                                           => ["stadium/_stadium.php"],
    "manufacture"                                       => ["manufacture/_manufacture.php"],
    "gl_save"                                           => ["_post/gl_save.php"],
    "gl_service"                                        => ["_admn/gl_service.php"],
    // URL                                                  PHP
  ];
  
  ////////////////////////////////////////////////////////////////////////////////////////////////
  
  static function ParseURL($uri=null, $die=true) {
    if(!isset($uri) && $die) { $uri = $_SERVER['REQUEST_URI'];  $store = true; }  else { $store = false; }
    if($store && is_object($_ENV->route)) return $_ENV->route;
    
    $parsed = parse_url($uri);
    if($parsed['scheme']) return false;
    
    $folders = explode("/", trim($parsed['path'], "/"));
    
    $file = '';
    $last = $folders[count($folders)-1];
    if(strpos($last, ".")!==false) {
      $file = $last;
      unset($folders[count($folders)-1]);
    }
    
    $get = $xget = [];
    parse_str($parsed['query'], $get);
    $stops = ['hsh'];
    foreach($get as $k=>$v) {
      if(substr($k,0,1)=="_" || substr($k,0,4)=="utm_" || in_array($k,$stops)) {
        $xget[$k] = $v;
        unset($get[$k]);
      }
    }
    
    $ret = (object)['folders'=>$folders, 'get'=>$get, 'xget'=>$xget, 'file'=>$file, 'uri'=>$uri, 'path'=>$parsed['path']];
    
    if($store) $_ENV->route = clone $ret;
    
    if($store && $die && $file) {  // must be at the very end
      dierr("file_routing: $uri not found");
    }
    
    return $ret;
  }
  
  
  static function RouteURL($uri=null, $die=true) {
    $parsed = self::ParseURL($uri, $die);  if(!$parsed) { if($die) dierr("#84791698574");  else return false; }
    $folders = implode("/", $parsed->folders);
    
    $route_id = null;  $masks = [];
    foreach(self::$routes as $path=>$info) {
      $starpos = strpos($path, "*");  $m = [];
      if($starpos) {
        if(substr($folders, 0, $starpos)<>substr($path, 0, $starpos)) continue;
        if(!preg_match("`".str_replace("\*", "([a-zA-Z0-9_\-\.]+)", preg_quote($path))."`", $folders, $m)) continue;
        if($m[0]<>$folders) continue;
      }
      else {
        if($folders<>$path) continue;
      }
      $route_id = $path;
      $masks = $m;
      $fldr = trim($starpos ? substr($path, 0, $starpos) : $path, "/");
      //break;  // break = take first match; none = take last one
    }
    
    if(!isset($route_id)) {
      if($die) {
        dierr("routing: /".SPCQA($folders)."/ not found");
      }
      else {
        return false;
      }
    }
    
    $r = self::$routes[$route_id];
    return (object)([
      'route_id'  => $route_id,
      'folder'    => $fldr,
      'subfolder' => $masks[1]?:'',
      'subf2'     => $masks[2]?:'',
      'subf3'     => $masks[3]?:'',
      'furi'      => implode("/", $parsed->folders),
      'uri'       => $parsed->uri,
      'php'       => $r[0],
    ]);
  }
  
  
  static function IncludeURL($uri=null) {
    $ret = '';
    
    self::$route = self::RouteURL($uri);
    
    if(self::$route->frame=="_ajax") { $_ENV->is_ajax = true;  $_ENV->is_json = true;  header('Content-Type: application/json; charset=utf-8'); }
    
    $inc = self::$route->php ? $_ENV->path2includes.self::$route->php : '';
    if($inc && file_exists($inc)) {
      $ret = include_var($inc);
    }
    
    if(!$ret && self::$route->ib) {  // default view for IB elements and list
      $ret = include_var($_ENV->path2includes."_default/_default.php");
    }
    
    return $ret;
  }
  
  
  ////////////////////////////////////////////////////////////////////////////////////////////////
  
}
