var fs_ExtractRGBA = ``;

var QMFPhysics = Object.freeze({
  masses: [1, 16, 12],  // H, O, C
  bondRange: 1.60,
  bondLength: 0.90,
  spring: 0.035,
  repulsionRange: 0.68,
  repulsion: 0.055
});

// Minimal particle chemistry.
// layer 0: one-hot atom type (r=H, g=O, b=C), a=bond mask
// layer 1: xy = sub-cell position, zw = velocity
function CalcFragmentShaderSource() {
  var extraOutputs = '';
  for(var z=2; z<FD; z++) extraOutputs += `    glFragColor[`+z+`] = vec4(0.);\n`;

  return `
  precision highp float;
  precision highp int;

  uniform `+field_Sampler+` u_fieldtexture;
  uniform highp uint u_nturn;

  in vec2 v_texcoord;

  out `+field_Vec4P+` glFragColor[`+FD+`];

  ivec3 tex3coord;
  ivec3 fieldSize;

  `+fs_ModuloTorus+`
  `+fs_GetCell()+`

  const float BOND_RANGE = `+QMFPhysics.bondRange+`;
  const float BOND_LENGTH = `+QMFPhysics.bondLength+`;
  const float SPRING = `+QMFPhysics.spring+`;
  const float REPULSION_RANGE = `+QMFPhysics.repulsionRange+`;
  const float REPULSION = `+QMFPhysics.repulsion+`;

  bool HasAtom(vec4 atom) {
    return atom.r + atom.g + atom.b > 0.5;
  }

  int AtomType(vec4 atom) {
    if(atom.r>0.5) return 1;  // H
    if(atom.g>0.5) return 2;  // O
    if(atom.b>0.5) return 3;  // C
    return 0;
  }

  int Valence(int atomType) {
    if(atomType==1) return 1;
    if(atomType==2) return 2;
    if(atomType==3) return 4;
    return 0;
  }

  float AtomMass(int atomType) {
    if(atomType==1) return `+QMFPhysics.masses[0]+`.;
    if(atomType==2) return `+QMFPhysics.masses[1]+`.;
    if(atomType==3) return `+QMFPhysics.masses[2]+`.;
    return 1.;
  }

  int BondMask(vec4 atom) {
    return int(floor(atom.a + 0.5));
  }

  vec2 AtomPosition(ivec2 offset) {
    return vec2(offset) + GetCell(offset.x, offset.y, 1).xy;
  }

  // A bond exists only when both atoms rank each other within their valence.
  // Recomputing the graph permits partner exchange without molecule IDs.
  bool IsBonded(ivec2 a, ivec2 b) {
    vec4 atomA = GetCell(a.x, a.y, 0);
    vec4 atomB = GetCell(b.x, b.y, 0);
    if(!HasAtom(atomA) || !HasAtom(atomB)) return false;

    float targetDistance = length(AtomPosition(b) - AtomPosition(a));
    if(targetDistance>BOND_RANGE) return false;

    int rankA = 0;
    int rankB = 0;
    for(int dy=-1; dy<=1; dy++) {
      for(int dx=-1; dx<=1; dx++) {
        if(dx==0 && dy==0) continue;

        ivec2 candidateA = a + ivec2(dx, dy);
        vec4 otherA = GetCell(candidateA.x, candidateA.y, 0);
        if(HasAtom(otherA)) {
          float distanceA = length(AtomPosition(candidateA) - AtomPosition(a));
          if(distanceA<BOND_RANGE && distanceA<targetDistance-0.0001) rankA++;
        }

        ivec2 candidateB = b + ivec2(dx, dy);
        vec4 otherB = GetCell(candidateB.x, candidateB.y, 0);
        if(HasAtom(otherB)) {
          float distanceB = length(AtomPosition(candidateB) - AtomPosition(b));
          if(distanceB<BOND_RANGE && distanceB<targetDistance-0.0001) rankB++;
        }
      }
    }

    return rankA<Valence(AtomType(atomA)) && rankB<Valence(AtomType(atomB));
  }

  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    tex3coord = ivec3(v_texcoord, 0);

    vec4 atom = GetCell(0, 0, 0);
    vec4 motion = GetCell(0, 0, 1);
    uint stage = u_nturn % 2u;

    if(stage==1u) {
      // Build the local bond graph and apply pairwise spring forces.
      if(HasAtom(atom)) {
        vec2 force = vec2(0.);
        int bonds = 0;
        int bitIndex = 0;

        for(int dy=-1; dy<=1; dy++) {
          for(int dx=-1; dx<=1; dx++) {
            if(dx==0 && dy==0) {
              bitIndex++;
              continue;
            }

            ivec2 offset = ivec2(dx, dy);
            vec4 other = GetCell(dx, dy, 0);
            if(HasAtom(other)) {
              vec2 delta = AtomPosition(offset) - motion.xy;
              float distance = length(delta);
              if(distance>0.0001) {
                vec2 direction = delta / distance;
                if(IsBonded(ivec2(0), offset)) {
                  float stretch = (distance - BOND_LENGTH) / (BOND_RANGE - BOND_LENGTH);
                  force += SPRING * (distance - BOND_LENGTH) * (1. - stretch) * direction;
                  bonds |= 1 << bitIndex;
                }
                else if(distance<REPULSION_RANGE) {
                  force -= REPULSION * (REPULSION_RANGE - distance) * direction;
                }
              }
            }
            bitIndex++;
          }
        }

        motion.zw += force / AtomMass(AtomType(atom));
        atom.a = float(bonds);
      }
    }
    else {
      // Advance physical positions, then only reassign their storage cells.
      int shift = int((u_nturn / 2u) % 2u);
      ivec2 local = ivec2(
        (tex3coord.x - shift + fieldSize.x) % 2,
        (tex3coord.y - shift + fieldSize.y) % 2
      );

      vec4 atoms[4], motions[4];
      vec2 positions[4], velocities[4];
      ivec2 sources[4], targets[4];
      bool used[4];

      for(int i=0; i<4; i++) {
        sources[i] = ivec2(i%2, i/2);
        ivec2 offset = sources[i] - local;
        atoms[i] = GetCell(offset.x, offset.y, 0);
        motions[i] = GetCell(offset.x, offset.y, 1);
        positions[i] = vec2(sources[i]) + motions[i].xy;
        velocities[i] = motions[i].zw;
        targets[i] = sources[i];
        used[i] = false;
        if(HasAtom(atoms[i])) positions[i] += velocities[i];
      }

      for(int i=0; i<4; i++) {
        if(!HasAtom(atoms[i])) continue;
        float bestCost = 1e30;
        int best = 0;
        for(int j=0; j<4; j++) {
          if(!used[j]) {
            vec2 delta = positions[i] - vec2(j%2, j/2);
            float cost = dot(delta, delta);
            if(cost<bestCost) {
              bestCost = cost;
              best = j;
            }
          }
        }
        used[best] = true;
        targets[i] = ivec2(best%2, best/2);
      }

      atom = vec4(0.);
      motion = vec4(0.);
      for(int i=0; i<4; i++) {
        if(HasAtom(atoms[i]) && all(equal(targets[i], local))) {
          atom = atoms[i];
          atom.a = float(BondMask(atom));
          motion = vec4(positions[i] - vec2(local), velocities[i]);
        }
      }
    }

    glFragColor[0] = atom;
    glFragColor[1] = motion;
`+extraOutputs+`
  }
  `;
}

var CalcProgram = createProgram4Frag(
  gl,
  CalcFragmentShaderSource(),
  ["a_position", "u_fieldtexture", "u_nturn"]
);
