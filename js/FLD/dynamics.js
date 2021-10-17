function ExtractRGBA(cell) {
  var s = '';
  
  var al   = (cell.a & 1);
  var fl   = (cell.a >>> 1) % 16;
  var dir  = (cell.a >>> 5) % 8;
  var gate = (cell.a >>> 8) % 8;
  var k    = (cell.a >>> 16);
  
  s += 'A(' + al + ', ' + fl + ', ' + dir + ', ' + gate + ', ' + k + ') ';
  
  s += 'B[' +
    ((cell.b >>> 0) % 4) + ' ' +
    ((cell.b >>> 2) % 4) + ' ' +
    ((cell.b >>> 4) % 4) + ' ' +
    ((cell.b >>> 6) % 4) + ', ' +
    ((cell.b >>> 8) % 4) +
  '] ';
  
  s += 'G{' + cell.g + '} ';
  
  s += 'R{' + 
    ((cell.r >>>  0) % 256) + ' ' +
    ((cell.r >>>  8) % 256) + ' ' +
    ((cell.r >>> 16) % 256) + ' ' +
    ((cell.r >>> 24) % 256) +
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
  
  uint ExtractDir(uvec4 cell) {  // direction of movement = 3 bit
    return (cell.a >> 5u) % 8u;
  }
  
  uint ExtractGate(uvec4 cell) {  // gate = 3 bit
    return (cell.a >> 8u) % 8u;
  }
  
  uint ExtractK(uvec4 cell) {  // kinetic energy = 16 bit
    return (cell.a >> 16u);
  }
  
  uint PackA(uint al, uint fl, uint dir, uint gate, uint k) {
    return (al    <<  0u) |
           (fl    <<  1u) |
           (dir   <<  5u) |
           (gate  <<  8u) |
           (k     << 16u);
  }
  
  uint ExtractDecay(uvec4 cell) {
    return 0u;
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
  
  uint ExtractL(uvec4 cell) {  // eLectron
    return (cell.b >> 8u) % 4u;
  }
  
  uint PackB(uint[5] bonds, uint L) {
    return (bonds[1] << 0u) |
           (bonds[2] << 2u) |
           (bonds[3] << 4u) |
           (bonds[4] << 6u) |
           (L        << 8u);
  }
  
  ////////////////////////////////////////////////////////////////
  
  uint[4] ExtractRF(uvec4 cell) {  // red field
    return uint[4](
      (cell.r >>  0u) % 256u,
      (cell.r >>  8u) % 256u,
      (cell.r >> 16u) % 256u,
      (cell.r >> 24u) % 256u
    );
  }
  
  uint PackR(uint[4] rF) {
    return (rF[0] <<  0u) |
           (rF[1] <<  8u) |
           (rF[2] << 16u) |
           (rF[3] << 24u);
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
      uint    dir0   = ExtractDir(  cells[0]),  dir   = dir0;
      uint    gate0  = ExtractGate( cells[0]),  gate  = gate0;
      uint    k0     = ExtractK(    cells[0]),  k     = k0;
      uint[5] bonds0 = ExtractBonds(cells[0]),  bonds = bonds0;
      uint    L0     = ExtractL(    cells[0]),  L     = L0;
      uint[4] rF0    = ExtractRF(   cells[0]),  rF    = rF0;
      
      uint stage = u_nturn % 6u;
      
      // bonds allocation ////////////////////////////////////////////////////////////////
      if(stage==0u) {
        if(al0>0u) {
          bonds = uint[5](0u, 0u, 0u, 0u, 0u);
          uint[5] attracts = uint[5](0u, 0u, 0u, 0u, 0u);
          uint freebonds = atom_bondnums[fl0];
          for(uint n=1u; n<`+RC+`u; n++) {
            uint[4] nr = ExtractRF(cells[n]);
            uint r = nr[revers[n]-1u];
            
            if(freebonds>0u) {
              bonds[n] = 1u;  attracts[n] = r;
              freebonds --;
            }
            else {  // if all bonds are busy - choose most-attracting bond
              uint min_n = 0u, min_r = 1000u;
              for(uint nn=1u; nn<n; nn++) {  // looking for minimum attract in existing bonds
                if(bonds[nn]==0u) continue;
                if(attracts[nn] <= min_r) {
                  min_r = attracts[nn];
                  min_n = nn;
                }
              }
              if(min_r < r) {
                bonds[min_n] = 0u;  attracts[min_n] = 0u;
                bonds[n]     = 1u;  attracts[n]     = r;
              }
            }
          }
        }
      }
      // eLectrons ////////////////////////////////////////////////////////////////
      else if(stage==1u) {
        L = 0u;
        if(true) {
          for(uint n=1u; n<`+RC+`u; n++) {
            if(ExtractAl(cells[n])>0u && ExtractBonds(cells[n])[revers[n]]>0u) {  // bonded to us
              L ++;
            }
          }
        }
      }
      // red field ////////////////////////////////////////////////////////////////
      else if(stage==2u) {
        rF = uint[4](0u, 0u, 0u, 0u);
        for(uint n=1u; n<`+RC+`u; n++) {
          if(ExtractL(cells[n])>0u) {  // electron
            if(al0==0u) {  // @ check bonds
              rF[revers[n]-1u] = 128u * 4u;
            }
          }
          else {
            uint[4] nr = ExtractRF(cells[n]);
            nr[n-1u] = 0u;  // !
            rF = array4_sum(rF, nr);
          }
        }
        rF = array4_div(rF, 4u);
      }
      // opening gates ////////////////////////////////////////////////////////////////
      else if(stage==3u) {
        if(al0>0u) {
          uint maxr = 0u, maxn = 0u;
          for(uint n=0u; n<`+RC+`u; n++) {
            uint[4] nr = ExtractRF(cells[n]);
            uint r = nr[revers[n]-1u];  // @ ! fix negative !
            if(n==dir0) r += k0;
            if(r>maxr) { maxr = r;  maxn = n; }
          }
          gate = maxn;
        }
      }
      // accepting gates ////////////////////////////////////////////////////////////////
      else if(stage==4u) {
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
      else if(stage==5u) {
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
      color.a = PackA(al, fl, dir, gate, k);
      color.b = PackB(bonds, L);
      //color.g = PackG(gF);
      color.r = PackR(rF);
      
      ` + fs_Prepare2Return('color') + `
    }
  }
`;//console.log(CalcFragmentShaderSource);
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_rulestexture", "u_nturn"]);
