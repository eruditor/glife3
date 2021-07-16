// CANVAS SIZE ////////////////////////////////////////////////////////////////

var IW = round100(canvas.parentNode.clientWidth);  // container (interface) width
var IH = round100(window.innerHeight);  // window height

//if(document.body.clientWidth < document.body.clientHeight) [FW, FH] = [FH, FW];
var zoom = Math.floor(Math.min(IW / FW, IH / FH));
if(zoom<1) zoom = 1;
if(FD>1 && zoom<2) zoom = 2;  // for displaying 3D case we need at least 2*2 pixels for each cell

canvas.width  = zoom * FW;  canvas.style.width  = canvas.width  + 'px';
canvas.height = zoom * FH;  canvas.style.height = canvas.height + 'px';

if(canvas.width>IW) {  // let canvas spread all its width without limits in case it is bigger than allocated area
  canvas.style.position = 'absolute';  canvas.style.left = '0';
  var tdiv = document.createElement('div');  tdiv.style.height = canvas.style.height;
  canvas.parentNode.insertBefore(tdiv, canvas.nextSibling);
}

// SURFACE ////////////////////////////////////////////////////////////////

class Surface {
  constructor() {
    this.zoom = 1;
    this.left = 0;
    this.top = 0;
  }
}
var surface = new Surface();

// SHOW SHADER ////////////////////////////////////////////////////////////////

function Color4Cell(layer=0, v=1, s=1, l=0.5) {
  var h = 0;
       if(layer==0) h = 300;
  else if(layer==1) h =  60;
  else if(layer==2) h = 180;
  else              h = 300;
       if(v==0) l = 0;
  else if(v==1) h +=  0;
  else if(v==2) h -= 20;
  else if(v==3) h += 20;
  else if(v==4) h -= 40;
  else if(v==5) h += 40;
  return HSL2RGB(h, s, l);
}

var fs_colors = `
    if(v==0u) ret = vec4(0., 0., 0., 1.);
`;
for(var z=0; z<FD; z++) {
  for(var v=1; v<RB; v++) {
    var rgb = Color4Cell(z, v);
    fs_colors += `    else if(layer==`+z+` && v==`+v+`u) ret = vec4(`+(rgb.r/255)+`, `+(rgb.g/255)+`, `+(rgb.b/255)+`, 1.);\n`;
  }
}

var fs_Color4Cell = `
  vec4 Color4Cell(uvec4 cell, int layer) {
    vec4 ret = vec4(0., 0., 0., 1.);
    
    ` + (pixelBits>=32 ? `uint[6] exa = ExtractA(cell.a);` : ``) + `
    
    uint aliv = ` + (pixelBits<32 ? `cell.a` : `exa[A_alive]`) + `;
    
    if(aliv==0u` + (pixelBits<32 ? ` && cell.b==0u` : ``) + `) return ret;
    
    ` + (
      pixelBits<32
      ? `uint v = aliv>0u ? aliv : cell.b % 10u;`
      : `uint v = exa[A_v];`  // aliv>255u ? aliv >> 8u : aliv % 10u
    ) + `
    
    ` + fs_colors + `
    
    ` + (
      pixelBits<32
      ? `float sat = aliv>0u ? 1. : float(cell.b - v) / 255.;`
      : `float sat = aliv>0u ? 1. : float(exa[A_decay] * 15u) / 255.;`
    ) + `
    
    ret.r *= sat;
    ret.g *= sat;
    ret.b *= sat;
    
    return ret;
  }
`;

var ShowFragmentShaderSource = `
  precision mediump float;
  precision highp int;
  
  uniform highp usampler3D u_fieldtexture;  // Field texture, UInt32
  uniform vec2 u_canvas;  // canvas width and height
  uniform vec3 u_surface;  // surface: (left, top, zoom)
  uniform int u_ps[`+ND+`];
  
  in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
  
  out vec4 color;
  
  ` + fs_ExtractA + `
  
  ` + fs_Color4Cell + `
  
  ` + fs_ExtractXY + `
  
  ivec3 tex3coord;
  ivec3 fieldSize;
  ` + fs_ModuloTorus + `
  ` + fs_GetCell() + `
  ` + fs_Trends + `
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    ivec2 xy = ivec2(gl_FragCoord.xy / u_canvas * vec2(fieldSize.xy) * 2.);  // current coords, [0..2F]
    
    // display 3rd dimension (layers 0,1,2,3) as 4 pixels in 2*2 square:
    // | z=0 | z=1 |
    // | z=2 | z=3 |
    int layer = 0;
    ` + (FD>1 ? `
      if((xy.x % 2) == 1) layer += 1;
      if((xy.y % 2) == 1) layer += 2;
    ` : ``) + `
    
    uvec4 cell;
    
    ivec2 tex2coord = ivec2(v_texcoord / u_surface.z - u_surface.xy);
    if(tex2coord.x<0 || tex2coord.y<0 || tex2coord.x>=fieldSize.x || tex2coord.y>=fieldSize.y) {
      color = vec4(0.5, 0.5, 0.5, 1.);
    }
    else {
      cell = texelFetch(u_fieldtexture, ivec3(tex2coord, layer), 0);
      color = Color4Cell(cell, layer);
    }
    
    
    ` + (Mode=='PRT' && zoom>=10 ? `
      ivec3 cur3coord = ivec3(tex2coord, layer);
      int dx3 = (3 + cur3coord.x + u_ps[0]) % 3;
      int dy3 = (3 + cur3coord.y + u_ps[1]) % 3;
      
      ivec2 cnv_coord = ivec2(gl_FragCoord.xy);
      if(cnv_coord.x % `+zoom+` == 0 || cnv_coord.y % `+zoom+` == 0) {  // @todo: support surface
        color = vec4(0.2, 0.2, 0.2, 1.);
        if(dx3==0 && cnv_coord.x % `+zoom+` == 0 || dy3==0 && cnv_coord.y % `+zoom+` == 0) {
          color = vec4(0., 0.7, 0., 1.);
        }
      }
    ` : ``) + `
    
    
    ` + (Mode=='MVM'? `
    int cnv_zoom = int(`+zoom+`. * u_surface.z);
    if(cnv_zoom>=8) {
      tex3coord = ivec3(tex2coord, layer);
      
      uvec4 cells[`+RC+`];
      ` + fs_GetNeibs + `
      
      ivec2 cnv_coord = ivec2( fract(v_texcoord / u_surface.z - u_surface.xy) * `+zoom+`. * u_surface.z );
      
      for(uint n=0u; n<`+RC+`u; n++) {
        if(ExtractAl(cells[n].a)==0u) continue;
        
        ivec4 xy = ExtractXY(cells[n]);
        
        uint trend = CalcTrend(xy);
        uint atrend = antitrends[trend];
        if(atrend!=n) continue;
        
        xy = ExtractXY(uvec4(XY4Trended(n, cells[n]), 0, 0));  // @ optimize it
        
        int px = (xy.x + `+mL+`) * cnv_zoom / `+mL2+`;
        int py = (xy.y + `+mL+`) * cnv_zoom / `+mL2+`;
        
        if(cnv_coord.x % cnv_zoom == px && cnv_coord.y % cnv_zoom == py) {
          color =
            n==0u
            ? vec4(1., 1., 1., 1.)
            : vec4(0., 1., 0., 1.)
          ;
        }
      }
    }
    ` : ``) + `
    
    
  }
`;//console.log(ShowFragmentShaderSource);
var ShowProgram = createProgram4Frag(gl, ShowFragmentShaderSource, ["a_position", "u_fieldtexture", "u_canvas", "u_surface", "u_ps"]);

// SHOW MAIN ////////////////////////////////////////////////////////////////

function Show(single=0, t=-1) {
  if(cfg.paused && single!=1) { return 0; }
  
  gl.useProgram(ShowProgram);
  gl.viewport(0, 0, gl.canvas.width, gl.canvas.height);
  
  gl.bindFramebuffer(gl.FRAMEBUFFER, null);  // render to canvas
  ActivateTexture(t<0 ? T0 : t, ShowProgram.location.u_fieldtexture);
  
  gl.uniform2f(ShowProgram.location.u_canvas, gl.canvas.width, gl.canvas.height);
  gl.uniform3f(ShowProgram.location.u_surface, surface.left, surface.top, surface.zoom);
  
  gl.uniform1iv(ShowProgram.location.u_ps, PS);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
  
  nshow ++;
  
  if(single!=1 && cfg.maxfps>60 && !cfg.showiter) requestAnimationFrame(Show);  // overwise Show() is called in Calc()
}

//  ////////////////////////////////////////////////////////////////