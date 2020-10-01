// INIT ////////////////////////////////////////////////////////////////

function Init() {
  
  InitSpacetime();
  
  InitRules();
  
  InitStats();
  
}

Init();

// START ////////////////////////////////////////////////////////////////

function Start(first=false) {
  
  if(first) Show(1);  // draw initial state as first frame (useful for GET-paused mode)
  
  Stats(true);
  
  Calc();  // start Calc-ing world iterations
  
  if(cfg.maxfps>60 && !cfg.showiter) Show();
  
}

Start(true);
