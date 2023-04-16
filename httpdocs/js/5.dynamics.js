// CALC SHADER ////////////////////////////////////////////////////////////////

var fs_ModuloTorus = `
  ivec3 ModuloTorus(ivec3 a, ivec3 b) {  // make the field torus-shaped (can use simple fract() for float vec3)
    ivec3 ret = a;
    if(a.x<0)    ret.x = b.x + a.x;
    if(a.y<0)    ret.y = b.y + a.y;
    if(a.z<0)    ret.z = b.z + a.z;
    if(a.x>=b.x) ret.x = a.x - b.x;
    if(a.y>=b.y) ret.y = a.y - b.y;
    if(a.z>=b.z) ret.z = a.z - b.z;
    return ret;
  }
`.trim();

function fs_GetCell(func='GetCell', tex='u_fieldtexture') {
  return (`
  `+field_Vec4P+` `+func+`(int dx, int dy, int dz) {
         if(dz>0 && tex3coord.z==`+(FD-1)+`) return `+field_Vec4+`(0);  // no upper for top layer
    else if(dz<0 && tex3coord.z==0)          return `+field_Vec4+`(0);  // no lower for bottom layer
    return texelFetch(`+tex+`, ModuloTorus(tex3coord + ivec3(dx, dy, dz), fieldSize), 0);
  }
  `).trim();
}

var fs_GetNeibs = '';
if(typeof(RC)!=='undefined') {
  fs_GetNeibs = `      `+field_Vec4P+` cells[`+RC+`];\n`;
  for(var k in RG) {
    fs_GetNeibs += `      cells[`+k+`] = GetCell(`+RG[k][0]+`, `+RG[k][1]+`, `+RG[k][2]+`);\n`;
  }
  fs_GetNeibs += `      `+field_Vec4P+` self = cells[`+R00+`];  // previous self cell state\n`;
}

// array index for samplers must be constant integral expressions, so we need this crap to address texture by layer
// we can use more convenient texture3D, but it's size (dimensions) is more limited than for 2D textures (rules texture for RB>4 exceeds the limit)
var fs_GetTexel2D = `uvec4 GetTexel2D(highp usampler2D tex[`+FD+`], int layer, ivec2 coord) {\n`;
for(var z=0; z<FD; z++) {
  fs_GetTexel2D += `    if(layer==`+z+`) return texelFetch(tex[`+z+`], coord, 0);\n`;
}
fs_GetTexel2D += `  }`;

// DATA PACKING ////////////////////////////////////////////////////////////////

var fs_ExtractRGBA = `
    uint ExtractAl(`+field_Vec4P+` cell) {  // aliveness
      return cell.a > 0u ? 1u : 0u;
    }
    
    uint ExtractFl(`+field_Vec4P+` cell) {  // flavor
      return cell.a>0u ? cell.a : cell.b % 10u;
    }
    
    uint ExtractDecay(`+field_Vec4P+` cell) {  // decay
      return cell.b<30u ? 0u : cell.b / 10u - 3u;
    }
`;

function fs_PackAliveness(alive='alive') {
  return DataFormat=='UI8'
  ? `
        // color.b = color decay value (optional); color.a must be set already!
             if(color.a>0u) color.b = 200u;           // alive cell
        else if(self.a>0u)  color.b = 100u + self.a;  // dying cell
        else if(self.b>30u) color.b = self.b - 10u;   // color decay for died cell
        else                color.b = 0u;             // empty cell
  `
  : `
  // @ ! see MVM !
  /*
        // highest 16bit = alive cell's color; lowest = decay and color of died cell
             if(`+alive+`>0u)  color.a = `+alive+` << 16u;        // alive cell
        else if(self.a>65535u) color.a = (self.a >> 16u) + 100u;  // dying cell
        else if(self.a>30u)    color.a = self.a - 10u;            // color decay for died cell
        else                   color.a = 0u;                      // empty cell
  */
  `;
}

function fs_Prepare2Return(varname='color') {
  var ret = '';
  for(var z=0; z<FD; z++) {
    ret += (z>0 ? `else ` : `     `) + `if(layer==`+z+`) glFragColor[`+z+`] = `+varname+`;\n      `;
  }
  return ret;
}

function IterateGLSLarray(line, tab='    ') {
  var ret = '';
  for(var l=0; l<LL; l++) {
    var ll = (LL>=10 && l<10 ? ' ' : '') + l;
    ret += tab + line.replaceAll('[l]', '['+ll+']').replaceAll('lll', ll) + '\n';
  }
  return ret.trim();
}

var fs_ExtractXY = '', fs_ExtractA = '', fs_Trends = '';
var fs_Show = function(zoom) { return ''; }

////////////////////////////////////////////////////////////////////////////////////////////////


// CALC MAIN ////////////////////////////////////////////////////////////////

function Calc(single=0) {
  if(cfg.paused && single!=1) return 0;
  
  gl.useProgram(CalcProgram);
  gl.viewport(0, 0, FW, FH);
  
  BindBuffersAttachments(Framebuffers[T1]);
  ActivateTexture(T0, CalcProgram.location.u_fieldtexture);
  if(Mode!='PRT') ActivateTexture(TT>2 ? T2 : T0, CalcProgram.location.u_prevtexture);
  
  if(Mode=='PRT') {
    //PS[0] = rndJ(-1, 2);  PS[1] = rndJ(-1, 2);
    //PS[0] = RG[nturn % RC][0];  PS[1] = RG[nturn % RC][1];
    PS[0] = floor(nturn / 3) % 3 - 1;  PS[1] = nturn % 3 - 1;
    gl.uniform1iv(CalcProgram.location.u_ps, PS);
  }
  
  if(Mode=='LFL') {
    gl.uniform1i(CalcProgram.location.u_rulestexture, TT + 0);
  }
  else {
    var rulestexture_nums = [];  for(var z=0; z<FD; z++) rulestexture_nums[z] = TT + z;
    gl.uniform1iv(CalcProgram.location.u_rulestexture, rulestexture_nums);
  }
  
  gl.uniform1ui(CalcProgram.location.u_nturn, nturn);
  
  gl.uniform1i(CalcProgram.location.u_td, TD);
  
  if(Mode=='FHP') gl.uniform1ui(CalcProgram.location.u_rdn, rndJ(0,10000));
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
  
  FlipTime();
  
  nturn ++;
  
  if(single==1) return true;
  
  if(cfg.showiter) { if(nturn % cfg.showiter == 0) Show(); }
  else if(cfg.maxfps<=60) Show();
  /* else Show() rotates in its own cycle */
  
  if((nturn % cfg.turn4stats)==0) Stats();
  
  if(cfg.pauseat>0 && nturn==cfg.pauseat) Pause(1);
  
  if(cfg.maxfps>1000) { if(nturn%10==0) setTimeout(Calc, 1); else Calc(); }
  else if(cfg.maxfps && cfg.maxfps!=60) setTimeout(Calc, Math.floor(1000 / cfg.maxfps));
  else requestAnimationFrame(Calc);
}

//  ////////////////////////////////////////////////////////////////