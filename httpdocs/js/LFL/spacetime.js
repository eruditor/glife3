function InitialFiller() {
  if(cfg.debug==1) {
    SetCell(5, 5, 0,   0, 0.9, 0, 1);
    SetCell(6, 5, 0,   0, 0.1, 0, 1);
    SetCell(5, 7, 0,   0, 0.5, 0, 1);
    //SetCell(8, 5, 0,   0, 0.5, 0, 1);
  }
  else if(cfg.shape=='orbium') {  // RD=13
    var orbium = [
      [0,0,0,0,0,0,0.1,0.14,0.1,0,0,0.03,0.03,0,0,0.3,0,0,0,0],
      [0,0,0,0,0,0.08,0.24,0.3,0.3,0.18,0.14,0.15,0.16,0.15,0.09,0.2,0,0,0,0],
      [0,0,0,0,0,0.15,0.34,0.44,0.46,0.38,0.18,0.14,0.11,0.13,0.19,0.18,0.45,0,0,0],
      [0,0,0,0,0.06,0.13,0.39,0.5,0.5,0.37,0.06,0,0,0,0.02,0.16,0.68,0,0,0],
      [0,0,0,0.11,0.17,0.17,0.33,0.4,0.38,0.28,0.14,0,0,0,0,0,0.18,0.42,0,0],
      [0,0,0.09,0.18,0.13,0.06,0.08,0.26,0.32,0.32,0.27,0,0,0,0,0,0,0.82,0,0],
      [0.27,0,0.16,0.12,0,0,0,0.25,0.38,0.44,0.45,0.34,0,0,0,0,0,0.22,0.17,0],
      [0,0.07,0.2,0.02,0,0,0,0.31,0.48,0.57,0.6,0.57,0,0,0,0,0,0,0.49,0],
      [0,0.59,0.19,0,0,0,0,0.2,0.57,0.69,0.76,0.76,0.49,0,0,0,0,0,0.36,0],
      [0,0.58,0.19,0,0,0,0,0,0.67,0.83,0.9,0.92,0.87,0.12,0,0,0,0,0.22,0.07],
      [0,0,0.46,0,0,0,0,0,0.7,0.93,1,1,1,0.61,0,0,0,0,0.18,0.11],
      [0,0,0.82,0,0,0,0,0,0.47,1,1,0.98,1,0.96,0.27,0,0,0,0.19,0.1],
      [0,0,0.46,0,0,0,0,0,0.25,1,1,0.84,0.92,0.97,0.54,0.14,0.04,0.1,0.21,0.05],
      [0,0,0,0.4,0,0,0,0,0.09,0.8,1,0.82,0.8,0.85,0.63,0.31,0.18,0.19,0.2,0.01],
      [0,0,0,0.36,0.1,0,0,0,0.05,0.54,0.86,0.79,0.74,0.72,0.6,0.39,0.28,0.24,0.13,0],
      [0,0,0,0.01,0.3,0.07,0,0,0.08,0.36,0.64,0.7,0.64,0.6,0.51,0.39,0.29,0.19,0.04,0],
      [0,0,0,0,0.1,0.24,0.14,0.1,0.15,0.29,0.45,0.53,0.52,0.46,0.4,0.31,0.21,0.08,0,0],
      [0,0,0,0,0,0.08,0.21,0.21,0.22,0.29,0.36,0.39,0.37,0.33,0.26,0.18,0.09,0,0,0],
      [0,0,0,0,0,0,0.03,0.13,0.19,0.22,0.24,0.24,0.23,0.18,0.13,0.05,0,0,0,0],
      [0,0,0,0,0,0,0,0,0.02,0.06,0.08,0.09,0.07,0.05,0.01,0,0,0,0,0]
    ];
    var cx = round(FW/2) - 13;
    var cy = round(FH/2) - 13;
    for(var sy in orbium) {
      for(var sx in orbium[sy]) {
        var x = cx + intval(sx);
        var y = cy + intval(sy);
        SetCell(x, y, 0,  0, orbium[sy][sx], 0, 1);
      }
    }
  }
  else if(cfg.shape=='1MTFAY') {  // RD=10
    var cells = [[
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0.07,0.17,0.05,0.38,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0.73,0.76,0.47,0.65,0,0,0,1.00,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0.69,0.61,0.51,0.38,0,0.22,0.29,0.73,0.02,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0.97,0.41,0.11,0.17,0.88,1.00,1.00,0.52,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,1.00,0.32,0.02,0.02,0.74,0.95,1.00,1.00,0.16,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0.27,1.00,0.08,0.20,0.63,0.82,0.94,1.00,1.00,0.08,0.17,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0.03,0.04,0.04,0.69,1.00,0.43,0.55,0.80,0.93,1.00,1.00,0.62,0.31,0.03,0,0,0,0,0],
      [0,0,0,0,0,0,0,1.00,0.53,0.02,0,1.00,0.99,0.75,0.89,0.98,1.00,1.00,1.00,0.12,0.02,0,0,0,0,0],
      [0,0,0,0,0,0,0,0.26,0.65,0.35,0.53,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,0.11,0.02,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0.72,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,0.07,0.01,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0.64,1.00,1.00,1.00,1.00,1.00,1.00,1.00,0.98,0.04,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0.02,0.03,0.32,0.79,1.00,1.00,1.00,1.00,1.00,1.00,0.34,0.01,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0.10,0.10,0.50,0.95,0.77,0.69,0.79,0.46,0.06,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0.02,0.09,0.27,0.29,0.09,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0.01,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]
    ],[
      [0,0,0,0,0,0,0,0,0,0.04,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0.40,0.64,0,0,0.97,0.01,0,1.00,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0.18,0.08,0,0.21,0.91,0.69,0.25,0,0.41,0.63,0.53,0.64,1.00,0.91,0.09,0,0,0,0,0,0,0],
      [0,0,0,0,0.22,0.49,0,0,0.02,0.44,0.98,0.66,1.00,1.00,1.00,1.00,1.00,0.08,0.88,0.35,0.21,0.02,0,0,0,0],
      [0,0,0,0.19,0.41,0.47,0.44,0.46,0.35,0.54,1.00,0.93,1.00,1.00,1.00,1.00,1.00,0.78,0.65,0.83,0.62,0.45,0.14,0,0,0],
      [0,0,0.10,0.51,0.19,0.38,0.77,1.00,0.71,0.60,0.57,0.46,0.41,1.00,0.93,0.92,1.00,1.00,1.00,1.00,1.00,0.77,0.53,0.11,0,0],
      [0,0.17,0.55,0.10,0.20,0.55,0.93,1.00,1.00,0.86,0.25,0.07,0,0,0,0.73,1.00,1.00,1.00,1.00,1.00,1.00,0.71,0.29,0,0],
      [0,0.43,0.61,0.38,0.40,0.76,1.00,1.00,1.00,1.00,0.15,0,0,0,0,0.27,0.80,0.99,1.00,1.00,1.00,1.00,0.77,0.44,0.07,0],
      [0,0.37,0.67,0.69,0.86,0.98,0.99,1.00,1.00,1.00,0.07,0,0,0,0,0,0.40,0.81,1.00,1.00,1.00,1.00,0.82,0.55,0.19,0],
      [0,0,0.86,0.82,0.95,1.00,1.00,1.00,0.85,0.65,0.01,0,0,0,0,0,0.12,0.68,0.97,1.00,1.00,1.00,0.93,0.65,0.33,0.01],
      [0,0,0.53,0.95,1.00,1.00,1.00,1.00,0.58,0.23,0,0,0,0,0,0,0,0.68,0.95,1.00,1.00,1.00,1.00,0.78,0.44,0.06],
      [0.21,0,0,0.98,1.00,1.00,1.00,1.00,0.04,0,0,0,0,0,0,0,0,0.69,0.96,1.00,1.00,1.00,1.00,0.89,0.47,0.10],
      [0,0.67,0,0.52,1.00,1.00,1.00,1.00,0.19,0,0,0,0,0,0,0,0.25,0.83,1.00,1.00,1.00,1.00,1.00,0.84,0.43,0.07],
      [0,0,0.31,0.49,1.00,1.00,1.00,1.00,0.80,0,0,0.42,0.06,0,0,0.31,0.71,0.97,1.00,1.00,1.00,1.00,1.00,0.71,0.36,0.02],
      [0,0,0,0,1.00,1.00,1.00,1.00,1.00,0.93,0.70,0.84,0.95,0.86,0.85,0.91,0.98,1.00,1.00,1.00,1.00,1.00,0.91,0.56,0.24,0],
      [0,0,0,0.24,0.98,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,0.68,0.41,0.07,0],
      [0,0,0,0.28,0.32,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,0.74,0.48,0.19,0,0],
      [0,0,0,0,0,0.16,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,0.99,0.72,0.48,0.24,0.01,0,0],
      [0,0,0,0,0,0.25,0.50,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,0.94,0.61,0.43,0.23,0.02,0,0,0],
      [0,0,0,0,0,0,0.29,0.62,1.00,1.00,1.00,1.00,1.00,1.00,1.00,1.00,0.84,0.68,0.53,0.35,0.16,0,0,0,0,0],
      [0,0,0,0,0,0,0,0.21,0.45,0.53,0.61,0.71,0.77,0.75,0.66,0.57,0.49,0.42,0.31,0.06,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0.15,0.31,0.41,0.45,0.43,0.39,0.33,0.22,0.10,0.01,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0.03,0.04,0.03,0,0,0,0,0,0,0,0,0,0,0]
    ],[
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0.03,0.02,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0.80,0.48,0.01,0,0.07,0.12,0.04,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,1.00,0.54,1.00,1.00,0.74,0.26,0.24,0.30,0.19,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0.12,0.43,0,0,0.76,1.00,1.00,1.00,1.00,1.00,0.35,0.11,0,0,0],
      [0.05,0,0,0,0,0,0,0,0,0,0,0.31,0.05,0,0,0.36,1.00,1.00,1.00,1.00,1.00,0.45,0.29,0,0,0],
      [0,0,0,0,0,0,0,0.70,0,0,0.62,0.05,0,0,0,0,0.96,1.00,1.00,1.00,1.00,0.49,0.47,0.09,0,0],
      [0,0,0,0,0,0,0,0.65,0.69,0,0.11,0.16,0,0,0,0,0.67,1.00,1.00,1.00,1.00,0.50,0.57,0.23,0,0],
      [0,0,0,0,0,0,0.51,0.78,0.79,0,0,0,0,0,0,0,0.37,0.96,1.00,1.00,1.00,0.58,0.59,0.39,0.02,0],
      [0,0,0,0,0,0,0.58,0.87,0.90,0.75,0,0,0,0,0,0,0,0.54,1.00,1.00,1.00,0.63,0.63,0.45,0.01,0],
      [0,0,0,0,0.04,0.02,0.24,0.91,0.89,1.00,0.80,0,0,0,0,0,0,0.69,1.00,1.00,1.00,0.76,0.67,0.40,0,0],
      [0,0,0,0,0,0,0.33,0.71,0.61,0.96,1.00,0.67,0,0,0,0,0,0.66,0.98,1.00,1.00,0.84,0.49,0.23,0,0],
      [0,0,0,0,0,0,0.09,0.55,0.69,0.87,1.00,1.00,0.17,0,0,0,0,0.62,0.95,1.00,0.93,0.65,0.31,0.11,0,0],
      [0,0,0,0,0,0,0,0.28,0.70,0.71,1.00,1.00,0.40,0,0.61,0.38,0.06,0.33,0.57,0.83,0.72,0.27,0.15,0.02,0,0],
      [0,0,0,0,0,0,0,0,0.67,0.95,0.97,1.00,0.56,0.55,0.62,0.48,0.75,0.28,0.37,0.74,0.25,0.13,0.04,0,0,0],
      [0,0,0,0,0,0,0,0.05,0.36,0.85,1.00,0.93,0.81,0.73,0.30,0.05,0,0.12,0.22,0.18,0.10,0.02,0,0,0,0],
      [0,0,0,0,0,0,0,0.05,0.24,0.34,0.23,0.02,0,0,0,0,0,0,0,0.01,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0.07,0.06,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]
    ]];
    var cx = round(FW/2) + 13;
    var cy = round(FH/2) - 13;
    for(var sy in cells[0]) {
      for(var sx in cells[0][sy]) {
        var x = cx - intval(sx);
        var y = cy + intval(sy);
        SetCell(x, y, 0,  cells[0][sy][sx], cells[1][sy][sx], cells[2][sy][sx], 1);
      }
    }
  }
  else if(cfg.debug==3) {
    for(var x=0; x<FW; x++) {
      for(var y=0; y<FH; y++) {
        var rr = Math.sqrt( (sqr(x/FW-0.5) + sqr(y/FH-0.5)) * 4) / LF;
        var density = round( (1 - rr) * 10000 * LD );
        if(rndF(0,10000)<density) {
          SetCell(x, y, 0,  0, rndF(1,100)/100, 0, 1);
        }
      }
    }
  }
  else if(cfg.shape=='low') {
    for(var x=0; x<FW; x++) {
      for(var y=0; y<FH; y++) {
        var rr = Math.sqrt( (sqr(x/FW-0.5) + sqr(y/FH-0.5)) * 4) / LF;
        var density = round( (1 - rr) * 10000 * LD );
        if(rndF(0,10000)<density) {
          SetCell(x, y, 0,  rndF(1,100)/200, rndF(1,100)/200, 0, 1);
        }
      }
    }
  }
  else if(cfg.shape=='green') {
    for(var x=0; x<FW; x++) {
      for(var y=0; y<FH; y++) {
        var rr = Math.sqrt( (sqr(x/FW-0.5) + sqr(y/FH-0.5)) * 4) / LF;
        var density = round( (1 - rr) * 10000 * LD );
        if(rndF(0,10000)<density) {
          SetCell(x, y, 0,  0, rndF(1,100)/100, 0, 1);
        }
      }
    }
  }
  else if(cfg.shape=='2color') {
    for(var x=0; x<FW; x++) {
      for(var y=0; y<FH; y++) {
        var rr = Math.sqrt( (sqr(x/FW-0.5) + sqr(y/FH-0.5)) * 4) / LF;
        var density = round( (1 - rr) * 10000 * LD );
        if(rndF(0,10000)<density) {
          SetCell(x, y, 0,  rndF(1,100)/100, rndF(1,100)/100, 0, 1);
        }
      }
    }
  }
  else {
    for(var x=0; x<FW; x++) {
      for(var y=0; y<FH; y++) {
        var rr = Math.sqrt( (sqr(x/FW-0.5) + sqr(y/FH-0.5)) * 4) / LF;
        var density = round( (1 - rr) * 10000 * LD );
        if(rndF(0,10000)<density) {
          SetCell(x, y, 0,  rndF(1,100)/100, rndF(1,100)/100, rndF(1,100)/100, 1);
        }
      }
    }
  }
}
