var fs_ExtractRGBA = ``;

var MS = [1, 2, 4];

function CalcFragmentShaderSource(Uniforms4Ruleset) {
  return `
  precision highp float;
  precision highp int;
  
  ////////////////////////////////////////////////////////////////
  
  #define R `+RD+`.
  #define iR `+RD+`
  #define iR1 `+(RD+1)+`
  #define dT `+ParseTerminateFraction(1/DT)+`
  
  #define eps 0.000001
  
  ////////////////////////////////////////////////////////////////
  
  ` + Uniforms4Ruleset + `
  
  ////////////////////////////////////////////////////////////////
  
  uniform `+field_Sampler+` u_fieldtexture;  // Field texture
  uniform highp uint u_nturn;
  
  in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
  
  out `+field_Vec4P+` glFragColor[`+FD+`];
  
  ivec3 tex3coord;
  ivec3 fieldSize;
  
  ` + fs_ModuloTorus + `
  
  ` + fs_GetCell() + `
  
  ` + fs_GetTexel2D + `
  
  ////////////////////////////////////////////////////////////////
  
  float bell1(float x, float m, float s) {
    if(s<=0.) return 0.;
    float v = (x-m)/s;
    return exp(-v*v/2.);
  }
  
  float logi1(float x, float m, float s) {
    float v = (x-m)/s;
    return 1./(1.+exp(-v));
  }
  
  float get1Weight(float r, int l) {
    `+(Named=='Monia-Ximia1' ? `
           if(l==0) return logi1(r, 0.1, 0.01) - logi1(r, 0.8, 0.01);
      else if(l==1) return logi1(r, 0.8, 0.01) - logi1(r, 1.0, 0.01);
      else if(l==2) return logi1(r, 0.6, 0.01) - logi1(r, 0.8, 0.01);
      else if(l==3) return 1.0 - r;
      else          return 0.;
    ` : ``)+`
    float Br = betaLen[l] / relR[l] * r;
    int iBr = int(Br);
    float mod1 = fract(Br);
    float height = iBr==0 ? beta0[l] : (iBr==1 ? beta1[l] : beta2[l]);
    return height * bell1(mod1, 0.5, 0.15);
  }
  
  float[`+LL+`] getWeight(in float r) {
    float[`+LL+`] ret;
    `+IterateGLSLarray('ret[l] = get1Weight(r, lll);')+`
    return ret;
  }
  
  float get1Growth(float v, int l) {
    `+(Named=='Monia-Ximia1' ? `
           if(l==0) return -2.*logi1(v, 0.1, 0.01);
      else if(l==1) return logi1(v, 0.1, 0.01) - 3.*logi1(v, 0.3, 0.01);  //return -0.5*logi1(v, 0.0, 0.01);
      else if(l==2) return logi1(v, 0.1, 0.01) - 3.*logi1(v, 0.3, 0.01);
      else if(l==3) return bell1(v, 0.2, 0.1);  //logi1(v, 0.1, 0.01) - 3.*logi1(v, 0.5, 0.01);
      else          return 0.;
    ` : ``)+`
    return eta[l] * ( bell1(v, mu[l], sigma[l]) * 2. - 1. );
  }
  
  ////////////////////////////////////////////////////////////////
  
  vec3 drawKernel() {
    vec2 uv = 3. * (v_texcoord.xy / float(fieldSize.y));
    ivec2 ij = ivec2(uv);
    
    int l = 3 * ij.x + (2 - ij.y);
    if(l>=`+LL+`) return vec3(0);
    
    vec2 xy = 2. * fract(uv) - 1.;
    vec2 ab = abs(xy);
    float r = length(xy);
    
    vec3 rgb;
    if(ab.x>=0.98 || ab.y>=0.98) { return vec3(0); }
    if(ab.x>=0.94 || ab.y>=0.94) { rgb[dst[l]] = 1.;  return rgb; }
    if(xy.y<-0.84) {
      float t = (xy.x/0.94 + 1.) / 2.;
      float gr = get1Growth(t, l);
      rgb = gr>0. ? vec3(gr, gr, 0) : vec3(0, -gr, -gr);
      return rgb;
    }
    
    if(r>1.) return vec3(0);
    rgb[src[l]] = get1Weight(r, l);
    
    return rgb;
  }
  
  ////////////////////////////////////////////////////////////////
  
  int DX=0, DY=0;
  vec4 self, cell;
  float[`+LL+`] sum, total, weight;
  
  int IncSum(int dx, int dy) {
    cell = GetCell(DX+dx, DY+dy, 0);
    `+IterateGLSLarray('sum[l] += cell[src[l]] * weight[l];  total[l] += weight[l];')+`
    return 0;
  }
  
  vec3 CalcGrown() {
    float r;
    int x, y;
    
    sum   = float[`+LL+`](`+(() => {var ret = ''; for(var l=0; l<LL; l++) { ret += (ret?', ':'') + '0.'; } return ret.trim();})()+`);
    total = float[`+LL+`](`+(() => {var ret = ''; for(var l=0; l<LL; l++) { ret += (ret?', ':'') + '0.'; } return ret.trim();})()+`);
    
    // central cell (self)
    x = 0;  y = 0;  r = 0.;
    weight = getWeight(r);
    IncSum(x, y);
    self = cell;
    
    // axes
    for(x=1; x<=iR; x++) {
      r = float(x) / R;
      weight = getWeight(r);
      IncSum( x,  0);
      IncSum(-x,  0);
      IncSum( 0,  x);
      IncSum( 0, -x);
    }
    
    // diagonals
    int diagR = int(floor( R / sqrt(2.) ));  // floor, not ceil or round!
    for(x=1; x<=diagR; x++) {
      r = sqrt(2.) * float(x) / R;
      weight = getWeight(r);
      IncSum( x,  x);
      IncSum( x, -x);
      IncSum(-x,  x);
      IncSum(-x, -x);
    }
    
    // semiquadrants
    for(x=1; x<iR; x++) {
      for(y=x+1; y<=iR; y++) {
        r = length(vec2(x,y)) / R;
        if(r>1.) continue;
        weight = getWeight(r);
        IncSum( x,  y);
        IncSum( x, -y);
        IncSum(-x,  y);
        IncSum(-x, -y);
        IncSum( y,  x);
        IncSum( y, -x);
        IncSum(-y,  x);
        IncSum(-y, -x);
      }
    }
    
    vec3 growths = vec3(0);
    float avg;
    `+IterateGLSLarray('avg = sum[l] / total[l];  growths[dst[l]] += get1Growth(avg, lll);')+`
    vec3 rgb = clamp(self.rgb + dT * growths, 0., 1.);
    
    return rgb;
  }
  
  const vec3 masses = vec3(`+MS[0]+`, `+MS[1]+`, `+MS[2]+`);  // masses of r, g, b substances
  
  const float D = 1.;  // size of cell's side
  const float D2 = D / 2.;
  
  vec3 IntersectSquares(vec2 dxdy, vec2 cm, vec2 vc, float l) {
    float l2 = l / 2., ll = l * l;
          
    vec2 qm = dxdy * D + cm + vc;  // transferring square center of mass
    vec4 Q = vec4(qm - l2, qm + l2);  // transferring square borders
    vec4 B = clamp(Q, -D2, D2);  // transferring square borders clipped to the cell
    float S = (B.z - B.x) * (B.w - B.y);  // transferred area
    float Sd = S / ll;  // percent of transferred area
    vec2 Cm = (B.xy + B.zw) / 2.;  // transferred area center of mass
    
    return vec3(Cm, Sd);
  }
  
  vec3 clampx(vec3 x, vec3 i, vec3 a) { return clamp(x, min(i,a), max(i,a)); }
  vec4 clampx(vec4 x, vec4 i, vec4 a) { return clamp(x, min(i,a), max(i,a)); }
  
  vec4 am0, cv0, dd0;
  vec2 drain8mc;
  vec4 Drain8(int dx, int dy, float kk) {
    vec4 am = GetCell(dx, dy, 0);
    vec4 cv = GetCell(dx, dy, 1);
    vec4 dd = GetCell(dx, dy, 2);
    
    vec4 drain8 = clampx( (dd0-dd)/kk, -am0/kk, am/kk );  // am0 can drain from am no more than am has
    
    float d8m = dot(masses, drain8.rgb);
    vec2 dcv = d8m>0. ? cv.zw : cv0.zw;
    drain8mc = d8m * dcv;  // momentum
    drain8.a = d8m * dot(dcv,dcv);  // energy
    
    return drain8;
  }
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    tex3coord = ivec3(v_texcoord, 0);
    
    uint stage = u_nturn % 3u;
    if(stage==0u) {
      vec3 rgb = CalcGrown();
      
      glFragColor[0] = self;
      glFragColor[1] = GetCell(0, 0, 1);
      glFragColor[2] = vec4(rgb - self.rgb, 0);  // requested change
    }
    else if(stage==1u) {
      am0 = GetCell( 0, 0, 0);
      cv0 = GetCell( 0, 0, 1);
      dd0 = GetCell( 0, 0, 2);
      
      vec4 sum8 = vec4(0);
      vec2 mc8 = vec2(0);
      sum8 += Drain8(-1, -1, 12.);  mc8 += drain8mc;
      sum8 += Drain8( 0, -1,  6.);  mc8 += drain8mc;
      sum8 += Drain8( 1, -1, 12.);  mc8 += drain8mc;
      sum8 += Drain8( 1,  0,  6.);  mc8 += drain8mc;
      sum8 += Drain8( 1,  1, 12.);  mc8 += drain8mc;
      sum8 += Drain8( 0,  1,  6.);  mc8 += drain8mc;
      sum8 += Drain8(-1,  1, 12.);  mc8 += drain8mc;
      sum8 += Drain8(-1,  0,  6.);  mc8 += drain8mc;
      
      vec4 am1 = am0 + sum8;
      float m0 = dot(masses, am0.rgb);
      float m1 = dot(masses, am1.rgb);
      
      vec4 cv1;
      cv1.xy = cv0.xy;  // center of mass stays the same
      cv1.zw = m1>0. ? (m0 * cv0.zw + mc8) / m1 : vec2(0);  // speed = sum(momentum) / mass
      
      am1.a += m0*dot(cv0.zw,cv0.zw) - m1*dot(cv1.zw,cv1.zw);  // internal energy
      
      glFragColor[0] = am1;
      glFragColor[1] = cv1;
      glFragColor[2] = dd0;
    }
    else if(stage==2u) {
      vec4 new_am = vec4(0);  // (r, g, b); a = internal/kinetic energy
      vec4 new_cv = vec4(0);
      float new_m = 0.;  // total mass
      for(int dx=-1; dx<=1; dx++) {
        for(int dy=-1; dy<=1; dy++) {
          vec4 am = GetCell(dx, dy, 0);  // r, g, b - amounts of substances
          vec4 cv = GetCell(dx, dy, 1);  // cx, cy, vx, vy
          if(dx==0 && dy==0) am0 = am;
          
          float lplus = 0.;
          
          float summ = am.r + am.g + am.b;
          lplus += (summ>3. ? summ/100. : 0.);
          float l = 1. + lplus;  // size of transferred area (normally l=D; while l>D means temperature-like spraying)
          
          float M = dot(masses, am.rgb);  if(M==0.) continue;
          float V = length(cv.zw);        //if(V==0.) continue;  // @ if l>D then V can be 0
          
          float M2 = M/2.;
          
          // excess of internal energy goes to splitting mass into two halves with slightly different speeds
          vec2 VE = V>0. && am.a>0. ? cv.zw / V * sqrt(am.a/M) : vec2(0);  // velocity of one half due to free energy
          
          vec2 ve1 = cv.zw+vec2(-VE.y, VE.x);  vec3 CmSd1 = IntersectSquares(vec2(dx,dy), cv.xy, ve1, l);
          vec2 ve2 = cv.zw+vec2( VE.y,-VE.x);  vec3 CmSd2 = IntersectSquares(vec2(dx,dy), cv.xy, ve2, l);
          
          float Sd = (CmSd1.z + CmSd2.z) / 2.;
          float tmass = M * Sd;  // transferred mass
          new_am += vec4(am.rgb,0) * Sd;  // transfer matter and energy
          new_am.a += M2 * ( CmSd1.z * dot(ve1,ve1) + CmSd2.z * dot(ve2,ve2) );
          new_cv += M2 * ( CmSd1.z * vec4(CmSd1.xy, ve1) + CmSd2.z * vec4(CmSd2.xy, ve2) );  // sum M*R and M*V (momentum), then divide by mass = CM and velocity
          new_m += tmass;
        }
      }
      new_cv = new_m>0. ? new_cv / new_m : vec4(0);
      new_am.a -= new_m * dot(new_cv.zw,new_cv.zw);
      
      glFragColor[0] = new_am;
      glFragColor[1] = new_cv;
      glFragColor[2] = vec4(0);
    }
    
    `+(cfg.debug==-1?`glFragColor[0] = vec4(drawKernel(), 1.);`:``)+`
  }
  `;
}
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource(GetUniforms4Ruleset()), ["a_position", "u_fieldtexture", "u_nturn"]);
//console.log(CalcFragmentShaderSource(GetUniforms4Ruleset()));
