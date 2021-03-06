// MOUSE EVENTS ////////////////////////////////////////////////////////////////

var mouseX = 0, mouseY = 0, mouseZ = 0, mouseRGBA = {};

gl.canvas.onmousemove = function(e) {
  mouseX = floor((e.offsetX / surface.zoom) / zoom - surface.left);
  mouseY = floor(((gl.canvas.height - e.offsetY) / surface.zoom) / zoom - surface.top);
};

gl.canvas.onmousedown = function(e) {
  Pause(1);
  if(e.which===1) {  // left click
    console.log(mouseX+':'+mouseY);
    Mous();
    gl.canvas.addEventListener('mousemove', Mous);
  }
  else if(e.which===3) {  // right click
    gl.bindFramebuffer(gl.FRAMEBUFFER, Framebuffers[T0]);
    for(var z=0; z<FD; z++) {
      gl.readBuffer(gl.COLOR_ATTACHMENT0 + z);
      gl.readPixels(0, 0, FW, FH, gldata_Format, gldata_Type, F);
      var cell = GetCell(mouseX, mouseY, 0);
      console.log('z='+z+', rgba=', cell);
      if(pixelBits>=32) {
        var s = '';
        
        var x = cell.r & 65535;  if(x<0)  x  += 65536;  x -= 32768;
        var y = cell.g & 65535;  if(y<0)  y  += 65536;  y -= 32768;
        var vx = cell.r >> 16;   if(vx<0) vx += 65536;  vx -= 32768;
        var vy = cell.g >> 16;   if(vy<0) vy += 65536;  vy -= 32768;
        s += '(' + x + ',' + y + ') (' + vx + ',' + vy + ') ';
        
        s += '[' +
        ((cell.b << 28) >>> 28) + ' ' +
        ((cell.b << 24) >>> 28) + ' ' +
        ((cell.b << 20) >>> 28) + ' ' +
        ((cell.b << 16) >>> 28) + ' ' +
        ((cell.b << 12) >>> 28) + ' ' +
        ((cell.b <<  8) >>> 28) + ' ' +
        ((cell.b <<  4) >>> 28) + ' ' +
        ((cell.b <<  0) >>> 28) +
        '] ';
        
        s += '{' +
        (cell.a & 1) + ' ' +
        ((cell.a << 27) >>> 28) + ' ' +
        ((cell.a << 24) >>> 29) + ' ' +
        ((cell.a << 20) >>> 28) + ' ' +
        ((cell.a << 16) >>> 28) + ' ' +
        (cell.a >>> 16) +
        '} ';
        
        console.log(s);
      }
      if(!cell.a) {
        continue;
      }
      else {
        mouseRGBA = {...cell};
        mouseZ = z;
        //break;
      }
    }
  }
};

gl.canvas.onmouseup = function(e) {
  if(e.which===1) {
    gl.canvas.removeEventListener('mousemove', Mous);
  }
  //Pause(-1);
};

gl.canvas.oncontextmenu = function() {
  return false;
};

// MOUSE SHADER ////////////////////////////////////////////////////////////////

var MousFragmentShaderSource = `
  precision mediump float;
  precision highp int;
  
  uniform highp usampler3D u_fieldtexture;  // Field texture, UInt32
  uniform ivec3 u_mouse;  // mouse coords (x,y,z)
  uniform uvec4 u_rgba;  // color to paint with
  
  in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
  
  out uvec4 glFragColor[`+FD+`];
  
  ivec3 tex3coord;
  ivec3 fieldSize;
  
  uvec4 GetCell() {
    return texelFetch(u_fieldtexture, tex3coord, 0);
  }
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    
    for(int layer=0; layer<`+FD+`; layer++) {
      
      tex3coord = ivec3(v_texcoord, layer);
      
      uvec4 color = GetCell();
      
      if(tex3coord==u_mouse) {
        color = u_rgba;
      }
      
      ` + fs_Prepare2Return('color') + `
    }
  }
`;
var MousProgram = createProgram4Frag(gl, MousFragmentShaderSource, ["a_position", "u_fieldtexture", "u_mouse", "u_rgba"]);

// MOUSE MAIN ////////////////////////////////////////////////////////////////

function Mous() {
  gl.useProgram(MousProgram);
  gl.viewport(0, 0, FW, FH);
  
  BindBuffersAttachments(Framebuffers[T1]);
  ActivateTexture(T0, MousProgram.location.u_fieldtexture);
  
  gl.uniform3i(MousProgram.location.u_mouse, mouseX, mouseY, mouseZ);
  gl.uniform4ui(MousProgram.location.u_rgba, mouseRGBA.r, mouseRGBA.g, mouseRGBA.b, mouseRGBA.a);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);

  FlipTime();
  
  Show(1);
}

//  ////////////////////////////////////////////////////////////////