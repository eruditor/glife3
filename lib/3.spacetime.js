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
  
  if(Family=='langton') {
    if(0) {
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

var T0 = 0, T1 = 1;  // previous (0) and current (1) moments

function FlipTime() {
  if(T1==1) { T1 = 0;  T0 = 1; } else { T1 = 1;  T0 = 0; } // switching between previous and current moment fields
}

// INIT SPACETIME ////////////////////////////////////////////////////////////////

function InitSpacetime() {
  T0 = 0;  T1 = 1;  // time moments for Calc
  
  InitialFill();
  
  SetTexture(T0, Textures[T0], F, FW, FH, FD);
}

// CREATE TEXTURES ////////////////////////////////////////////////////////////////

Textures[0] = CreateTexture(FW, FH, FD);
Textures[1] = CreateTexture(FW, FH, FD);

var Framebuffers = new Array(2);
Framebuffers[0] = CreateFramebuffer(Textures[0], FD);
Framebuffers[1] = CreateFramebuffer(Textures[1], FD);

//  ////////////////////////////////////////////////////////////////