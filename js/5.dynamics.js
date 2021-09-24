// CALC SHADER ////////////////////////////////////////////////////////////////

var fs_ModuloTorus = `
  ivec3 ModuloTorus(ivec3 a, ivec3 b) {  // make the field torus-shaped (can use simple fract() for float vec3)
    ivec3 ret = a;
    if(a.x<0)    ret.x = b.x + a.x;
    if(a.y<0)    ret.y = b.y + a.y;
    if(a.z<0)    ret.z = b.z + a.z;
    if(a.x>=b.x) ret.x = a.x - b.x;
    if(a.y>=b.y) ret.y = a.y - b.y;
    if(a.z>=b.z) ret.z = a.z - b.z;
    return ret;
  }
`;

function fs_GetCell(func='GetCell', tex='u_fieldtexture') {
  return `
  uvec4 `+func+`(int dx, int dy, int dz) {
         if(dz>0 && tex3coord.z==`+(FD-1)+`) return uvec4(0);  // no upper for top layer
    else if(dz<0 && tex3coord.z==0)          return uvec4(0);  // no lower for bottom layer
    return texelFetch(`+tex+`, ModuloTorus(tex3coord + ivec3(dx, dy, dz), fieldSize), 0);
  }
  `;
}

var fs_GetNeibs = ``;
for(var k in RG) {
  fs_GetNeibs += `      cells[`+k+`] = GetCell(`+RG[k][0]+`, `+RG[k][1]+`, `+RG[k][2]+`);\n`;
}

// array index for samplers must be constant integral expressions, so we need this crap to address texture by layer
// we can use more convenient texture3D, but it's size (dimensions) is more limited than for 2D textures (rules texture for RB>4 exceeds the limit)
var fs_GetTexel2D = `uvec4 GetTexel2D(highp usampler2D tex[`+FD+`], int layer, ivec2 coord) {\n`;
for(var z=0; z<FD; z++) {
  fs_GetTexel2D += `    if(layer==`+z+`) return texelFetch(tex[`+z+`], coord, 0);\n`;
}
fs_GetTexel2D += `  }`;

// DATA PACKING ////////////////////////////////////////////////////////////////

var fs_ExtractRGBA = `
    uint ExtractAl(uvec4 cell) {  // aliveness
      return cell.a > 0u ? 1u : 0u;
    }
    
    uint ExtractFl(uvec4 cell) {  // flavor
      return cell.a>0u ? cell.a : cell.b % 10u;
    }
    
    uint ExtractDecay(uvec4 cell) {  // decay
      return cell.b<30u ? 0u : cell.b / 10u - 3u;
    }
`;

function fs_PackAliveness(alive='alive') {
  return DataFormat=='UI8'
  ? `
        // color.b = color decay value (optional); color.a must be set already!
             if(color.a>0u) color.b = 200u;           // alive cell
        else if(self.a>0u)  color.b = 100u + self.a;  // dying cell
        else if(self.b>30u) color.b = self.b - 10u;   // color decay for died cell
        else                color.b = 0u;             // empty cell
  `
  : `
  // @ ! see MVM !
  /*
        // highest 16bit = alive cell's color; lowest = decay and color of died cell
             if(`+alive+`>0u)  color.a = `+alive+` << 16u;        // alive cell
        else if(self.a>65535u) color.a = (self.a >> 16u) + 100u;  // dying cell
        else if(self.a>30u)    color.a = self.a - 10u;            // color decay for died cell
        else                   color.a = 0u;                      // empty cell
  */
  `;
}

function fs_Prepare2Return(varname='color') {
  var ret = '';
  for(var z=0; z<FD; z++) {
    ret += (z>0 ? `else ` : `     `) + `if(layer==`+z+`) glFragColor[`+z+`] = `+varname+`;\n      `;
  }
  return ret;
}

var fs_ExtractXY = fs_ExtractA = fs_Trends = ``;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(Mode=='PRT') {
  var CalcFragmentShaderSource = `
    precision mediump float;
    precision highp int;
    
    uniform highp usampler3D u_fieldtexture;  // Field texture
    uniform highp usampler2D u_rulestexture[`+FD+`];  // Rules texture
    
    uniform int u_ps[`+ND+`];  // global shift of 3*3 squares
    
    in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
    
    out uvec4 glFragColor[`+FD+`];
    
    ivec3 tex3coord;
    ivec3 fieldSize;
    
    ` + fs_ModuloTorus + `
    
    ` + fs_GetCell() + `
    
    ` + fs_GetTexel2D + `
    
    void main() {
      fieldSize = textureSize(u_fieldtexture, 0);
      
      uvec4 color;
      
      for(int layer=0; layer<`+FD+`; layer++) {
        ivec3 cur3coord = ivec3(v_texcoord, layer);
        int dx3 = (3 + cur3coord.x + u_ps[0]) % 3;
        int dy3 = (3 + cur3coord.y + u_ps[1]) % 3;
        ivec3 shift2grid = ivec3(1 - dx3, 1 - dy3, 0);
        
        tex3coord = ivec3(v_texcoord, layer) + shift2grid;
        
        // getting cell's neighborhood
        uvec4 cells[`+RC+`];
        ` + fs_GetNeibs + `
        
        // finding rule for this neighborhood
        
        uint rulecoord = 0u;
        for(int n=0; n<`+RC+`; n++) {
          rulecoord *= `+RB+`u;
          rulecoord += cells[n].a;  // 8bit-packing only!
        }
        ivec2 t = ivec2(rulecoord, 0);
        if(t.x>=`+RX+`) { t.y = t.x / `+RX+`;  t.x = t.x % `+RX+`; }
        
        uvec4 rule = GetTexel2D(u_rulestexture, layer, t);
        int new_square = 256 * 256 * int(rule.g) + 256 * int(rule.b) + int(rule.a);
        uint u_new_square = 256u * rule.b + rule.a;
        
        int dxdy10 = 10 * dx3 + dy3;
        int pwr = 0;  // coordinate of needed cell in partition's square
             if(dxdy10== 0) pwr = 7;
        else if(dxdy10== 1) pwr = 0;
        else if(dxdy10== 2) pwr = 1;
        else if(dxdy10==10) pwr = 6;
        else if(dxdy10==11) pwr = 8;
        else if(dxdy10==12) pwr = 2;
        else if(dxdy10==20) pwr = 5;
        else if(dxdy10==21) pwr = 4;
        else if(dxdy10==22) pwr = 3;
        
        uint nth_bit = 0u;  // float pow() doesn't work here due to rounding precision!
        for(int n=0; n<=pwr; n++) {
          nth_bit = u_new_square % `+RB+`u;
          u_new_square = (u_new_square - nth_bit) / `+RB+`u;
        }
        
        color.a = nth_bit;
        
        uvec4 self = GetCell(dx3 - 1, dy3 - 1, 0);  // previous self cell state
        
        ` + fs_PackAliveness('color.a') + `
        
        ` + fs_Prepare2Return('color') + `
      }
    }
  `;
  var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_rulestexture", "u_ps"]);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
else if(Mode=='MVM') {
  function ExtractRGBA(cell) {
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
    ((cell.a << 16) >>> 24) + ' ' +
    (cell.a >>> 16) +
    '} ';
    
    return s;
  }
  
  var fs_ExtractRGBA = `
    uint ExtractAl(uvec4 cell) {  // aliveness
      return cell.a & 1u;
    }
    
    uint ExtractFl(uvec4 cell) {  // flavor
      return cell.a << 27u >> 28u;
    }
    
    uint ExtractDecay(uvec4 cell) {  // decay
      return cell.a << 24u >> 29u;
    }
  `;
  
  var fs_ExtractXY = `
    ivec4 ExtractXY(uvec4 cell) {
      return ivec4(
        // atom coords
        int(cell.x & 65535u) - 32768,
        int(cell.y & 65535u) - 32768,
        // atom velocity
        int(cell.x >> 16u) - 32768,
        int(cell.y >> 16u) - 32768
      );
    }
    
    uvec2 PackXY(ivec4 xy) {
      return uvec2(
        (xy.x + 32768) | ((xy.z + 32768) << 16),
        (xy.y + 32768) | ((xy.w + 32768) << 16)
      );
    }
  `;
  
  // bonds
  var fs_ExtractB = `
    uint[8] ExtractB(uvec4 cell) {
      return uint[8](
        (cell.b << 28u) >> 28u,  // lowest 4 bit
        (cell.b << 24u) >> 28u,
        (cell.b << 20u) >> 28u,
        (cell.b << 16u) >> 28u,
        (cell.b << 12u) >> 28u,
        (cell.b <<  8u) >> 28u,
        (cell.b <<  4u) >> 28u,
        (cell.b <<  0u) >> 28u
      );
    }
    
    uint PackB(uint[8] b) {
      return (b[0] <<  0u) | 
             (b[1] <<  4u) | 
             (b[2] <<  8u) | 
             (b[3] << 12u) | 
             (b[4] << 16u) |
             (b[5] << 20u) |
             (b[6] << 24u) |
             (b[7] << 28u);
    }
  `;
  
  // trending = moving particle transfer to another cell:
  // : request for moving away alive cell or acceptance for nearby empty cell
  var fs_ExtractA = `
    #define A_alive 0
    #define A_v     1
    #define A_decay 2
    #define A_trend 3
    #define A_dbg   4
    
    uint[6] ExtractA(uint cell_a) {
      uint[6] ret;
      ret[A_alive] = cell_a & 1u;           //  1 bit = is the cell alive
      ret[A_v]     = cell_a << 27u >> 28u;  //  4 bit = cell's value
      ret[A_decay] = cell_a << 24u >> 29u;  //  3 bit = color decay for died cell
      ret[A_trend] = cell_a << 16u >> 24u;  //  4 bit = trending/accepting flags
      ret[A_dbg]   = cell_a >> 16u;         // 16 bit = debug info
      return ret;
    }
    
    uint ExtractAv(uint cell_a) {
      return (cell_a & 1u)>0u ? cell_a << 27u >> 28u : 0u;
    }
    
    uint ExtractAt(uint cell_a) {
      return cell_a << 16u >> 24u;
    }
    
    uint PackA(uint al, uint v, uint trend, uint self_a, uint dbg2) {
      uint decay = 0u;
      if(al==0u) {
        v = 0u;  // paranoic
        uint[6] exa = ExtractA(self_a);  // @ optimize it
             if(exa[A_alive]>0u) decay = 7u;
        else if(exa[A_decay]>0u) decay = exa[A_decay] - 1u;
      }
      else {
        al = 1u;  // paranoic
      }
      
      return (al    <<  0u) |
             (v     <<  1u) |
             (decay <<  5u) |
             (trend <<  8u) |
             (dbg2  << 16u);
    }
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
  
  var cartesian2spiral = [], spiral2cartesian = [], antitrends = [];
  for(var i=0; i<RC; i++) {
    var c = RG0 + RGD * RG[i][1] + RG[i][0];  // cartesian indexing
    cartesian2spiral[c] = i;
    spiral2cartesian[i] = c;
  }
  for(var i=0; i<RC; i++) {
    var ac = RG0 - RGD * RG[i][1] - RG[i][0];  // cartesian antitrend for i-th spiral
    antitrends[i] = cartesian2spiral[ac];
  }
  var c2s = '', s2c = '', atd = '';
  for(var i=0; i<RC; i++) {
    c2s += (c2s!='' ? ', ' : '') + cartesian2spiral[i] + 'u';
    s2c += (s2c!='' ? ', ' : '') + spiral2cartesian[i] + 'u';
    atd += (atd!='' ? ', ' : '') + antitrends[i] + 'u';
  }
  var fs_Cartesian = `
    uint cartesian2spiral[`+RC+`] = uint[`+RC+`](`+c2s+`);
    uint spiral2cartesian[`+RC+`] = uint[`+RC+`](`+s2c+`);
    uint antitrends[`+RC+`]       = uint[`+RC+`](`+atd+`);
  `;
  
  var fs_Trends = `
    
    ` + fs_Cartesian + `
    
    uint CalcTrend(ivec4 xy) {
      ivec2 c = xy.xy / `+mL+`;
      if(c.x<-`+RGR+`) c.x = -`+RGR+`;  if(c.x>`+RGR+`) c.x = `+RGR+`;
      if(c.y<-`+RGR+`) c.y = -`+RGR+`;  if(c.y>`+RGR+`) c.y = `+RGR+`;
      return cartesian2spiral[`+RG0+` + `+RGD+` * c.y + c.x];
    }
    
    ` + fs_NthDirection + `
    
    uvec2 XY4Trended(uint n, uvec4 cell) {
      return uvec2(ivec2(cell.xy) + `+mL2+` * NthDirection(n));
    }
  `;
  
  var CalcFragmentShaderSource = `
    precision mediump float;
    precision highp int;
    
    uniform highp usampler3D u_fieldtexture;  // Field texture
    uniform highp usampler2D u_rulestexture[`+FD+`];  // Rules texture
    
    in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
    
    out uvec4 glFragColor[`+FD+`];
    
    ivec3 tex3coord;
    ivec3 fieldSize;
    uint dbg;
    
    ` + fs_ModuloTorus + `
    
    ` + fs_GetCell() + `
    
    ` + fs_GetTexel2D + `
    
    ` + fs_ExtractRGBA + `
    
    ` + fs_ExtractXY + `
    
    ` + fs_ExtractB + `
    
    ` + fs_ExtractA + `
    
    ` + fs_Trends + `
    
    uint CountBonds(uint[8] b) {
      uint ret = 0u;
      for(uint i=0u; i<=7u; i++) {
        if(b[i]>0u) ret ++; 
      }
      return ret;
    }
    
    float atom_masses[4]   = float[4](0., 1., 2., 3.);
    uint atom_bondnums[4]   = uint[4](0u, 1u, 2u, 4u);  // number of covalent bonds
    int atom_bondenergies[4] = int[4]( 0, 20, 25, 15);
    
    void main() {
      fieldSize = textureSize(u_fieldtexture, 0);
      
      dbg = 0u;
      
      for(int layer=0; layer<`+FD+`; layer++) {
        tex3coord = ivec3(v_texcoord, layer);
        
        // getting cell's neighborhood
        uvec4 cells[`+RC+`];
        /*
        for(uint n=0u; n<`+RC+`u; n++) {
          ivec2 ndir = NthDirection(n);
          cells[n] = GetCell(ndir.x, ndir.y, 0);
        }
        */
        ` + fs_GetNeibs + `
        
        uvec4 self = cells[0];  // previous self cell state
        
        uvec4 color = uvec4(0);
        
        uint[8] b = uint[8](0u, 0u, 0u, 0u, 0u, 0u, 0u, 0u);  // new b
        
        uint al0 = ExtractAl(self);
        uint al = 0u;
        
        uint v = 0u;
        
        uint trend0 = ExtractAt(self.a);
        uint trend = 0u;
        
        // alive cell ////////////////////////////////////////////////////////////////
        if(al0>0u) {
          ivec4 xy = ExtractXY(self);
          xy.xy += xy.zw / 2;
          
          v = ExtractAv(self.a);  // cell's value
          
          uint[8] b0 = ExtractB(self);
          uint bn = atom_bondnums[v];
          uint b0count = CountBonds(b0);
          
          // forces
          
          vec2 d2xy = vec2(0);
          uint freebonds = bn;
          int bondenergy[8] = int[8](0,0,0,0,0,0,0,0);
          
          for(uint n=1u; n<9u; n++) {
            uint nv = ExtractAv(cells[n].a);  // @ optimize it
            if(nv==0u) continue;
            
            ivec4 nxy = ExtractXY(cells[n]);  // neib's coords and velocity
            nxy.xy += nxy.zw / 2;
            
            ivec2 ndir = NthDirection(n);  // points from current cell to neib cell
            ivec2 dl = `+mL2+` * ndir + nxy.xy - xy.xy;  // points from current atom coords to neib atom coords
            
            float dist = sqrt(float(dl.x*dl.x + dl.y*dl.y));  // distance between atoms
            //float dist = float(abs(dl.x) + abs(dl.y));
            
            if(dist>8.*`+fmL+`) continue;  // @ careful here, with bond-breaking
            
            uint bondidx = n - 1u;  // 0..7
            uint[8] nb = ExtractB(cells[n]);  // neib's bonds
            
            //d2xy += atom_masses[nv] * `+fmL+` * vec2(dl) / dist / dist * `+fmL+` / dist;  // gravity
            //d2xy += 0.02 / atom_masses[v] * vec2(dl) / dist * (dist - `+mL2+`.);  // harmonic
            //d2xy += 100. / atom_masses[v] * vec2(dl) / dist * (dist/`+mL2+`. - 0.8);  // harmonic
            
            
            bool bonded = false;
            if(b0[bondidx]>0u) {  // this cell has a bond to n-th neib
              uint antibondidx = antitrends[n] - 1u;
              if(nb[antibondidx]>0u) {  // neib also has a bond to this cell
                bonded = true;
                //d2xy += atom_masses[nv] * `+fmL+` * vec2(dl) / dist / dist * `+fmL+` / dist;  // gravity
                //d2xy += 100. / atom_masses[v] * vec2(dl) / dist * (`+mL2+`./dist - `+mL2+`./dist*`+mL2+`./dist);  // EM
                //float dist2 = dist + 3000.;  d2xy += 20. / atom_masses[v] * vec2(dl) / dist * (`+mL2+`./dist2 - `+mL2+`./dist2*`+mL2+`./dist2);  // EM-shifted
                //d2xy += 100. / atom_masses[v] * vec2(dl) / dist * (dist/`+mL2+`. - 0.8);  // harmonic
                //d2xy += 0.3 * atom_masses[nv] / (atom_masses[v] + atom_masses[nv]) * vec2(nxy.zw - xy.zw);  // inelastic collisions
                
                /*
                float xx = 2. * dist / `+mL+`.;  if(xx<0.85) xx = 0.85;
                float xx_2 = 1. / xx / xx;
                float ff = xx_2 - xx_2 * xx_2;  // x^-2 - x^-4: max=25 f(0.8)=88, f(0.85)=53 f(0.9)=23
                d2xy += 100. / atom_masses[v] * vec2(dl) / dist * ff;
                */
                
                float a = 0.0002, re = `+mL+`., De = 200000.;
                float e = exp(a * (re - dist));
                float f = 2. * De * a * e * (1. - e);
                if(f<-10.) f = -10.;
                d2xy += 1. / atom_masses[v] * vec2(dl) / dist * f;
              }
            }
            
            if(!bonded && b0count>=bn && CountBonds(nb)>=atom_bondnums[nv]) {
              //d2xy += -10. / atom_masses[v] * vec2(dl) / dist * (`+mL+`./(`+mL+`. + dist));  // repulsion
            }
            
            // setting new bonds
            
            if(freebonds>0u) {
              b[bondidx] = 1u;
              bondenergy[bondidx] = atom_bondenergies[nv] * (2*`+mL2+` - int(dist));
              freebonds --;
            }
            else {  // if all bonds are busy - choose most-energy-preferable bond
              uint min_o = 0u;  int min_e = 1000;
              for(uint o=0u; o<bondidx; o++) {  // looking for minimum energy in existing bonds
                if(b[o]==0u) continue;
                if(bondenergy[o] < min_e) {
                  min_e = bondenergy[o];
                  min_o = o;
                }
              }
              if(min_e<atom_bondenergies[nv]) {
                b[min_o]   = 0u;  bondenergy[min_o] = 0;
                b[bondidx] = 1u;  bondenergy[min_o] = atom_bondenergies[nv] * (2*`+mL2+` - int(dist));
              }
            }
          }
          
          // acceleration
          xy.z += int(round(d2xy.x));
          xy.w += int(round(d2xy.y));
          
          if(xy.z<-`+mV+`) xy.z = -`+mV+`;
          if(xy.w<-`+mV+`) xy.w = -`+mV+`;
          if(xy.z> `+mV+`) xy.z =  `+mV+`;
          if(xy.w> `+mV+`) xy.w =  `+mV+`;
          
          // movement
          xy.xy += xy.zw / 2;
          
          if(xy.x<-`+mR+`) xy.x = -`+mR+`;
          if(xy.y<-`+mR+`) xy.y = -`+mR+`;
          if(xy.x> `+mR+`) xy.x =  `+mR+`;
          if(xy.y> `+mR+`) xy.y =  `+mR+`;
          
          al = 1u;  // stay same by default
          
          if(trend0!=0u) {  // was trending at previous turn
            uvec4 acceptor = cells[trend0];
            if(ExtractAl(acceptor)==0u && ExtractAt(acceptor.a)==trend0) {  // neighbour empty cell accepted transfer from self to it
              al = 0u;
            }
            else {
              trend = CalcTrend(xy);
            }
          }
          else {
            trend = CalcTrend(xy);  // wanted transfer marker
          }
          
          // packing back 2*16bit to 1*32bit
          uvec2 PackXY = PackXY(xy);
          color.x = PackXY.x;
          color.y = PackXY.y;
        }
        // empty cell ////////////////////////////////////////////////////////////////
        else {
          if(trend0!=0u) {  // accepting cell
            uint atrend = antitrends[trend0];
            uvec4 trender = cells[atrend];
            if(ExtractAl(trender)>0u && ExtractAt(trender.a)==trend0) {
              color.xy = XY4Trended(atrend, trender);  // @ motion skips a beat here
              al = 1u;
              v = ExtractAv(trender.a);
            }
          }
          else {  // looking for something to accept
            for(uint n=1u; n<`+RC+`u; n++) {
              if(ExtractAl(cells[n])==0u || ExtractAt(cells[n].a)==0u) continue;
              uint atrend = antitrends[n];
              if(ExtractAt(cells[n].a)!=atrend) continue;
              trend = atrend;
              break;  // first of trending nearby cells wins the space  // @ need to choose most far-went-away cell by distance
            }
          }
        }
        //  ////////////////////////////////////////////////////////////////
        
        color.b = PackB(b);
        
        color.a = PackA(al, v, trend, self.a, dbg);
        
        ` + fs_Prepare2Return('color') + `
      }
    }
  `;//console.log(CalcFragmentShaderSource);
  var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_rulestexture"]);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
else if(Named=='Bond4C') {
  function ExtractRGBA(cell) {
    var s = '';
    
    var al    = (cell.a & 1);
    var fl    = (cell.a >>> 1) % 16;
    var decay = (cell.a >>> 5);
    s += '(' + al + ', ' + fl + ', ' + decay + ') ';
    
    var gate  = (cell.g >>> 0) % 8;
    var gone  = (cell.g >>> 3) % 8;
    s += '{' + gate + ', ' + gone + '} ';
    
    s += '[' +
      ((cell.b >>> 0) % 4) + ' ' +
      ((cell.b >>> 2) % 4) + ' ' +
      ((cell.b >>> 4) % 4) + ' ' +
      ((cell.b >>> 6) % 4) +
    '] ';
    
    var speed = (cell.r >>> 0) % 8;
    var strid = (cell.r >>> 3) % 8;
    s += '(' + speed + ', ' + strid + ') ';
    
    return s;
  }
  
  var fs_ExtractRGBA = `
    ////////////////////////////////////////////////////////////////
    
    uint ExtractAl(uvec4 cell) {  // aliveness = 1 bit
      return cell.a & 1u;
    }
    
    uint ExtractFl(uvec4 cell) {  // flavor = 4 bit
      return (cell.a >> 1u) % 16u;
    }
    
    uint ExtractDecay(uvec4 cell) {  // decay = 3 bit
      return cell.a >> 5u;
    }
    
    uint PackA(uint al, uint fl, uint decay) {
      return (al    <<  0u) |
             (fl    <<  1u) |
             (decay <<  5u);
    }
    
    ////////////////////////////////////////////////////////////////
    
    uint[5] ExtractBonds(uvec4 cell) {
      return uint[5](
        0u,
        (cell.b >> 0u) % 4u,
        (cell.b >> 2u) % 4u,
        (cell.b >> 4u) % 4u,
        (cell.b >> 6u) % 4u
      );
    }
    
    uint PackB(uint[5] bonds) {
      return (bonds[1] << 0u) |
             (bonds[2] << 2u) |
             (bonds[3] << 4u) |
             (bonds[4] << 6u);
    }
    
    ////////////////////////////////////////////////////////////////
        
    uint ExtractGate(uvec4 cell) {
      return (cell.g >> 0u) % 8u;
    }
    
    uint ExtractGone(uvec4 cell) {  // direction of actual previous movement; may differ from speed when forced by stretched bond
      return (cell.g >> 3u) % 8u;
    }
    
    uint PackG(uint gate, uint gone) {
      return (gate << 0u) |
             (gone << 3u);
    }
    
    ////////////////////////////////////////////////////////////////
    
    uint ExtractSpeed(uvec4 cell) {  // default movement direction
      return (cell.r >> 0u) % 8u;
    }
    
    uint ExtractStrid(uvec4 cell) {  // strid(e) = @ unused atm
      return (cell.r >> 3u) % 8u;
    }
    
    uint PackR(uint speed, uint strid) {
      return (speed << 0u) |
             (strid << 3u);
    }
    
    ////////////////////////////////////////////////////////////////
  `;
  
  var CalcFragmentShaderSource = `
    precision mediump float;
    precision highp int;
    
    uniform highp usampler3D u_fieldtexture;  // Field texture
    uniform highp usampler2D u_rulestexture[`+FD+`];  // Rules texture
    uniform highp uint u_nturn;  // nturn
    
    in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
    
    out uvec4 glFragColor[`+FD+`];
    
    ivec3 tex3coord;
    ivec3 fieldSize;
    
    ` + fs_ModuloTorus + `
    ` + fs_GetCell() + `
    ` + fs_GetTexel2D + `
    ` + fs_ExtractRGBA + `
    
    float atom_masses[4]   = float[4](0., 1., 2., 3.);
    uint atom_bondnums[4]   = uint[4](0u, 1u, 2u, 4u);  // number of covalent bonds
    int atom_bondenergies[4] = int[4]( 0, 20, 25, 15);
    
    uint revers[5] = uint[5](0u, 3u, 4u, 1u, 2u);
    
    void main() {
      fieldSize = textureSize(u_fieldtexture, 0);
      
      for(int layer=0; layer<`+FD+`; layer++) {
        tex3coord = ivec3(v_texcoord, layer);
        
        // getting cell's neighborhood
        
        uvec4 cells[`+RC+`];
        ` + fs_GetNeibs + `
        
        // extracting self-cell info
        
        uint    al0    = ExtractAl(   cells[0]),  al    = al0;
        uint    fl0    = ExtractFl(   cells[0]),  fl    = fl0;
        uint    decay0 = ExtractDecay(cells[0]),  decay = 0u;
        uint    gate0  = ExtractGate( cells[0]),  gate  = 0u;
        uint    gone0  = ExtractGone( cells[0]),  gone  = gone0;
        uint    speed0 = ExtractSpeed(cells[0]),  speed = speed0;
        uint    strid0 = ExtractStrid(cells[0]),  strid = strid0;  // @ strids are broken!
        uint[5] bonds0 = ExtractBonds(cells[0]),  bonds = uint[5](0u, 0u, 0u, 0u, 0u);
        
        uint paired = 0u;  // number of paired bonds of this cell
        uint backed = 0u;  // number of paired bonds of this cell, excluding forward one
        uint moveto = 99u;  // forces moving (opening gate) to this direction; 99 = null
        uint freebonds = atom_bondnums[fl];
        int[5] bondenergy = int[5](0, 0, 0, 0, 0);
        
        // paired bonds ////////////////////////////////////////////////////////////////
        
        for(uint n=1u; n<`+RC+`u; n++) {
          uint nb = ExtractBonds(cells[n])[revers[n]];  // neib's bond to this cell
          
          if(bonds0[n]==3u) {
            //
          }
          else if(nb==3u) {
            bonds0[n] = 2u;
          }
          else if(bonds0[n]>0u && nb>0u) {  // paired bond
            bonds0[n] = 2u;
            paired ++;
            if(n!=gate0) backed ++;
          }
          else if(bonds0[n]>0u) {
            bonds0[n] = al0>0u ? 1u : 0u;
          }
          
          if(al0>0u) {
            if(nb==3u) {  // stretched bond forces us to stand
              uint nstrid = ExtractStrid(cells[n]);
              if(nstrid>0u) {
                moveto = nstrid;
              }
              else {
                moveto = 0u;
              }
            }
            else if(bonds0[n]==2u && ExtractAl(cells[n])==0u) {  // moving to direction of stretched bond
              if(speed0==n) {
                moveto = n;
              }
              else {
                moveto = ExtractGone(cells[n]);
                if(ExtractAl(cells[moveto])>0u) moveto = n;
              }
            }
          }
        }
        
        // sluice transfers ////////////////////////////////////////////////////////////////
        
        if(gate0>0u) {  // was open at previous turn
          if(al0>0u) {  // alive cell
            uvec4 mate = cells[gate0];
            if(ExtractGate(mate)==revers[gate0]) {  // sluice open
              if(ExtractAl(mate)==0u) {  // mate is empty => transfering whole cell
                al = 0u;
                decay = 3u;
                gone = gate0;
                speed = speed0;
                bonds = bonds0;  // keeping bonds for elastic bonding
                if(backed>0u) {
                  bonds[gate0] = 3u;
                  for(uint n=1u; n<`+RC+`u; n++) {
                    if(ExtractDecay(cells[n])==0u) continue;
                    uint nspeed = ExtractSpeed(cells[n]);
                    if(nspeed==0u) continue;
                    if(nspeed!=speed && nspeed!=gone) strid = nspeed;
                  }
                }
              }
              else {  // mate is alive => exchanging momentum
                speed = ExtractSpeed(mate);
              }
            }
          }
          else {  // dead cell
            uvec4 mate = cells[gate0];
            if(ExtractAl(mate)>0u && ExtractGate(mate)==revers[gate0]) {
              al = 1u;
              fl = ExtractFl(mate);
              gone = gate0;
              speed = ExtractSpeed(mate);
            }
          }
        }
        
        // opening bond windows ////////////////////////////////////////////////////////////////
        
        if(al>0u) {
          for(uint n=1u; n<`+RC+`u; n++) {
            if(ExtractAl(cells[n])==0u && bonds0[n]<2u) continue;  // not bonding to dead cells exept for previuosly paired-bonded
            if(al0==0u && ExtractGate(cells[n])==revers[n]) continue;  // not bonding to previous me myself
            
            int e = atom_bondenergies[ExtractFl(cells[n])];
            
            if(freebonds>0u) {
              bonds[n] = bonds0[n]>1u ? 2u : 1u;
              bondenergy[n] = e;
              freebonds --;
            }
            else {  // if all bonds are busy - choose most-energy-preferable bond
              uint min_n = 0u;  int min_e = 1000;
              for(uint nn=1u; nn<n; nn++) {  // looking for minimum energy in existing bonds
                if(bonds[nn]==0u) continue;
                if(bondenergy[nn] < min_e) {
                  min_e = bondenergy[nn];
                  min_n = nn;
                }
              }
              if(min_e < e) {
                bonds[min_n] = 0u;  bondenergy[min_n] = 0;
                bonds[n] = bonds0[n]>1u ? 2u : 1u;  bondenergy[n] = e;
              }
            }
          }
        }
        else if(al0==0u) {
          bonds = bonds0;  // keeping bonds for died cells (for stretched bonds)
        }
        
        // opening gate ////////////////////////////////////////////////////////////////
        
        bool passive = false;
        if(al0==0u && al==0u || speed0==0u && speed==0u) passive = true;
        
        if(moveto!=99u && al>0u) {
          gate = moveto;
        }
        else if(passive) {
          for(uint n=1u; n<`+RC+`u; n++) {
            if(n==gone && bonds0[n]==3u) continue;  // not opening gate to previous me myself; @ need to allow normal hits from gone side
            if(ExtractAl(cells[n])==0u) continue;
            if(ExtractGate(cells[n])==revers[n]) {  // neib moves to us
              gate = n;
              break;
            }
          }
        }
        else if(al0>0u && al>0u) {
          gate = speed;
          if(ExtractAl(cells[speed])>0u) {  // if cell in direction of speed is busy - move where we are pushed to
            for(uint n=1u; n<`+RC+`u; n++) {
              if(n==speed) continue;  // face-to-face collision is ok
              if(ExtractAl(cells[n])==0u) continue;
              uint ngate = ExtractGate(cells[n]);
              if(ngate==revers[n]) {  // neib is pushing us
                gate = ngate;  // moving in direction of pushing cell
                break;
              }
            }
          }
        }
        
        // decay ////////////////////////////////////////////////////////////////
        
             if(al>0u)     decay = 7u;
        else if(al0>0u)    decay = 3u;
        else if(decay0>0u) decay = decay0 - 1u;
        
        if(decay==0u) {  // clearing decayed cell
          fl = 0u;
          gone = 0u;
          speed = 0u;
          strid = 0u;
          bonds = uint[5](0u, 0u, 0u, 0u, 0u);
        }
        
        // rgba packing ////////////////////////////////////////////////////////////////
        
        uvec4 color = uvec4(0);
        color.a = PackA(al, fl, decay);
        color.b = PackB(bonds);
        color.g = PackG(gate, gone);
        color.r = PackR(speed, strid);
        
        ` + fs_Prepare2Return('color') + `
      }
    }
  `;//console.log(CalcFragmentShaderSource);
  var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_rulestexture", "u_nturn"]);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
else if(Named=='Bond4C2') {
  function ExtractRGBA(cell) {
    var s = '';
    
    var al    = (cell.a & 1);
    var fl    = (cell.a >>> 1) % 16;
    var decay = (cell.a >>> 5);
    s += '(' + al + ', ' + fl + ', ' + decay + ') ';
    
    var gate  = (cell.g >>> 0) % 8;
    var gone  = (cell.g >>> 3) % 8;
    s += '{' + gate + ', ' + gone + '} ';
    
    s += '[' +
      ((cell.b >>> 0) % 4) + ' ' +
      ((cell.b >>> 2) % 4) + ' ' +
      ((cell.b >>> 4) % 4) + ' ' +
      ((cell.b >>> 6) % 4) +
    '] ';
    
    var speed = (cell.r >>> 0) % 8;
    var strid = (cell.r >>> 3) % 8;
    s += '(' + speed + ', ' + strid + ') ';
    
    return s;
  }
  
  var fs_ExtractRGBA = `
    ////////////////////////////////////////////////////////////////
    
    uint ExtractAl(uvec4 cell) {  // aliveness = 1 bit
      return cell.a & 1u;
    }
    
    uint ExtractFl(uvec4 cell) {  // flavor = 4 bit
      return (cell.a >> 1u) % 16u;
    }
    
    uint ExtractDecay(uvec4 cell) {  // decay = 3 bit
      return cell.a >> 5u;
    }
    
    uint PackA(uint al, uint fl, uint decay) {
      return (al    <<  0u) |
             (fl    <<  1u) |
             (decay <<  5u);
    }
    
    ////////////////////////////////////////////////////////////////
    
    uint[5] ExtractBonds(uvec4 cell) {
      return uint[5](
        0u,
        (cell.b >> 0u) % 4u,
        (cell.b >> 2u) % 4u,
        (cell.b >> 4u) % 4u,
        (cell.b >> 6u) % 4u
      );
    }
    
    uint PackB(uint[5] bonds) {
      return (bonds[1] << 0u) |
             (bonds[2] << 2u) |
             (bonds[3] << 4u) |
             (bonds[4] << 6u);
    }
    
    ////////////////////////////////////////////////////////////////
        
    uint ExtractGate(uvec4 cell) {
      return (cell.g >> 0u) % 8u;
    }
    
    uint ExtractGone(uvec4 cell) {  // direction of actual previous movement; may differ from speed when forced by stretched bond
      return (cell.g >> 3u) % 8u;
    }
    
    uint PackG(uint gate, uint gone) {
      return (gate << 0u) |
             (gone << 3u);
    }
    
    ////////////////////////////////////////////////////////////////
    
    uint ExtractSpeed(uvec4 cell) {  // default movement direction
      return (cell.r >> 0u) % 8u;
    }
    
    uint ExtractStrid(uvec4 cell) {  // strid(e) = @ unused atm
      return (cell.r >> 3u) % 8u;
    }
    
    uint PackR(uint speed, uint strid) {
      return (speed << 0u) |
             (strid << 3u);
    }
    
    ////////////////////////////////////////////////////////////////
  `;
  
  var CalcFragmentShaderSource = `
    precision mediump float;
    precision highp int;
    
    uniform highp usampler3D u_fieldtexture;  // Field texture
    uniform highp usampler2D u_rulestexture[`+FD+`];  // Rules texture
    uniform highp uint u_nturn;  // nturn
    
    in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
    
    out uvec4 glFragColor[`+FD+`];
    
    ivec3 tex3coord;
    ivec3 fieldSize;
    
    ` + fs_ModuloTorus + `
    ` + fs_GetCell() + `
    ` + fs_GetTexel2D + `
    ` + fs_ExtractRGBA + `
    
    float atom_masses[4]   = float[4](0., 1., 2., 3.);
    uint atom_bondnums[4]   = uint[4](0u, 1u, 2u, 4u);  // number of covalent bonds
    int atom_bondenergies[4] = int[4]( 0, 20, 25, 15);
    
    int BondPairEnergies(uint fl1, uint fl2) {
      if(fl1>fl2) { uint fl0 = fl1;  fl1 = fl2;  fl2 = fl0; }
      int e = 0;
           if(fl1==1u && fl2==1u) e = 435;  // H-H
      else if(fl1==1u && fl2==2u) e = 464;  // O-H
      else if(fl1==1u && fl2==3u) e = 413;  // C-H
      else if(fl1==2u && fl2==2u) e = 138;  // O-O
      else if(fl1==2u && fl2==3u) e = 335;  // C-O
      else if(fl1==3u && fl2==3u) e = 347;  // C-C
      // N-H 389
      // O-N
      // C-N 293
      // N-N 159
      return e;
    }
    
    uint revers[5] = uint[5](0u, 3u, 4u, 1u, 2u);
    
    void main() {
      fieldSize = textureSize(u_fieldtexture, 0);
      
      for(int layer=0; layer<`+FD+`; layer++) {
        tex3coord = ivec3(v_texcoord, layer);
        
        // getting cell's neighborhood
        
        uvec4 cells[`+RC+`];
        ` + fs_GetNeibs + `
        
        // extracting self-cell info
        
        uint    al0    = ExtractAl(   cells[0]),  al    = al0;
        uint    fl0    = ExtractFl(   cells[0]),  fl    = fl0;
        uint    decay0 = ExtractDecay(cells[0]),  decay = 0u;
        uint    gate0  = ExtractGate( cells[0]),  gate  = 0u;
        uint    gone0  = ExtractGone( cells[0]),  gone  = gone0;
        uint    speed0 = ExtractSpeed(cells[0]),  speed = speed0;
        uint    strid0 = ExtractStrid(cells[0]),  strid = strid0;  // @ strids are broken!
        uint[5] bonds0 = ExtractBonds(cells[0]),  bonds = bonds0;
        
        uint freebonds = atom_bondnums[fl];
        int[5] bondenergy = int[5](0, 0, 0, 0, 0);
        
        uint stage = u_nturn % 4u;  // 0 = bonds, 1 = gate opening, 2 = gate accepting, 3 = movement
        
        // bonds opening ////////////////////////////////////////////////////////////////
        if(stage==0u) {
          // opening new bond windows
          if(al0>0u) {
            bonds = uint[5](0u, 0u, 0u, 0u, 0u);
            for(uint n=1u; n<`+RC+`u; n++) {
              uint bnd = 
                bonds0[n]>=2u && ExtractAl(cells[n])==0u && ExtractBonds(cells[n])[revers[n]]==3u ? 3u :
                bonds0[n]>=2u ? 2u :
                1u
              ;
              
              if(ExtractAl(cells[n])>0u || bnd==3u) {
                int e = BondPairEnergies(fl0, ExtractFl(cells[n]));
                
                if(freebonds>0u) {
                  bonds[n] = bnd;
                  bondenergy[n] = e;
                  freebonds --;
                }
                else {  // if all bonds are busy - choose most-energy-preferable bond
                  uint min_n = 0u;  int min_e = 1000;
                  for(uint nn=1u; nn<n; nn++) {  // looking for minimum energy in existing bonds
                    if(bonds[nn]==0u) continue;
                    if(bondenergy[nn] < min_e) {
                      min_e = bondenergy[nn];
                      min_n = nn;
                    }
                  }
                  if(min_e < e) {
                    bonds[min_n] = 0u;  bondenergy[min_n] = 0;
                    bonds[n] = bnd;  bondenergy[n] = e;
                  }
                }
              }
            }
          }
          else {
            for(uint n=1u; n<`+RC+`u; n++) {
              if(ExtractDecay(cells[n])==0u) bonds[n] = 0u;
            }
          }
        }
        // gate opening ////////////////////////////////////////////////////////////////
        else if(stage==1u) {
          if(al0>0u) {
            uint maxprio = 0u, curprio = 0u, curgate = 0u;
            
            for(uint n=1u; n<`+RC+`u; n++) {
              uint nb = ExtractBonds(cells[n])[revers[n]];  // neib's bond to this cell
              
              // bond pairing status
              if(bonds0[n]==3u && nb==3u) {
              }
              else if(bonds0[n]>0u && nb>0u) {  // paired bond
                bonds[n] = 2u;
              }
              else if(bonds0[n]>0u) {
                bonds[n] = al0>0u ? 1u : 0u;
              }
              
              // bonded gates
              if(bonds[n]==3u) {  // stretched bond forces
                if(gone0==revers[n]) {  // backwards
                  curprio = 40u;  curgate = 0u;
                }
                else if(speed0==n) {
                  curprio = 30u;  curgate = n;
                }
                else {
                  uint ngone = ExtractGone(cells[n]);
                  if(ngone>0u) {
                    curprio = 20u;  curgate = ngone;
                  }
                  else {
                    curprio = 10u;  curgate = n;
                  }
                }
              }
              
              if(curprio>maxprio) {
                maxprio = curprio;  gate = curgate;
              }
            }
            
            
            if(maxprio>0u) {
              // gate is defined by bonds
            }
            else if(speed0>0u) {
              gate = speed0;
              if(ExtractAl(cells[speed])>0u) {  // if cell in direction of speed is busy - move where we are pushed to
                for(uint n=1u; n<`+RC+`u; n++) {
                  if(n==speed0) continue;  // face-to-face collision is ok
                  if(ExtractAl(cells[n])==0u) continue;
                  uint ngate = ExtractGate(cells[n]);
                  if(ngate==revers[n]) {  // neib is pushing us
                    gate = ngate;  // moving in direction of pushing cell
                    break;
                  }
                }
              }
            }
            else {
              for(uint n=1u; n<`+RC+`u; n++) {
                if(ExtractAl(cells[n])==0u) continue;
                if(ExtractSpeed(cells[n])==revers[n]) {  // neib moves to us
                  gate = n;
                  break;  // @ todo: accept not first but most priority
                }
                if(bonds[n]==1u) {  // unpaired bond
                  gate = revers[n];  // slight repulsion for refused bond
                }
              }
            }
          }
        }
        // gate accepting ////////////////////////////////////////////////////////////////
        else if(stage==2u) {
          if(al0==0u) {
            int maxprio = 0, curprio = 0;
            for(uint n=1u; n<`+RC+`u; n++) {
              if(ExtractAl(cells[n])==0u) continue;
              if(ExtractGate(cells[n])==revers[n]) {  // neib gate to us
                uint nspeed = ExtractSpeed(cells[n]);
                if(nspeed==revers[n]) {  // neib speed to us
                  curprio = 40;
                }
                else if(nspeed==n) {  // neib speed away from us
                  curprio =  0;
                }
                else if(nspeed!=0u) {  // neib moves perpendicular
                  curprio = 20;
                }
                else {  // neib stands
                  uint ngone = ExtractGone(cells[n]);
                  if(ngone==revers[n]) {
                    curprio = 31;
                  }
                  else if(ngone==n) {
                    curprio =  0;
                  }
                  else {
                    curprio = 30;
                  }
                }
                
                if(curprio>maxprio) {
                  maxprio = curprio;  gate = n;
                }
              }
            }
          }
          else {
            gate = gate0;
          }
        }
        // movement, sluice transfer ////////////////////////////////////////////////////////////////
        else if(stage==3u) {
          if(gate0>0u) {  // was open at previous turn
            if(al0>0u) {  // alive cell
              uvec4 mate = cells[gate0];
              if(ExtractGate(mate)==revers[gate0]) {  // sluice open
                if(ExtractAl(mate)==0u) {  // mate is empty => transfering whole cell
                  al = 0u;
                  gone = gate0;
                  speed = speed0;
                  
                  uint behind = 0u;  // bonds left behind
                  for(uint n=1u; n<`+RC+`u; n++) {
                    if(n==gate0) continue;
                    if(bonds0[n]==2u) {
                      bonds[n] = 3u;
                      behind ++;
                    }
                    else {
                      bonds[n] = 0u;
                    }
                  }
                  if(behind>0u) bonds[gate0] = 3u;
                }
                else {  // mate is alive => exchanging momentum
                  speed = ExtractSpeed(mate);
                }
              }
            }
            else {  // dead cell
              uvec4 mate = cells[gate0];
              if(ExtractGate(mate)==revers[gate0]) {
                if(ExtractAl(mate)>0u) {
                  al = 1u;
                  fl = ExtractFl(mate);
                  gone = revers[gate0];
                  speed = ExtractSpeed(mate);
                  
                  uint[5] nbonds = ExtractBonds(mate);
                  uint behind = 0u;  // bonds mate left behind
                  for(uint n=1u; n<`+RC+`u; n++) {
                    if(n!=revers[gate0] && nbonds[n]==2u) behind ++;
                  }
                  bonds[gate0] = behind>0u ? 3u : 0u;
                }
              }
            }
          }
        }
        // --- ////////////////////////////////////////////////////////////////
        
        
        // decay ////////////////////////////////////////////////////////////////
        
             if(al>0u)     decay = 7u;
        else if(al0>0u)    decay = 4u;
        else if(decay0>0u) decay = decay0 - 1u;
        
        if(decay==0u) {  // clearing decayed cell
          fl = 0u;
          gone = 0u;
          speed = 0u;
          strid = 0u;
          bonds = uint[5](0u, 0u, 0u, 0u, 0u);
        }
        
        // rgba packing ////////////////////////////////////////////////////////////////
        
        uvec4 color = uvec4(0);
        color.a = PackA(al, fl, decay);
        color.b = PackB(bonds);
        color.g = PackG(gate, gone);
        color.r = PackR(speed, strid);
        
        ` + fs_Prepare2Return('color') + `
      }
    }
  `;//console.log(CalcFragmentShaderSource);
  var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_rulestexture", "u_nturn"]);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
else {
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
    
    ` + (TT>2 ? fs_GetCell('GetPrevCell', 'u_prevtexture') : ``) + `
    
    ` + fs_GetTexel2D + `
    
    void main() {
      fieldSize = textureSize(u_fieldtexture, 0);
      
      uvec4 color;
      
      for(int layer=0; layer<`+FD+`; layer++) {
        
        // getting cell's neighborhood
        
        tex3coord = ivec3(v_texcoord, layer);
        
        uvec4 cells[`+RC+`];
        ` + fs_GetNeibs + `
        
        // finding rule for this neighborhood
        
        uint rulecoord = 0u;
        for(int n=0; n<`+RC+`; n++) {
          rulecoord *= `+RB+`u;
          rulecoord += cells[n].a;  // @ 8bit-packing only!
        }
        ivec2 t = ivec2(rulecoord, 0);
        if(t.x>=`+RX+`) { t.y = t.x / `+RX+`;  t.x = t.x % `+RX+`; }
        
        uvec4 rule = GetTexel2D(u_rulestexture, layer, t);
        
        // rule.a is the new color (value) of the cell
        color.a = rule.a;
        
        ` + (TT>2 ? `
        uvec4 prev = GetPrevCell(0, 0, 0);
        // color.a - prev.a
        if(prev.a>0u) {
          int inewv = int(color.a) - int(prev.a);
          if(inewv<0) inewv += `+RB+`;
          color.a = uint(inewv);
        }
        ` : ``) + `
        
        uvec4 self = cells[0];  // previous self cell state
        
        ` + fs_PackAliveness('color.a') + `
        
        ` + fs_Prepare2Return('color') + `
      }
    }
  `;
  var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_prevtexture", "u_rulestexture"]);
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


// CALC MAIN ////////////////////////////////////////////////////////////////

function Calc(single=0) {
  if(cfg.paused && single!=1) return 0;
  
  gl.useProgram(CalcProgram);
  gl.viewport(0, 0, FW, FH);
  
  BindBuffersAttachments(Framebuffers[T1]);
  ActivateTexture(T0, CalcProgram.location.u_fieldtexture);
  if(Mode!='PRT') ActivateTexture(TT>2 ? T2 : T0, CalcProgram.location.u_prevtexture);
  
  if(Mode=='PRT') {
    //PS[0] = rndJ(-1, 2);  PS[1] = rndJ(-1, 2);
    PS[0] = floor(nturn / 3) % 3 - 1;  PS[1] = nturn % 3 - 1;
    //PS[0] = RG[nturn % RC][0];  PS[1] = RG[nturn % RC][1];
    
    gl.uniform1iv(CalcProgram.location.u_ps, PS);
  }
  
  var rulestexture_nums = [];  for(var z=0; z<FD; z++) rulestexture_nums[z] = TT + z;
  gl.uniform1iv(CalcProgram.location.u_rulestexture, rulestexture_nums);
  
  gl.uniform1ui(CalcProgram.location.u_nturn, nturn);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
  
  FlipTime();
  
  nturn ++;
  
  if(single==1) return true;
  
  if(cfg.showiter) { if(nturn % cfg.showiter == 0) Show(); }
  else if(cfg.maxfps<=60) Show();
  /* else Show() rotates in its own cycle */
  
  if((nturn % cfg.turn4stats)==0) Stats();
  
  if(cfg.pauseat>0 && nturn==cfg.pauseat) Pause(1);
  
  if(cfg.maxfps>1000) { if(nturn%10==0) setTimeout(Calc, 1); else Calc(); }
  else if(cfg.maxfps && cfg.maxfps!=60) setTimeout(Calc, Math.floor(1000 / cfg.maxfps));
  else requestAnimationFrame(Calc);
}

//  ////////////////////////////////////////////////////////////////