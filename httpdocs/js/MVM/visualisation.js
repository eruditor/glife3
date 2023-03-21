var fs_Show = function(zoom) {
  return `
  int cnv_zoom = int(`+zoom+`. * u_surface.z);
  if(cnv_zoom>=8) {
    tex3coord = ivec3(tex2coord, layer);
    
    ` + fs_GetNeibs + `
    
    ivec2 cnv_coord = ivec2( fract(v_texcoord / u_surface.z - u_surface.xy) * `+zoom+`. * u_surface.z );
    
    for(uint n=0u; n<`+RC+`u; n++) {
      if(ExtractAl(cells[n])==0u) continue;
      
      ivec4 xy = ExtractXY(cells[n]);
      
      uint trend = CalcTrend(xy);
      uint atrend = antitrends[trend];
      if(atrend!=n) continue;
      
      xy = ExtractXY(uvec4(XY4Trended(n, cells[n]), 0, 0));  // @ optimize it
      
      int px = (xy.x + `+mL+`) * cnv_zoom / `+mL2+`;
      int py = (xy.y + `+mL+`) * cnv_zoom / `+mL2+`;
      
      if(cnv_coord.x==px && cnv_coord.y==py) {
        color =
          n==0u
          ? vec4(1., 1., 1., 1.)
          : vec4(1., 1., 0., 1.)
        ;
      }
    }
  }
  `;
}