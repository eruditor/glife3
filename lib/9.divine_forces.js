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
      if(!cell.a) {
        continue;
      }
      else {
        mouseRGBA = {...cell};
        mouseZ = z;
        break;
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
  #version 300 es
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
    
    uvec4 colors[`+FD+`];
    
    for(int layer=0; layer<`+FD+`; layer++) {
      
      tex3coord = ivec3(v_texcoord, layer);
      
      uvec4 color = GetCell();
      
      if(tex3coord==u_mouse) {
        color = u_rgba;
      }
      
      colors[layer] = color;
    }
    
    glFragColor[0] = colors[0];
    ` + (FD>1 ? `glFragColor[1] = colors[1];` : ``) + `
    ` + (FD>2 ? `glFragColor[2] = colors[2];` : ``) + `
    ` + (FD>3 ? `glFragColor[3] = colors[3];` : ``) + `
  }
`;

var MousFragmentShader = createShader(gl, gl.FRAGMENT_SHADER, MousFragmentShaderSource.trim());

var MousProgram = createProgram(gl, CommonVertexShader, MousFragmentShader, ["a_position", "u_fieldtexture", "u_mouse", "u_rgba"]);

// MOUSE MAIN ////////////////////////////////////////////////////////////////

function Mous() {
  gl.useProgram(MousProgram);
  
  gl.bindFramebuffer(gl.FRAMEBUFFER, Framebuffers[T1]);
  
  var color_attachments = [];
  for(let layer=0; layer<FD; layer++) {
    color_attachments[layer] = gl.COLOR_ATTACHMENT0 + layer;
  }
  gl.drawBuffers(color_attachments);
  
  gl.viewport(0, 0, FW, FH);
  
  gl.activeTexture(gl.TEXTURE0 + T0);
  gl.bindTexture(gl.TEXTURE_3D, Textures[T0]);
  gl.uniform1i(MousProgram.location.u_fieldtexture, T0);
  gl.uniform3i(MousProgram.location.u_mouse, mouseX, mouseY, mouseZ);
  gl.uniform4ui(MousProgram.location.u_rgba, mouseRGBA.r, mouseRGBA.g, mouseRGBA.b, mouseRGBA.a);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);

  FlipTime();
  
  Show(1);
}
