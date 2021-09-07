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
  var alpha =
    v > 0
    ? (DataFormat=='UI32' ? v + 32767 : v)
    : 0
  ;
  SetCell(x, y, z, 0, 0, 0, alpha);
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
  else if(cfg.debug && 0) {
    if(1) {
      var FW2 = round(FW/2);
      var FH2 = round(FH/2);
      InitSetCell(FW2  , FH2+1, 0, 1);
      InitSetCell(FW2+1, FH2  , 0, 1);
      InitSetCell(FW2-1, FH2-1, 0, 1);
      InitSetCell(FW2  , FH2-1, 0, 1);
      InitSetCell(FW2+1, FH2-1, 0, 1);
      
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
  else if(Mode=='MVM') {
    if(cfg.debug==1) {
      var vv = 215;
      SetCell( 9, 4, 0, (2000 + 32768) + (vv + 32768) * 65536, (2000 + 32768) + (-vv + 32768) * 65536, 0, (1 << 1) + 1);
      SetCell(10, 5, 0, (   0 + 32768) + ( 0 + 32768) * 65536, (   0 + 32768) + (  0 + 32768) * 65536, 0, (3 << 1) + 1);
    }
    else if(cfg.debug==2) {
      var vv = 215;
      SetCell( 8, 3, 0, (   0 + 32768) + (vv + 32768) * 65536, (   0 + 32768) + ( vv + 32768) * 65536, 0, (1 << 1) + 1);
      SetCell( 8, 5, 0, (   0 + 32768) + (vv + 32768) * 65536, (   0 + 32768) + (  0 + 32768) * 65536, 0, (2 << 1) + 1);
      SetCell( 8, 7, 0, (   0 + 32768) + (vv + 32768) * 65536, (   0 + 32768) + (-vv + 32768) * 65536, 0, (3 << 1) + 1);
    }
    else if(cfg.debug==3) {
      var vv = 0;
      SetCell( 9, 4, 0, (-4000 + 32768) + (vv + 32768) * 65536, (   0 + 32768) + (-vv + 32768) * 65536, 0, (1 << 1) + 1);
      SetCell(10, 4, 0, (    0 + 32768) + ( 0 + 32768) * 65536, (   0 + 32768) + (  0 + 32768) * 65536, 0, (3 << 1) + 1);
    }
    else if(cfg.debug==4) {
      var vv = 0;
      SetCell( 9, 4, 0, (-4000 + 32768) + ( 0 + 32768) * 65536, (    0 + 32768) + ( 50 + 32768) * 65536, 0, (1 << 1) + 1);
      SetCell(10, 4, 0, (    0 + 32768) + ( 0 + 32768) * 65536, (    0 + 32768) + (  0 + 32768) * 65536, 0, (3 << 1) + 1);
      //SetCell(11, 4, 0, ( 4000 + 32768) + ( 0 + 32768) * 65536, (    0 + 32768) + (  0 + 32768) * 65536, 0, (1 << 1) + 1);
      SetCell(10, 3, 0, (    0 + 32768) + (80 + 32768) * 65536, (-3000 + 32768) + (  0 + 32768) * 65536, 0, (2 << 1) + 1);
      //SetCell(10, 5, 0, (    0 + 32768) + ( 0 + 32768) * 65536, ( 3500 + 32768) + (  0 + 32768) * 65536, 0, (2 << 1) + 1);
    }
    else if(cfg.debug==5) {
      SetCell( 3, 3, 0, (0 + 32768) + (210 + 32768) * 65536, (0 + 32768) + ( 120 + 32768) * 65536, 0, (1 << 1) + 1);
      SetCell( 3, 5, 0, (0 + 32768) + (200 + 32768) * 65536, (0 + 32768) + (   0 + 32768) * 65536, 0, (2 << 1) + 1);
      SetCell( 3, 7, 0, (0 + 32768) + (220 + 32768) * 65536, (0 + 32768) + (-105 + 32768) * 65536, 0, (1 << 1) + 1);
      //SetCell(12, 5, 0, (0 + 32768) + (  0 + 32768) * 65536, (0 + 32768) + (   0 + 32768) * 65536, 0, (1 << 1) + 1);
    }
    else if(cfg.debug==6) {
      SetCell( 4, 5, 0, (0 + 32768) + (220 + 32768) * 65536, (0 + 32768) + (   0 + 32768) * 65536, 0, (1 << 1) + 1);
      SetCell( 5, 3, 0, (0 + 32768) + (  0 + 32768) * 65536, (0 + 32768) + ( 200 + 32768) * 65536, 0, (2 << 1) + 1);
    }
    else if(cfg.debug==7) {
      SetCell( 3, 5, 0, (0 + 32768) + (220 + 32768) * 65536, (-3000 + 32768) + (   0 + 32768) * 65536, 0, (1 << 1) + 1);
      SetCell( 5, 5, 0, (0 + 32768) + (  0 + 32768) * 65536, ( 3000 + 32768) + (   0 + 32768) * 65536, 0, (2 << 1) + 1);
    }
    else if(cfg.debug==8) {
      SetCell( 2, 2, 0, (0 + 32768) + (-200 + 32768) * 65536, (0 + 32768) + (-200 + 32768) * 65536, 0, (1 << 1) + 1);
      SetCell( 2, 4, 0, (0 + 32768) + (-200 + 32768) * 65536, (0 + 32768) + (   0 + 32768) * 65536, 0, (1 << 1) + 1);
      SetCell( 2, 6, 0, (0 + 32768) + (-200 + 32768) * 65536, (0 + 32768) + ( 200 + 32768) * 65536, 0, (1 << 1) + 1);
      SetCell( 4, 2, 0, (0 + 32768) + (   0 + 32768) * 65536, (0 + 32768) + (-200 + 32768) * 65536, 0, (1 << 1) + 1);
      SetCell( 4, 4, 0, (0 + 32768) + (   0 + 32768) * 65536, (0 + 32768) + (   0 + 32768) * 65536, 0, (1 << 1) + 1);
      SetCell( 4, 6, 0, (0 + 32768) + (   0 + 32768) * 65536, (0 + 32768) + ( 200 + 32768) * 65536, 0, (1 << 1) + 1);
      SetCell( 6, 2, 0, (0 + 32768) + ( 200 + 32768) * 65536, (0 + 32768) + (-200 + 32768) * 65536, 0, (1 << 1) + 1);
      SetCell( 6, 4, 0, (0 + 32768) + ( 200 + 32768) * 65536, (0 + 32768) + (   0 + 32768) * 65536, 0, (1 << 1) + 1);
      SetCell( 6, 6, 0, (0 + 32768) + ( 200 + 32768) * 65536, (0 + 32768) + ( 200 + 32768) * 65536, 0, (1 << 1) + 1);
    }
    else {
      var lstep = 1000;
      var speedlimit = round(mL/100);
      for(var z=0; z<FD; z++) {
        for(var x=round(FW/2-FW*LF/2); x<round(FW/2+FW*LF/2); x++) {
          for(var y=round(FH/2-FH*LF/2); y<round(FH/2+FH*LF/2); y++) {
            if(y<0 || y>=FH) continue;
            if(rndF(0,lstep)<100*LD) {
              var rv = rndF(0, 1000);
              var v = (rv>=1000/2 ? 1 : (rv>=1000/4 ? 2 : (rv>=1000/8 ? 3 : 0)));
              if(v>=RB) v = RB - 1;
              if(v==0) continue;
              var xx = rndF(-mL+1, mL-1) + 32768;
              var yy = rndF(-mL+1, mL-1) + 32768;
              var vx = rndF(-speedlimit, speedlimit) + 32768;
              var vy = rndF(-speedlimit, speedlimit) + 32768;
              SetCell(x, y, z, xx + vx * 65536, yy + vy * 65536, 0, (v << 1) + 1);
            }
          }
        }
      }
    }
  }
  else if(Mode=='BND') {
    if(cfg.debug==1) {
      SetCell(2, 2, 0, 2, 0, 0, (1 << 1) + 1);
      SetCell(7, 2, 0, 4, 0, 0, (2 << 1) + 1);
      SetCell(2, 4, 0, 2, 0, 0, (1 << 1) + 1);
      SetCell(8, 4, 0, 4, 0, 0, (2 << 1) + 1);
    }
    else {
      var lstep = 1000;
      var speedlimit = round(mL/100);
      for(var z=0; z<FD; z++) {
        for(var x=round(FW/2-FW*LF/2); x<round(FW/2+FW*LF/2); x++) {
          for(var y=round(FH/2-FH*LF/2); y<round(FH/2+FH*LF/2); y++) {
            if(y<0 || y>=FH) continue;
            if(rndF(0,lstep)<100*LD) {
              var rv = rndF(0, 1000);
              var v = (rv>=1000/2 ? 1 : (rv>=1000/4 ? 2 : (rv>=1000/8 ? 3 : 0)));
              if(v>=RB) v = RB - 1;
              if(v==0) continue;
              var speed = rndF(0, 5);
              SetCell(x, y, z, speed, 0, 0, (v << 1) + 1);
            }
          }
        }
      }
    }
  }
  else {
    for(var z=0; z<FD; z++) {
      for(var x=round(FW/2-FW*LF/2); x<round(FW/2+FW*LF/2); x++) {
        for(var y=round(FH/2-FH*LF/2); y<round(FH/2+FH*LF/2); y++) {
          if(y<0 || y>=FH) continue;
          if(z>=1 && x>FW/2) continue;
          if(z>=2 && y<FH/2) continue;
          var density = round((1 - Math.abs(2*y/FH-1)/LF) * Lstep * LD);
          if(rndF(0,Lstep)<density) {
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

var DT = 1;  // direction of time (1=forward, -1=backward)

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

// PARTITIONS ////////////////////////////////////////////////////////////////
// partitions are shifting neib boundaries (see Toffoli & Margolus)

const ND = FD>1 ? 3 : 2;  // number of space dimensions
var PS = new Array(ND);  // shift in x,y,z-coord for current turn

// INIT SPACETIME ////////////////////////////////////////////////////////////////

function InitSpacetime() {
  T0 = 0;  T1 = 1;  T2 = 2;  // time moments for Calc
  
  // clearing textures: required for TT=3 case
  F.fill(0);  for(var t=0; t<TT; t++) SetTexture(t, Textures[t], F, FW, FH, FD);
  
  // setting initial field
  InitialFill();  SetTexture(T0, Textures[T0], F, FW, FH, FD);
  
  // setting previous moment
  if(TT>2) { InitialFill();  SetTexture(T2, Textures[T2], F, FW, FH, FD); }
  
  // clearing partition shift
  for(var d=0; d<ND; d++) PS[d] = 0;
}

// CREATE TEXTURES ////////////////////////////////////////////////////////////////

for(var t=0; t<TT; t++) Textures[t] = CreateTexture(FW, FH, FD);

var Framebuffers = new Array(TT);
for(var t=0; t<TT; t++) Framebuffers[t] = CreateFramebuffer(Textures[t], FD);

//  ////////////////////////////////////////////////////////////////