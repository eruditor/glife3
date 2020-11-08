// WEBGL2 INIT ////////////////////////////////////////////////////////////////

var canvas = document.querySelector("#cnv");

var gl = canvas.getContext("webgl2");
if(!gl) alert('Enable WebGL2 in your browser');

var gl_ext = gl.getExtension('EXT_color_buffer_float');  // for floating-point math

// DATA FORMAT ////////////////////////////////////////////////////////////////

// main: Field and Rules
var pixelBits = 8;  // 8 or 32
var gldata_Format   = gl.RGBA_INTEGER;
var gldata_Internal = pixelBits==32 ? gl.RGBA32UI     : gl.RGBA8UI;
var gldata_Type     = pixelBits==32 ? gl.UNSIGNED_INT : gl.UNSIGNED_BYTE;
var jsdata_Array    = pixelBits==32 ? Uint32Array     : Uint8Array;

// auxiliary: floating-point math for Analysis
var glFl32_Format   = gl.RGBA;  //RGBA_INTEGER  // if EXT_color_buffer_float is not supported - use INT32
var glFl32_Internal = gl.RGBA32F;  //RGBA32I
var glFl32_Type     = gl.FLOAT;  //INT

// auxiliary: high precision UInt32 math for Analysis
var glUI32_Format   = gl.RGBA_INTEGER;
var glUI32_Internal = gl.RGBA32UI;
var glUI32_Type     = gl.UNSIGNED_INT;
var jsUI32_Array    = Uint32Array;

// PROGRAM ////////////////////////////////////////////////////////////////

gl.pixelStorei(gl.UNPACK_ALIGNMENT, 1);  // 1 byte alignment (not default 4) for WebGL

function createShader(gl, type, source) {
  var shader = gl.createShader(type);
  source = "#version 300 es\n" + source.trim();
  gl.shaderSource(shader, source);
  gl.compileShader(shader);
  
  var success = gl.getShaderParameter(shader, gl.COMPILE_STATUS);
  if(!success) {
    console.log(gl.getShaderInfoLog(shader));
    gl.deleteShader(shader);
    return false;
  }
  
  return shader;
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

function createProgram4Frag(gl, FragmentShaderSource, varnames=[]) {
  FragmentShader = createShader(gl, gl.FRAGMENT_SHADER, FragmentShaderSource);
  return createProgram(gl, CommonVertexShader, FragmentShader, varnames);
}

// VERTEX SHADER ////////////////////////////////////////////////////////////////

var CommonVertexShaderSource = `
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

var CommonVertexShader = createShader(gl, gl.VERTEX_SHADER, CommonVertexShaderSource);

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
      0,   0,
      0,  FH,
      FW, FH,
      FW,  0,
    ]),
    gl.STATIC_DRAW
  );
}

SetPositionBuffer();  // it seems with fixed a_position we can create position buffer before any program exists

// TEXTURES ////////////////////////////////////////////////////////////////

var Textures = [];

function CreateTexture(width, height, depth=0, type='') {
  var gl_tex = depth==0 ? gl.TEXTURE_2D : gl.TEXTURE_3D;
  var texture = gl.createTexture();
  gl.bindTexture(gl_tex, texture);
       if(depth==0)     gl.texImage2D(gl_tex, 0, gldata_Internal, width, height,        0, gldata_Format, gldata_Type, null);
  else if(type=='Fl32') gl.texImage3D(gl_tex, 0, glFl32_Internal, width, height, depth, 0, glFl32_Format, glFl32_Type, null);
  else if(type=='UI32') gl.texImage3D(gl_tex, 0, glUI32_Internal, width, height, depth, 0, glUI32_Format, glUI32_Type, null);
  else                  gl.texImage3D(gl_tex, 0, gldata_Internal, width, height, depth, 0, gldata_Format, gldata_Type, null);
  gl.texParameteri(gl_tex, gl.TEXTURE_MIN_FILTER, gl.NEAREST);
  gl.texParameteri(gl_tex, gl.TEXTURE_MAG_FILTER, gl.NEAREST);
  gl.texParameteri(gl_tex, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
  gl.texParameteri(gl_tex, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
  return texture;
}

function SetTexture(texture_num, texture, data, width, height, depth=0, type='') {
  var gl_tex = depth==0 ? gl.TEXTURE_2D : gl.TEXTURE_3D;
  gl.activeTexture(gl.TEXTURE0 + texture_num);
  gl.bindTexture(gl_tex, texture);
       if(depth==0)     gl.texImage2D(gl_tex, 0, gldata_Internal, width, height,        0, gldata_Format, gldata_Type, data);
  else if(type=='Fl32') gl.texImage3D(gl_tex, 0, glFl32_Internal, width, height, depth, 0, glFl32_Format, glFl32_Type, data);
  else if(type=='UI32') gl.texImage3D(gl_tex, 0, glUI32_Internal, width, height, depth, 0, glUI32_Format, glUI32_Type, data);
  else                  gl.texImage3D(gl_tex, 0, gldata_Internal, width, height, depth, 0, gldata_Format, gldata_Type, data);
}

function CreateFramebuffer(texture, depth=0) {
  var framebuffer = gl.createFramebuffer();
  gl.bindFramebuffer(gl.FRAMEBUFFER, framebuffer);
  if(depth==0)                    gl.framebufferTexture2D(   gl.FRAMEBUFFER, gl.COLOR_ATTACHMENT0    , gl.TEXTURE_2D, texture, 0   );
  else for(var z=0; z<depth; z++) gl.framebufferTextureLayer(gl.FRAMEBUFFER, gl.COLOR_ATTACHMENT0 + z,                texture, 0, z);
  return framebuffer;
}

function BindBuffersAttachments(buf, layer=-1) {
  gl.bindFramebuffer(gl.FRAMEBUFFER, buf);
  var color_attachments = [];
  for(var z=0; z<FD; z++) {
    color_attachments[z] = (layer<0 || z==layer ? gl.COLOR_ATTACHMENT0 + z : gl.NONE);
  }
  gl.drawBuffers(color_attachments);
}

function ActivateTexture(texnum, u_location) {
  gl.activeTexture(gl.TEXTURE0 + texnum);
  gl.bindTexture(gl.TEXTURE_3D, Textures[texnum]);
  gl.uniform1i(u_location, texnum);
}

//  ////////////////////////////////////////////////////////////////

//gl.clearColor(0, 0, 0, 1);  gl.clear(gl.COLOR_BUFFER_BIT);