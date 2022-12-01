<?

class Timer {
  
  private $func = '';
  private $start = 0;
  private $finish = 0;
  private $parts = [];
  
  public function __construct($func='') {
    if(!$func) {
      $caller = debug_backtrace(!DEBUG_BACKTRACE_PROVIDE_OBJECT|DEBUG_BACKTRACE_IGNORE_ARGS,2)[1];
      $func = $caller['class'] . $caller['type'] . $caller['function'];
    }
    $this->func = $func;
    $this->start = microtime(true);
  }
  
  public function __destruct() {
    $this->finish = microtime(true);
    Page::$tm[$this->func] += $this->finish - $this->start;
    //Page::$tm[$this->func.":Ncalls"] ++;
    if($this->parts) foreach($this->parts as $part=>$v) {
      Page::$tm[$this->func."-".$part] += $v[1] - $v[0];
    }
  }
  
  public function start($part) {
    $this->parts[$part] = [microtime(true)];
  }
  
  public function finish($part) {
    $this->parts[$part][1] = microtime(true);
  }
  
}