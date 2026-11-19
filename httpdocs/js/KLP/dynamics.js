var fs_ExtractRGBA = ``;

var CalcFragmentShaderSource = `
  precision highp float;
  precision highp int;

  uniform `+field_Sampler+` u_fieldtexture;
  uniform highp uint u_nturn;
  uniform float u_dt;
  uniform float u_focus;
  uniform float u_maxMove;
  uniform float u_density[`+FD+`];
  uniform float u_repulsion[`+FD+`];
  uniform float u_bonding[`+FD+`];
  uniform float u_adhesion;

  in vec2 v_texcoord;
  out `+field_Vec4P+` glFragColor[`+FD+`];

  ivec3 tex3coord;
  ivec3 fieldSize;

  `+fs_ModuloTorus+`
  `+fs_GetCell()+`

  vec4 Interacted() {
    vec4 source = GetCell(0,0,0);
    float mass = source.r;
    if(mass<=0.) return source;

    float kinetic0 = 0.5*dot(source.gb,source.gb)/mass;
    vec2 velocity = source.gb/mass;
    float repulsion = u_repulsion[tex3coord.z];
    vec2 impulse = vec2(0);

    if(repulsion>0.) {
      for(int iy=0; iy<3; iy++) {
        for(int ix=0; ix<3; ix++) {
          int nx = ix-1;
          int ny = iy-1;
          if(nx==0 && ny==0) continue;

          vec4 other = GetCell(nx,ny,0);
          float otherMass = other.r;
          if(otherMass<=0.) continue;

          float totalMass = mass+otherMass;
          float excess = max(totalMass-u_density[tex3coord.z],0.);
          if(excess<=0.) continue;

          float reducedMass = mass*otherMass/totalMass;
          vec2 offset = vec2(nx,ny);
          vec2 direction = offset/length(offset);
          float approach =
            dot(other.gb/otherMass-velocity,direction);
          if(approach>=0.) continue;

          float response = clamp(
            u_dt*repulsion*excess/u_density[tex3coord.z],
            0., 1.
          );

          // A partial elastic collision: response=1 reflects the normal
          // relative velocity, while smaller values dissipate part of it.
          float collisionImpulse =
            -2.*response*reducedMass*approach;
          impulse -= collisionImpulse*direction;
        }
      }
    }

    for(int otherLayer=0; otherLayer<`+FD+`; otherLayer++) {
      if(otherLayer==tex3coord.z) continue;
      float bonding = u_bonding[tex3coord.z]*u_bonding[otherLayer];
      if(bonding<=0.) continue;

      vec4 other = GetCell(0,0,otherLayer-tex3coord.z);
      float otherMass = other.r;
      if(otherMass<=0.) continue;

      float reducedMass = mass*otherMass/(mass+otherMass);
      impulse += u_adhesion*bonding*reducedMass
        * (other.gb/otherMass-velocity);
    }

    source.gb += impulse;
    source.a += kinetic0-0.5*dot(source.gb,source.gb)/mass;
    return source;
  }

  vec4 Stream(vec4 source, vec2 target, out float kinetic) {
    kinetic = 0.;
    float mass = source.r;
    if(mass<=0.) return vec4(0);

    vec2 velocity = source.gb/mass;
    vec2 move = clamp(u_dt*velocity, vec2(-u_maxMove), vec2(u_maxMove));
    vec2 overlap = max(vec2(0), 1.-abs(target-move));
    float weight = overlap.x*overlap.y;
    if(weight<=0.) return vec4(0);

    // Correct the snapping error over roughly one cell-crossing time.
    // The correction velocity therefore stays comparable to velocity.
    float correction = min(
      u_focus*max(abs(move.x),abs(move.y)),
      1.
    );
    vec2 correctedVelocity =
      velocity-correction*(target-move)/u_dt;

    // Keep total energy as well as momentum.  The correction creates a
    // velocity variance.  It is paid for by the cell's internal energy.
    vec2 fraction = fract(abs(move));
    vec2 variance1 = fraction*(1.-fraction);
    float meanKinetic = 0.5*(
      dot(velocity,velocity)
      + correction*correction
        *(variance1.x+variance1.y)/(u_dt*u_dt)
    );
    float excess = source.a/mass
      + 0.5*dot(velocity,velocity)-meanKinetic;
    float streamMass = weight*mass;
    kinetic = 0.5*streamMass
      * dot(correctedVelocity,correctedVelocity);

    return vec4(
      streamMass,
      streamMass*correctedVelocity,
      streamMass*excess
    );
  }

  vec4 Advected() {
    vec4 result = vec4(0);
    float kineticSum = 0.;
    for(int iy=0; iy<5; iy++) {
      for(int ix=0; ix<5; ix++) {
        int sx = ix-2;
        int sy = iy-2;
        vec4 source = GetCell(sx,sy,0);
        float kinetic;
        result += Stream(source,-vec2(sx,sy),kinetic);
        kineticSum += kinetic;
      }
    }
    if(result.r>0.) {
      result.a += kineticSum
        - 0.5*dot(result.gb,result.gb)/result.r;
    }
    return result;
  }

  void main() {
    fieldSize = textureSize(u_fieldtexture,0);
    for(int layer=0; layer<`+FD+`; layer++) {
      tex3coord = ivec3(v_texcoord,layer);
      vec4 result;
      if(u_nturn%2u==0u) result = Interacted();
      else               result = Advected();
      `+fs_Prepare2Return('result')+`
    }
  }
`;

var CalcProgram = createProgram4Frag(
  gl,
  CalcFragmentShaderSource,
  [
    "a_position", "u_fieldtexture", "u_nturn",
    "u_dt", "u_focus", "u_maxMove",
    "u_density", "u_repulsion", "u_bonding", "u_adhesion"
  ]
);
