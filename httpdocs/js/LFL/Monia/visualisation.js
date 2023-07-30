var fs_Color4Cell = `
  vec4 Color4Cell(`+field_Vec4P+` cell, int layer) {
    vec4 ret = cell;
    ret.r += cell.a;  ret.g += cell.a;  ret.b += cell.a;  // white color for alpha-particles
    ret.a = 1.;
    return ret;
  }
`;

var fs_Show = function(zoom) {
  return `
  int d = int(`+zoom+`. * u_surface.z);  // canvas zoom
  float v = cell.r+cell.g+cell.b;
  if(d>=16 && color!=vec4(0.5, 0.5, 0.5, 1.) && v>0.0000001) {
    vec2 shift = vec2(0., 0.);
    //if(tex2coord.y % 2 == 0) shift = vec2(0.5, 0.);
    
    ivec2 cnvc = ivec2( fract(v_texcoord / u_surface.z - u_surface.xy + shift) * `+zoom+`. * u_surface.z );  // canvas coords
    int x = cnvc.x, y = cnvc.y;
    int d2 = d / 2;
    int d4 = d / 4;
    int d1 = d - 1;
    int xx = x - d2;
    int yy = y - d2;
    int y32 = yy * 3 / 2;
    
    float vv = v * 100.;
    
    vec4 cv = texelFetch(u_fieldtexture, ivec3(tex2coord, 1), 0);
    
    float dx = float(x-d2);
    float dy = float(y-d2);
    if(dx*cv.z>=0. && dy*cv.w>=0.) {
      float l = length(cv.zw);
      if(l>=0.2*length(vec2(dx,dy))/float(d2)) {
        float lx = l>0. ? cv.z/l : 0.;
        float ly = l>0. ? cv.w/l : 0.;
        if(round(dx*ly)==round(dy*lx)) {
          color = vec4(vv, vv, 0., 1.);
        }
      }
    }
    
    ivec2 cm = ivec2((cv.xy + vec2(0.5)) * float(d));
    if(x==cm.x && y==cm.y) {
      color = vec4(vv, vv, vv, 1.);
    }
  }
  `;
}