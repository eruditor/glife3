var fs_Color4Cell = `
  bool QmfHasAtom(vec4 atom) {
    return atom.r + atom.g + atom.b > 0.5;
  }

  vec4 QmfAtomColor(vec4 atom) {
    if(atom.r>0.5) return vec4(0.92, 0.92, 0.92, 1.);  // H
    if(atom.g>0.5) return vec4(0.95, 0.15, 0.12, 1.);  // O
    if(atom.b>0.5) return vec4(0.25, 0.55, 1.00, 1.);  // C
    return vec4(0., 0., 0., 1.);
  }

  float QmfSegmentDistance(vec2 p, vec2 a, vec2 b) {
    vec2 ab = b - a;
    float denominator = dot(ab, ab);
    float t = denominator>0. ? clamp(dot(p-a, ab) / denominator, 0., 1.) : 0.;
    return length(p - (a + t*ab));
  }

  vec4 Color4Cell(`+field_Vec4P+` cell, int layer) {
    return layer==0 ? QmfAtomColor(cell) : vec4(0., 0., 0., 1.);
  }
`;

var fs_Show = function(zoom) {
  return `
  int d = int(`+zoom+`. * u_surface.z);
  if(layer==0 && d>=6 && color!=vec4(0.5, 0.5, 0.5, 1.)) {
    ivec2 canvasCoord = ivec2(
      fract(v_texcoord / u_surface.z - u_surface.xy)
      * `+zoom+`. * u_surface.z
    );
    vec2 point = (vec2(canvasCoord) + 0.5) / float(d) - 0.5;
    vec4 atom = texelFetch(u_fieldtexture, ivec3(tex2coord, 0), 0);
    vec4 motion = texelFetch(u_fieldtexture, ivec3(tex2coord, 1), 0);
    color = vec4(0., 0., 0., 1.);

    if(QmfHasAtom(atom)) {
      int mask = int(mod(floor(atom.a + 0.5), 1024.));
      vec2 atomPosition = motion.xy;
      int bitIndex = 0;
      for(int dy=-1; dy<=1; dy++) {
        for(int dx=-1; dx<=1; dx++) {
          if(dx==0 && dy==0) {
            bitIndex++;
            continue;
          }
          if((mask & (1 << bitIndex)) != 0) {
            ivec2 nc = ModuloTorus(
              ivec3(tex2coord + ivec2(dx, dy), 0),
              fieldSize
            ).xy;
            vec4 otherAtom = texelFetch(u_fieldtexture, ivec3(nc, 0), 0);
            int reverseBit = (-dy + 1) * 3 + (-dx + 1);
            int otherMask = int(mod(floor(otherAtom.a + 0.5), 1024.));
            if(QmfHasAtom(otherAtom) && (otherMask & (1 << reverseBit)) != 0) {
              vec4 otherMotion = texelFetch(u_fieldtexture, ivec3(nc, 1), 0);
              vec2 other = vec2(dx, dy) + otherMotion.xy;
              if(QmfSegmentDistance(point, atomPosition, other)<0.055) {
                color = vec4(0.85, 0.72, 0.25, 1.);
              }
            }
          }
          bitIndex++;
        }
      }
    }

    // Draw atoms from adjacent source cells too. This lets a circle cross a
    // cell boundary continuously instead of being clipped or wrapped locally.
    for(int sy=-1; sy<=1; sy++) {
      for(int sx=-1; sx<=1; sx++) {
        ivec2 sourceCoord = ModuloTorus(
          ivec3(tex2coord + ivec2(sx, sy), 0),
          fieldSize
        ).xy;
        vec4 sourceAtom = texelFetch(u_fieldtexture, ivec3(sourceCoord, 0), 0);
        if(!QmfHasAtom(sourceAtom)) continue;
        vec4 sourceMotion = texelFetch(u_fieldtexture, ivec3(sourceCoord, 1), 0);
        vec2 sourcePosition = vec2(sx, sy) + sourceMotion.xy;
        if(length(point-sourcePosition)<0.22) {
          color = QmfAtomColor(sourceAtom);
        }
      }
    }
  }
  `;
}
