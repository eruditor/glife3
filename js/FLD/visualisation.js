var fs_Show = function(zoom) {
  return `
  int d = int(`+zoom+`. * u_surface.z);  // canvas zoom
  if(d>=8 && color!=vec4(0.5, 0.5, 0.5, 1.)) {
    tex3coord = ivec3(tex2coord, layer);
    
    ivec2 cnvc = ivec2( fract(v_texcoord / u_surface.z - u_surface.xy) * `+zoom+`. * u_surface.z );  // canvas coords
    int x = cnvc.x, y = cnvc.y;
    int d2 = d / 2;
    int d4 = d / 4;
    int d1 = d - 1;
    vec4 clr;
    
    clr = vec4(0., 0., 0., 1.);
    if(ExtractAl(cell)>0u) {
      uint dir = ExtractDir(cell);
           if(dir==0u) { if(isqr(d2 - x) + isqr(d2 - y) >= isqr(d2)) color = clr; }
      else if(dir==1u) { if(x + y      < d2 || d  - x + y     <= d2) color = clr; }
      else if(dir==2u) { if(y + d1 - x < d2 || d1 - y + d - x <= d2) color = clr; }
      else if(dir==3u) { if(x + d1 - y < d2 || d1 - x + d - y <= d2) color = clr; }
      else if(dir==4u) { if(x + y      < d2 || d  - y + x     <= d2) color = clr; }
    }
    
    clr = vec4(0., 0.3, 0., 1.);
    uint gate = ExtractGate(cell);
         if(gate==1u) { if(y==1  ) color = clr; }
    else if(gate==2u) { if(x==d-2) color = clr; }
    else if(gate==3u) { if(y==d-2) color = clr; }
    else if(gate==4u) { if(x==1  ) color = clr; }
    
    uint[5] bonds = ExtractBonds(cell);
    for(uint n=1u; n<`+RC+`u; n++) {
      if(bonds[n]==0u) continue;
      clr =
        bonds[n]==1u ? vec4(1., 1., 1., 1.) :
        (bonds[n]==2u ? vec4(0., 1., 0., 1.) :
                        vec4(1., 0., 0., 1.)
        );
           if(n==1u) { if((y<4   ) && (x==d2 || x==d2-1 || x==d2-2 || x==d2+1)) color = clr; }
      else if(n==2u) { if((x>=d-4) && (y==d2 || y==d2-1 || y==d2-2 || y==d2+1)) color = clr; }
      else if(n==3u) { if((y>=d-4) && (x==d2 || x==d2-1 || x==d2-2 || x==d2+1)) color = clr; }
      else if(n==4u) { if((x<4   ) && (y==d2 || y==d2-1 || y==d2-2 || y==d2+1)) color = clr; }
    }
    
    uint[4] rF = ExtractRF(cell);
    if(abs(x-d2)<=10 && abs(y-d2)<=10) {
      if(x<=d2 && abs(y-d2)<2) color = vec4(float(rF[3]) / 128., 0., 0., 1.);
      if(x>=d2 && abs(y-d2)<2) color = vec4(float(rF[1]) / 128., 0., 0., 1.);
      if(abs(x-d2)<2 && y<=d2) color = vec4(float(rF[0]) / 128., 0., 0., 1.);
      if(abs(x-d2)<2 && y>=d2) color = vec4(float(rF[2]) / 128., 0., 0., 1.);
    }
    if(abs(x-d2)<2 && abs(y-d2)<2) color = vec4(.2, .2, .2, 1.);
    
    uint L = ExtractL(cell);
    if(L>0u) {
      clr = L==1u ? vec4(0., 1., 0., 1.) : vec4(0., 1., 1., 1.);
      if(abs(x-d2)<3 && abs(y-d2)<3) color = clr;
    }
  }
  `;
}