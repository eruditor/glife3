function ExtractRGBA(cell) {
  var s = '';
  
  var al   = (cell.a & 1);
  var fl   = (cell.a >>> 1) % 16;
  var el   = (cell.a >>> 5) % 8;
  var spin = (cell.a >>> 8) % 2;
  s += 'A(' + al + ', ' + fl + ', ' + el + ', ' + spin + ') ';
  
  s += 'B[' +
    ((cell.b >>> 0) % 4) + ' ' +
    ((cell.b >>> 2) % 4) + ' ' +
    ((cell.b >>> 4) % 4) + ' ' +
    ((cell.b >>> 6) % 4) + 
  '] ';
  
  var dir   = (cell.g >>> 0) % 8;
  var gate  = (cell.g >>> 3) % 8;
  var force = (cell.g >>> 6) % 8;
  var k     = (cell.g >>> 16);
  s += 'G{' + dir + ', ' + gate + ', ' + force + ', ' + k + '} ';
  
  s += 'R{' + 
    ((cell.r >>>  0) % 65536) + ' ' +
    ((cell.r >>> 16) % 65536) +
   '} ';
  
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
  
  uint ExtractEl(uvec4 cell) {  // electrons = 3 bit
    return (cell.a >> 5u) % 8u;
  }
  
  uint ExtractSpin(uvec4 cell) {  // spin = 1 bit
    return (cell.a >> 8u) % 2u;
  }
  
  uint PackA(uint al, uint fl, uint el, uint spin) {
    return (al    <<  0u) |
           (fl    <<  1u) |
           (el    <<  5u) |
           (spin  <<  8u);
  }
  
  uint ExtractDecay(uvec4 cell) { return 0u; }
  
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
  
  uint ExtractDir(uvec4 cell) {  // direction of movement = 3 bit
    return (cell.g >> 0u) % 8u;
  }
  
  uint ExtractGate(uvec4 cell) {  // gate = 3 bit
    return (cell.g >> 3u) % 8u;
  }
  
  uint ExtractForce(uvec4 cell) {  // force = 3 bit
    return (cell.g >> 6u) % 8u;
  }
  
  uint ExtractK(uvec4 cell) {  // kinetic energy = 16 bit
    return (cell.g >> 16u);
  }
  
  uint PackG(uint dir, uint gate, uint force, uint k) {
    return (dir   <<  0u) |
           (gate  <<  3u) |
           (force <<  6u) |
           (k     << 16u);
  }
  
  ////////////////////////////////////////////////////////////////
  
  uint[2] ExtractRF(uvec4 cell) {  // red field
    return uint[2](
      (cell.r >>  0u) % 65536u,
      (cell.r >> 16u) % 65536u
    );
  }
  
  uint PackR(uint[2] rF) {
    return (rF[0] <<  0u) |
           (rF[1] << 16u);
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
  
  uint BondPairEnergies(uint fl1, uint fl2) {
    if(fl1>fl2) { uint fl0 = fl1;  fl1 = fl2;  fl2 = fl0; }
    int e = 0;
         if(fl1==1u && fl2==1u) e = 435;  // H-H
    else if(fl1==1u && fl2==2u) e = 464;  // O-H
    else if(fl1==1u && fl2==3u) e = 413;  // C-H
    else if(fl1==2u && fl2==2u) e = 138;  // O-O
    else if(fl1==2u && fl2==3u) e = 335;  // C-O
    else if(fl1==3u && fl2==3u) e = 347;  // C-C
    // N-H 389
    // O-N 210
    // C-N 293
    // N-N 159
    return uint(e);
  }
  
  uint revers[5] = uint[5](0u, 3u, 4u, 1u, 2u);
  
  uint[4] array4_sum(uint[4] ar1, uint[4] ar2) {
    return uint[4](ar1[0]+ar2[0], ar1[1]+ar2[1], ar1[2]+ar2[2], ar1[3]+ar2[3]);
  }
  
  uint[4] array4_div(uint[4] ar, uint d) {
    return uint[4](ar[0]/d, ar[1]/d, ar[2]/d, ar[3]/d);
  }
  
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
      uint    el0    = ExtractEl(   cells[0]),  el    = el0;
      uint    spin0  = ExtractSpin( cells[0]),  spin  = spin0;
      uint[5] bonds0 = ExtractBonds(cells[0]),  bonds = bonds0;
      uint    dir0   = ExtractDir(  cells[0]),  dir   = dir0;
      uint    gate0  = ExtractGate( cells[0]),  gate  = gate0;
      uint    force0 = ExtractForce(cells[0]),  force = force0;
      uint    k0     = ExtractK(    cells[0]),  k     = k0;
      uint[2] rF0    = ExtractRF(   cells[0]),  rF    = rF0;
      
      uint stage = u_nturn % 7u;
      
      // bonds allocation ////////////////////////////////////////////////////////////////
      if(stage==0u) {
        if(al0>0u) {
          bonds = uint[5](0u, 0u, 0u, 0u, 0u);
          uint[5] attracts = uint[5](0u, 0u, 0u, 0u, 0u);
          uint freebonds = atom_bondnums[fl0];
          for(uint n=1u; n<`+RC+`u; n++) {
            if(freebonds>0u) {
              bonds[n] = 1u;
              freebonds --;
            }
          }
        }
      }
      // eLectrons ////////////////////////////////////////////////////////////////
      else if(stage==1u) {
        el = 0u;
        if(al0==0u) {
          spin = 0u;
          for(uint n=1u; n<`+RC+`u; n++) {
            if(ExtractAl(cells[n])>0u && ExtractBonds(cells[n])[revers[n]]>0u) {  // bonded to us
              el ++;
              spin = ExtractSpin(cells[n]);
            }
          }
        }
      }
      // red field ////////////////////////////////////////////////////////////////
      else if(stage==2u) {
        rF = uint[2](0u, 0u);
        
        if(el0==1u) {
          rF[spin0] = 100u * 5u;
        }
        else if(el0==2u) {
          rF[0] = 100u * 5u;
          rF[1] = 100u * 5u;
        }
        
        for(uint n=1u; n<`+RC+`u; n++) {
          uint[2] nr = ExtractRF(cells[n]);
          rF[0] += nr[0];
          rF[1] += nr[1];
        }
        rF[0] /= 5u;
        rF[1] /= 5u;
      }
      // electron forces ////////////////////////////////////////////////////////////////
      else if(stage==3u) {
        force = 0u;
        if(el0==1u) {
          uint aspin = (spin0 + 1u) % 2u;  // anti-spin
          uint maxr = 0u, maxn = 0u;
          for(uint n=0u; n<`+RC+`u; n++) {
            uint r = ExtractRF(cells[n])[aspin];
            if(r>maxr) { maxr = r;  maxn = n; }
          }
          force = maxn;
          k = maxr;
        }
        else if(el0==2u) {
          force = 0u;
          k = 100u;
        }
      }
      // opening gates ////////////////////////////////////////////////////////////////
      else if(stage==4u) {
        if(al0>0u) {
          uint maxek = 0u, maxn = 0u;
          for(uint n=1u; n<`+RC+`u; n++) {
            uint eforce = 0u, ek = 0u;
            if(bonds0[n]>0u && ExtractEl(cells[n])>0u) {
              eforce = ExtractForce(cells[n]);
              ek = ExtractK(cells[n]);
            }
            if(n==dir0) { eforce = dir0;  ek += k0; }
            if(ek>maxek) { maxek = ek;  maxn = eforce; }
          }
          gate = maxn;
        }
      }
      // accepting gates ////////////////////////////////////////////////////////////////
      else if(stage==5u) {
        if(al0==0u) {
          int maxprio = 0, curprio = 0;
          for(uint n=1u; n<`+RC+`u; n++) {
            if(ExtractAl(cells[n])==0u) continue;
            if(ExtractGate(cells[n])==revers[n]) {  // neib gate to us
              curprio = 10;
              if(curprio>maxprio) {
                maxprio = curprio;
                gate = n;
              }
            }
          }
        }
      }
      // movement transfer ////////////////////////////////////////////////////////////////
      else if(stage==6u) {
        if(gate0>0u) {  // was open at previous turn
          if(al0>0u) {  // alive cell
            uvec4 mate = cells[gate0];
            if(ExtractGate(mate)==revers[gate0]) {  // sluice open
              if(ExtractAl(mate)==0u) {  // mate is empty => transfering whole cell
                al = 0u;
                bonds = uint[5](0u, 0u, 0u, 0u, 0u);
              }
            }
          }
          else {  // dead cell
            uvec4 mate = cells[gate0];
            if(ExtractGate(mate)==revers[gate0]) {
              if(ExtractAl(mate)>0u) {
                al = 1u;
                fl = ExtractFl(mate);
                dir = ExtractDir(mate);
                k = ExtractK(mate);
              }
            }
          }
        }
        gate = 0u;
      }
      // --- ////////////////////////////////////////////////////////////////
      
      // rgba packing ////////////////////////////////////////////////////////////////
      
      uvec4 color = uvec4(0);
      color.a = PackA(al, fl, el, spin);
      color.b = PackB(bonds);
      color.g = PackG(dir, gate, force, k);
      color.r = PackR(rF);
      
      ` + fs_Prepare2Return('color') + `
    }
  }
`;//console.log(CalcFragmentShaderSource);
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_rulestexture", "u_nturn"]);
