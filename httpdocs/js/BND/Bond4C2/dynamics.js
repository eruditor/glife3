function ExtractRGBA(cell) {
  var s = '';
  
  var al    = (cell.a & 1);
  var fl    = (cell.a >>> 1) % 16;
  var decay = (cell.a >>> 5);
  s += 'A(' + al + ', ' + fl + ', ' + decay + ') ';
  
  var gate  = (cell.g >>> 0) % 8;
  var gone  = (cell.g >>> 3) % 8;
  var gpas  = (cell.g >>> 6) % 2;
  s += 'G{' + gate + ', ' + gone + ', ' + gpas + '} ';
  
  s += 'B[' +
    ((cell.b >>> 0) % 4) + ' ' +
    ((cell.b >>> 2) % 4) + ' ' +
    ((cell.b >>> 4) % 4) + ' ' +
    ((cell.b >>> 6) % 4) +
  '] ';
  
  var speed = (cell.r >>> 0) % 8;
  var strid = (cell.r >>> 3);  //  % 8
  s += 'S(' + speed + ', ' + strid + ') ';
  
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
  
  uint ExtractGone(uvec4 cell) {
    return (cell.g >> 3u) % 8u;
  }
  
  uint ExtractGpas(uvec4 cell) {
    return (cell.g >> 6u) % 2u;
  }
  
  uint PackG(uint gate, uint gone, uint gpas) {
    return (gate << 0u) |
           (gone << 3u) |
           (gpas << 6u);
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
  
  uniform `+field_Sampler+` u_fieldtexture;
  uniform highp usampler2D u_rulestexture[`+FD+`];
  uniform highp uint u_nturn;  // nturn
  
  in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
  
  out `+field_Vec4P+` glFragColor[`+FD+`];
  
  ivec3 tex3coord;
  ivec3 fieldSize;
  
  ` + fs_ModuloTorus + `
  ` + fs_GetCell() + `
  ` + fs_GetTexel2D + `
  ` + fs_ExtractRGBA + `
  
  float atom_masses[4]   = float[4](0., 1., 2., 3.);
  uint atom_bondnums[4]   = uint[4](0u, 1u, 2u, 4u);  // number of covalent bonds
  int atom_bondenergies[4] = int[4]( 0, 20, 25, 15);
  
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
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    
    for(int layer=0; layer<`+FD+`; layer++) {
      tex3coord = ivec3(v_texcoord, layer);
      
      // getting cell's neighborhood
      
      ` + fs_GetNeibs + `
      
      // extracting self-cell info
      
      uint    al0    = ExtractAl(   cells[0]),  al    = al0;
      uint    fl0    = ExtractFl(   cells[0]),  fl    = fl0;
      uint    decay0 = ExtractDecay(cells[0]),  decay = 0u;
      uint    gate0  = ExtractGate( cells[0]),  gate  = 0u;
      uint    gone0  = ExtractGone( cells[0]),  gone  = gone0;
      uint    gpas0  = ExtractGpas( cells[0]),  gpas  = gpas0;  // passive gate flag
      uint    speed0 = ExtractSpeed(cells[0]),  speed = speed0;
      uint    strid0 = ExtractStrid(cells[0]),  strid = strid0;
      uint[5] bonds0 = ExtractBonds(cells[0]),  bonds = bonds0;
      
      uint stage = u_nturn % 3u;
      
      // opening bonds and gates ////////////////////////////////////////////////////////////////
      if(stage==0u) {
        bonds = uint[5](0u, 0u, 0u, 0u, 0u);
        gpas = 0u;
        
        if(al0>0u) {
          // opening new bond windows
          uint[5] bondenergy = uint[5](0u, 0u, 0u, 0u, 0u);
          uint freebonds = atom_bondnums[fl];
          for(uint n=1u; n<`+RC+`u; n++) {
            if(ExtractAl(cells[n])>0u) {
              uint e = BondPairEnergies(fl0, ExtractFl(cells[n]));
              
              if(freebonds>0u) {
                bonds[n] = 1u;  bondenergy[n] = e;
                freebonds --;
              }
              else {  // if all bonds are busy - choose most-energy-preferable bond
                uint min_n = 0u, min_e = 1000u;
                for(uint nn=1u; nn<n; nn++) {  // looking for minimum energy in existing bonds
                  if(bonds[nn]==0u) continue;
                  if(bondenergy[nn] <= min_e) {
                    min_e = bondenergy[nn];
                    min_n = nn;
                  }
                }
                if(min_e < e) {
                  bonds[min_n] = 0u;  bondenergy[min_n] = 0u;
                  bonds[n]     = 1u;  bondenergy[n]     = e;
                }
              }
            }
          }
          
          // opening new gates
          if(strid0>0u) {
            gate = strid0==7u ? 0u : strid0;
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
                gpas = 1u;
                break;  // @ todo: accept not first but most priority
              }
            }
          }
        }
      }
      // gate and bond accepting ////////////////////////////////////////////////////////////////
      else if(stage==1u) {
        strid = 0u;
        
        // gate accepting
        if(al0==0u) {
          int maxprio = 0, curprio = 0;
          for(uint n=1u; n<`+RC+`u; n++) {
            if(ExtractAl(cells[n])==0u) continue;
            if(ExtractGate(cells[n])==revers[n]) {  // neib gate to us
              uint nspeed = ExtractSpeed(cells[n]);
              
              if(nspeed==revers[n]) curprio = 40;  // neib speed to us
              else if(nspeed==n)    curprio = 10;  // neib speed away from us
              else if(nspeed!=0u)   curprio = 20;  // neib moves perpendicular
              else                  curprio = 30;  // neib stands
              
              if(curprio>maxprio) {
                maxprio = curprio;
                gate = n;  gpas = 1u;
              }
            }
          }
        }
        else {
          gate = gate0;
          
          if(strid0==0u) {  // strid movement may occur once per 2 stages
            int maxprio = 0;
            for(uint n=1u; n<`+RC+`u; n++) {
              if(ExtractAl(cells[n])==0u) continue;
              
              // bond pairing status
              if(bonds0[n]>0u) {
                bonds[n] = ExtractBonds(cells[n])[revers[n]]>0u ? 2u : 1u;
              }
              
              int curprio = 0;  uint curstrid = 0u;
              if(bonds[n]>=2u) {
                uint ngate = ExtractGate(cells[n]);
                if(ExtractGpas(cells[n])>0u) ngate = 0u;  // passive gate
                if(ngate==0u && speed0==0u) continue;  // not forcing nonmoving cells to stand
                
                if(ngate>0u) curprio = 20;
                else if(ExtractStrid(cells[n])==0u) curprio = 10;
                else curprio = 0;  // not inheriting strid=0
                
                curstrid = ngate==0u ? 7u : ngate;
              }
              else {
                curprio = 1;
                curstrid = revers[n];  // slight repulsion for non-bonded neibs
              }
              
              if(curprio>maxprio) {
                maxprio = curprio;
                strid = curstrid;
              }
            }
          }
        }
      }
      // movement, sluice transfer ////////////////////////////////////////////////////////////////
      else if(stage==2u) {
        if(gate0>0u) {  // was open at previous turn
          if(al0>0u) {  // alive cell
            uvec4 mate = cells[gate0];
            if(ExtractGate(mate)==revers[gate0]) {  // sluice open
              if(ExtractAl(mate)==0u) {  // mate is empty => transfering whole cell
                al = 0u;
                speed = speed0;
                strid = 0u;
              }
              else {  // mate is alive => exchanging momentum
                speed = ExtractSpeed(mate);
                strid = 0u;
              }
            }
          }
          else {  // dead cell
            uvec4 mate = cells[gate0];
            if(ExtractGate(mate)==revers[gate0]) {
              if(ExtractAl(mate)>0u) {
                al = 1u;
                fl = ExtractFl(mate);
                speed = ExtractSpeed(mate);
                strid = ExtractStrid(mate);
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
        gpas = 0u;
        speed = 0u;
        strid = 0u;
        bonds = uint[5](0u, 0u, 0u, 0u, 0u);
      }
      
      // rgba packing ////////////////////////////////////////////////////////////////
      
      `+field_Vec4P+` color = uvec4(0);
      color.a = PackA(al, fl, decay);
      color.b = PackB(bonds);
      color.g = PackG(gate, gone, gpas);
      color.r = PackR(speed, strid);
      
      ` + fs_Prepare2Return('color') + `
    }
  }
`;//console.log(CalcFragmentShaderSource);
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_rulestexture", "u_nturn"]);
