# jquery.fft [![Build Status](https://travis-ci.org/hotstaff/jquery.fft.svg?branch=master)](https://travis-ci.org/hotstaff/jquery.fft)
Simple and pure javascript FFT module.
The module do not require jQuery, and can be used with node.js.

# Instllation

### Direct download
Download the script [here](https://github.com/hotstaff/jquery.fft/blob/master/jquery.fft.js) and include it.
```html
<script src="/path/to/jquery.fft.js"></script>
```
```javascript
var fft = new FFT();
```

### Package Manager
The module supports [npm](https://www.npmjs.com/package/jquery.fft) under the name `jquery.fft`.
``` sh
npm install jquery.fft
```
```javascript
var FFT = require('jquery.fft');
var fft = new FFT();
```

# Simple Example
```javascript
var real = [1,1,1,1];  //this is input array
var imaginary = new Array(real.length); 
imaginary.fill(0);   

//FFT
var fft = new FFT(); 
fft.calc(1, real, imaginary);

// real = [4,0,0,0]
// imaginary = [0,0,0,0]
// NOTE: This "calc()" method rewrites orignal array.

//iFFT
fft.calc(-1, real, imaginary);
// real = [1,1,1,1]
// imaginary = [0,0,0,0]
```

# Amplitude, Power, Phase
```javascript
var real = [1,0,1,0];  //this is input array
var imaginary = new Array(real.length); 
imaginary.fill(0); 

// FFT
var fft = new FFT();
fft.calc(1, real, imaginary);
// real = [2,0,2,0]
// imaginary = [0,0,0,0]

// Amplitude
var amplitude = fft.amplitude(real, imaginary);
// amplitude = [2,0]

// Power
var power = fft.power(real, imaginary);
// power = [4,0]

// Phase
var phase = fft.phase(real, imaginary);
// phase = [0,0]

// NOTE: "amplitude()", "power()", "phase()" method return new array;
```

# frequencies, periods
```javascript
var real = [1,0,1,0];  //this is input array
var imaginary = new Array(real.length); 
imaginary.fill(0); 

var fft = new FFT();
fft.calc(1, real, imaginary);

// frequencies
// usage: frequencies(real, imaginary, samplingrate)

var frequencies = fft.frequencies(real, imaginary, 1); //samplingrate default is 1;
// frequencies = [0, 0.25] 
// NOTE: frequencies array length is N/2

// periods 
// "periods()" method returns 1/frequency array.
// usage: periods(real, imaginary, samplingrate)

var periods = fft.periods(real, imaginary, 1); //samplingrate default is 1;
// periods = [null, 4]
// NOTE: First value is always null; 
```

# License 

The MIT License (MIT)

Copyright (c) 2017 Hideto Manjo

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

