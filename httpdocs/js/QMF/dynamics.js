var fs_ExtractRGBA = ``;

// layer 0: rgb = substance amounts; layer 1: xy = center of mass, zw = velocity
function CalcFragmentShaderSource() {
  var extraOutputs = '';
  for(var z=2; z<FD; z++) extraOutputs += `    glFragColor[`+z+`] = vec4(0.);\n`;

  return `
  precision highp float;
  precision highp int;

  uniform `+field_Sampler+` u_fieldtexture;
  uniform highp uint u_rdn;

  in vec2 v_texcoord;

  out `+field_Vec4P+` glFragColor[`+FD+`];

  ivec3 tex3coord;
  ivec3 fieldSize;

  `+fs_ModuloTorus+`

  `+fs_GetCell()+`

  uint Hash(uint x) {
    x ^= x >> 16u;
    x *= 0x7feb352du;
    x ^= x >> 15u;
    x *= 0x846ca68bu;
    return x ^ (x >> 16u);
  }

  float Random01(ivec2 coord) {
    uint seed = uint(coord.x) * 0x9e3779b9u
              ^ uint(coord.y) * 0x85ebca6bu
              ^ u_rdn;
    return float(Hash(seed)) / 4294967296.0;
  }

  float IntersectionArea(vec2 center, ivec2 destination) {
    vec2 dst = vec2(destination);
    vec2 lo = max(center - 0.5, dst - 0.5);
    vec2 hi = min(center + 0.5, dst + 0.5);
    vec2 size = max(hi - lo, vec2(0.));
    return size.x * size.y;
  }

  bool IsForward(ivec2 destination, vec2 velocity) {
    if(destination.x<0 && velocity.x>=0.) return false;
    if(destination.x>0 && velocity.x<=0.) return false;
    if(destination.y<0 && velocity.y>=0.) return false;
    if(destination.y>0 && velocity.y<=0.) return false;
    return any(notEqual(destination, ivec2(0)));
  }

  ivec2 ChooseDestination(vec2 center, vec2 velocity, ivec2 sourceCoord) {
    float random = Random01(sourceCoord);
    float cumulative = 0.;

    for(int dy=-1; dy<=1; dy++) {
      for(int dx=-1; dx<=1; dx++) {
        ivec2 destination = ivec2(dx, dy);
        if(!IsForward(destination, velocity)) continue;
        cumulative += IntersectionArea(center, destination);
        if(random < cumulative) return destination;
      }
    }

    return ivec2(0);
  }

  vec2 IntersectionCenter(vec2 center, ivec2 destination) {
    vec2 dst = vec2(destination);
    vec2 lo = max(center - 0.5, dst - 0.5);
    vec2 hi = min(center + 0.5, dst + 0.5);
    return (lo + hi) / 2. - dst;
  }

  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    tex3coord = ivec3(v_texcoord, 0);

    vec3 newAmounts = vec3(0.);
    vec4 newMotion = vec4(0.);
    float totalAmount = 0.;

    for(int sy=-1; sy<=1; sy++) {
      for(int sx=-1; sx<=1; sx++) {
        ivec2 sourceOffset = ivec2(sx, sy);
        vec4 amounts = GetCell(sx, sy, 0);
        vec4 motion = GetCell(sx, sy, 1);
        float amount = amounts.r + amounts.g + amounts.b;
        if(amount<=0.) continue;

        ivec2 sourceCoord = ModuloTorus(
          ivec3(tex3coord.xy + sourceOffset, 0),
          fieldSize
        ).xy;
        vec2 velocity = clamp(motion.zw, vec2(-0.25), vec2(0.25));
        vec2 shiftedCenter = motion.xy + velocity;
        ivec2 destination = ChooseDestination(shiftedCenter, velocity, sourceCoord);

        if(all(equal(destination, -sourceOffset))) {
          vec2 center = IntersectionCenter(shiftedCenter, destination);
          newAmounts += amounts.rgb;
          newMotion += amount * vec4(center, motion.zw);
          totalAmount += amount;
        }
      }
    }

    if(totalAmount>0.) newMotion /= totalAmount;

    glFragColor[0] = vec4(newAmounts, 0.);
    glFragColor[1] = newMotion;
`+extraOutputs+`
  }
  `;
}

var CalcProgram = createProgram4Frag(
  gl,
  CalcFragmentShaderSource(),
  ["a_position", "u_fieldtexture", "u_rdn"]
);
