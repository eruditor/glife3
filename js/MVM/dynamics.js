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
