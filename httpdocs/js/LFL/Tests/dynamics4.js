var fs_ExtractRGBA = ``;

var CalcFragmentShaderSource = `
  precision highp float;
  precision highp int;
  
  ////////////////////////////////////////////////////////////////
  
  #define R `+RD+`.
  #define iR `+RD+`
  #define iR1 `+(RD+1)+`
  #define dT `+(1/DT)+`
  
  #define EPSILON 0.000001
  #define mult matrixCompMult
  
  const ivec4 iv0 = ivec4(0);
  const ivec4 iv1 = ivec4(1);
  const ivec4 iv2 = ivec4(2);
  const ivec4 iv3 = ivec4(3);
  const vec4 v0 = vec4(0.);
  const vec4 v1 = vec4(1.);
  const mat4 m0 = mat4(v0, v0, v0, v0);
  const mat4 m1 = mat4(v1, v1, v1, v1);
  
  ////////////////////////////////////////////////////////////////
  
  #define species`+Specie+`
  
  #ifdef species0
  /*
  {b:[1],      m:0.272,s:0.0595,  h:0.197,r:0.91,  c0:0,c1:0},
  {b:[1],      m:0.349,s:0.1585,  h:0.686,r:0.62,  c0:0,c1:0},
  {b:[1,1/4],  m:0.2,s:0.0332,    h:0.406,r:0.5,   c0:0,c1:0},
  {b:[0,1],    m:0.114,s:0.0528,  h:0.366,r:0.97,  c0:1,c1:1},
  {b:[1],      m:0.447,s:0.0777,  h:0.714,r:0.72,  c0:1,c1:1},
  {b:[5/6,1],  m:0.247,s:0.0342,  h:0.889,r:0.8,   c0:1,c1:1},
  {b:[1],      m:0.21,s:0.0617,   h:0.500,r:0.96,  c0:2,c1:2},
  {b:[1],      m:0.462,s:0.1192,  h:0.311,r:0.56,  c0:2,c1:2},
  {b:[1],      m:0.446,s:0.1793,  h:0.794,r:0.78,  c0:2,c1:2},
  {b:[11/12,1],m:0.327,s:0.1408,  h:0.491,r:0.79,  c0:0,c1:1},
  {b:[3/4,1],  m:0.476,s:0.0995,  h:0.651,r:0.5,   c0:0,c1:2},
  {b:[11/12,1],m:0.379,s:0.0697,  h:0.957,r:0.72,  c0:1,c1:0},
  {b:[1],      m:0.262,s:0.0877,  h:0.600,r:0.68,  c0:1,c1:2},
  {b:[1/6,1,0],m:0.412,s:0.1101,  h:0.614,r:0.82,  c0:2,c1:0},
  {b:[1],      m:0.201,s:0.0786,  h:0.397,r:0.82,  c0:2,c1:1},
  */
  // species: VT049W fission: Tessellatium (sometimes reproductive)
  //                         0       1       2       3       4       5       6       7       8       9       A       B       C       D       E       F
  const mat4 betaLen = mat4( 1.,     1.,     2.,     2.,     1.,     2.,     1.,     1.,     1.,     2.,     2.,     2.,     1.,     2.,     1.,     0 );  // kernel ring number
  const mat4   beta0 = mat4( 1.,     1.,     1.,     0.,     1.,     5./6.,  1.,     1.,     1.,     11./12.,3./4.,  11./12.,1.,     1./6.,  1.,     0 );  // kernel ring heights
  const mat4   beta1 = mat4( 0.,     0.,     1./4.,  1.,     0.,     1.,     0.,     0.,     0.,     1.,     1.,     1.,     0.,     1.,     0.,     0 );
  const mat4   beta2 = mat4( 0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0 );
  const mat4      mu = mat4( 0.272,  0.349,  0.2,    0.114,  0.447,  0.247,  0.21,   0.462,  0.446,  0.327,  0.476,  0.379,  0.262,  0.412,  0.201,  0 );  // growth center
  const mat4   sigma = mat4( 0.0595, 0.1585, 0.0332, 0.0528, 0.0777, 0.0342, 0.0617, 0.1192, 0.1793, 0.1408, 0.0995, 0.0697, 0.0877, 0.1101, 0.0786, 1 );  // growth width
  const mat4     eta = mat4( 0.19,   0.66,   0.39,   0.38,   0.74,   0.92,   0.59,   0.37,   0.94,   0.51,   0.77,   0.92,   0.71,   0.59,   0.41,   0 );  // growth strength
  const mat4    relR = mat4( 0.91,   0.62,   0.5,    0.97,   0.72,   0.8,    0.96,   0.56,   0.78,   0.79,   0.5,    0.72,   0.68,   0.55,   0.82,   1 );  // relative kernel radius
  const mat4     src = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     0.,     0.,     1.,     1.,     2.,     2.,     0 );  // source channels
  const mat4     dst = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     1.,     2.,     0.,     2.,     0.,     1.,     0 );  // destination channels
  //                         0       1       2       3       4       5       6       7       8       9       A       B       C       D       E       F
  #endif
  
  #ifdef species1
  // species: Z18A9R reproductive: Tessellatium (highly reproductive)
  const mat4 betaLen = mat4( 1., 1., 2., 2., 1., 2., 1., 1., 1., 2., 2., 2., 1., 2., 1., v0 );  // kernel ring number
  const mat4   beta0 = mat4( 1., 1., 1., 0., 1., 3./4., 1., 1., 1., 11./12., 3./4., 1., 1., 1./4., 1., v0 );  // kernel ring heights
  const mat4   beta1 = mat4( 0., 0., 1./4., 1., 0., 1., 0., 0., 0., 1., 1., 11./12., 0., 1., 0., v0 );
  const mat4   beta2 = mat4( 0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0. );
  const mat4      mu = mat4( 0.175, 0.382, 0.231, 0.123, 0.398, 0.224, 0.193, 0.512, 0.427, 0.286, 0.508, 0.372, 0.196, 0.371, 0.246, v0 );  // growth center
  const mat4   sigma = mat4( 0.0682, 0.1568, 0.034, 0.0484, 0.0816, 0.0376, 0.063, 0.1189, 0.1827, 0.1422, 0.1079, 0.0724, 0.0934, 0.1107, 0.0712, v1 );  // growth width
  const mat4     eta = mat4( 0.138, 0.544, 0.326, 0.256, 0.544, 0.544, 0.442, 0.198, 0.58, 0.282, 0.396, 0.618, 0.382, 0.374, 0.376, v0 );  // growth strength
  const mat4    relR = mat4( 0.78, 0.56, 0.6, 0.84, 0.76, 0.82, 1.0, 0.68, 0.99, 0.72, 0.56, 0.65, 0.85, 0.54, 0.82, v1 );  // relative kernel radius
  const mat4     src = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     0.,     0.,     1.,     1.,     2.,     2.,     0. );  // source channels
  const mat4     dst = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     1.,     2.,     0.,     2.,     0.,     1.,     0. );  // destination channels
  #endif
  
  #ifdef species2
  // species: G6G6CR ciliates: Ciliatium
  const mat4 betaLen = mat4( 1., 1., 1., 2., 1., 2., 1., 1., 1., 1., 1., 2., 1., 1., 2., v0 );  // kernel ring number
  const mat4   beta0 = mat4( 1., 1., 1., 1./12., 1., 5./6., 1., 1., 1., 1., 1., 1., 1., 1., 1., v0 );  // kernel ring heights
  const mat4   beta1 = mat4( 0., 0., 0., 1., 0., 1., 0., 0., 0., 0., 0., 11./12., 1., 0., 0., v0 );
  const mat4   beta2 = mat4( 0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0. );
  const mat4      mu = mat4( 0.118, 0.174, 0.244, 0.114, 0.374, 0.222, 0.306, 0.449, 0.498, 0.295, 0.43, 0.353, 0.238, 0.39, 0.1, v0 );  // growth center
  //const mat4   sigma = mat4( 0.0639, 0.159, 0.0287, 0.0469, 0.0822, 0.0294, 0.0775, 0.124, 0.1836, 0.1373, 0.0999, 0.0754, 0.0995, 0.1144, 0.0601, v1 );  // growth width
  const mat4   sigma = mat4( 0.0639, 0.159, 0.0287, 0.0469, 0.0822, 0.0294, 0.0775, 0.124, 0.1836, 0.1373, 0.0999, 0.0954, 0.0995, 0.1094, 0.0601, v1 );  // growth width
  const mat4     eta = mat4( 0.082, 0.462, 0.496, 0.27, 0.518, 0.576, 0.324, 0.306, 0.544, 0.374, 0.33, 0.528, 0.498, 0.43, 0.26, v0 );  // growth strength
  const mat4    relR = mat4( 0.85, 0.61, 0.5, 0.81, 0.85, 0.93, 0.88, 0.74, 0.97, 0.92, 0.56, 0.56, 0.95, 0.59, 0.58, v1 );  // relative kernel radius
  const mat4     src = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     0.,     0.,     1.,     1.,     2.,     2.,     0. );  // source channels
  const mat4     dst = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     1.,     2.,     0.,     2.,     0.,     1.,     0. );  // destination channels
  #endif
  
  #ifdef species3
  // species: tri-color ghosts
  //const float R = 10.;  const float T = 10.;
  const mat4 betaLen = mat4( 2., 3., 1., 2., 3., 1., 2., 3., 1., v0, v0 );  // kernel ring number
  const mat4   beta0 = mat4( 1./4., 1., 1., 1./4., 1., 1., 1./4., 1., 1., v0, v0 );  // kernel ring heights
  const mat4   beta1 = mat4( 1., 3./4., 0., 1., 3./4., 0., 1., 3./4., 0., v0, v0 );
  const mat4   beta2 = mat4( 0., 3./4., 0., 0., 3./4., 0., 0., 3./4., 0., v0, v0 );
  const mat4      mu = mat4( 0.16, 0.22, 0.28, 0.16, 0.22, 0.28, 0.16, 0.22, 0.28, v0, v0 );  // growth center
  const mat4   sigma = mat4( 0.025, 0.042, 0.025, 0.025, 0.042, 0.025, 0.025, 0.042, 0.025, v1, v1 );  // growth width
  const mat4     eta = mat4( 0.666, 0.666, 0.666, 0.666, 0.666, 0.666, 0.666, 0.666, 0.666, v0, v0 );  // growth strength
  const mat4    relR = mat4( 1., 1., 1., 1., 1., 1., 1., 1., 1., v0, v0 );  // relative kernel radius
  const mat4     src = mat4( 0., 0., 0., 1., 1., 1., 2., 2., 2., v0, v0 );  // source channels
  const mat4     dst = mat4( 0., 0., 0., 1., 1., 1., 2., 2., 2., v0, v0 );  // destination channels
  #endif
  
  #ifdef species4
  // species: KH97WU courting: Tessellatium (slightly reproductive)
  const mat4 betaLen = mat4( 1., 1., 2., 2., 1., 2., 1., 1., 1., 2., 2., 1., 1., 2., 1., v0 );  // kernel ring number
  const mat4   beta0 = mat4( 1., 1., 1., 0., 1., 5./6., 1., 1., 1., 11./12., 3./4., 1., 1., 1./6., 1., v0 );  // kernel ring heights
  const mat4   beta1 = mat4( 0., 0., 1./4., 1., 0., 1., 0., 0., 0., 1., 1., 0., 0., 1., 0., v0 );
  const mat4   beta2 = mat4( 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., v0 );
  const mat4      mu = mat4( 0.204, 0.359, 0.176, 0.128, 0.386, 0.229, 0.181, 0.466, 0.466, 0.37, 0.447, 0.391, 0.299, 0.398, 0.183, v0 );  // growth center
  const mat4   sigma = mat4( 0.0574, 0.152, 0.0314, 0.0545, 0.0825, 0.0348, 0.0657, 0.1224, 0.1789, 0.1372, 0.1064, 0.0644, 0.0891, 0.1065, 0.0773, v1 );  // growth width
  const mat4     eta = mat4( 0.116, 0.448, 0.332, 0.392, 0.398, 0.614, 0.448, 0.224, 0.624, 0.352, 0.342, 0.634, 0.362, 0.472, 0.242, v0 );  // growth strength
  const mat4    relR = mat4( 0.93, 0.59, 0.58, 0.97, 0.79, 0.87, 1.0, 0.64, 0.67, 0.68, 0.5, 0.85, 0.69, 0.87, 0.66, v1 );  // relative kernel radius
  const mat4     src = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     0.,     0.,     1.,     1.,     2.,     2.,     0. );  // source channels
  const mat4     dst = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     1.,     2.,     0.,     2.,     0.,     1.,     0. );  // destination channels
  #endif
  
  #ifdef species5
  // species: XEH4YR explosive: Tessellatium (explosive)
  const mat4 betaLen = mat4( 1., 1., 2., 2., 1., 2., 1., 1., 1., 2., 2., 2., 1., 3., 1., v0 );  // kernel ring number
  const mat4   beta0 = mat4( 1., 1., 1., 0., 1., 5./6., 1., 1., 1., 11./12., 3./4., 11./12., 1., 1./6., 1., v0 );  // kernel ring heights
  const mat4   beta1 = mat4( 0., 0., 1./4., 1., 0., 1., 0., 0., 0., 1., 1., 1., 0., 1., 0., v0 );
  const mat4   beta2 = mat4( 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., v0 );
  const mat4      mu = mat4( 0.282, 0.354, 0.197, 0.164, 0.406, 0.251, 0.259, 0.517, 0.455, 0.264, 0.472, 0.417, 0.208, 0.395, 0.184, v0 );  // growth center
  const mat4   sigma = mat4( 0.0646, 0.1584, 0.0359, 0.056, 0.0738, 0.0383, 0.0665, 0.1164, 0.1806, 0.1437, 0.0939, 0.0666, 0.0815, 0.1049, 0.0748, v1 );  // growth width
  const mat4     eta = mat4( 0.082, 0.544, 0.26, 0.294, 0.508, 0.56, 0.326, 0.21, 0.638, 0.346, 0.384, 0.748, 0.44, 0.366, 0.294, v0 );  // growth strength
  const mat4    relR = mat4( 0.85, 0.62, 0.69, 0.84, 0.82, 0.86, 1.0, 0.5, 0.78, 0.6, 0.5, 0.7, 0.67, 0.6, 0.8, v1 );  // relative kernel radius
  const mat4     src = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     0.,     0.,     1.,     1.,     2.,     2.,     0. );  // source channels
  const mat4     dst = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     1.,     2.,     0.,     2.,     0.,     1.,     0. );  // destination channels
  #endif
  
  #ifdef species6
  // species: HAESRE zigzagging: Tessellatium (zigzaging)
  const mat4 betaLen = mat4( 1., 1., 2., 2., 1., 2., 1., 1., 1., 2., 2., 2., 1., 2., 1., v0 );  // kernel ring number
  const mat4   beta0 = mat4( 1., 1., 1., 0., 1., 3./4., 1., 1., 1., 11./12., 5./6., 1., 1., 1./4., 1., v0 );  // kernel ring heights
  const mat4   beta1 = mat4( 0., 0., 1./4., 1., 0., 1., 0., 0., 0., 1., 1., 11./12., 0., 1., 0., v0 );
  const mat4   beta2 = mat4( 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., v0 );
  const mat4      mu = mat4( 0.272, 0.337, 0.129, 0.132, 0.429, 0.239, 0.25, 0.497, 0.486, 0.276, 0.425, 0.352, 0.21, 0.381, 0.244, v0 );  // growth center
  const mat4   sigma = mat4( 0.0674, 0.1576, 0.0382, 0.0514, 0.0813, 0.0409, 0.0691, 0.1166, 0.1751, 0.1344, 0.1026, 0.0797, 0.0921, 0.1056, 0.0813, v1 );  // growth width
  const mat4     eta = mat4( 0.15, 0.474, 0.342, 0.192, 0.524, 0.598, 0.426, 0.348, 0.62, 0.338, 0.314, 0.608, 0.292, 0.426, 0.346, v0 );  // growth strength
  const mat4    relR = mat4( 0.87, 0.65, 0.67, 0.98, 0.77, 0.83, 1.0, 0.7, 0.99, 0.69, 0.7, 0.57, 0.89, 0.84, 0.76, v1 );  // relative kernel radius
  const mat4     src = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     0.,     0.,     1.,     1.,     2.,     2.,     0. );  // source channels
  const mat4     dst = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     1.,     2.,     0.,     2.,     0.,     1.,     0. );  // destination channels
  #endif
  
  #ifdef species7
  // species: GDNQYX variety: Tessellatium (stable)
  const mat4 betaLen = mat4( 1.,     1.,     2.,     2.,     1.,     2.,     1.,     1.,     1.,     2.,     2.,     2.,     1.,     2.,     1.,     0. );  // kernel ring number
  const mat4   beta0 = mat4( 1.,     1.,     1.,     0.,     1.,     5./6.,  1.,     1.,     1.,     11./12.,3./4.,  1.,     1.,     1./6.,  1.,     0. );  // kernel ring heights
  const mat4   beta1 = mat4( 0.,     0.,     1./4.,  1.,     0.,     1.,     0.,     0.,     0.,     1.,     1.,     11./12.,0.,     1.,     0.,     0. );
  const mat4   beta2 = mat4( 0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0. );
  const mat4      mu = mat4( 0.242,  0.375,  0.194,  0.122,  0.413,  0.221,  0.192,  0.492,  0.426,  0.361,  0.464,  0.361,  0.235,  0.381,  0.216,  0. );  // growth center
  const mat4   sigma = mat4( 0.061,  0.1553, 0.0361, 0.0531, 0.0774, 0.0365, 0.0649, 0.1219, 0.1759, 0.1381, 0.1044, 0.0686, 0.0924, 0.1118, 0.0748, 1. );  // growth width
  const mat4     eta = mat4( 0.144,  0.506,  0.332,  0.3,    0.502,  0.58,   0.344,  0.268,  0.582,  0.326,  0.418,  0.642,  0.39,   0.378,  0.294,  0. );  // growth strength
  const mat4    relR = mat4( 0.98,   0.59,   0.5,    0.93,   0.73,   0.88,   0.93,   0.61,   0.84,   0.7,    0.57,   0.73,   0.74,   0.87,   0.72,   1. );  // relative kernel radius
  const mat4     src = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     0.,     0.,     1.,     1.,     2.,     2.,     0. );  // source channels
  const mat4     dst = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     1.,     2.,     0.,     2.,     0.,     1.,     0. );  // destination channels
  //                         0       1       2       3       4       5       6       7       8       9       10      11      12      13      14      15
  #endif
  
  #ifdef species8
  // species: 5N7KKM Tessellatium (stable)
  const mat4 betaLen = mat4( 1., 1., 2., 2., 1., 2., 1., 1., 1., 2., 2., 2., 1., 2., 1., v0 );  // kernel ring number
  const mat4   beta0 = mat4( 1., 1., 1., 0., 1., 3./4., 1., 1., 1., 11./12., 3./4., 1., 1., 1./6., 1., v0 );  // kernel ring heights
  const mat4   beta1 = mat4( 0., 0., 1./4., 1., 0., 1., 0., 0., 0., 1., 1., 11./12., 0., 1., 0., v0 );
  const mat4   beta2 = mat4( 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., v0 );
  const mat4      mu = mat4( 0.22, 0.351, 0.177, 0.126, 0.437, 0.234, 0.179, 0.489, 0.419, 0.341, 0.469, 0.369, 0.219, 0.385, 0.208, v0 );  // growth center
  const mat4   sigma = mat4( 0.0628, 0.1539, 0.0333, 0.0525, 0.0797, 0.0369, 0.0653, 0.1213, 0.1775, 0.1388, 0.1054, 0.0721, 0.0898, 0.1102, 0.0749, v1 );  // growth width
  const mat4     eta = mat4( 0.174, 0.46, 0.31, 0.242, 0.508, 0.566, 0.406, 0.27, 0.588, 0.294, 0.388, 0.62, 0.348, 0.436, 0.39, v0 );  // growth strength
  const mat4    relR = mat4( 0.87, 0.52, 0.58, 0.89, 0.78, 0.79, 1.0, 0.64, 0.96, 0.66, 0.69, 0.61, 0.81, 0.81, 0.71, v1 );  // relative kernel radius
  const mat4     src = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     0.,     0.,     1.,     1.,     2.,     2.,     0. );  // source channels
  const mat4     dst = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     1.,     2.,     0.,     2.,     0.,     1.,     0. );  // destination channels
  #endif
  
  #ifdef species9
  // species: Y3CS55 emitter: Papillatium (fast emitter)
  const mat4 betaLen = mat4( 1., 1., 1., 2., 1., 2., 1., 1., 1., 1., 1., 3., 1., 1., 2., v0 );  // kernel ring number
  const mat4   beta0 = mat4( 1., 1., 1., 1./12., 1., 5./6., 1., 1., 1., 1., 1., 1., 1., 1., 1., v0 );  // kernel ring heights
  const mat4   beta1 = mat4( 0., 0., 0., 1., 0., 1., 0., 0., 0., 0., 0., 11./12., 0., 0., 1./12., v0 );
  const mat4   beta2 = mat4( 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., 0., v0 );
  const mat4      mu = mat4( 0.168, 0.1, 0.265, 0.111, 0.327, 0.223, 0.293, 0.465, 0.606, 0.404, 0.377, 0.297, 0.319, 0.483, 0.1, v0 );  // growth center
  const mat4   sigma = mat4( 0.062, 0.1495, 0.0488, 0.0555, 0.0763, 0.0333, 0.0724, 0.1345, 0.1807, 0.1413, 0.1136, 0.0701, 0.1038, 0.1185, 0.0571, v1 );  // growth width
  const mat4     eta = mat4( 0.076, 0.562, 0.548, 0.306, 0.568, 0.598, 0.396, 0.298, 0.59, 0.396, 0.156, 0.426, 0.558, 0.388, 0.132, v0 );  // growth strength
  const mat4    relR = mat4( 0.58, 0.68, 0.5, 0.87, 1.0, 1.0, 0.88, 0.88, 0.86, 0.98, 0.63, 0.53, 1.0, 0.89, 0.59, v1 );  // relative kernel radius
  const mat4     src = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     0.,     0.,     1.,     1.,     2.,     2.,     0. );  // source channels
  const mat4     dst = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     1.,     2.,     0.,     2.,     0.,     1.,     0. );  // destination channels
  #endif
  
  #ifdef species10
  // species: F45LYC cloud
  const mat4 betaLen = mat4( 3,      2,      1.,     3.,     2.,     1.,     3.,     2.,     1.,     2.,     1.,     1.,     2.,     2.,     1.,     0. );  // kernel ring number
  const mat4   beta0 = mat4( 1.,     2./3.,  1.,     1.,     5./12., 1.,     1.,     1./6.,  1.,     1./6.,  1.,     1.,     7./12., 1./4.,  1.,     0. );  // kernel ring heights
  const mat4   beta1 = mat4( 1./4.,  1,      0.,     1./12., 1.,     0.,     1./12., 1.,     0.,     1.,     0.,     0.,     1.,     1.,     0.,     0. );
  const mat4   beta2 = mat4( 11./12.,0,      0.,     2./3.,  0.,     0.,     7./12., 0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0. );
  const mat4      mu = mat4( 0.151,  0.217,  0.249,  0.358,  0.243,  0.463,  0.145,  0.181,  0.31,   0.116,  0.326,  0.68,   0.276,  0.242,  0.119,  0. );  // growth center
  const mat4   sigma = mat4( 0.0176, 0.0693, 0.0606, 0.025,  0.0752, 0.112,  0.01,   0.0844, 0.0847, 0.0602, 0.087,  0.1145, 0.0671, 0.035,  0.0922, 1. );  // growth width
  const mat4     eta = mat4( 0.2,    0.2,    0.2,    0.2,    0.2,    0.2,    0.2,    0.2,    0.2,    0.2,    0.2,    0.2,    0.2,    0.2,    0.65,   0. );  // growth strength
  const mat4    relR = mat4( 1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      1. );  // relative kernel radius
  const mat4     src = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     0.,     0.,     1.,     1.,     2.,     2.,     0. );  // source channels
  const mat4     dst = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     1.,     2.,     0.,     2.,     0.,     1.,     0. );  // destination channels
  //                         0       1       2       3       4       5       6       7       8       9       A       B       C       D       E       F
  #endif
  
  #ifdef species11
  // species: 1MTFAY Self-replicator
  /*
  0 {b:[1, 5/12, 3/4],m:0.179,s:0.0313,  h:1,r:1,  c0:0,c1:0},
  1 {b:[3/4, 1],      m:0.235,s:0.0671,  h:1,r:1,  c0:0,c1:0},
  2 {b:[1],           m:0.329,s:0.044,   h:1,r:1,  c0:0,c1:0},
  3 {b:[1, 0, 1/4],   m:0.273,s:0.0431,  h:1,r:1,  c0:1,c1:1},
  4 {b:[5/12, 1],     m:0.335,s:0.067,   h:1,r:1,  c0:1,c1:1},
  5 {b:[1],           m:0.532,s:0.1051,  h:1,r:1,  c0:1,c1:1},
  6 {b:[1, 0, 1/2],   m:0.244,s:0.01,    h:1,r:1,  c0:2,c1:2},
  7 {b:[0, 1],        m:0.133,s:0.0894,  h:1,r:1,  c0:2,c1:2},
  8 {b:[1],           m:0.292,s:0.1006,  h:1,r:1,  c0:2,c1:2},
  9 {b:[1/3, 1],      m:0.197,s:0.0646,  h:1,r:1,  c0:0,c1:1},
  A {b:[1],           m:0.651,s:0.1282,  h:1,r:1,  c0:1,c1:0},
  B {b:[1/3, 1],      m:0.29, s:0.0673,  h:1,r:1,  c0:1,c1:2},
  C {b:[1],           m:0.232,s:0.0956,  h:1,r:1,  c0:2,c1:1},
  D {b:[1/6, 1],      m:0.27, s:0.0405,  h:1,r:1,  c0:2,c1:0},
  E {b:[1],           m:0.36, s:0.0955,  h:1,r:1,  c0:0,c1:2},
  */
  const mat4 betaLen = mat4( 3,      2,      1,      3,      2,      1,      3,      2,      1,      2,      1,      2,      1,      2,      1,      0 );  // kernel ring number
  const mat4   beta0 = mat4( 1,      3./4.,  1,      1,      5./12., 1,      1,      0,      1,      1./3.,  1,      1./3.,  1,      1./6.,  1,      0 );  // kernel ring heights
  const mat4   beta1 = mat4( 5./12., 1,      0,      0,      1,      0,      0,      1,      0,      1,      0,      1,      0,      1,      0,      0 );
  const mat4   beta2 = mat4( 3./4.,  0,      0,      1./4.,  0,      0,      1./2.,  0,      0,      0,      0,      0,      0,      0,      0,      0 );
  const mat4      mu = mat4( 0.179,  0.235,  0.329,  0.273,  0.335,  0.532,  0.244,  0.133,  0.292,  0.197,  0.651,  0.29,   0.232,  0.27,   0.36,   0 );  // growth center
  const mat4   sigma = mat4( 0.0313, 0.0671, 0.044,  0.0431, 0.067,  0.1051, 0.01,   0.0894, 0.1006, 0.0646, 0.1282, 0.0673, 0.0956, 0.0405, 0.0955, 1 );  // growth width
  const mat4     eta = mat4( 1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      0 );  // growth strength
  const mat4    relR = mat4( 1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      1,      1 );  // relative kernel radius
  const mat4     src = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     0.,     1.,     1.,     2.,     2.,     0.,     0. );  // source channels
  const mat4     dst = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     1.,     0.,     2.,     1.,     0.,     2.,     0. );  // destination channels
  //                         0       1       2       3       4       5       6       7       8       9       A       B       C       D       E       F
  //mat4( , , , , , , , , , , , , , , , 0 );
  #endif
  
  ////////////////////////////////////////////////////////////////
  
  const vec4 kmv = vec4(0.5);    // kernel ring center
  const mat4 kmu = mat4(kmv, kmv, kmv, kmv);
  const vec4 ksv = vec4(0.15);    // kernel ring width
  const mat4 ksigma = mat4(ksv, ksv, ksv, ksv);
  
  const ivec4 src0 = ivec4(src[0]), src1 = ivec4(src[1]), src2 = ivec4(src[2]), src3 = ivec4(src[3]);
  const ivec4 dst0 = ivec4(dst[0]), dst1 = ivec4(dst[1]), dst2 = ivec4(dst[2]), dst3 = ivec4(dst[3]);  
  
  ////////////////////////////////////////////////////////////////
  
  uniform `+field_Sampler+` u_fieldtexture;  // Field texture
  
  in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
  
  out `+field_Vec4P+` glFragColor[`+FD+`];
  
  ivec3 tex3coord;
  ivec3 fieldSize;
  
  ` + fs_ModuloTorus + `
  
  ` + fs_GetCell() + `
  
  ` + fs_GetTexel2D + `
  
  ////////////////////////////////////////////////////////////////
  
  mat4 bell(in mat4 x, in mat4 m, in mat4 s) {
    mat4 v = -mult(x-m, x-m) / s / s / 2.;
    return mat4( exp(v[0]), exp(v[1]), exp(v[2]), exp(v[3]) );
  }
  
  mat4 getWeight(in float r) {
    if(r > 1.) return m0;
    
    mat4 Br = betaLen / relR * r;  // scale radius by number of rings and relative radius
    ivec4 Br0 = ivec4(Br[0]), Br1 = ivec4(Br[1]), Br2 = ivec4(Br[2]), Br3 = ivec4(Br[3]);

    // (Br==0 ? beta0 : 0) + (Br==1 ? beta1 : 0) + (Br==2 ? beta2 : 0)
    mat4 height = mat4(
      beta0[0] * vec4(equal(Br0, iv0)) + beta1[0] * vec4(equal(Br0, iv1)) + beta2[0] * vec4(equal(Br0, iv2)),
      beta0[1] * vec4(equal(Br1, iv0)) + beta1[1] * vec4(equal(Br1, iv1)) + beta2[1] * vec4(equal(Br1, iv2)),
      beta0[2] * vec4(equal(Br2, iv0)) + beta1[2] * vec4(equal(Br2, iv1)) + beta2[2] * vec4(equal(Br2, iv2)),
      beta0[3] * vec4(equal(Br3, iv0)) + beta1[3] * vec4(equal(Br3, iv1)) + beta2[3] * vec4(equal(Br3, iv2))
    );
    mat4 mod1 = mat4( fract(Br[0]), fract(Br[1]), fract(Br[2]), fract(Br[3]) );
    return mult(height, bell(mod1, kmu, ksigma));
  }
  
  vec4 getSrc(in vec3 v, in ivec4 srcv) {  // get colors (vectorized) from source channels
    return
      v.r * vec4(equal(srcv, iv0)) + 
      v.g * vec4(equal(srcv, iv1)) +
      v.b * vec4(equal(srcv, iv2));
  }
  
  float getDst(in mat4 m, in ivec4 ch) {  // get color for destination channel
    return 
      dot(m[0], vec4(equal(dst0, ch))) + 
      dot(m[1], vec4(equal(dst1, ch))) + 
      dot(m[2], vec4(equal(dst2, ch))) + 
      dot(m[3], vec4(equal(dst3, ch)));
  }
  
  mat4 getVal(vec4 cell) {  // get values at given position
    vec3 val = cell.rgb;
    return mat4(
      getSrc(val, src0),
      getSrc(val, src1),
      getSrc(val, src2),
      getSrc(val, src3)
    );
  }
  
  ////////////////////////////////////////////////////////////////
  
  vec4 self, cell;
  mat4 sum = mat4(0), total = mat4(0), weight;
  
  int IncSum(int dx, int dy) {
    cell = GetCell(dx, dy, 0);
    //if(dx==0 && dy==0) self = cell;
    
    mat4 valSrc = getVal(cell);
    
    sum += mult(valSrc, weight);
    total += weight;
    
    return 0;
  }
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    tex3coord = ivec3(v_texcoord, 0);
    
    float r;
    int x, y;
    
    /*
    // non-optimized full-square scan
    for(x=-iR; x<=iR; x++) {
      for(y=-iR; y<=iR; y++) {
        r = length(x, y) / R;
        if(r>1.) continue;
        weight = getWeight(r);
        IncSum(x, y);
      }
    }
    */
    
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
      if(r>1.) continue;
      weight = getWeight(r);
      IncSum( x,  x);
      IncSum( x, -x);
      IncSum(-x,  x);
      IncSum(-x, -x);
    }
    
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
    
    mat4 avg = sum / (total + EPSILON);
    mat4 growth = mult(eta, bell(avg, mu, sigma) * 2. - 1.);
    vec3 growthDst = vec3( getDst(growth, iv0), getDst(growth, iv1), getDst(growth, iv2) );
    vec3 rgb = clamp(self.rgb + dT * growthDst, 0., 1.);
    
    //if(tex3coord.x<`+round(FW/4)+`) rgb = clamp(rgb - vec3(0.05,0,0), 0., 1.);
    
    glFragColor[0] = vec4(rgb, 1.);
  }
`;
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture"]);
//console.log(CalcFragmentShaderSource);