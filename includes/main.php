<?

echo "
  <div class=zabst>
    <b>GLife is a cellular automata platform based on WebGL2.</b>
    <ul>
    <li> Universal logic: represents both
        <a href='/show/?glife=Game+of+Life&FW=1200&FH=700&LF=80&maxfps=300'>Conway's Game of Life</a> and
        <a href='/show/?glife=Langtons+Ant&LF=1&maxfps=300'>Langton's Ant</a>
        as particular examples.
    <li> 2D/3D: multi-layer grids.
    <li> Cross-platfom: works in (almost every) browser; independent of hardware; doesn't need any software installation.
    <li> Insanely fast: GPU is ~1000 times faster than CPU due to parallel computing.
    <li> Open-source: <a href='https://github.com/eruditor/glife3' class=ext>github</a>.
    </ul>
    It's primary target is the search of artificial life (alife) — evolving self-repairing self-replicating structures.<br>
    &rarr; <a href='/show/?glife=Harbinger&fseed=186356772&LF=100'>First emergent appearance of something vaguely resembling Artificial Life in Cellular Automata</a><br>
    &rarr; <a href='/show/?glife=Plexus&fseed=2779294873'>Second one</a>.
          <a href='/show/?gl_run=727595'>Third one</a>.
          <a href='/show/?gl_run=839157'>#4</a>.
          <a href='/show/?gl_run=1023439&maxfps=300'>#5</a>.
          <a href='/show/?gl_run=1023442&maxfps=300'>#6</a>.
          <a href='/?glife=Hive'>#7!</a>.
          <br>
  </div>
";

echo GLifeJS::View();