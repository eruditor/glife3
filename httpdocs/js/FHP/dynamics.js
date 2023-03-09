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
  uniform highp usampler2D u_rulestexture[`+FD+`];  // Rules texture
  uniform highp uint u_rnd;
  
  in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
  
  out uvec4 glFragColor[`+FD+`];
  
  ivec3 tex3coord;
  ivec3 fieldSize;
  
  ` + fs_ModuloTorus + `
  ` + fs_GetCell() + `
  ` + fs_GetTexel2D + `
  ` + fs_ExtractRGBA + `
  ` + fs_NthDirection + `
  
  // A single iteration of Bob Jenkins' One-At-A-Time hashing algorithm.
  uint hash(uint x) {
    x += ( x << 10u );
    x ^= ( x >>  6u );
    x += ( x <<  3u );
    x ^= ( x >> 11u );
    x += ( x << 15u );
    return x;
  }
  uint hash(uvec2 v) { return hash( v.x ^ hash(v.y) ); }
  uint hash(uvec3 v) { return hash( v.x ^ hash(v.y) ^ hash(v.z) ); }
  
  uint uRnd(uint mod) {  // random number simulation (with spooky-quantum global correlations)
    return hash(uvec3(v_texcoord.xy, u_rnd)) % mod;
  }
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    
    uvec4 color;
    
    for(int layer=0; layer<`+FD+`; layer++) {
      
      // getting cell's neighborhood
      
      tex3coord = ivec3(v_texcoord, layer);
      
      uvec4 cells[`+RC+`];
      /*` + fs_GetNeibs + `*/
      int x0 = tex3coord.y % 2 - 1;
      int x1 = x0 + 1;
      cells[0] = GetCell( 0,  0, 0);
      cells[1] = GetCell(x0, -1, 0);
      cells[2] = GetCell(x1, -1, 0);
      cells[3] = GetCell( 1,  0, 0);
      cells[4] = GetCell(x1,  1, 0);
      cells[5] = GetCell(x0,  1, 0);
      cells[6] = GetCell(-1,  0, 0);
     
      // step1: straight movement
      uint[8] new_speeds;
      for(uint n=0u; n<`+RC+`u; n++) {
        new_speeds[n] = ExtractNthSpeed(cells[n], n);
      }
      new_speeds[7] = ExtractNthSpeed(cells[0], 7u);  // 7 = obstacle
      
      // step2: global force
      /*
      if(uRnd(1000u)<5u) {
        if(new_speeds[2]==1u && new_speeds[4]==0u) {
           new_speeds[2] = 0u;  new_speeds[4] = 1u;
        }
        if(new_speeds[1]==1u && new_speeds[5]==0u) {
           new_speeds[1] = 0u;  new_speeds[5] = 1u;
        }
      }
      */
      
      // step3: local interaction
      uint moved = PackR(new_speeds);
      uint rnd = uRnd(2u);  
      uint rulecoord = moved + (rnd << 8u);  // rule coord equals packed new_speeds + random
      ivec2 t = ivec2(rulecoord, 0);
      if(t.x>=`+RX+`) { t.y = t.x / `+RX+`;  t.x = t.x % `+RX+`; }
      uvec4 rule = GetTexel2D(u_rulestexture, layer, t);
      color.r = rule.a;
      
      uvec4 self = cells[0];  // previous self cell state
      
      ` + fs_PackAliveness('color.a') + `
      
      ` + fs_Prepare2Return('color') + `
    }
  }
`;
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_rulestexture", "u_rnd"]);