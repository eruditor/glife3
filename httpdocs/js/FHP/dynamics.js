function ExtractRGBA(cell) {
  var s = '';
  
  var al    = (cell.a & 1);
  var fl    = (cell.a >>> 1) % 16;
  var decay = (cell.a >>> 5);
  s += 'A(' + al + ', ' + fl + ', ' + decay + ') ';
  
  s += 'R[' +
    ((cell.r >>> 0) % 2) + ' ' +
    ((cell.r >>> 1) % 2) + ' ' +
    ((cell.r >>> 2) % 2) + ' ' +
    ((cell.r >>> 3) % 2) + ' ' +
    ((cell.r >>> 4) % 2) + ' ' +
    ((cell.r >>> 5) % 2) + ' ' +
    ((cell.r >>> 6) % 2) + ' ' +
    ((cell.r >>> 7) % 2) +
  '] ';
  
  return s;
}


var fs_ExtractRGBA = `
  ////////////////////////////////////////////////////////////////
  
  uint ExtractAl(uvec4 cell) {  // aliveness
    return cell.r;
  }
  
  uint ExtractFl(uvec4 cell) {  // flavor
    return 1u;
  }
  
  uint ExtractDecay(uvec4 cell) {  // decay
    return 0u;
  }
  
  uint PackA(uint al, uint fl, uint decay) {
    return (al    <<  0u) |
           (fl    <<  1u) |
           (decay <<  5u);
  }
  
  ////////////////////////////////////////////////////////////////
  
  uint[8] ExtractSpeeds(uvec4 cell) {  // movement directions
    return uint[8](
      (cell.r >> 0u) % 2u,
      (cell.r >> 1u) % 2u,
      (cell.r >> 2u) % 2u,
      (cell.r >> 3u) % 2u,
      (cell.r >> 4u) % 2u,
      (cell.r >> 5u) % 2u,
      (cell.r >> 6u) % 2u,
      (cell.r >> 7u) % 2u
    );
  }
  
  uint ExtractNthSpeed(uvec4 cell, uint n) {
    return (cell.r >> n) % 2u;
  }
  
  uint PackR(uint[8] speeds) {
    return (speeds[0] << 0u) |
           (speeds[1] << 1u) |
           (speeds[2] << 2u) |
           (speeds[3] << 3u) |
           (speeds[4] << 4u) |
           (speeds[5] << 5u) |
           (speeds[6] << 6u) |
           (speeds[7] << 7u);
  }
  
  ////////////////////////////////////////////////////////////////
`;


var fs_NthDirection = ``;
for(var k in RG) {
  fs_NthDirection += `      if(n==`+k+`u) return ivec2(`+RG[k][0]+`, `+RG[k][1]+`);\n`;
}
fs_NthDirection = `
    ivec2 NthDirection(uint n) {
      ` + fs_NthDirection + `
    }
`;


var CalcFragmentShaderSource = `
  precision mediump float;
  precision highp int;
  
  uniform highp usampler3D u_fieldtexture;  // Field texture
  uniform highp usampler3D u_prevtexture;  // Previous Field texture
  uniform highp usampler2D u_rulestexture[`+FD+`];  // Rules texture
  
  in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
  
  out uvec4 glFragColor[`+FD+`];
  
  ivec3 tex3coord;
  ivec3 fieldSize;
  
  ` + fs_ModuloTorus + `
  ` + fs_GetCell() + `
  ` + fs_GetTexel2D + `
  ` + fs_ExtractRGBA + `
  ` + fs_NthDirection + `
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    
    uvec4 color;
    
    for(int layer=0; layer<`+FD+`; layer++) {
      
      // getting cell's neighborhood
      
      tex3coord = ivec3(v_texcoord, layer);
      
      uvec4 cells[`+RC+`];
      ` + fs_GetNeibs + `
      
      uint[8] new_speeds;
      for(uint n=0u; n<`+RC+`u; n++) {
        new_speeds[n] = ExtractNthSpeed(cells[n], n);
      }
      color.r = PackR(new_speeds);
      
      uvec4 self = cells[0];  // previous self cell state
      
      ` + fs_PackAliveness('color.a') + `
      
      ` + fs_Prepare2Return('color') + `
    }
  }
`;
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_prevtexture", "u_rulestexture"]);