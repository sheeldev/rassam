function saveCheckPoint() {
    checkPoint = context.getImageData(0, 0, canvas.width, canvas.height);
}
function loadCheckPoint() {
    context.putImageData(checkPoint, 0, 0);
}
function restore() {
    context.clearRect(0, 0, canvas.width, canvas.height);
    initCanvas();
    initTempCanvas();
    current = "Original";
    degrees = 0;
    $(".range").each(function() {
        $(this).val(0);
    });
    $('#s-range').val(1);
    saveCheckPoint();
    canvasPos();
}
function applyFilter(filter) {
    if (!isStreaming)
        loadCheckPoint();
    current = filter;
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height),
        rgba = imageData.data,
        length = rgba.length;
    for (var i = 0; i < length; i += 4) {
        rgba[i] = filters[filter].r[rgba[i]];
        rgba[i + 1] = filters[filter].g[rgba[i + 1]];
        rgba[i + 2] = filters[filter].b[rgba[i + 2]];
    }
    imageData.data = rgba;
    context.putImageData(imageData, 0, 0);
}
var degrees = 0;
function applyRotation() {
    degrees += 90
    if (degrees >= 360)
        degrees = 0;
    if (degrees === 0 || degrees === 180) {
        canvas.width = tempCanvas.width;
        canvas.height = tempCanvas.height;
    } else {
        canvas.width = tempCanvas.height;
        canvas.height = tempCanvas.width;
    }
    context.save();
    context.translate(canvas.width / 2, canvas.height / 2);
    context.rotate(degrees * Math.PI / 180);
    context.drawImage(tempCanvas, -tempCanvas.width * 0.5, -tempCanvas.height * 0.5);
    context.restore();
    canvasPos();
}
function applyFlipH() {
    context.save();
    context.translate(tempCanvas.width, 0);
    context.scale(-1, 1);
    context.drawImage(tempCanvas, 0, 0, tempCanvas.width, tempCanvas.height, 0, 0, canvas.width, canvas.height);
    context.restore();
}
function applyBrightness(adjustment) {
    adjustment = parseInt(adjustment, 10);
    brightnessV = adjustment;
    if (!isStreaming)
        loadCheckPoint();
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height),
        rgba = imageData.data,
        length = rgba.length;
    for (var i = 0; i < length; i += 4) {
        rgba[i] += adjustment;
        rgba[i + 1] += adjustment;
        rgba[i + 2] += adjustment;
    }
    imageData.data = rgba;
    context.putImageData(imageData, 0, 0);
}
function applyContrast(contrast) {
    contrast = parseInt(contrast, 10);
    contrastV = contrast;
    if (!isStreaming)
        loadCheckPoint();
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height),
        rgba = imageData.data,
        length = rgba.length;
    var factor = (259 * (contrast + 255)) / (255 * (259 - contrast));
    for (var i = 0; i < length; i += 4) {
        rgba[i] = factor * (rgba[i] - 128) + 128;
        rgba[i + 1] = factor * (rgba[i + 1] - 128) + 128;
        rgba[i + 2] = factor * (rgba[i + 2] - 128) + 128;
    }
    imageData.data = rgba;
    context.putImageData(imageData, 0, 0);
}
function applySaturation(saturation) {
    saturation = parseFloat(saturation);
    saturationV = saturation;
    if (!isStreaming)
        loadCheckPoint();
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    var dA = imageData.data;
    var sv = saturation;
    var luR = 0.3086;
    var luG = 0.6094;
    var luB = 0.0820;
    var az = (1 - sv) * luR + sv;
    var bz = (1 - sv) * luG;
    var cz = (1 - sv) * luB;
    var dz = (1 - sv) * luR;
    var ez = (1 - sv) * luG + sv;
    var fz = (1 - sv) * luB;
    var gz = (1 - sv) * luR;
    var hz = (1 - sv) * luG;
    var iz = (1 - sv) * luB + sv;
    for (var i = 0; i < dA.length; i += 4) {
        var red = dA[i];
        var green = dA[i + 1];
        var blue = dA[i + 2];
        var saturatedRed = (az * red + bz * green + cz * blue);
        var saturatedGreen = (dz * red + ez * green + fz * blue);
        var saturateddBlue = (gz * red + hz * green + iz * blue);
        dA[i] = saturatedRed;
        dA[i + 1] = saturatedGreen;
        dA[i + 2] = saturateddBlue;
    }
    imageData.data = dA;
    context.putImageData(imageData, 0, 0);
}
function applyNoise(value) {
    value = parseInt(value, 10);
    noiseV = value;
    if (!isStreaming)
        loadCheckPoint();
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    data = imageData.data;
    for (var i = 0; i < data.length; i += 4) {
        noise = value - Math.random() * value / 2;
        data[i] += noise;
        data[i + 1] += noise;
        data[i + 2] += noise;
    }
    imageData.data = data;
    context.putImageData(imageData, 0, 0);
}
function applySharpen(mix) {
    loadCheckPoint();
    mix = parseFloat(mix);
    var w = canvas.width;
    var h = canvas.height;
    var weights = [0, -1, 0, -1, 5, -1, 0, -1, 0],
        katet = Math.round(Math.sqrt(weights.length)),
        half = (katet * 0.5) | 0,
        dstData = context.createImageData(w, h),
        dstBuff = dstData.data,
        srcBuff = context.getImageData(0, 0, w, h).data,
        y = h;
    while (y--) {
        x = w;
        while (x--) {
            var sy = y,
                sx = x,
                dstOff = (y * w + x) * 4,
                r = 0,
                g = 0,
                b = 0,
                a = 0;
            for (var cy = 0; cy < katet; cy++) {
                for (var cx = 0; cx < katet; cx++) {
                    var scy = sy + cy - half;
                    var scx = sx + cx - half;
                    if (scy >= 0 && scy < h && scx >= 0 && scx < w) {
                        var srcOff = (scy * w + scx) * 4;
                        var wt = weights[cy * katet + cx];
                        r += srcBuff[srcOff] * wt;
                        g += srcBuff[srcOff + 1] * wt;
                        b += srcBuff[srcOff + 2] * wt;
                        a += srcBuff[srcOff + 3] * wt;
                    }
                }
            }
            dstBuff[dstOff] = r * mix + srcBuff[dstOff] * (1 - mix);
            dstBuff[dstOff + 1] = g * mix + srcBuff[dstOff + 1] * (1 - mix);
            dstBuff[dstOff + 2] = b * mix + srcBuff[dstOff + 2] * (1 - mix)
            dstBuff[dstOff + 3] = srcBuff[dstOff + 3];
        }
    }
    context.putImageData(dstData, 0, 0);
}
function effectBlurImage(radius, blurAlphaChannel) {
    loadCheckPoint();
    if (isNaN(radius) || radius < 1)
        return;
    if (blurAlphaChannel)
        effectBlurCanvasRGBA(0, 0, radius);
    else
        effectBlurCanvasRGB(0, 0, radius);
}
var blurRadius = 32;
var radiusFactor = 1.5;
var divider = radiusFactor;
var startRadius = blurRadius / divider;
var steps = 3;
function applyFrameBlur(value) {
    var gradientPixels = getLinearGradientMap(canvas.width, canvas.height, canvas.width * 0.5, canvas.height * 0.5, -Math.PI * 0.1, canvas.width * 2, true);
    var outerRadius = Math.sqrt(Math.pow(canvas.width / 2, 2) + Math.pow(canvas.height / 2, 2));
    blurRadius = parseInt(value, 10);
    radiusFactor = 1.5;
    divider = radiusFactor;
    for (var i = 1; i < steps; i++) {
        divider += Math.pow(radiusFactor, i + 1);
    }
    startRadius = blurRadius / divider;
    compoundBlurImage(gradientPixels, startRadius, radiusFactor, steps, true);
}
function applyVignette() {
    context.save();
    var gradient,
        outerRadius = Math.sqrt(Math.pow(canvas.width / 2, 2) + Math.pow(canvas.height / 2, 2));
    context.globalCompositeOperation = 'source-over';
    gradient = context.createRadialGradient(canvas.width / 2, canvas.height / 2, 0, canvas.width / 2, canvas.height / 2, outerRadius);
    gradient.addColorStop(0, 'rgba(0, 0, 0, 0)');
    gradient.addColorStop(0.5, 'rgba(0, 0, 0, 0.3)');
    gradient.addColorStop(1, 'rgba(0, 0, 0, 0.6)');
    context.fillStyle = gradient;
    context.fillRect(0, 0, canvas.width, canvas.height);
    context.globalCompositeOperation = 'lighter';
    gradient = context.createRadialGradient(canvas.width / 2, canvas.height / 2, 0, canvas.width / 2, canvas.height / 2, outerRadius);
    gradient.addColorStop(0, 'rgba(255, 255, 255, 0.1)');
    gradient.addColorStop(0.5, 'rgba(255, 255, 255, 0)');
    gradient.addColorStop(1, 'rgba(0, 0, 0, 0)');
    context.fillStyle = gradient;
    context.fillRect(0, 0, canvas.width, canvas.height);
    context.restore();
}
function applyGrayscale() {
    effect = "Grayscale";
    if (!isStreaming)
        loadCheckPoint();
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    var dataArray = imageData.data;
    for (var i = 0; i < dataArray.length; i += 4) {
        var red = dataArray[i];
        var green = dataArray[i + 1];
        var blue = dataArray[i + 2];
        var alpha = dataArray[i + 3];
        var gray = (red + green + blue) / 3;
        dataArray[i] = gray;
        dataArray[i + 1] = gray;
        dataArray[i + 2] = gray;
        dataArray[i + 3] = alpha;
    }
    imageData.data = dataArray;
    context.putImageData(imageData, 0, 0);
}
function applySepia() {
    effect = "Sepia";
    if (!isStreaming)
        loadCheckPoint();
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    var dataArray = imageData.data;
    for (var i = 0; i < dataArray.length; i += 4) {
        var red = dataArray[i];
        var green = dataArray[i + 1];
        var blue = dataArray[i + 2];
        var alpha = dataArray[i + 3];
        var outRed = (red * .393) + (green * .769) + (blue * .189);
        var outGreen = (red * .349) + (green * .686) + (blue * .168);
        var outBlue = (red * .272) + (green * .534) + (blue * .131);
        dataArray[i] = outRed < 255 ? outRed : 255;
        dataArray[i + 1] = outGreen < 255 ? outGreen : 255;
        dataArray[i + 2] = outBlue < 255 ? outBlue : 255
        dataArray[i + 3] = alpha;
    }
    imageData.data = dataArray;
    context.putImageData(imageData, 0, 0);
}
function applyInvert() {
    effect = "Invert";
    if (!isStreaming)
        loadCheckPoint();
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    var dataArr = imageData.data;
    for (var i = 0; i < dataArr.length; i += 4) {
        var r = dataArr[i];
        var g = dataArr[i + 1];
        var b = dataArr[i + 2];
        var a = dataArr[i + 3];
        var invertedRed = 255 - r;
        var invertedGreen = 255 - g;
        var invertedBlue = 255 - b;
        dataArr[i] = invertedRed;
        dataArr[i + 1] = invertedGreen;
        dataArr[i + 2] = invertedBlue;
    }
    imageData.data = dataArr;
    context.putImageData(imageData, 0, 0);
}
function applyR(value) {
    value = parseInt(value, 10);
    loadCheckPoint();
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    data = imageData.data;
    for (var i = 0; i < data.length; i += 4) {
        data[i] = 255 - ((255 - data[i]) * (255 - value * 1) / 255);
    }
    imageData.data = data;
    context.putImageData(imageData, 0, 0);
}
function applyG(value) {
    value = parseInt(value, 10);
    loadCheckPoint();
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    data = imageData.data;
    for (var i = 0; i < data.length; i += 4) {
        data[i + 1] = 255 - ((255 - data[i + 1]) * (255 - value * 1) / 255);
    }
    imageData.data = data;
    context.putImageData(imageData, 0, 0);
}
function applyB(value) {
    value = parseInt(value, 10);
    loadCheckPoint();
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    data = imageData.data;
    for (var i = 0; i < data.length; i += 4) {
        data[i + 2] = 255 - ((255 - data[i + 2]) * (255 - value * 1) / 255);
    }
    imageData.data = data;
    context.putImageData(imageData, 0, 0);
}
function applyCrop(value) {
    context.save();
    context.clearRect(0, 0, canvas.width, canvas.height);
    value = parseInt(value, 10);
    var sourceX = 0;
    var sourceY = 0;
    if (value > 0) {
        sourceX = tempCanvas.width / value;
        sourceY = tempCanvas.height / value;
    }
    var sourceWidth = tempCanvas.width - sourceX * 2;
    var sourceHeight = tempCanvas.height - sourceY * 2;
    var destX = 0;
    var destY = 0;
    var destWidth = canvas.width;
    var destHeight = canvas.height;
    context.drawImage(tempCanvas, sourceX, sourceY, sourceWidth, sourceHeight, destX, destY, destWidth, destHeight);
    context.restore();
}
function applyFrame(frame) {
    loadCheckPoint();
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height),
        data = imageData.data;
    var mask = new Image();
    mask.src = "assets/img/masks/" + frame + ".jpg";
    var maskData;
    mask.onload = function() {
        var tempCanvas = document.createElement('canvas');
        var width = canvas.width;
        var height = canvas.height;
        tempCanvas.width = width;
        tempCanvas.height = height;
        var tempContext = tempCanvas.getContext("2d");
        tempContext.drawImage(mask, 0, 0, mask.width, mask.height, 0, 0, width, height);
        var tempData = tempContext.getImageData(0, 0, tempCanvas.width, tempCanvas.height);
        maskData = tempData.data;
        for (var i = 0; i < data.length; i += 4) {
            data[i] = data[i] * maskData[i] / 255;
            data[i + 1] = data[i + 1] * maskData[i + 1] / 255;
            data[i + 2] = data[i + 2] * maskData[i + 2] / 255;
        }
        imageData.data = data;
        context.putImageData(imageData, 0, 0);
    }
}
function applySpecial(special) {
    loadCheckPoint();
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height),
        data = imageData.data;
    var mask = new Image();
    mask.src = "assets/img/special/" + special + ".jpg";
    var maskData;
    mask.onload = function() {
        var tempCanvas = document.createElement('canvas');
        var width = canvas.width;
        var height = canvas.height;
        tempCanvas.width = width;
        tempCanvas.height = height;
        var tempContext = tempCanvas.getContext("2d");
        tempContext.drawImage(mask, 0, 0, mask.width, mask.height, 0, 0, width, height);
        var tempData = tempContext.getImageData(0, 0, tempCanvas.width, tempCanvas.height);
        maskData = tempData.data;
        for (var i = 0; i < data.length; i += 4) {
            data[i] = data[i] * maskData[i] / 255;
            data[i + 1] = data[i + 1] * maskData[i + 1] / 255;
            data[i + 2] = data[i + 2] * maskData[i + 2] / 255;
        }
        imageData.data = data;
        context.putImageData(imageData, 0, 0);
    }
}
function applyTexture(special) {
    loadCheckPoint();
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height),
        data = imageData.data;
    var mask = new Image();
    mask.src = "assets/img/textures/" + special + ".jpg";
    var maskData;
    mask.onload = function() {
        var tempCanvas = document.createElement('canvas');
        var width = canvas.width;
        var height = canvas.height;
        tempCanvas.width = width;
        tempCanvas.height = height;
        var tempContext = tempCanvas.getContext("2d");
        tempContext.drawImage(mask, 0, 0, mask.width, mask.height, 0, 0, width, height);
        var tempData = tempContext.getImageData(0, 0, tempCanvas.width, tempCanvas.height);
        maskData = tempData.data;
        for (var i = 0; i < data.length; i += 4) {
            data[i] = data[i] * maskData[i] / 255;
            data[i + 1] = data[i + 1] * maskData[i + 1] / 255;
            data[i + 2] = data[i + 2] * maskData[i + 2] / 255;
        }
        imageData.data = data;
        context.putImageData(imageData, 0, 0);
    }
}
var mul_table = [512, 512, 456, 512, 328, 456, 335, 512, 405, 328, 271, 456, 388, 335, 292, 512, 454, 405, 364, 328, 298, 271, 496, 456, 420, 388, 360, 335, 312, 292, 273, 512, 482, 454, 428, 405, 383, 364, 345, 328, 312, 298, 284, 271, 259, 496, 475, 456, 437, 420, 404, 388, 374, 360, 347, 335, 323, 312, 302, 292, 282, 273, 265, 512, 497, 482, 468, 454, 441, 428, 417, 405, 394, 383, 373, 364, 354, 345, 337, 328, 320, 312, 305, 298, 291, 284, 278, 271, 265, 259, 507, 496, 485, 475, 465, 456, 446, 437, 428, 420, 412, 404, 396, 388, 381, 374, 367, 360, 354, 347, 341, 335, 329, 323, 318, 312, 307, 302, 297, 292, 287, 282, 278, 273, 269, 265, 261, 512, 505, 497, 489, 482, 475, 468, 461, 454, 447, 441, 435, 428, 422, 417, 411, 405, 399, 394, 389, 383, 378, 373, 368, 364, 359, 354, 350, 345, 341, 337, 332, 328, 324, 320, 316, 312, 309, 305, 301, 298, 294, 291, 287, 284, 281, 278, 274, 271, 268, 265, 262, 259, 257, 507, 501, 496, 491, 485, 480, 475, 470, 465, 460, 456, 451, 446, 442, 437, 433, 428, 424, 420, 416, 412, 408, 404, 400, 396, 392, 388, 385, 381, 377, 374, 370, 367, 363, 360, 357, 354, 350, 347, 344, 341, 338, 335, 332, 329, 326, 323, 320, 318, 315, 312, 310, 307, 304, 302, 299, 297, 294, 292, 289, 287, 285, 282, 280, 278, 275, 273, 271, 269, 267, 265, 263, 261, 259];
var shg_table = [9, 11, 12, 13, 13, 14, 14, 15, 15, 15, 15, 16, 16, 16, 16, 17, 17, 17, 17, 17, 17, 17, 18, 18, 18, 18, 18, 18, 18, 18, 18, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24];
function Blureffect() {
    this.r = 0;
    this.g = 0;
    this.b = 0;
    this.a = 0;
    this.next = null;
}
