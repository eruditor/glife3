// WEBGL2 INIT ////////////////////////////////////////////////////////////////

var canvas = document.querySelector("#cnv");

var gl = canvas.getContext("webgl2");  //, {premultipliedAlpha:false}  //"experimental-webgl"
if(!gl) alert('Enable WebGL2 in your browser');

// DATA FORMAT ////////////////////////////////////////////////////////////////

var pixelBits = 8;  // 8 or 32

var gldata_Format = gl.RGBA_INTEGER;
var gldata_InternalFormat = pixelBits==32 ? gl.RGBA32UI     : gl.RGBA8UI;
var gldata_Type           = pixelBits==32 ? gl.UNSIGNED_INT : gl.UNSIGNED_BYTE;
var jsdata_Array          = pixelBits==32 ? Uint32Array     : Uint8Array;

// PROGRAM ////////////////////////////////////////////////////////////////

gl.pixelStorei(gl.UNPACK_ALIGNMENT, 1);  // 1 byte alignment (not default 4) for WebGL

function createShader(gl, type, source) {
  var shader = gl.createShader(type);
  gl.shaderSource(shader, source);
  gl.compileShader(shader);
  var success = gl.getShaderParameter(shader, gl.COMPILE_STATUS);
  if(success) {
    return shader;
  }
  else {
    console.log(gl.getShaderInfoLog(shader));
    gl.deleteShader(shader);
  }
}

function createProgram(gl, vertexShader, fragmentShader, varnames=[]) {
  var program = gl.createProgram();
  gl.attachShader(program, vertexShader);
  gl.attachShader(program, fragmentShader);
  gl.linkProgram(program);
  
  var success = gl.getProgramParameter(program, gl.LINK_STATUS);
  if(!success) {
    console.log(gl.getProgramInfoLog(program));
    gl.deleteProgram(program);
    return false;
  }
  
  program.location = {};  // we use same "program" object to store variable locations
  for(var k in varnames) {
    var varname = varnames[k];
    var vartype = varname.substring(0,2);
         if(vartype=="a_") program.location[varname] = gl.getAttribLocation(program,  varname);
    else if(vartype=="u_") program.location[varname] = gl.getUniformLocation(program, varname);
  }
  
  return program;
}

// VERTEX SHADER ////////////////////////////////////////////////////////////////

var CommonVertexShaderSource = `
  #version 300 es
  precision mediump float;
  
  uniform highp usampler3D u_fieldtexture;  // Field texture, UInt32
  ivec3 fieldSize;
  
  in vec2 a_position;  // input data from vertice coords buffer
  
  out vec2 v_texcoord;  // texture coord to pass to fragment shader (linearly interpolated)
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    
    vec2 clipSpace = (a_position / vec2(fieldSize.xy)) * 2.0 - 1.0;  // convert the position from pixels to clip space: [0..FW/FH/FD] -> [-1..1]
    
    gl_Position = vec4(clipSpace, 0, 1);  // gl_Position is a special variable a vertex shader is responsible for setting
    
    v_texcoord = a_position;  // pass the texCoord to the fragment shader; the GPU will interpolate this value between points
  }
`;

var CommonVertexShader = createShader(gl, gl.VERTEX_SHADER, CommonVertexShaderSource.trim());

// POSITION BUFFER ////////////////////////////////////////////////////////////////

function SetPositionBuffer() {
  var program_location_a_position = 0;  // assuming program.location.a_position is always 0
  
  var positionBuffer = gl.createBuffer();
  gl.bindBuffer(gl.ARRAY_BUFFER, positionBuffer);
  gl.enableVertexAttribArray(program_location_a_position);
  
  gl.vertexAttribPointer( // Tell the attribute how to get data out of positionBuffer (ARRAY_BUFFER)
    program_location_a_position,
    2,  // size (2 components per iteration)
    gl.FLOAT,  // type (the data is 32bit floats)
    false,  // normalize
    0,  // stride (0 = move forward size * sizeof(type) each iteration to get the next position)
    0,  // offset (start at the beginning of the buffer)
  );
  
  gl.bufferData(gl.ARRAY_BUFFER,
    new Float32Array([
      0,  0,
      0, FH,
      FW, FH,
      FW,  0,
    ]),
    gl.STATIC_DRAW
  );
}

SetPositionBuffer();  // it seems with fixed a_position we can create position buffer before any program exists

// TEXTURES ////////////////////////////////////////////////////////////////

function CreateTexture(width, height, depth=1) {
  var texture = gl.createTexture();
  gl.bindTexture(gl.TEXTURE_3D, texture);
  
  gl.texImage3D(gl.TEXTURE_3D, 0, gldata_InternalFormat, width, height, depth, 0, gldata_Format, gldata_Type, null);
  
  gl.texParameteri(gl.TEXTURE_3D, gl.TEXTURE_MIN_FILTER, gl.NEAREST);
  gl.texParameteri(gl.TEXTURE_3D, gl.TEXTURE_MAG_FILTER, gl.NEAREST);
  gl.texParameteri(gl.TEXTURE_3D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
  gl.texParameteri(gl.TEXTURE_3D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);

  return texture;
}

function CreateTexture2D(width, height) {
  var texture = gl.createTexture();
  gl.bindTexture(gl.TEXTURE_2D, texture);
  
  gl.texImage2D(gl.TEXTURE_2D, 0, gldata_InternalFormat, width, height, 0, gldata_Format, gldata_Type, null);
  
  gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.NEAREST);
  gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.NEAREST);
  gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
  gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);

  return texture;
}

function SetTexture(texture_num, texture, width, height, depth=1, data) {
  gl.activeTexture(gl.TEXTURE0 + texture_num);
  gl.bindTexture(gl.TEXTURE_3D, texture);
  gl.texImage3D(gl.TEXTURE_3D, 0, gldata_InternalFormat, width, height, depth, 0, gldata_Format, gldata_Type, data);
}

function SetTexture2D(texture_num, texture, width, height, data) {
  gl.activeTexture(gl.TEXTURE0 + texture_num);
  gl.bindTexture(gl.TEXTURE_2D, texture);
  gl.texImage2D(gl.TEXTURE_2D, 0, gldata_InternalFormat, width, height, 0, gldata_Format, gldata_Type, data);
}

function CreateFramebuffer(texture) {
  var framebuffer = gl.createFramebuffer();
  gl.bindFramebuffer(gl.FRAMEBUFFER, framebuffer);
  for(let layer=0; layer<FD; layer++) {
    gl.framebufferTextureLayer(gl.FRAMEBUFFER, gl.COLOR_ATTACHMENT0 + layer, texture, 0, layer);
  }
  return framebuffer;
}

var Textures = new Array(2);
Textures[0] = CreateTexture(FW, FH, FD);
Textures[1] = CreateTexture(FW, FH, FD);

var Framebuffers = new Array(2);
Framebuffers[0] = CreateFramebuffer(Textures[0]);
Framebuffers[1] = CreateFramebuffer(Textures[1]);
