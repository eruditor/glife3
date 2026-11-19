var fs_ExtractRGBA = ``;

var CalcFragmentShaderSource = `
  precision highp float;
  precision highp int;

  uniform `+field_Sampler+` u_fieldtexture;
  uniform float u_dt;

  in vec2 v_texcoord;
  out `+field_Vec4P+` glFragColor[`+FD+`];

  ivec3 tex3coord;
  ivec3 fieldSize;

  `+fs_ModuloTorus+`
  `+fs_GetCell()+`

  void AddSource(
    int sx, int sy,
    inout float mass,
    inout vec2 moment,
    inout vec2 momentum,
    inout float totalEnergy
  ) {
    vec4 matter = GetCell(sx,sy,-tex3coord.z);
    if(matter.r<=0.) return;
    vec4 motion = GetCell(sx,sy,1-tex3coord.z);

    vec2 center = clamp(motion.xy,vec2(0),vec2(1));
    vec2 rectMin = vec2(
      center.x<=.5 ? 0. : 2.*center.x-1.,
      center.y<=.5 ? 0. : 2.*center.y-1.
    );
    vec2 rectMax = vec2(
      center.x<=.5 ? 2.*center.x : 1.,
      center.y<=.5 ? 2.*center.y : 1.
    );
    vec2 rectSize = rectMax-rectMin;
    float area = rectSize.x*rectSize.y;
    if(area<=0.) return;

    vec2 velocity = motion.zw;
    float dt = u_dt;
    float fastest = max(abs(velocity.x),abs(velocity.y));
    if(fastest>0.) dt = min(dt,1./fastest);
    vec2 move = dt*velocity;

    vec2 sourceOffset = vec2(sx,sy);
    vec2 overlapMin = max(rectMin+sourceOffset+move,vec2(0));
    vec2 overlapMax = min(rectMax+sourceOffset+move,vec2(1));
    vec2 overlapSize = overlapMax-overlapMin;
    if(overlapSize.x<=0. || overlapSize.y<=0.) return;

    float streamedMass =
      matter.r*overlapSize.x*overlapSize.y/area;
    vec2 overlapCenter = .5*(overlapMin+overlapMax);
    mass += streamedMass;
    moment += streamedMass*overlapCenter;
    momentum += streamedMass*velocity;
    totalEnergy += streamedMass*(
      matter.g/matter.r + .5*dot(velocity,velocity)
    );
  }

  void main() {
    fieldSize = textureSize(u_fieldtexture,0);
    for(int layer=0; layer<`+FD+`; layer++) {
      tex3coord = ivec3(v_texcoord,layer);
      float mass = 0.;
      vec2 moment = vec2(0);
      vec2 momentum = vec2(0);
      float totalEnergy = 0.;
      for(int iy=0; iy<3; iy++) {
        for(int ix=0; ix<3; ix++) {
          AddSource(
            ix-1,iy-1,
            mass,moment,momentum,totalEnergy
          );
        }
      }
      vec2 center = mass>0. ? moment/mass : vec2(0);
      vec2 velocity = mass>0. ? momentum/mass : vec2(0);
      float internalEnergy = totalEnergy
        - .5*mass*dot(velocity,velocity);
      vec4 result = layer==0
        ? vec4(mass,internalEnergy,0,0)
        : vec4(center,velocity);
      `+fs_Prepare2Return('result')+`
    }
  }
`;

var CalcProgram = createProgram4Frag(
  gl,
  CalcFragmentShaderSource,
  [
    "a_position", "u_fieldtexture", "u_dt"
  ]
);
