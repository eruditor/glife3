var fs_Color4Cell = `
  vec4 Color4Cell(`+field_Vec4P+` cell, int layer) {
    return vec4(cell.rgb, 1.);
  }
`;

var fs_Show = function(zoom) {
  return `
  int d = int(`+zoom+`. * u_surface.z);
  float amount = cell.r + cell.g + cell.b;
  if(d>=16 && color!=vec4(0.5, 0.5, 0.5, 1.) && amount>0.0000001) {
    ivec2 canvasCoord = ivec2(
      fract(v_texcoord / u_surface.z - u_surface.xy)
      * `+zoom+`. * u_surface.z
    );
    int d2 = d / 2;
    float brightness = amount * 100.;

    vec4 motion = texelFetch(u_fieldtexture, ivec3(tex2coord, 1), 0);

    vec2 arrowCoord = vec2(canvasCoord - ivec2(d2));
    float speed = length(motion.zw);
    if(dot(arrowCoord, motion.zw)>=0. && speed>0.) {
      vec2 direction = motion.zw / speed;
      float along = dot(arrowCoord, direction);
      float across = abs(arrowCoord.x*direction.y - arrowCoord.y*direction.x);
      if(along<=5.*speed*float(d2) && across<0.75) {
        color = vec4(brightness, brightness, 0., 1.);
      }
    }

    ivec2 center = ivec2((motion.xy + vec2(0.5)) * float(d));
    if(all(equal(canvasCoord, center))) {
      color = vec4(brightness, brightness, brightness, 1.);
    }
  }
  `;
}
