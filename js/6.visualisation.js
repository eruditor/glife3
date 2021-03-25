// CANVAS SIZE ////////////////////////////////////////////////////////////////

var IW = round100(canvas.parentNode.clientWidth);  // container (interface) width

//if(document.body.clientWidth < document.body.clientHeight) [FW, FH] = [FH, FW];
var zoom = Math.floor(IW / FW);
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
    if(cell.a==0u) return ret;
    uint v;
    v = cell.a>200u ? (cell.a - 200u) : (cell.a % 10u);
    ` + fs_colors + `
    float sat = cell.a>200u ? 1. : float(cell.a) / 255.;
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
  
  in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
  
  out vec4 color;
  
  ` + fs_Color4Cell + `
  
  void main() {
    ivec3 fieldSize = textureSize(u_fieldtexture, 0);
    ivec2 xy = ivec2(gl_FragCoord.xy / u_canvas * vec2(fieldSize.xy) * 2.);  // current coords, [0..2F]
    
    // display 3rd dimension (layers 0,1,2,3) as 4 pixels in 2*2 square:
    // | z=0 | z=1 |
    // | z=2 | z=3 |
    int layer = 0;
    ` + (FD>1 ? `
      if((xy.x % 2) == 1) layer += 1;
      if((xy.y % 2) == 1) layer += 2;
    ` : ``) + `
    
    ivec2 tex2coord = ivec2(v_texcoord / u_surface.z - u_surface.xy);
    if(tex2coord.x<0 || tex2coord.y<0 || tex2coord.x>=fieldSize.x || tex2coord.y>=fieldSize.y) {
      color = vec4(0.5, 0.5, 0.5, 1.);
    }
    else {
      uvec4 cell = texelFetch(u_fieldtexture, ivec3(tex2coord, layer), 0);
      color = Color4Cell(cell, layer);
    }
  }
`;
var ShowProgram = createProgram4Frag(gl, ShowFragmentShaderSource, ["a_position", "u_fieldtexture", "u_canvas", "u_surface"]);

// SHOW MAIN ////////////////////////////////////////////////////////////////

function Show(single=0, t=-1) {
  if(cfg.paused && single!=1) { return 0; }
  
  gl.useProgram(ShowProgram);
  gl.viewport(0, 0, gl.canvas.width, gl.canvas.height);
  
  gl.bindFramebuffer(gl.FRAMEBUFFER, null);  // render to canvas
  ActivateTexture(t<0 ? T0 : t, ShowProgram.location.u_fieldtexture);
  
  gl.uniform2f(ShowProgram.location.u_canvas, gl.canvas.width, gl.canvas.height);
  gl.uniform3f(ShowProgram.location.u_surface, surface.left, surface.top, surface.zoom);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
  
  nshow ++;
  
  if(single!=1 && cfg.maxfps>60 && !cfg.showiter) requestAnimationFrame(Show);  // overwise Show() is called in Calc()
}

//  ////////////////////////////////////////////////////////////////