/*!
 * jQuery FFT Plugin v1.0.3
 * This code is javascript converted version of following VBA source:
 * Tetsuya Kawamura 川村 哲也,
 * エンジニアのためのExcelナビシリーズ 数値計算入門, page 194, (2012)
 * ISBN 987-4-9011092-81-4
 *
 * Copyright 2017 Hideto Manjo
 * Released under the MIT license
 */

 /*jslint
    bitwise, node, browser, for, this
 */

 /*global
    define
 */

;
(function (root, factory) {
    "use strict";
    if (typeof define === "function" && define.amd) {
        define(factory);
    } else if (typeof exports === "object") {
        module.exports = factory();
    } else {
        root.FFT = factory();
    }
}(this, function () {
    "use strict";
    var FFT = function FFT(settings) {
        this.settings = settings || {samplingrate: 1};
    };

    FFT.prototype = {
        dim: function dim(F1, F2) {
            var N = F1.length;

            //limited 2^x
            while (true) {
                if (!(N & (N - 1))) {
                    break;
                }
                N = N + 1;
                F1.push(0);
                F2.push(0);
            }
            return N;
        },
        loop: function loop(N, callback) {
            var m = N / 2;
            var ans = [];
            var i;

            for (i = 0; i < m; i = i + 1) {
                ans.push(callback.call(null, i));
            }
            return ans;
        },
        amplitude: function amplitude(F1, F2) {
            return this.loop(this.dim(F1, F2), function (i) {
                return Math.sqrt(F1[i] * F1[i] + F2[i] * F2[i]);
            });
        },
        power: function power(F1, F2) {
            return this.loop(this.dim(F1, F2), function (i) {
                return F1[i] * F1[i] + F2[i] * F2[i];
            });
        },
        phase: function phase(F1, F2) {
            return this.loop(this.dim(F1, F2), function (i) {
                return Math.atan2(F2[i], F1[i]);
            });
        },
        frequencies: function frequencies(F1, F2, samplingrate) {
            var sps = samplingrate || this.settings.samplingrate;
            var N = this.dim(F1, F2);
            return this.loop(N, function (i) {
                return sps * (i) / N;
            });
        },
        periods: function periods(F1, F2, samplingrate) {
            var sps = samplingrate || this.settings.samplingrate;
            var N = this.dim(F1, F2);
            return this.loop(N, function (i) {
                return N / (sps * (i));
            });
        },
        calc: function calc(SW, F1, F2) {
            var N = this.dim(F1, F2);
            var WN = 2 * Math.PI / N;
            var INDEX = Math.log2(N);
            var DX = 1 / N;
            var DX2 = 1.0 / Math.sqrt(N);
            var m = N;

            var A1;
            var A2;
            var B1;
            var B2;
            var W1;
            var W2;
            var C;
            var S;
            var T;

            var i;
            var j;
            var k;
            var l;
            var jl;
            var jm;
            var m1;
            var kl;

            for (l = 0; l < INDEX; l = l + 1) {
                T = 0;
                m1 = m;
                m = m / 2;

                for (k = 0; k < m; k = k + 1) {
                    kl = k - m1;
                    C = Math.cos(T);
                    S = -SW * Math.sin(T);
                    T = T + WN;

                    for (j = m1; j < N + 1; j = j + m1) {
                        jl = j + kl;
                        jm = jl + m;
                        A1 = F1[jl];
                        A2 = F1[jm];
                        B1 = F2[jl];
                        B2 = F2[jm];
                        F1[jl] = A1 + A2;
                        F2[jl] = B1 + B2;
                        F1[jm] = (A1 - A2) * C + (B1 - B2) * S;
                        F2[jm] = (B1 - B2) * C - (A1 - A2) * S;
                    }
                }
                WN = 2 * WN;
            }

            j = 0;
            for (i = 0; i < N - 1; i = i + 1) {

                if (i < j) {
                    W1 = F1[j];
                    W2 = F2[j];
                    F1[j] = F1[i];
                    F2[j] = F2[i];
                    F1[i] = W1;
                    F2[i] = W2;
                }

                k = N / 2;
                while (true) {
                    if (k > j) {
                        break;
                    }
                    j = j - k;
                    k = k / 2;
                }
                j = j + k;
            }
            
            /*
            if (SW < 0) {
                for (i = 0; i < N; i = i + 1) {
                    F1[i] = DX * F1[i];
                    F2[i] = DX * F2[i];
                }
            }
            */
            
            for (i = 0; i < N; i = i + 1) {
                F1[i] = DX2 * F1[i];
                F2[i] = DX2 * F2[i];
            }
            
            return [F1, F2];
        }
    };
    // export
    return FFT;
}));
