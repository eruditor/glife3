var fs_Show = function(zoom) {
  return `
  int d = int(`+zoom+`. * u_surface.z);  // canvas zoom
  if(d>=8 && color!=vec4(0.5, 0.5, 0.5, 1.)) {
    tex3coord = ivec3(tex2coord, layer);
    
    vec2 shift = vec2(0., 0.);
    ivec2 tex2coord = ivec2(v_texcoord / u_surface.z - u_surface.xy);
    if(tex2coord.y % 2 == 1) shift = vec2(0.5, 0.);
    
    ivec2 cnvc = ivec2( fract(v_texcoord / u_surface.z - u_surface.xy + shift) * `+zoom+`. * u_surface.z );  // canvas coords
    int x = cnvc.x, y = cnvc.y;
    int d2 = d / 2;
    int d4 = d / 4;
    int d1 = d - 1;
    int xx = x - d2;
    int yy = y - d2;
    int y32 = yy * 3 / 2;
    
    vec4 clr;
    
    clr = vec4(0., 0., 0., 1.);
    
    uint[8] speeds = ExtractSpeeds(cell);
    for(uint n=0u; n<`+RC+`u; n++) {
      if(speeds[n]==0u) continue;
      
           if(n==0u) clr = vec4(1., 1., 1., 1.);
      else if(n==1u) clr = vec4(1., 0., 0., 1.);
      else if(n==2u) clr = vec4(1., 1., 0., 1.);
      else if(n==3u) clr = vec4(0., 1., 0., 1.);
      else if(n==4u) clr = vec4(0., 1., 1., 1.);
      else if(n==5u) clr = vec4(0., 0., 1., 1.);
      else if(n==6u) clr = vec4(1., 0., 1., 1.);
      else           clr = vec4(0.8, 0.8, 0.8, 1.);
      
      //if(x==d2+int(n)) color = clr;
      
           if(n==0u && abs(xx)<d4 && abs(yy)<d4) color = clr;
      else if(n==1u && xx>y32  && xx<0)    color = clr;
      else if(n==2u && xx>0    && xx<-y32) color = clr;
      else if(n==3u && xx>y32  && xx>-y32) color = clr;
      else if(n==4u && xx>0    && xx<y32)  color = clr;
      else if(n==5u && xx>-y32 && xx<0)    color = clr;
      else if(n==6u && xx<y32  && xx<-y32) color = clr;
      
      /*
           if(n==1u) { if((y==0   || y==1  ) && (x==d2 || x==d2-1 || x==d2-2 || x==d2+1)) color = clr; }
      else if(n==2u) { if((x==d-1 || x==d-2) && (y==d2 || y==d2-1 || y==d2-2 || y==d2+1)) color = clr; }
      else if(n==3u) { if((y==d-1 || y==d-2) && (x==d2 || x==d2-1 || x==d2-2 || x==d2+1)) color = clr; }
      else if(n==4u) { if((x==0   || x==1  ) && (y==d2 || y==d2-1 || y==d2-2 || y==d2+1)) color = clr; }
      */
    }
  }
  `;
}