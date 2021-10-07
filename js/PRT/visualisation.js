var fs_Show = function(zoom) {
  return `
  if(`+zoom+`>=10) {
    ivec3 cur3coord = ivec3(tex2coord, layer);
    int dx3 = (3 + cur3coord.x + u_ps[0]) % 3;
    int dy3 = (3 + cur3coord.y + u_ps[1]) % 3;
    
    ivec2 cnv_coord = ivec2(gl_FragCoord.xy);
    if(cnv_coord.x % `+zoom+` == 0 || cnv_coord.y % `+zoom+` == 0) {  // @todo: support surface
      color = vec4(0.2, 0.2, 0.2, 1.);
      if(dx3==0 && cnv_coord.x % `+zoom+` == 0 || dy3==0 && cnv_coord.y % `+zoom+` == 0) {
        color = vec4(0., 0.7, 0., 1.);
      }
    }
  }
  `;
}
