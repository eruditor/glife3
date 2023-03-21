// MOUSE EVENTS ////////////////////////////////////////////////////////////////

var mouseX = 0, mouseY = 0, mouseZ = 0, mouseRGBA = {};

gl.canvas.onmousemove = function(e) {
  mouseX = floor((e.offsetX / surface.zoom) / zoom - surface.left);
  mouseY = floor(((gl.canvas.height - e.offsetY) / surface.zoom) / zoom - surface.top);
  
  if(Rgeom==16 || Rgeom==162) {
    if(mouseY % 2 == 1) mouseX = floor((e.offsetX / surface.zoom) / zoom - surface.left + 0.5);
    mouseX += mouseY / 2;
    mouseX %= FW;
  }
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
      console.log('xyz=('+mouseX+' '+mouseY+' '+z+'), rgba=', cell);
      
      if(typeof ExtractRGBA === 'function') {
        console.log(ExtractRGBA(cell));
      }
      
      mouseRGBA = {...cell};
      mouseZ = z;
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
  
  uniform `+field_Sampler+` u_fieldtexture;
  uniform ivec3 u_mouse;  // mouse coords (x,y,z)
  uniform `+field_Vec4P+` u_rgba;  // color to paint with
  
  in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
  
  out `+field_Vec4P+` glFragColor[`+FD+`];
  
  ivec3 tex3coord;
  ivec3 fieldSize;
  uint dbg;
  
  `+field_Vec4P+` GetCell() {
    return texelFetch(u_fieldtexture, tex3coord, 0);
  }
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    
    for(int layer=0; layer<`+FD+`; layer++) {
      
      tex3coord = ivec3(v_texcoord, layer);
      
      `+field_Vec4P+` color = GetCell();
      
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