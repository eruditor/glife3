// CALC ORGA ////////////////////////////////////////////////////////////////
// search for organized structures, potential ogranisms
// orga = flat configuration of alive cells with alive cell in the middle

var ORGA = {};

ORGA.rad = 3;  // radius of orga
ORGA.minlive = 5;  // min number of alive cells in orga
ORGA.maxlive = Math.pow(2*ORGA.rad+1, 2) - 2;  // almost full square is too simplistic to be orga
ORGA.wh = 40;  // distance of same-orga search
ORGA.nmin = 1;  // minimum number of same-neib cells in wh-radius to count in
ORGA.inited = false;

// ORGA.FILL ////////////////////////////////////////////////////////////////

ORGA.FillFragmentShaderSource = `
  precision mediump float;
  precision highp int;
  
  uniform highp usampler3D u_fieldtexture;
  
  in vec2 v_texcoord;  // [0..FW, 0..FH]
  
  out uvec4 glFragColor[`+FD+`];
  
  ivec3 tex3coord;
  ivec3 fieldSize;
  
  ` + fs_ModuloTorus + `
  
  ` + fs_GetCell() + `
  
  #define rad `+ORGA.rad+`
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    
    for(int layer=0; layer<`+FD+`; layer++) {
      tex3coord = ivec3(v_texcoord, layer);
      
      uvec4 curr = texelFetch(u_fieldtexture, tex3coord, 0);
      
      uvec4 color = uvec4(0);
      
      if(curr.a>0u) {
        
        uint summs[4], idx = 0u, bits = 0u, nlive = 0u;
        for(int dx=-rad; dx<=rad; dx++) {
          for(int dy=-rad; dy<=rad; dy++) {
            bits ++;
            if(bits>=32u) {
              bits = 0u;
              idx ++;
            }
            
            uvec4 cell = GetCell(dx, dy, 0);
            
            summs[idx] *= `+RB+`u;
            
            if(cell.a>0u) {
              summs[idx] += cell.a;
              nlive ++;
            }
          }
        }
        
        if(nlive<`+ORGA.minlive+`u) {  // too few alive cells in orga
          color = uvec4(0, 0, 0, 255);
        }
        else if(nlive>`+ORGA.maxlive+`u) {  // almost entirely full square is too simplistic to be an orga structure
          color = uvec4(0, 0, 0, 255);
        }
        else {
          color.r = summs[0];
          color.g = summs[1];
          color.b = summs[2];
          color.a = summs[3];
        }
        
      }
      
      ` + fs_Prepare2Return('color') + `
    }
  }
`;

function FillORGA(plane=0) {
  if(plane==0) { tex = T0;  buf = ORGA.FBF0; }
  else         { tex = T1;  buf = ORGA.FBF1; }
  
  gl.useProgram(ORGA.FillProgram);
  gl.viewport(0, 0, FW, FH);
  
  BindBuffersAttachments(buf);
  ActivateTexture(tex, ORGA.FillProgram.location.u_fieldtexture);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
}

// ORGA.CALC ////////////////////////////////////////////////////////////////

ORGA.CalcFragmentShaderSource = `
  precision mediump float;
  precision highp int;
  
  uniform highp usampler3D u_orgatexF0;
  uniform highp usampler3D u_orgatexF1;
  
  in vec2 v_texcoord;  // [0..FW, 0..FH]
  
  out uvec4 glFragColor[`+FD+`];
  
  ivec3 tex3coord;
  ivec3 fieldSize;
  
  ` + fs_ModuloTorus + `
  
  ` + fs_GetCell('GetCell0', 'u_orgatexF0') + `
  ` + fs_GetCell('GetCell1', 'u_orgatexF1') + `
  
  #define wh `+ORGA.wh+`
  
  void main() {
    fieldSize = textureSize(u_orgatexF0, 0);
    
    uvec4 color = uvec4(0);
    
    for(int layer=0; layer<`+FD+`; layer++) {
      tex3coord = ivec3(v_texcoord, layer);
      
      uint n_all = 0u, n_same = 0u;
      
      uvec4 curr = texelFetch(u_orgatexF0, tex3coord, 0);
      
      if(curr==uvec4(0)) {  // dead cell
        color = uvec4(0);
      }
      else if(curr==uvec4(0, 0, 0, 255)) {  // alive cell but too small orga
        color = uvec4(0, 0, 0, 255);
      }
      else if(curr==texelFetch(u_orgatexF1, tex3coord, 0)) {  // this orga is the same as in previous turn = it's static
        color = uvec4(0, 0, 0, 244);
      }
      else {
        // taking only left part of surrounding square, to avoid double-counting same pair matches
        for(int dx=-wh; dx<=0; dx++) {
          for(int dy=-wh; dy<=wh; dy++) {
            if(dx==0 && dy<=0) continue;
            
            uvec4 cell = GetCell0(dx, dy, 0);
            
            if(cell==uvec4(0)) continue;
            
            n_all ++;
            
            if(cell==curr) n_same ++;
          }
        }
        color.r = n_all;
        color.g = n_same;
        color.b = 0u;
        color.a = 1u;
      }
      
      ` + fs_Prepare2Return('color') + `
    }
  }
`;

function CalcORGA() {
  gl.useProgram(ORGA.CalcProgram);
  gl.viewport(0, 0, FW, FH);
  
  BindBuffersAttachments(ORGA.FBC);
  ActivateTexture(ORGA.TXF0, ORGA.CalcProgram.location.u_orgatexF0);
  ActivateTexture(ORGA.TXF1, ORGA.CalcProgram.location.u_orgatexF1);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
}

// ORGA.SHOW ////////////////////////////////////////////////////////////////

ORGA.ShowFragmentShaderSource = `
  precision mediump float;
  
  uniform highp usampler3D u_orgatexC;
  
  in vec2 v_texcoord;  // [0..FW, 0..FH]
  
  out vec4 color;
  
  uint nmin = `+ORGA.nmin+`u;
  
  void main() {
    ivec2 xy = ivec2(gl_FragCoord.xy);  // canvas coords
    ivec3 texcoord = ivec3(xy, 0);
    ` + (FD>1 ? `
      if(xy.x>=`+FW+`) { texcoord.z += 1;  texcoord.x = xy.x % `+FW+`; }
      if(xy.y>=`+FH+`) { texcoord.z += 2;  texcoord.y = xy.y % `+FH+`; }
    ` : ``) + `
    
    uvec4 texel = texelFetch(u_orgatexC, texcoord, 0);
    uint n_all  = texel.r;
    uint n_same = texel.g;
    
    color = vec4(0, 0, 0, 1);
    
    if(texel==uvec4(0)) {  // dead
      color = vec4(0, 0, 0, 1);
    }
    else if(texel==uvec4(0, 0, 0, 255)) {  // alive but small
      color = vec4(0.2, 0.2, 0.2, 1);
    }
    else if(texel==uvec4(0, 0, 0, 244)) {  // static orga
      color = vec4(0.3, 0, 0, 1);
    }
    else if(n_same<nmin) {  // normal orga but too few of them around
      color = vec4(0, 0, 0.5, 1);
    }
    else {
      float proc = 1000. * float(n_same) / float(n_all);
      if(proc<10.) {
        color = vec4(1, proc/10., 0, 1);
      }
      else if(proc<20.) {
        color = vec4((20.-proc)/10., 1, 0, 1);
      }
      else {
        color = vec4(0, 1, 1, 1);
      }
    }
    
    //color.r = float(texel.r) / 255.;  color.g = float(texel.g) / 255.;  color.b = float(texel.b) / 255.;
  }
`;

function ShowORGA() {
  gl.useProgram(ORGA.ShowProgram);
  gl.viewport(0, 0, gl.canvas.width, gl.canvas.height);
  
  gl.bindFramebuffer(gl.FRAMEBUFFER, null);
  ActivateTexture(ORGA.TXC, ORGA.ShowProgram.location.u_orgatexC);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
}

// ORGA.GET ////////////////////////////////////////////////////////////////

function GetORGA() {
  gl.bindFramebuffer(gl.FRAMEBUFFER, ORGA.FBC);
  for(var z=0; z<FD; z++) {
    gl.readBuffer(gl.COLOR_ATTACHMENT0 + z);
    gl.readPixels(0, 0, FW, FH, glUI32_Format, glUI32_Type, ORGA.F, 4 * FW * FH * z);
  }
  
  function ORGA_GetCell(x, y, z) {
    if(x<0 || y<0 || z<0 || x>FW || y>FH || z>FD) return;
    var s = 4 * (z * FH * FW + y * FW + x);
    return {'r':ORGA.F[s+0], 'g':ORGA.F[s+1], 'b':ORGA.F[s+2], 'a':ORGA.F[s+3]};
  }
  
  var orga = {num: [], sum: []};
  for(var z=0; z<FD; z++) {
    orga.num[z] = 0;  orga.sum[z] = 0;
    for(var x=0; x<FW; x++) {
      for(var y=0; y<FH; y++) {
        var cell = ORGA_GetCell(x, y, z);
        var n_all  = cell.r;
        var n_same = cell.g;
        if(!n_all) continue;
        if(n_same<ORGA.nmin) continue;
        
        orga.num[z] ++;
        orga.sum[z] += 1000 * n_same / n_all;
      }
    }
  }
  return orga;
}

// ORGA.INIT ////////////////////////////////////////////////////////////////

function InitORGA() {
  if(ORGA.inited) return false;
  
  // using UInt32 textures here for big radius or big RB
  
  ORGA.F = new jsUI32_Array(4 * FW * FH * FD);
  
  ORGA.TXF0 = FD+TT+0;  Textures[ORGA.TXF0] = CreateTexture(FW, FH, FD, 'UI32');  // fill-texture for F[T0]
  ORGA.TXF1 = FD+TT+1;  Textures[ORGA.TXF1] = CreateTexture(FW, FH, FD, 'UI32');  // fill-texture for F[T1]
  ORGA.TXC  = FD+TT+2;  Textures[ORGA.TXC ] = CreateTexture(FW, FH, FD, 'UI32');  // calc-texture
  
  ORGA.FBF0 = CreateFramebuffer(Textures[ORGA.TXF0], FD);
  ORGA.FBF1 = CreateFramebuffer(Textures[ORGA.TXF1], FD);
  ORGA.FBC  = CreateFramebuffer(Textures[ORGA.TXC ], FD);
  
  ORGA.FillProgram = createProgram4Frag(gl, ORGA.FillFragmentShaderSource, ["a_position", "u_fieldtexture"]);
  ORGA.CalcProgram = createProgram4Frag(gl, ORGA.CalcFragmentShaderSource, ["a_position", "u_orgatexF0", "u_orgatexF1"]);
  ORGA.ShowProgram = createProgram4Frag(gl, ORGA.ShowFragmentShaderSource, ["a_position", "u_orgatexC"]);
  
  ORGA.inited = true;
}

// ORGA.MAIN ////////////////////////////////////////////////////////////////

function StatORGA(show=false, i=0) {
  InitORGA();
  
  FillORGA(0);
  FillORGA(1);
  
  CalcORGA();
  
  if(show) ShowORGA();
  
  var orga = GetORGA();
  for(var z=0; z<FD; z++) {
    rec[S1].orga_num[z] = round(orga.num[z]);
    rec[S1].orga_sum[z] = round(orga.sum[z]);
  }
  
  return orga;
}

//  ////////////////////////////////////////////////////////////////