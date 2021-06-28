// CALC SHADER ////////////////////////////////////////////////////////////////

var fs_ModuloTorus = `
  ivec3 ModuloTorus(ivec3 a, ivec3 b) {  // make the field torus-shaped (can use simple fract() for float vec3)
    ivec3 ret = a;
    if(a.x<0) ret.x = b.x + a.x;
    if(a.y<0) ret.y = b.y + a.y;
    if(a.z<0) ret.z = b.z + a.z;
    if(a.x>=b.x) ret.x = a.x - b.x;
    if(a.y>=b.y) ret.y = a.y - b.y;
    if(a.z>=b.z) ret.z = a.z - b.z;
    return ret;
  }
`;

function fs_GetCell(func='GetCell', tex='u_fieldtexture') {
  return `
  uvec4 `+func+`(int dx, int dy, int dz) {
         if(dz>0 && tex3coord.z==`+(FD-1)+`) return uvec4(0);  // no upper for top layer
    else if(dz<0 && tex3coord.z==0)          return uvec4(0);  // no lower for bottom layer
    return texelFetch(`+tex+`, ModuloTorus(tex3coord + ivec3(dx, dy, dz), fieldSize), 0);
  }
  `;
}

var fs_GetNeibs = ``;
for(var k in RG) {
  fs_GetNeibs += `      cells[`+k+`] = GetCell(`+RG[k][0]+`, `+RG[k][1]+`, `+RG[k][2]+`);\n`;
}

// array index for samplers must be constant integral expressions, so we need this crap to address texture by layer
// we can use more convenient texture3D, but it's size (dimensions) is more limited than for 2D textures (rules texture for RB>4 exceeds the limit)
var fs_GetTexel2D = `uvec4 GetTexel2D(highp usampler2D tex[`+FD+`], int layer, ivec2 coord) {\n`;
for(var z=0; z<FD; z++) {
  fs_GetTexel2D += `    if(layer==`+z+`) return texelFetch(tex[`+z+`], coord, 0);\n`;
}
fs_GetTexel2D += `  }`;

function fs_PackAliveness(alive='alive') {
  return pixelBits>=32
  ? `
        // highest 16bit = alive cell's color; lowest = decay and color of died cell
             if(`+alive+`>0u)  color.a = `+alive+` << 16u;        // alive cell
        else if(self.a>65535u) color.a = (self.a >> 16u) + 100u;  // dying cell
        else if(self.a>30u)    color.a = self.a - 10u;            // color decay for died cell
        else                   color.a = 0u;                      // empty cell
  `
  : `
        // color.b = color decay value (optional); color.a must be set already!
             if(color.a>0u) color.b = 200u;           // alive cell
        else if(self.a>0u)  color.b = 100u + self.a;  // dying cell
        else if(self.b>30u) color.b = self.b - 10u;   // color decay for died cell
        else                color.b = 0u;             // empty cell
  `;
}


function fs_Prepare2Return(varname='color') {
  var ret = '';
  for(var z=0; z<FD; z++) {
    ret += (z>0 ? `else ` : `     `) + `if(layer==`+z+`) glFragColor[`+z+`] = `+varname+`;\n      `;
  }
  return ret;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(Mode=='PRT') {
  var CalcFragmentShaderSource = `
    precision mediump float;
    precision highp int;
    
    uniform highp usampler3D u_fieldtexture;  // Field texture
    uniform highp usampler2D u_rulestexture[`+FD+`];  // Rules texture
    
    uniform int u_ps[`+ND+`];  // global shift of 3*3 squares
    
    in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
    
    out uvec4 glFragColor[`+FD+`];
    
    ivec3 tex3coord;
    ivec3 fieldSize;
    
    ` + fs_ModuloTorus + `
    
    ` + fs_GetCell() + `
    
    ` + fs_GetTexel2D + `
    
    void main() {
      fieldSize = textureSize(u_fieldtexture, 0);
      
      uvec4 color;
      
      for(int layer=0; layer<`+FD+`; layer++) {
        ivec3 cur3coord = ivec3(v_texcoord, layer);
        int dx3 = (3 + cur3coord.x + u_ps[0]) % 3;
        int dy3 = (3 + cur3coord.y + u_ps[1]) % 3;
        ivec3 shift2grid = ivec3(1 - dx3, 1 - dy3, 0);
        
        tex3coord = ivec3(v_texcoord, layer) + shift2grid;
        
        // getting cell's neighborhood
        uvec4 cells[`+RC+`];
        ` + fs_GetNeibs + `
        
        // finding rule for this neighborhood
        
        uint rulecoord = 0u;
        for(int n=0; n<`+RC+`; n++) {
          rulecoord *= `+RB+`u;
          rulecoord += cells[n].a;  // 8bit-packing only!
        }
        ivec2 t = ivec2(rulecoord, 0);
        if(t.x>=`+RX+`) { t.y = t.x / `+RX+`;  t.x = t.x % `+RX+`; }
        
        uvec4 rule = GetTexel2D(u_rulestexture, layer, t);
        int new_square = 256 * 256 * int(rule.g) + 256 * int(rule.b) + int(rule.a);
        uint u_new_square = 256u * rule.b + rule.a;
        
        int dxdy10 = 10 * dx3 + dy3;
        int pwr = 0;  // coordinate of needed cell in partition's square
             if(dxdy10== 0) pwr = 7;
        else if(dxdy10== 1) pwr = 0;
        else if(dxdy10== 2) pwr = 1;
        else if(dxdy10==10) pwr = 6;
        else if(dxdy10==11) pwr = 8;
        else if(dxdy10==12) pwr = 2;
        else if(dxdy10==20) pwr = 5;
        else if(dxdy10==21) pwr = 4;
        else if(dxdy10==22) pwr = 3;
        
        uint nth_bit = 0u;  // float pow() doesn't work here due to rounding precision!
        for(int n=0; n<=pwr; n++) {
          nth_bit = u_new_square % `+RB+`u;
          u_new_square = (u_new_square - nth_bit) / `+RB+`u;
        }
        
        color.a = nth_bit;
        
        uvec4 self = GetCell(dx3 - 1, dy3 - 1, 0);  // previous self cell state
        
        ` + fs_PackAliveness('color.a') + `
        
        ` + fs_Prepare2Return('color') + `
      }
    }
  `;
  var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_rulestexture", "u_ps"]);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
else if(Mode=='MVM') {
  var CalcFragmentShaderSource = `
    precision mediump float;
    precision highp int;
    
    uniform highp usampler3D u_fieldtexture;  // Field texture
    uniform highp usampler2D u_rulestexture[`+FD+`];  // Rules texture
    
    in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
    
    out uvec4 glFragColor[`+FD+`];
    
    ivec3 tex3coord;
    ivec3 fieldSize;
    
    ` + fs_ModuloTorus + `
    
    ` + fs_GetCell() + `
    
    ` + fs_GetTexel2D + `
    
    ivec4 ExtractCV(uvec4 cell) {
      return ivec4(
        // atom coords
        int(cell.x & 65535u) - 32768,
        int(cell.y & 65535u) - 32768,
        // atom velocity
        int(cell.x >> 16u) - 32768,
        int(cell.y >> 16u) - 32768
      );
    }
    
    uvec2 PackCV(ivec4 cv) {
      return uvec2(
        (cv.x + 32768) | ((cv.z + 32768) << 16),
        (cv.y + 32768) | ((cv.w + 32768) << 16)
      );
    }
    
    uint CalcTrend(ivec4 cv) {
           if(cv.x<-1000 && cv.y<-1000) return 1u;
      else if(cv.x> 1000 && cv.y<-1000) return 3u;
      else if(              cv.y<-1000) return 2u;
      else if(cv.x<-1000 && cv.y> 1000) return 7u;
      else if(cv.x> 1000 && cv.y> 1000) return 5u;
      else if(              cv.y> 1000) return 6u;
      else if(cv.x<-1000              ) return 8u;
      else if(cv.x> 1000              ) return 4u;
      else                              return 0u;
    }
    
    uint antitrends[9] = uint[9](0u, 5u, 6u, 7u, 8u, 1u, 2u, 3u, 4u);
    
    uvec4 XY4Trended(int n, uvec4 cell) {
           if(n==1) return uvec4(cell.x - 2000u, cell.y - 2000u, 5u, 0u);
      else if(n==3) return uvec4(cell.x + 2000u, cell.y - 2000u, 7u, 0u);
      else if(n==2) return uvec4(cell.x        , cell.y - 2000u, 6u, 0u);
      else if(n==7) return uvec4(cell.x - 2000u, cell.y + 2000u, 3u, 0u);
      else if(n==5) return uvec4(cell.x + 2000u, cell.y + 2000u, 1u, 0u);
      else if(n==6) return uvec4(cell.x        , cell.y + 2000u, 2u, 0u);
      else if(n==8) return uvec4(cell.x - 2000u, cell.y        , 4u, 0u);
      else if(n==4) return uvec4(cell.x + 2000u, cell.y        , 8u, 0u);
    }
    
    void main() {
      fieldSize = textureSize(u_fieldtexture, 0);
      
      for(int layer=0; layer<`+FD+`; layer++) {
        tex3coord = ivec3(v_texcoord, layer);
        
        // getting cell's neighborhood
        uvec4 cells[`+RC+`];
        ` + fs_GetNeibs + `
        
        uvec4 self = cells[0];  // previous self cell state
        
        uvec4 color = uvec4(0);
        uint alive = 0u;  // aliveness = living cell type, to be put to color.a
        
        if(self.a>65535u) {  // alive cell  // 32bit-packing only!
          ivec4 cv = ExtractCV(self);
          int xx = cv.x, yy = cv.y;  // atom coords
          int vx = cv.z, vy = cv.w;  // atom velocity
          
          // movement
          cv.x += cv.z;
          cv.y += cv.w;
          
          alive = self.a >> 16u;  // stay same by default
          
          if(self.b!=0u) {  // was trending at previous turn
            uvec4 acceptor = cells[self.b];
            if(acceptor.a<=65535u && acceptor.b==self.b) {  // neighbour empty cell accepted transfer from self to it
              alive = 0u;
            }
            else {
              color.b = CalcTrend(cv);
              if(cv.x<-2000 && cv.z<0 || cv.x>2000 && cv.z>0) { cv.z = -cv.z; }
              if(cv.y<-2000 && cv.w<0 || cv.y>2000 && cv.w>0) { cv.w = -cv.w; }
            }
          }
          else {
            color.b = CalcTrend(cv);  // wanted transfer marker
          }
          
          // packing back 2*16bit to 1*32bit
          uvec2 packcv = PackCV(cv);
          color.x = packcv.x;
          color.y = packcv.y;
        }
        else {  // empty cell
          if(self.b>0u) {  // accepting cell
            uint atrend = antitrends[self.b];
            uvec4 trender = cells[atrend];
            if(trender.a>65535u && trender.b==self.b) {
              color = self;  // motion skips a beat here
              color.b = 0u;
              alive = trender.a >> 16u;
            }
          }
          else {  // looking for something to accept
            for(int n=1; n<`+RC+`; n++) {
              if(cells[n].b==0u || cells[n].b>10000u) continue;
              
              uint atrend = antitrends[n];
              
              if(cells[n].b!=atrend) continue;
              
              color = XY4Trended(n, cells[n]);  // last of trending nearby cells wins
            }
          }
        }
        
        ` + fs_PackAliveness('alive') + `
        
        ` + fs_Prepare2Return('color') + `
      }
    }
  `;//console.log(CalcFragmentShaderSource);
  var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_rulestexture", "u_ps"]);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
else {
  var CalcFragmentShaderSource = `
    precision mediump float;
    precision highp int;
    
    uniform highp usampler3D u_fieldtexture;  // Field texture
    uniform highp usampler3D u_prevtexture;  // Previous Field texture
    uniform highp usampler2D u_rulestexture[`+FD+`];  // Rules texture
    
    in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
    
    out uvec4 glFragColor[`+FD+`];
    
    ivec3 tex3coord;
    ivec3 fieldSize;
    
    ` + fs_ModuloTorus + `
    
    ` + fs_GetCell() + `
    
    ` + (TT>2 ? fs_GetCell('GetPrevCell', 'u_prevtexture') : ``) + `
    
    ` + fs_GetTexel2D + `
    
    void main() {
      fieldSize = textureSize(u_fieldtexture, 0);
      
      uvec4 color;
      
      for(int layer=0; layer<`+FD+`; layer++) {
        
        // getting cell's neighborhood
        
        tex3coord = ivec3(v_texcoord, layer);
        
        uvec4 cells[`+RC+`];
        ` + fs_GetNeibs + `
        
        // finding rule for this neighborhood
        
        uint rulecoord = 0u;
        for(int n=0; n<`+RC+`; n++) {
          rulecoord *= `+RB+`u;
          rulecoord += cells[n].a;  // 8bit-packing only!
        }
        ivec2 t = ivec2(rulecoord, 0);
        if(t.x>=`+RX+`) { t.y = t.x / `+RX+`;  t.x = t.x % `+RX+`; }
        
        uvec4 rule = GetTexel2D(u_rulestexture, layer, t);
        
        // rule.a is the new color (value) of the cell
        color.a = rule.a;
        
        ` + (TT>2 ? `
        uvec4 prev = GetPrevCell(0, 0, 0);
        // color.a - prev.a
        if(prev.a>0u) {
          int inewv = int(color.a) - int(prev.a);
          if(inewv<0) inewv += `+RB+`;
          color.a = uint(inewv);
        }
        ` : ``) + `
        
        uvec4 self = cells[0];  // previous self cell state
        
        ` + fs_PackAliveness('color.a') + `
        
        ` + fs_Prepare2Return('color') + `
      }
    }
  `;
  var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_prevtexture", "u_rulestexture"]);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


// CALC MAIN ////////////////////////////////////////////////////////////////

function Calc(single=0) {
  if(cfg.paused && single!=1) return 0;
  
  gl.useProgram(CalcProgram);
  gl.viewport(0, 0, FW, FH);
  
  BindBuffersAttachments(Framebuffers[T1]);
  ActivateTexture(T0, CalcProgram.location.u_fieldtexture);
  if(Mode!='PRT') ActivateTexture(TT>2 ? T2 : T0, CalcProgram.location.u_prevtexture);
  
  if(Mode=='PRT') {
    //PS[0] = rndJ(-1, 2);  PS[1] = rndJ(-1, 2);
    PS[0] = floor(nturn / 3) % 3 - 1;  PS[1] = nturn % 3 - 1;
    //PS[0] = RG[nturn % RC][0];  PS[1] = RG[nturn % RC][1];
    
    gl.uniform1iv(CalcProgram.location.u_ps, PS);
  }
  
  var rulestexture_nums = [];  for(var z=0; z<FD; z++) rulestexture_nums[z] = TT + z;
  gl.uniform1iv(CalcProgram.location.u_rulestexture, rulestexture_nums);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
  
  FlipTime();
  
  nturn ++;
  
  if(single==1) return true;
  
  if(cfg.showiter) { if(nturn % cfg.showiter == 0) Show(); }
  else if(cfg.maxfps<=60) Show();
  /* else Show() rotates in its own cycle */
  
  if((nturn % cfg.turn4stats)==0) Stats();
  
  if(cfg.pauseat>0 && nturn==cfg.pauseat) Pause(1);
  
  if(cfg.maxfps>1000) { if(nturn%10==0) setTimeout(Calc, 1); else Calc(); }
  else if(cfg.maxfps && cfg.maxfps!=60) setTimeout(Calc, Math.floor(1000 / cfg.maxfps));
  else requestAnimationFrame(Calc);
}

//  ////////////////////////////////////////////////////////////////