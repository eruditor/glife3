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
      
      `+field_Vec4P+` color = `+field_Vec4+`(0);
      color.a = PackA(al, fl, decay);
      color.b = PackB(bonds);
      color.g = PackG(gate, gone);
      color.r = PackR(speed, strid);
      
      ` + fs_Prepare2Return('color') + `
    }
  }
`;//console.log(CalcFragmentShaderSource);
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_rulestexture", "u_nturn"]);
