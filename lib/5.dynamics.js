// CALC SHADER ////////////////////////////////////////////////////////////////

// array index for samplers must be constant integral expressions, so we need this crap to address texture by layer
// we can use more convenient texture3D, but it's size (dimensions) is more limited than for 2D textures (rules texture for RB>4 exceeds the limit)
var fs_GetTexel = `
  uvec4 GetTexel(usampler2D tex[`+FD+`], int layer, ivec2 coord) {
`;
for(var z=0; z<FD; z++) {
  fs_GetTexel += `
    if(layer==`+z+`) return texelFetch(tex[`+z+`], coord, 0);
  `;
}
fs_GetTexel += `
  }
`;

var fs_GetNeib = ``;
for(var k in RG) {
  fs_GetNeib += `
    cells[`+k+`] = GetCell(`+RG[k][0]+`, `+RG[k][1]+`, `+RG[k][2]+`);
  `;
}

var CalcFragmentShaderSource = `
  #version 300 es
  precision mediump float;
  precision highp int;
  
  uniform highp usampler3D u_fieldtexture;  // Field texture
  uniform highp usampler2D u_rulestexture[`+FD+`];  // Rules texture
  
  in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
  
  out uvec4 glFragColor[`+FD+`];
  
  ivec3 tex3coord;
  ivec3 fieldSize;
  
  ivec3 modulo3(ivec3 a, ivec3 b) {  // make the field torus-shaped (can use simple fract() for float vec3)
    ivec3 ret = a;
    if(a.x<0) ret.x = b.x + a.x;
    if(a.y<0) ret.y = b.y + a.y;
    if(a.z<0) ret.z = b.z + a.z;
    if(a.x>=b.x) ret.x = a.x - b.x;
    if(a.y>=b.y) ret.y = a.y - b.y;
    if(a.z>=b.z) ret.z = a.z - b.z;
    return ret;
  }
  
  uvec4 GetCell(int x, int y, int z) {
         if(z>0 && tex3coord.z==`+(FD-1)+`) return uvec4(0);  // no upper for top layer
    else if(z<0 && tex3coord.z==0)          return uvec4(0);  // no lower for bottom layer
    return texelFetch(u_fieldtexture, modulo3(tex3coord + ivec3(x, y, z), fieldSize), 0);
  }
  
  ` + fs_GetTexel + `
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    
    uvec4 colors[`+FD+`];
    uvec4 color;
    
    for(int layer=0; layer<`+FD+`; layer++) {
      tex3coord = ivec3(v_texcoord, layer);
      
      uvec4 cells[`+RC+`];
      ` + fs_GetNeib + `
      
      uint rulecoord = 0u;
      for(int n=0; n<`+RC+`; n++) {
        rulecoord *= `+RB+`u;
        rulecoord += cells[n].a;
      }
      ivec2 t = ivec2(rulecoord, 0);
      if(t.x>`+RX+`) { t.y = t.x / `+RX+`;  t.x = t.x % `+RX+`; }
      
      uvec4 rule = GetTexel(u_rulestexture, layer, t);
      
      color = cells[0];  // by default cell stays the same as in previous turn
      
           if(rule.a>0u)   color.b = 255u;
      else if(color.a>0u)  color.b = 100u + color.a;  // live cell dying
      else if(color.b>30u) color.b -= 10u;  // color decay for died cell
      else                 color.b = 0u;
      
      color.a = rule.a;
      
      colors[layer] = color;
    }
    
    glFragColor[0] = colors[0];
    ` + (FD>1 ? `glFragColor[1] = colors[1];` : ``) + `
    ` + (FD>2 ? `glFragColor[2] = colors[2];` : ``) + `
    ` + (FD>3 ? `glFragColor[3] = colors[3];` : ``) + `
  }
`;

var CalcFragmentShader = createShader(gl, gl.FRAGMENT_SHADER, CalcFragmentShaderSource.trim());

var CalcProgram = createProgram(gl, CommonVertexShader, CalcFragmentShader, ["a_position", "u_fieldtexture", "u_rulestexture"]);

// CALC MAIN ////////////////////////////////////////////////////////////////

function Calc() {
  if(cfg.paused) return 0;
  
  gl.useProgram(CalcProgram);
  
  gl.bindFramebuffer(gl.FRAMEBUFFER, Framebuffers[T1]);
  
  var color_attachments = [];
  for(let layer=0; layer<FD; layer++) {
    color_attachments[layer] = gl.COLOR_ATTACHMENT0 + layer;
  }
  gl.drawBuffers(color_attachments);
  
  gl.viewport(0, 0, FW, FH);
  
  gl.activeTexture(gl.TEXTURE0 + T0);
  gl.bindTexture(gl.TEXTURE_3D, Textures[T0]);
  gl.uniform1i(CalcProgram.location.u_fieldtexture, T0);
  gl.uniform1iv(CalcProgram.location.u_rulestexture, [2,3,4]);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);

  FlipTime();
  
  nturn ++;
  
  if(cfg.showiter) { if(nturn % cfg.showiter == 0) Show(); }
  else if(cfg.maxfps<=60) Show();
  // else Show() rotates in its own cycle

  if((nturn % cfg.turn4stats)==0) Stats();
  
  if(cfg.maxfps>1000) { if(nturn%10==0) setTimeout(Calc, 1); else Calc(); }
  else if(cfg.maxfps && cfg.maxfps!=60) setTimeout(Calc, Math.floor(1000 / cfg.maxfps));
  else requestAnimationFrame(Calc);
}
