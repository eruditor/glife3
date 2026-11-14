var fs_ExtractRGBA = ``;

var CalcFragmentShaderSource = `
  precision highp float;
  precision highp int;

  uniform `+field_Sampler+` u_fieldtexture;
  uniform highp uint u_nturn;
  uniform float u_dt;
  uniform float u_focus;
  uniform float u_maxMove;

  in vec2 v_texcoord;
  out `+field_Vec4P+` glFragColor[`+FD+`];

  ivec3 tex3coord;
  ivec3 fieldSize;

  `+fs_ModuloTorus+`
  `+fs_GetCell()+`

  vec4 Stream(vec4 source, vec2 target) {
    float mass = source.r;
    if(mass<=0.) return vec4(0);

    vec2 velocity = source.gb/mass;
    vec2 move = clamp(u_dt*velocity, vec2(-u_maxMove), vec2(u_maxMove));
    vec2 overlap = max(vec2(0), 1.-abs(target-move));
    float weight = overlap.x*overlap.y;
    if(weight<=0.) return vec4(0);

    // target-move is the error introduced by snapping this part of the
    // translated unit square to a cell centre.  Its area-weighted mean is
    // exactly zero, so this correction cannot change total momentum.
    vec2 correctedVelocity =
      velocity-u_focus*(target-move)/u_dt;

    // Keep total energy as well as momentum.  The correction creates a
    // velocity variance.  It is paid for by the cell's internal energy.
    vec2 fraction = fract(abs(move));
    vec2 variance1 = fraction*(1.-fraction);
    float meanKinetic = 0.5*(
      dot(velocity,velocity)
      + u_focus*u_focus*(variance1.x+variance1.y)/(u_dt*u_dt)
    );
    float internal = source.a/mass-meanKinetic;
    float streamMass = weight*mass;
    float streamEnergy = streamMass*(
      internal+0.5*dot(correctedVelocity,correctedVelocity)
    );

    return vec4(
      streamMass,
      streamMass*correctedVelocity,
      streamEnergy
    );
  }

  void main() {
    fieldSize = textureSize(u_fieldtexture,0);
    tex3coord = ivec3(v_texcoord,0);

    vec4 result = vec4(0);
    for(int iy=0; iy<5; iy++) {
      for(int ix=0; ix<5; ix++) {
        int sx = ix-2;
        int sy = iy-2;
        vec4 source = GetCell(sx,sy,0);
        result += Stream(source,-vec2(sx,sy));
      }
    }
    glFragColor[0] = result;
  }
`;

var CalcProgram = createProgram4Frag(
  gl,
  CalcFragmentShaderSource,
  [
    "a_position", "u_fieldtexture", "u_nturn",
    "u_dt", "u_focus", "u_maxMove"
  ]
);
