// SPACE (FIELD) ////////////////////////////////////////////////////////////////

var F = new jsdata_Array(4 * FW * FH * FD);

function SetCell(x, y, z, r, g, b, a) {
  if(x<0 || y<0 || z<0 || x>FW || y>FH || z>FD) return;
  var s = 4 * (z * FH * FW + y * FW + x);
  F[s+0] = r;
  F[s+1] = g;
  F[s+2] = b;
  F[s+3] = a;
}

function GetCell(x, y, z) {
  if(x<0 || y<0 || z<0 || x>FW || y>FH || z>FD) return;
  var s = 4 * (z * FH * FW + y * FW + x);
  return {'r':F[s+0], 'g':F[s+1], 'b':F[s+2], 'a':F[s+3]};
}

function InitSetCell(x, y, z, v) {
  SetCell(x, y, z, 0, 0, 0, v>0 ? 200+v : 0);
}

function InitialFill() {
  F.fill(0);  // zeroing F in case this call is not the first
  
  if(Family=='Langton') {
    if(LF<=0.0101) {
      InitSetCell(round(FW/2), round(FH/2), 1, 1);
    }
    else {
      var z = 1;
      for(var x=round(FW/2-FW*LF/2); x<round(FW/2+FW*LF/2); x++) {
        for(var y=round(FH/2-FH*LF/2); y<round(FH/2+FH*LF/2); y++) {
          if(y<0 || y>=FH) continue;
          var density = round((1 - Math.abs(2*y/FH-1)/LF - Math.abs(2*x/FW-1)/LF) * 10);
          if(rndF(0,10)<density) {
            InitSetCell(x, y, z, rndF(1, RB));
          }
        }
      }
    }
  }
  else if(cfg.debug) {
    if(1) {
      InitSetCell(1, 2, 0, 1);
      InitSetCell(2, 2, 0, 1);
      InitSetCell(3, 2, 0, 1);
      InitSetCell(4, 3, 0, 1);
    }
    return;
  }
  else if(Family=='Conway' && LF==0.33) {
    for(var d=1; d<1000; d++) {
      var x = round(FW/2 + FW/4*Math.cos(2*Math.PI*d/1000));
      var y = round(FH/2 + FH/4*Math.sin(2*Math.PI*d/1000));
      var z = 0;
      var v = 1;
      InitSetCell(x, y, z, v);
    }
  }
  else {
    for(var z=0; z<FD; z++) {
      for(var x=round(FW/2-FW*LF/2); x<round(FW/2+FW*LF/2); x++) {
        for(var y=round(FH/2-FH*LF/2); y<round(FH/2+FH*LF/2); y++) {
          if(y<0 || y>=FH) continue;
          if(z>=1 && x>FW/2) continue;
          if(z>=2 && y<FH/2) continue;
          var density = round((1 - Math.abs(2*y/FH-1)/LF) * 10);
          if(rndF(0,10)<density) {
            var v = RB>2 ? rndF(1, RB) : 1;
            InitSetCell(x, y, z, v);
          }
        }
      }
    }
  }
}

// TIME (ITERATIONS) ////////////////////////////////////////////////////////////////

var T0 = 0, T1 = 1, T2 = 2;  // previous (0) and current (1) moments; or T2=t+1 (next), T1=t+0 (prev), T0=t-1 (grand-parent)

var DT = 1;

function FlipTime() {  // switching between previous and current moment fields
  if(TT==2) {
    if(T0==0) { T0 = 1;  T1 = 0; }
    else      { T0 = 0;  T1 = 1; }
  }
  else if(TT==3) {
    if(DT>0) {
           if(T0==0) { T0 = 1;  T1 = 2;  T2 = 0; }
      else if(T0==1) { T0 = 2;  T1 = 0;  T2 = 1; }
      else           { T0 = 0;  T1 = 1;  T2 = 2; }
    }
    else {
           if(T0==0) { T0 = 2;  T1 = 1;  T2 = 0; }
      else if(T0==1) { T0 = 0;  T1 = 2;  T2 = 1; }
      else           { T0 = 1;  T1 = 0;  T2 = 2; }
    }
  }
  else { alert('incorrect time configuration'); }
}

// INIT SPACETIME ////////////////////////////////////////////////////////////////

function InitSpacetime() {
  T0 = 0;  T1 = 1;  T2 = 2;  // time moments for Calc
  
  // clearing textures: required for TT=3 case
  F.fill(0);  for(var t=0; t<TT; t++) SetTexture(t, Textures[t], F, FW, FH, FD);
  
  // setting initial field
  InitialFill();  SetTexture(T0, Textures[T0], F, FW, FH, FD);
  
  // setting previous moment
  if(TT>2) { InitialFill();  SetTexture(T2, Textures[T2], F, FW, FH, FD); }
}

// CREATE TEXTURES ////////////////////////////////////////////////////////////////

for(var t=0; t<TT; t++) Textures[t] = CreateTexture(FW, FH, FD);

var Framebuffers = new Array(TT);
for(var t=0; t<TT; t++) Framebuffers[t] = CreateFramebuffer(Textures[t], FD);

//  ////////////////////////////////////////////////////////////////