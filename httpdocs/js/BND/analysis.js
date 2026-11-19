function StatSkipCollect() {
  if(cfg.maxfps>=1000) sleep(cfg.sleep);  // overwise GPU can freeze system UI
  return true;
}
