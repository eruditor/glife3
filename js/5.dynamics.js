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

function fs_PackAliveness(alive='alive') {
  return pixelBits<32
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
  
  // b[0] = trending = moving particle transfer to another cell:
  // request for moving away alive cell or acceptance for nearby empty cell
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
  
  // -- highest 16bit = debug info;  next 8 bit = alive cell's color;  lowest 8bit = decay and color of died cell
  var fs_ExtractA = `
    #define A_alive 0
    #define A_v     1
    #define A_decay 2
    #define A_trend 3
    #define A_aux   4
    #define A_dbg   5
    
    uint[6] ExtractA(uint cell_a) {
      uint[6] ret;
      ret[A_alive] = cell_a & 1u;           //  1 bit = is the cell alive
      ret[A_v]     = cell_a << 27u >> 28u;  //  4 bit = cell's value
      ret[A_decay] = cell_a << 24u >> 29u;  //  3 bit = color decay for died cell
      ret[A_trend] = cell_a << 20u >> 28u;  //  4 bit = trending/accepting flags
      ret[A_aux]   = cell_a << 16u >> 28u;  //  4 bit = aux = ...
      ret[A_dbg]   = cell_a >> 16u;         // 16 bit = debug info
      return ret;
    }
    
    uint ExtractAl(uint cell_a) {
      return cell_a & 1u;
    }
    
    uint ExtractAv(uint cell_a) {
      return (cell_a & 1u)>0u ? cell_a << 27u >> 28u : 0u;
    }
    
    uint ExtractAt(uint cell_a) {
      return cell_a << 20u >> 28u;
    }
    
    uint PackA(uint al, uint v, uint trend, uint aux, uint self_a, uint dbg) {
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
             (aux   << 12u) |
             (dbg   << 16u);
    }
  `;
  
  var fs_Trends = `
    uint CalcTrend(ivec4 xy) {
           if(xy.x<=-`+mL+` && xy.y<=-`+mL+`) return 1u;
      else if(xy.x>= `+mL+` && xy.y<=-`+mL+`) return 3u;
      else if(                 xy.y<=-`+mL+`) return 2u;
      else if(xy.x<=-`+mL+` && xy.y>= `+mL+`) return 7u;
      else if(xy.x>= `+mL+` && xy.y>= `+mL+`) return 5u;
      else if(                 xy.y>= `+mL+`) return 6u;
      else if(xy.x<=-`+mL+`                 ) return 8u;
      else if(xy.x>= `+mL+`                 ) return 4u;
      else                                    return 0u;
    }
    
    //                           0   1   2   3   4   5   6   7   8
    uint antitrends[9] = uint[9](0u, 5u, 6u, 7u, 8u, 1u, 2u, 3u, 4u);
    
    uvec2 XY4Trended(uint n, uvec4 cell) {
           if(n==1u) return uvec2(cell.x - `+mL2+`u, cell.y - `+mL2+`u);
      else if(n==3u) return uvec2(cell.x + `+mL2+`u, cell.y - `+mL2+`u);
      else if(n==2u) return uvec2(cell.x           , cell.y - `+mL2+`u);
      else if(n==7u) return uvec2(cell.x - `+mL2+`u, cell.y + `+mL2+`u);
      else if(n==5u) return uvec2(cell.x + `+mL2+`u, cell.y + `+mL2+`u);
      else if(n==6u) return uvec2(cell.x           , cell.y + `+mL2+`u);
      else if(n==8u) return uvec2(cell.x - `+mL2+`u, cell.y           );
      else if(n==4u) return uvec2(cell.x + `+mL2+`u, cell.y           );
      else if(n==0u) return uvec2(cell.x           , cell.y           );
    }
    
    ivec2 NthDirection(uint n) {  // @ =Rgeom
           if(n==1u) return ivec2(-1, -1);
      else if(n==2u) return ivec2( 0, -1);
      else if(n==3u) return ivec2( 1, -1);
      else if(n==4u) return ivec2( 1,  0);
      else if(n==5u) return ivec2( 1,  1);
      else if(n==6u) return ivec2( 0,  1);
      else if(n==7u) return ivec2(-1,  1);
      else if(n==8u) return ivec2(-1,  0);
      else if(n==0u) return ivec2( 0,  0);
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
    
    ` + fs_ModuloTorus + `
    
    ` + fs_GetCell() + `
    
    ` + fs_GetTexel2D + `
    
    ` + fs_ExtractXY + `
    
    ` + fs_ExtractB + `
    
    ` + fs_ExtractA + `
    
    ` + fs_Trends + `
    
    uint CountArray17(uint[8] b) {
      uint ret = 0u;
      for(uint i=1u; i<=7u; i++) {
        if(b[i]>0u) ret ++; 
      }
      return ret;
    }
    
    bool ScanArray17(uint[8] b, uint n) {
      return (b[1]==n || b[2]==n || b[3]==n || b[4]==n || b[5]==n || b[6]==n || b[7]==n) ? true : false;
    }
    
    float atom_masses[4]   = float[4](0., 1., 2., 3.);
    uint atom_bondnums[4]   = uint[4](0u, 1u, 2u, 3u);  // number of covalent bonds
    int atom_bondenergies[4] = int[4]( 0, 1 , 2 , 3 );
    
    void main() {
      fieldSize = textureSize(u_fieldtexture, 0);
      
      for(int layer=0; layer<`+FD+`; layer++) {
        tex3coord = ivec3(v_texcoord, layer);
        
        // getting cell's neighborhood
        uvec4 cells[`+RC+`];
        ` + fs_GetNeibs + `
        
        uvec4 self = cells[0];  // previous self cell state
        
        uvec4 color = uvec4(0);
        
        uint[8] b = uint[8](0u, 0u, 0u, 0u, 0u, 0u, 0u, 0u);  // new b
        
        uint al0 = ExtractAl(self.a);
        uint al = 0u;
        
        uint v = 0u;
        
        uint trend0 = ExtractAt(self.a);
        uint trend = 0u;
        
        uint dbg = 0u;
        
        // alive cell ////////////////////////////////////////////////////////////////
        if(al0>0u) {
          ivec4 xy = ExtractXY(self);
          
          v = ExtractAv(self.a);  // cell's value
          
          uint[8] b0 = ExtractB(self);
          uint bn = atom_bondnums[v];
          uint b0count = CountArray17(b0);
          
          // forces
          
          vec2 d2xy = vec2(0);
          uint freebonds = bn;
          int bondenergy[8] = int[8](0,0,0,0,0,0,0,0);
          
          for(uint n=1u; n<`+RC+`u; n++) {
            uint nv = ExtractAv(cells[n].a);  // @ optimize it
            if(nv==0u) continue;
            
            ivec4 nxy = ExtractXY(cells[n]);  // neib's coords and velocity
            ivec2 ndir = NthDirection(n);  // points from current cell to neib cell
            ivec2 dl = `+mL2+` * ndir + nxy.xy - xy.xy;  // points from current atom coords to neib atom coords
            
            float dist = sqrt(float(dl.x*dl.x + dl.y*dl.y));  // distance between atoms
            //float dist = float(abs(dl.x) + abs(dl.y));
            
            if(dist>8.*`+fmL+`) continue;
            
            uint[8] nb = ExtractB(cells[n]);  // neib's bonds
            
            float charge = 1.;  //if(nv!=v) charge = -1.;
            //d2xy += charge * atom_masses[nv] * `+fmL+` * vec2(dl) / dist / dist * `+fmL+` / dist;  // gravity
            //d2xy += 0.02 / atom_masses[v] * vec2(dl) / dist * (dist - `+mL2+`.);  // harmonic
            
            bool bonded = false;
            if(ScanArray17(b0, n)) {  // this cell has a bond to n-th neib
              uint an = antitrends[n];
              if(ScanArray17(nb, an)) {  // neib also has a bond to this cell
                bonded = true;
                //d2xy += charge * atom_masses[nv] * `+fmL+` * vec2(dl) / dist / dist * `+fmL+` / dist;  // gravity
                //d2xy += 100. / atom_masses[v] * vec2(dl) / dist * (`+mL2+`./dist - `+mL2+`./dist*`+mL2+`./dist);  // EM
                //float dist2 = dist + 3000.;  d2xy += 20. / atom_masses[v] * vec2(dl) / dist * (`+mL2+`./dist2 - `+mL2+`./dist2*`+mL2+`./dist2);  // EM-shifted
                d2xy += 0.001 / atom_masses[v] * vec2(dl) / dist * (dist - `+mL2+`.);  // harmonic
                d2xy += 0.3 * atom_masses[nv] / (atom_masses[v] + atom_masses[nv]) * vec2(nxy.zw - xy.zw);  // inelastic collisions
              }
            }
            
            if(!bonded && b0count>=bn && CountArray17(nb)>=atom_bondnums[nv]) {
              d2xy += -10. / atom_masses[v] * vec2(dl) / dist * (`+mL+`./(`+mL+`. + dist));  // repulsion
            }
            
            // setting new bonds
            if(freebonds>0u) {
              b[freebonds] = n;
              bondenergy[freebonds] = atom_bondenergies[nv];
              freebonds --;
            }
            else {  // if all bonds are busy - choose most-energy-preferable bond
              uint min_o = 0u;  int min_e = 1000;
              for(uint o=1u; o<=bn; o++) {  // looking for minimum energy in existing bonds
                if(bondenergy[o] < min_e) {
                  min_e = bondenergy[o];
                  min_o = o;
                }
              }
              if(min_e<atom_bondenergies[nv]) {
                b[min_o] = n;
                bondenergy[min_o] = atom_bondenergies[nv];
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
          xy.x += xy.z;
          xy.y += xy.w;
          
          if(xy.x<-`+mR+`) xy.x = -`+mR+`;
          if(xy.y<-`+mR+`) xy.y = -`+mR+`;
          if(xy.x> `+mR+`) xy.x =  `+mR+`;
          if(xy.y> `+mR+`) xy.y =  `+mR+`;
          
          al = 1u;  // stay same by default
          
          if(trend0!=0u) {  // was trending at previous turn
            uvec4 acceptor = cells[trend0];
            if(ExtractAl(acceptor.a)==0u && ExtractAt(acceptor.a)==trend0) {  // neighbour empty cell accepted transfer from self to it
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
          if(trend0>0u) {  // accepting cell
            uint atrend = antitrends[trend0];
            uvec4 trender = cells[atrend];
            if(ExtractAl(trender.a)>0u && ExtractAt(trender.a)==trend0) {
              color.xy = XY4Trended(atrend, trender);  // @ motion skips a beat here
              al = 1u;
              v = ExtractAv(trender.a);
            }
          }
          else {  // looking for something to accept
            for(uint n=1u; n<`+RC+`u; n++) {
              if(ExtractAl(cells[n].a)==0u || ExtractAt(cells[n].a)==0u) continue;
              uint atrend = antitrends[n];
              if(ExtractAt(cells[n].a)!=atrend) continue;
              trend = atrend;
              break;  // first of trending nearby cells wins the space  // @ need to choose most far-went-away cell by distance
            }
          }
        }
        //  ////////////////////////////////////////////////////////////////
        
        color.b = PackB(b);
        
        color.a = PackA(al, v, trend, 0u, self.a, dbg);
        
        ` + fs_Prepare2Return('color') + `
      }
    }
  `;//console.log(CalcFragmentShaderSource);
  var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_rulestexture", "u_ps"]);
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