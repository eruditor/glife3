// FOURIER TRANSFORM ////////////////////////////////////////////////////////////////

var DFT = {};


DFT.inited = false;

function InitDFT() {
  if(DFT.inited) return false;
  
  DFT.TX = new Array(2);
  DFT.TX[0] = FD+TT+0;  Textures[DFT.TX[0]] = CreateTexture(FW, FH, FD, 'Fl32');
  DFT.TX[1] = FD+TT+1;  Textures[DFT.TX[1]] = CreateTexture(FW, FH, FD, 'Fl32');
  
  DFT.FB = new Array(2);
  DFT.FB[0] = CreateFramebuffer(Textures[DFT.TX[0]], FD);
  DFT.FB[1] = CreateFramebuffer(Textures[DFT.TX[1]], FD);
  
  DFT.inited = true;
}


DFT.fillinited = false;
DFT.filled = false;

DFT.FillFragmentShaderSource = `
  precision highp float;
  precision highp int;
  
  uniform highp usampler3D u_fieldtexture;
  
  in vec2 v_texcoord;  // [0..FW, 0..FH]
  out vec4 glFragColor[`+FD+`];
  
  void main() {
    for(int layer=0; layer<`+FD+`; layer++) {
      uvec4 pixel =  texelFetch(u_fieldtexture, ivec3(v_texcoord, layer), 0);
      
      vec4 color = vec4(0);
      color.r = pixel.a>0u ? 1. : float(pixel.b) / 255.;
      
      ` + fs_Prepare2Return('color') + `
    }
  }
`;

function FillFT() {
  InitDFT();
  
  if(!DFT.fillinited) {
    DFT.FillProgram = createProgram4Frag(gl, DFT.FillFragmentShaderSource, ["a_position", "u_fieldtexture"]);
    DFT.fillinited = true;
  }
  
  gl.useProgram(DFT.FillProgram);
  gl.viewport(0, 0, FW, FH);
  
  BindBuffersAttachments(DFT.FB[0]);
  ActivateTexture(T0, DFT.FillProgram.location.u_fieldtexture);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
  
  ShowFT();
  
  DFT.filled = true;
}


DFT.filterinited = false;
DFT.FilterFragmentShaderSource = `
  precision highp float;
  precision highp int;
  
  uniform highp sampler3D u_ffttexture;
  
  in vec2 v_texcoord;  // [0..FW, 0..FH]
  out vec4 glFragColor[`+FD+`];
  
  void main() {
    for(int layer=0; layer<`+FD+`; layer++) {
      vec4 f =  texelFetch(u_ffttexture, ivec3(v_texcoord, layer), 0);
      float x = (v_texcoord.x - `+FW+`./2.) / `+FW+`.;
      float y = (v_texcoord.y - `+FH+`./2.) / `+FH+`.;
      vec4 color = f;
      float r2 = f.x*f.x+f.y*f.y;
      if(r2<0.07 || abs(x*y)<0.001 || (x*x+y*y)<0.03) color = vec4(0);
      else color *= 1.7;  // artificial coefficient, just a kludge
      ` + fs_Prepare2Return('color') + `
    }
  }
`;

function FilterFT(back=0) {
  if(!DFT.filled) FillFT();
  
  if(!DFT.filterinited) {
    DFT.FilterProgram = createProgram4Frag(gl, DFT.FilterFragmentShaderSource, ["a_position", "u_ffttexture"]);
    DFT.filterinited = true;
  }
  
  var fb, tx;
  if(!back) { fb = 1;  tx = 0; }
  else      { fb = 0;  tx = 1; }
  
  gl.useProgram(DFT.FilterProgram);
  gl.viewport(0, 0, FW, FH);
  
  BindBuffersAttachments(DFT.FB[fb]);
  ActivateTexture(DFT.TX[tx], DFT.FilterProgram.location.u_ffttexture);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
  
  if(!back) FilterFT(1);
  else ShowFT();
}


DFT.calcinited = false;

DFT.CalcFragmentShaderSource = `
  precision highp float;
  precision highp int;
  
  uniform highp sampler3D u_ffttexture;
  uniform int u_layer;
  uniform int u_invrs;
  uniform int u_horiz;
  
  in vec2 v_texcoord;  // [0..FW, 0..FH]
  out vec4 glFragColor[`+FD+`];
  
  #define PI 3.14159265359
  #define FW `+FW+`
  #define fW `+FW+`.
  #define FH `+FH+`
  #define fH `+FH+`.
  #define fW2 `+round(FW/2)+`.
  #define fH2 `+round(FH/2)+`.
  
  vec2 DFT1(vec2 xy, int layer) {
    float dvapi = 2. * PI;
    if(u_invrs>0) dvapi = -dvapi;
    vec2 sum = vec2(0, 0);
    if(u_horiz>0) {
      for(int i=0; i<FW; i++) {
        vec4 f = texelFetch(u_ffttexture, ivec3(i, xy.y, layer), 0);
        float ii = float(i) + 0.5;
        float a = dvapi * (xy.x-fW2) * (ii-fW2) / fW;
        float c = cos(a);
        float s = sin(a);
        sum.x += f.x * c + f.y * s;
        sum.y += f.y * c - f.x * s;
      }
      sum /= sqrt(fW);
    }
    else {
      for(int j=0; j<FH; j++) {
        vec4 f = texelFetch(u_ffttexture, ivec3(xy.x, j, layer), 0);
        float jj = float(j) + 0.5;
        float a = dvapi * (xy.y-fH2) * (jj-fH2) / fH;
        float c = cos(a);
        float s = sin(a);
        sum.x += f.x * c + f.y * s;
        sum.y += f.y * c - f.x * s;
      }
      sum /= sqrt(fH);
    }
    return sum;
  }
  
  void main() {
    vec2 dft = DFT1(v_texcoord, u_layer);
    vec4 color = vec4(dft, 0, 1);
    `+(function() {
      var ret = '';
      for(var z=0; z<FD; z++) ret += 'glFragColor['+z+'] = u_layer=='+z+' ? color : vec4(0);\n    ';
      return ret;
    })()+`
  }
`;

function CalcDFT(layer=0, invrs=0, horiz=0) {
  InitDFT();
  
  if(!DFT.calcinited) {
    DFT.CalcProgram = createProgram4Frag(gl, DFT.CalcFragmentShaderSource, ["a_position", "u_ffttexture", "u_layer", "u_invrs", "u_horiz"]);
    DFT.calcinited = true;
  }
  
  var fb, tx;
  if(!horiz) { fb = 1;  tx = 0; }
  else       { fb = 0;  tx = 1; }
  
  gl.useProgram(DFT.CalcProgram);
  gl.viewport(0, 0, FW, FH);
  
  BindBuffersAttachments(DFT.FB[fb], layer);  // rendering only 1 layer of 3D texture per call
  ActivateTexture(DFT.TX[tx], DFT.CalcProgram.location.u_ffttexture);
  
  gl.uniform1i(DFT.CalcProgram.location.u_layer, layer);
  gl.uniform1i(DFT.CalcProgram.location.u_invrs, invrs);
  gl.uniform1i(DFT.CalcProgram.location.u_horiz, horiz);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
  
  if(!horiz) CalcDFT(layer, invrs, 1);  // run horizontal after vertical: full 2D-FT = sum of two 1D-FT (rows and columns)
}


DFT.showinited = false;

DFT.ShowFragmentShaderSource = `
  precision mediump float;
  
  uniform highp sampler3D u_ffttexture;
  
  in vec2 v_texcoord;  // [0..FW, 0..FH]
  
  out vec4 color;
  
  #define PI 3.14159265359
  
  void main() {
    ivec2 xy = ivec2(gl_FragCoord.xy);  // canvas coords
    ivec3 texcoord = ivec3(xy, 0);
    ` + (FD>1 ? `
      if(xy.x>=`+FW+`) { texcoord.z += 1;  texcoord.x = xy.x % `+FW+`; }
      if(xy.y>=`+FH+`) { texcoord.z += 2;  texcoord.y = xy.y % `+FH+`; }
    ` : ``) + `
    
    vec4 texel = texelFetch(u_ffttexture, texcoord, 0);
    vec2 dft = texel.rg;
    color.r = 0.;
    color.g = sqrt(dft.x*dft.x + dft.y*dft.y);  if(color.g<0.2) color.g = 0.;  else color.g = log2(1. + color.g);
    color.b = dft.x!=0. ? (0.5 + atan(dft.y, dft.x)/(2.*PI)) : (dft.y>0. ? 0.75 : 0.25);  color.b /= 4.;
    color.a = 1.;
  }
`;

function ShowFT(tx=0) {
  InitDFT();
  
  if(!DFT.showinited) {
    DFT.ShowProgram = createProgram4Frag(gl, DFT.ShowFragmentShaderSource, ["a_position", "u_ffttexture"]);
    DFT.showinited = false;
  }
  
  gl.useProgram(DFT.ShowProgram);
  gl.viewport(0, 0, gl.canvas.width, gl.canvas.height);
  
  gl.bindFramebuffer(gl.FRAMEBUFFER, null);
  ActivateTexture(DFT.TX[tx], DFT.ShowProgram.location.u_ffttexture);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
}


function StatDFT(invrs=0) {
  Pause(1);
  
  if(!DFT.filled) FillFT();
  
  for(var z=0; z<FD; z++) CalcDFT(z, invrs);
  
  ShowFT(0);
}


// InitialFill: circles for DFT testing
          if(0) {
                 if(z==0) { if(sqr(2*((x % 100)-50)/FW) + sqr(2*(y-FH/2)/FW) < 0.01) InitSetCell(x, y, z, 1); }
            else if(z==1) { if(sqr(2*(x-FW/2)/FW) + sqr(2*((y % 100)-50)/FW) < 0.01) InitSetCell(x, y, z, 1); }
            else if(z==2) { if(sqr(2*(((x+y) % 100)-50)/FW) + sqr(2*((x-y)-FH/2)/FW) < 0.01) InitSetCell(x, y, z, 1); }
            //continue;
          }


DFT.html = `
  <td>
    <input type=button value='FillFT' onclick='FillFT();' title='Fill DFT buffer with initial data'>
    <input type=button value='DFT' onclick='StatDFT();' title='Make Discrete Fourier Transform'>
    <input type=button value='IDFT' onclick='StatDFT(1);' title='Inverse Discrete Fourier Transform'>
    <input type=button value='FilterFT' onclick='FilterFT();' title='Cut off central areas'>
    <br>
    <input type=button value='ShowFT0' onclick='ShowFT(0);' title='Show DFT buffer#0'>
    <input type=button value='ShowFT1' onclick='ShowFT(1);' title='Show DFT buffer#1'>
  </td>
`;