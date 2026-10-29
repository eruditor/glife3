// x, y, kx, ky, width, amplitude psi1, amplitude psi2, phase1, phase2
const KLP_PACKET_COUNT = GetIntParam('n', 32);
var KLPPackets = [];

function KLPRandom(a, b) {
  return a + (b-a) * rndF(0, 1000000) / 1000000;
}

function KLPCreatePackets() {
  var packets = [];
  for(var n=0; n<KLP_PACKET_COUNT; n++) {
    var angle = KLPRandom(0, 2*Math.PI);
    var waveNumber = KLPRandom(0.30, 0.65);
    var type = rndF(0, 3);
    packets.push([
      KLPRandom(0, FW), KLPRandom(0, FH),
      waveNumber*Math.cos(angle), waveNumber*Math.sin(angle),
      KLPRandom(5.0, 8.0),
      type==1 ? 0 : KLPRandom(0.45, 0.65),
      type==0 ? 0 : KLPRandom(0.45, 0.65),
      KLPRandom(0, 2*Math.PI),
      KLPRandom(0, 2*Math.PI),
    ]);
  }
  return packets;
}

if(FD!=1 || RB<4 || FW%2 || FH%2 || KLP_PACKET_COUNT<1) {
  throw new Error('KLP requires FD=1, RB>=4, even field size and n>=1');
}

function InitialFiller() {
  for(var y=0; y<FH; y++) {
    for(var x=0; x<FW; x++) {
      var psi1r = 0, psi1i = 0;
      var psi2r = 0, psi2i = 0;

      for(var packet of KLPPackets) {
        var dx = x-packet[0], dy = y-packet[1];
        if(dx> FW/2) dx -= FW;  if(dx < -FW/2) dx += FW;
        if(dy> FH/2) dy -= FH;  if(dy < -FH/2) dy += FH;

        var width = packet[4];
        var envelope = Math.exp(-(dx*dx + dy*dy) / (2*width*width));
        var phase = packet[2]*dx + packet[3]*dy;

        psi1r += packet[5] * envelope * Math.cos(phase + packet[7]);
        psi1i += packet[5] * envelope * Math.sin(phase + packet[7]);
        psi2r += packet[6] * envelope * Math.cos(phase + packet[8]);
        psi2i += packet[6] * envelope * Math.sin(phase + packet[8]);
      }

      SetCell(x, y, 0, psi1r, psi1i, psi2r, psi2i);
    }
  }
}

var KLPInitSpacetime = window.InitSpacetime;
window.InitSpacetime = function() {
  KLPPackets = KLPCreatePackets();
  KLPInitSpacetime();
};
