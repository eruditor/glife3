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
  if(Mode=='MVM') {
         if(v==0) return {'r':  0, 'g':  0, 'b':  0};
    else if(v==1) return {'r':  0, 'g':  0, 'b':255};
    else if(v==2) return {'r':220, 'g':  0, 'b':  0};
    else if(v==3) return {'r':  0, 'g':200, 'b':  0};
  }
  else if(Mode=='BND') {
         if(v==0) return {'r':  0, 'g':  0, 'b':  0};
    else if(v==1) return {'r':200, 'g':  0, 'b':200};
    else if(v==2) return {'r':180, 'g':180, 'b':  0};
    else if(v==3) return {'r':  0, 'g':200, 'b':200};
  }
  
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
    
    uint aliv = ExtractAl(cell);
    uint decay = ExtractDecay(cell);
    
    if(aliv==0u && decay==0u) return ret;
    
    uint v = ExtractFl(cell);
    
    ` + fs_colors + `
    
    float sat = aliv>0u ? 1. : float(decay * 15u) / 255.;
    
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
  uniform highp uint u_nturn;  // nturn
  
  in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
  
  out vec4 color;
  
  ` + fs_ExtractRGBA + `
  
  ` + fs_ExtractA + `
  
  ` + fs_Color4Cell + `
  
  ` + fs_ExtractXY + `
  
  ivec3 tex3coord;
  ivec3 fieldSize;
  uint dbg;
  ` + fs_ModuloTorus + `
  ` + fs_GetCell() + `
  ` + fs_Trends + `
  
  int isqr(int v) { return v * v; }
  
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
        if(ExtractAl(cells[n])==0u) continue;
        
        ivec4 xy = ExtractXY(cells[n]);
        
        uint trend = CalcTrend(xy);
        uint atrend = antitrends[trend];
        if(atrend!=n) continue;
        
        xy = ExtractXY(uvec4(XY4Trended(n, cells[n]), 0, 0));  // @ optimize it
        
        int px = (xy.x + `+mL+`) * cnv_zoom / `+mL2+`;
        int py = (xy.y + `+mL+`) * cnv_zoom / `+mL2+`;
        
        if(cnv_coord.x==px && cnv_coord.y==py) {
          color =
            n==0u
            ? vec4(1., 1., 1., 1.)
            : vec4(1., 1., 0., 1.)
          ;
        }
      }
    }
    ` : ``) + `
    
    
    ` + (Named=='Bond4C' || Named=='Bond4C2'? `
    int d = int(`+zoom+`. * u_surface.z);  // canvas zoom
    if(d>=8 && color!=vec4(0.5, 0.5, 0.5, 1.)) {
      tex3coord = ivec3(tex2coord, layer);
      
      ivec2 cnvc = ivec2( fract(v_texcoord / u_surface.z - u_surface.xy) * `+zoom+`. * u_surface.z );  // canvas coords
      int x = cnvc.x, y = cnvc.y;
      int d2 = d / 2;
      int d4 = d / 4;
      int d1 = d - 1;
      vec4 clr;
      
      clr = vec4(0., 0., 0., 1.);
      //if(ExtractAl(cell)>0u) {
        uint speed = ExtractSpeed(cell);
             if(speed==0u) { if(isqr(d2 - x) + isqr(d2 - y) >= isqr(d2)) color = clr; }
        else if(speed==1u) { if(x + y      < d2 || d  - x + y     <= d2) color = clr; }
        else if(speed==2u) { if(y + d1 - x < d2 || d1 - y + d - x <= d2) color = clr; }
        else if(speed==3u) { if(x + d1 - y < d2 || d1 - x + d - y <= d2) color = clr; }
        else if(speed==4u) { if(x + y      < d2 || d  - y + x     <= d2) color = clr; }
      //}
      
      clr = vec4(0., 0.3, 0., 1.);
      uint gate = ExtractGate(cell);
           if(gate==1u) { if(y==1  ) color = clr; }
      else if(gate==2u) { if(x==d-2) color = clr; }
      else if(gate==3u) { if(y==d-2) color = clr; }
      else if(gate==4u) { if(x==1  ) color = clr; }
      
      clr = vec4(0.3, 0.3, 0., 1.);
      uint gone = ExtractGone(cell);
           if(gone==1u) { if(y==2   && abs(x-d2)<d4) color = clr; }
      else if(gone==2u) { if(x==d-3 && abs(y-d2)<d4) color = clr; }
      else if(gone==3u) { if(y==d-3 && abs(x-d2)<d4) color = clr; }
      else if(gone==4u) { if(x==2   && abs(y-d2)<d4) color = clr; }
      
      uint[5] bonds = ExtractBonds(cell);
      for(uint n=1u; n<`+RC+`u; n++) {
        if(bonds[n]==0u) continue;
        clr =
           bonds[n]==1u ? vec4(1., 1., 1., 1.) :
          (bonds[n]==2u ? vec4(0., 1., 0., 1.) :
                          vec4(1., 0., 0., 1.)
          );
             if(n==1u) { if((y==0   || y==1  ) && (x==d2 || x==d2-1 || x==d2-2 || x==d2+1)) color = clr; }
        else if(n==2u) { if((x==d-1 || x==d-2) && (y==d2 || y==d2-1 || y==d2-2 || y==d2+1)) color = clr; }
        else if(n==3u) { if((y==d-1 || y==d-2) && (x==d2 || x==d2-1 || x==d2-2 || x==d2+1)) color = clr; }
        else if(n==4u) { if((x==0   || x==1  ) && (y==d2 || y==d2-1 || y==d2-2 || y==d2+1)) color = clr; }
      }
    }
    ` : ``) + `
    
    ` + (Named=='Bond4C2'? `
    if(v_texcoord.x<=0.1 && v_texcoord.y<=0.1) {
      uint stage = u_nturn % 4u;
      color =
        stage==0u ? vec4(1., 0., 0., 1.) :
        stage==1u ? vec4(0., 1., 0., 1.) :
        stage==2u ? vec4(0., 0., 1., 1.) :
                    vec4(1., 1., 1., 1.);
    }
    ` : ``) + `
    
  }
`;//console.log(ShowFragmentShaderSource);
var ShowProgram = createProgram4Frag(gl, ShowFragmentShaderSource, ["a_position", "u_fieldtexture", "u_canvas", "u_surface", "u_ps", "u_nturn"]);

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
  
  gl.uniform1ui(ShowProgram.location.u_nturn, nturn - 1);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
  
  nshow ++;
  
  if(single!=1 && cfg.maxfps>60 && !cfg.showiter) requestAnimationFrame(Show);  // overwise Show() is called in Calc()
}

//  ////////////////////////////////////////////////////////////////