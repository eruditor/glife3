var fs_ExtractRGBA = ``;

var CalcFragmentShaderSource = `
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

  vec2 Imaginary(vec2 z) {
    return vec2(-z.y, z.x);
  }

  vec2 Rotate(vec2 z, float angle) {
    float c = cos(angle), s = sin(angle);
    return vec2(c*z.x-s*z.y, s*z.x+c*z.y);
  }

  vec2 Densities(vec4 cell) {
    return vec2(dot(cell.rg, cell.rg), dot(cell.ba, cell.ba));
  }

  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    tex3coord = ivec3(v_texcoord, 0);

    vec4 self = GetCell(0, 0, 0);
    uint stage = u_nturn % 9u;

    if(stage==0u || stage==8u) {
      vec2 rho = Densities(self);
      int d = `+KLPPhysics.bindingRadius+`;
      vec2 shell = (
        Densities(GetCell(-d, 0, 0)) + Densities(GetCell(d, 0, 0))
        + Densities(GetCell(0, -d, 0)) + Densities(GetCell(0, d, 0))
      ) / 4.;
      float omega1 = -`+KLPPhysics.cubic+`*rho.x
        + `+KLPPhysics.quintic+`*rho.x*rho.x
        - `+KLPPhysics.cross+`*rho.y
        - `+KLPPhysics.binding+`*shell.y;
      float omega2 = -`+KLPPhysics.cubic+`*rho.y
        + `+KLPPhysics.quintic+`*rho.y*rho.y
        - `+KLPPhysics.cross+`*rho.x
        - `+KLPPhysics.binding+`*shell.x;
      glFragColor[0] = vec4(
        Rotate(self.rg, -0.5*`+KLPPhysics.dt+`*omega1),
        Rotate(self.ba, -0.5*`+KLPPhysics.dt+`*omega2)
      );
      return;
    }

    int kineticStage = stage<=4u ? int(stage)-1 : 7-int(stage);
    bool horizontal = kineticStage<2;
    int phase = kineticStage % 2;
    int coordinate = horizontal ? tex3coord.x : tex3coord.y;
    int local = (coordinate - phase + (horizontal ? fieldSize.x : fieldSize.y)) % 2;
    int shift = local==0 ? 1 : -1;
    vec4 mate = horizontal ? GetCell(shift, 0, 0) : GetCell(0, shift, 0);

    float angle = `+KLPPhysics.mixing+` * (stage==4u ? 1. : 0.5);
    float c = cos(angle);
    float s = sin(angle);
    glFragColor[0] = vec4(
      c*self.rg + s*Imaginary(mate.rg),
      c*self.ba + s*Imaginary(mate.ba)
    );
  }
`;

var CalcProgram = createProgram4Frag(
  gl,
  CalcFragmentShaderSource,
  ["a_position", "u_fieldtexture", "u_nturn"]
);
