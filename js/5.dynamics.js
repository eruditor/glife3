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

function fs_Prepare2Return(varname='color') {
  var ret = '';
  for(var z=0; z<FD; z++) {
    ret += (z>0 ? `else ` : `     `) + `if(layer==`+z+`) glFragColor[`+z+`] = `+varname+`;\n      `;
  }
  return ret;
}

if(PRT) {
  var CalcFragmentShaderSource = `
    precision mediump float;
    precision highp int;
    
    uniform highp usampler3D u_fieldtexture;  // Field texture
    uniform highp usampler2D u_rulestexture[`+FD+`];  // Rules texture
    
    uniform int u_sqdx;  // global shift of 3*3 squares
    uniform int u_sqdy;
    
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
        int dx3 = (3 + cur3coord.x + u_sqdx) % 3;
        int dy3 = (3 + cur3coord.y + u_sqdy) % 3;
        ivec3 shift2grid = ivec3(1 - dx3, 1 - dy3, 0);
        
        tex3coord = ivec3(v_texcoord, layer) + shift2grid;
        
        // getting cell's neighborhood
        uvec4 cells[`+RC+`];
        ` + fs_GetNeibs + `
        
        // finding rule for this neighborhood
        
        uint rulecoord = 0u;
        for(int n=0; n<`+RC+`; n++) {
          rulecoord *= `+RB+`u;
          rulecoord += cells[n].a;
        }
        ivec2 t = ivec2(rulecoord, 0);
        if(t.x>`+RX+`) { t.y = t.x / `+RX+`;  t.x = t.x % `+RX+`; }
        
        uvec4 rule = GetTexel2D(u_rulestexture, layer, t);
        int new_square = 256 * int(rule.b) + int(rule.a);
        
        int dxdy10 = 10 * dx3 + dy3;
        int pwr = 0;
             if(dxdy10== 0) pwr = 7;
        else if(dxdy10== 1) pwr = 0;
        else if(dxdy10== 2) pwr = 1;
        else if(dxdy10==10) pwr = 6;
        else if(dxdy10==11) pwr = 8;
        else if(dxdy10==12) pwr = 2;
        else if(dxdy10==20) pwr = 5;
        else if(dxdy10==21) pwr = 4;
        else if(dxdy10==22) pwr = 3;
        int bit = (new_square / int(pow(`+RB+`., float(pwr)))) % `+RB+`;
        color.a = uint(bit);
        
        // rule.a is the new color (value) of the cell
        //color.a = rule.a;
        
        uvec4 self = GetCell(dx3 - 1, dy3 - 1, 0);  // previous self cell state
        
        // color.b = color decay value (optional)
             if(color.a>0u) color.b = 200u;           // alive cell
        else if(self.a>0u)  color.b = 100u + self.a;  // dying cell
        else if(self.b>30u) color.b = self.b - 10u;   // color decay for died cell
        else                color.b = 0u;             // empty cell
        
        ` + fs_Prepare2Return('color') + `
      }
    }
  `;
  var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_rulestexture", "u_sqdx", "u_sqdy"]);
}
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
          rulecoord += cells[n].a;
        }
        ivec2 t = ivec2(rulecoord, 0);
        if(t.x>`+RX+`) { t.y = t.x / `+RX+`;  t.x = t.x % `+RX+`; }
        
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
        
        // color.b = color decay value (optional)
             if(color.a>0u) color.b = 200u;           // alive cell
        else if(self.a>0u)  color.b = 100u + self.a;  // dying cell
        else if(self.b>30u) color.b = self.b - 10u;   // color decay for died cell
        else                color.b = 0u;             // empty cell
        
        ` + fs_Prepare2Return('color') + `
      }
    }
  `;
  var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_prevtexture", "u_rulestexture"]);
}

// CALC MAIN ////////////////////////////////////////////////////////////////

function Calc(single=0) {
  if(cfg.paused && single!=1) return 0;
  
  gl.useProgram(CalcProgram);
  gl.viewport(0, 0, FW, FH);
  
  BindBuffersAttachments(Framebuffers[T1]);
  ActivateTexture(T0, CalcProgram.location.u_fieldtexture);
  if(!PRT) ActivateTexture(TT>2 ? T2 : T0, CalcProgram.location.u_prevtexture);
  
  if(PRT) {
    var sqdx = 0, sqdy = 0;
    //sqdx = rndJ(-1, 2);  sqdy = rndJ(-1, 2);
    //sqdx = floor(nturn / 3) % 3 - 1;  sqdy = nturn % 3 - 1;
    var rg = RG[nturn % RC];  sqdx = rg[0];  sqdy = rg[1];
    
    gl.uniform1i(CalcProgram.location.u_sqdx, sqdx);
    gl.uniform1i(CalcProgram.location.u_sqdy, sqdy);
  }
  
  var rulestexture_nums = [];  for(var z=0; z<FD; z++) rulestexture_nums[z] = TT + z;
  gl.uniform1iv(CalcProgram.location.u_rulestexture, rulestexture_nums);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
  
  FlipTime();
  
  nturn ++;
  
  if(single==1) return true;
  
  if(cfg.pauseat>0 && nturn==cfg.pauseat) Pause(1);
  
  if(cfg.showiter) { if(nturn % cfg.showiter == 0) Show(); }
  else if(cfg.maxfps<=60) Show();
  // else Show() rotates in its own cycle
  
  if((nturn % cfg.turn4stats)==0) Stats();
  
  if(cfg.maxfps>1000) { if(nturn%10==0) setTimeout(Calc, 1); else Calc(); }
  else if(cfg.maxfps && cfg.maxfps!=60) setTimeout(Calc, Math.floor(1000 / cfg.maxfps));
  else requestAnimationFrame(Calc);
}

//  ////////////////////////////////////////////////////////////////