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

// array index for samplers must be constant integral expressions, so we need this crap to address texture by layer
// we can use more convenient texture3D, but it's size (dimensions) is more limited than for 2D textures (rules texture for RB>4 exceeds the limit)
var fs_GetTexel2D = `uvec4 GetTexel2D(highp usampler2D tex[`+FD+`], int layer, ivec2 coord) {\n`;
for(var z=0; z<FD; z++) {
  fs_GetTexel2D += `    if(layer==`+z+`) return texelFetch(tex[`+z+`], coord, 0);\n`;
}
fs_GetTexel2D += `  }`;

var fs_GetNeibs = ``;
for(var k in RG) {
  fs_GetNeibs += `      cells[`+k+`] = GetCell(`+RG[k][0]+`, `+RG[k][1]+`, `+RG[k][2]+`);\n`;
}

function fs_Prepare2Return(varname='color') {
  var ret = '';
  for(var z=0; z<FD; z++) {
    ret += (z>0 ? `else ` : `     `) + `if(layer==`+z+`) glFragColor[`+z+`] = `+varname+`;\n      `;
  }
  return ret;
}

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
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    
    uvec4 color;
    
    for(int layer=0; layer<`+FD+`; layer++) {
      
      // getting cell's neighborhood
      
      tex3coord = ivec3(v_texcoord, layer);
      
      uvec4 cells[`+RC+`];
      ` + fs_GetNeibs + `
      
      // finding rule for this neighborhood
      
      uint rulecoord = 0u, nliveneib = 0u;
      for(int n=0; n<`+RC+`; n++) {
        rulecoord *= `+RB+`u;
        uint cellna = cells[n].a;
        if(cellna>200u) {
          rulecoord += (cellna - 200u);
          nliveneib ++;  // number of alive neighbors (for analytics)
        }
      }
      ivec2 t = ivec2(rulecoord, 0);
      if(t.x>`+RX+`) { t.y = t.x / `+RX+`;  t.x = t.x % `+RX+`; }
      
      uvec4 rule = GetTexel2D(u_rulestexture, layer, t);
      
      uvec4 prev = cells[0];  // previous cell state
      
      // setting new cell value (based on the rule)
      // rule.a is the new color (value) of the cell
      // color.a: 200u+ = alive cell, 100u+ - decaying dead cell, 0u = empty cell
      
           if(rule.a>0u)   color.a = 200u + rule.a;   // alive cell
      else if(prev.a>200u) color.a = prev.a - 100u;   // dying cell
      else if(prev.a>30u)  color.a = prev.a - 10u;    // color decay for died cell
      else                 color.a = 0u;              // empty cell
      
      // storing neighboorhood data in rgb of the cell (needed for analytics)
      // one may also store not rulecoord, but some data from rule texel
      
      color.r =  (nliveneib << 4u);         // higher 4 bits for nliveneib
      color.r += (rulecoord >> 16u) % 16u;  // lower 4 bits for rulecoord
      color.g =  (rulecoord >>  8u) % 256u;
      color.b =  (rulecoord >>  0u) % 256u;
      
      ` + fs_Prepare2Return('color') + `
    }
  }
`;
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_rulestexture"]);

// CALC MAIN ////////////////////////////////////////////////////////////////

function Calc() {
  if(cfg.paused) return 0;
  
  gl.useProgram(CalcProgram);
  gl.viewport(0, 0, FW, FH);
  
  BindBuffersAttachments(Framebuffers[T1]);
  ActivateTexture(T0, CalcProgram.location.u_fieldtexture);
  
  var rulestexture_nums = [];  for(var z=0; z<FD; z++) rulestexture_nums[z] = 2 + z;
  gl.uniform1iv(CalcProgram.location.u_rulestexture, rulestexture_nums);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);

  FlipTime();
  
  nturn ++;
  
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