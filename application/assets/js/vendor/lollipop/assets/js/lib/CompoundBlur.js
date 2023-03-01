function compoundBlurImage(radiusData, minRadius, increaseFactor, blurLevels, blurAlphaChannel) {
    var w = canvas.width;
    var h = canvas.height;
    loadCheckPoint();
    if (isNaN(minRadius) || minRadius <= 0 || isNaN(increaseFactor) || increaseFactor == 0)
        return;
    if (blurAlphaChannel)
        compundBlurCanvasRGBA(0, 0, w, h, radiusData, minRadius, increaseFactor, blurLevels);
    else
        compundBlurCanvasRGB(0, 0, w, h, radiusData, minRadius, increaseFactor, blurLevels);
}
function getLinearGradientMap(width, height, centerX, centerY, angle, length, mirrored) {
    var cnv = document.createElement('canvas');
    cnv.width = width;
    cnv.height = height;
    var x1 = centerX + Math.cos(angle) * length * 0.5;
    var y1 = centerY + Math.sin(angle) * length * 0.5;
    var x2 = centerX - Math.cos(angle) * length * 0.5;
    var y2 = centerY - Math.sin(angle) * length * 0.5;
    var context = cnv.getContext("2d");
    var gradient = context.createLinearGradient(x1, y1, x2, y2);
    if (!mirrored) {
        gradient.addColorStop(0, "white");
        gradient.addColorStop(1, "black");
    } else {
        gradient.addColorStop(0, "white");
        gradient.addColorStop(0.5, "black");
        gradient.addColorStop(1, "white");
    }
    context.fillStyle = gradient;
    context.fillRect(0, 0, width, height);
    return context.getImageData(0, 0, width, height);
}
function getRadialGradientMap(width, height, centerX, centerY, radius1, radius2) {
    var cnv = document.createElement('canvas');
    cnv.width = width;
    cnv.height = height;
    var context = cnv.getContext("2d");
    var gradient = context.createRadialGradient(centerX, centerY, radius1, centerX, centerY, radius2);
    gradient.addColorStop(1, "white");
    gradient.addColorStop(0, "black");
    context.fillStyle = gradient;
    context.fillRect(0, 0, width, height);
    return context.getImageData(0, 0, width, height);
}
function compundBlurCanvasRGB(top_x, top_y, width, height, radiusData, minRadius, increaseFactor, blurLevels) {
    if (isNaN(minRadius) || minRadius <= 0 || isNaN(increaseFactor) || increaseFactor == 0)
        return;
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    renderCompundBlurRGB(imageData, radiusData, width, height, minRadius, increaseFactor, blurLevels);
    context.putImageData(imageData, top_x, top_y);
}
function compundBlurCanvasRGBA(top_x, top_y, width, height, radiusData, minRadius, increaseFactor, blurLevels) {
    if (isNaN(minRadius) || minRadius <= 0 || isNaN(increaseFactor) || increaseFactor == 0)
        return;
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    renderCompundBlurRGBA(imageData, radiusData, width, height, minRadius, increaseFactor, blurLevels);
    context.putImageData(imageData, top_x, top_y);
}
function renderCompundBlurRGB(imageData, radiusData, width, height, radius, increaseFactor, blurLevels) {
    var x,
        y,
        i,
        p,
        yp,
        yi,
        yw,
        r_sum,
        g_sum,
        b_sum,
        r_out_sum,
        g_out_sum,
        b_out_sum,
        r_in_sum,
        g_in_sum,
        b_in_sum,
        pr,
        pg,
        pb,
        rbs;
    var imagePixels = imageData.data;
    var radiusPixels = radiusData.data;
    var wh = width * height;
    var wh4 = wh << 2;
    var pixels = [];
    for (var i = 0; i < wh4; i++) {
        pixels[i] = imagePixels[i];
    }
    var currentIndex = 0;
    var steps = blurLevels;
    blurLevels -= 1;
    while (steps-- >= 0) {
        var iradius = (radius + 0.5) | 0;
        if (iradius == 0)
            continue;
        if (iradius > 256)
            iradius = 256;
        var div = iradius + iradius + 1;
        var w4 = width << 2;
        var widthMinus1 = width - 1;
        var heightMinus1 = height - 1;
        var radiusPlus1 = iradius + 1;
        var sumFactor = radiusPlus1 * (radiusPlus1 + 1) / 2;
        var effectStart = new Blureffect();
        var effect = effectStart;
        for (i = 1; i < div; i++) {
            effect = effect.next = new Blureffect();
            if (i == radiusPlus1)
                var effectEnd = effect;
        }
        effect.next = effectStart;
        var effectIn = null;
        var effectOut = null;
        yw = yi = 0;
        var mul_sum = mul_table[iradius];
        var shg_sum = shg_table[iradius];
        for (y = 0; y < height; y++) {
            r_in_sum = g_in_sum = b_in_sum = r_sum = g_sum = b_sum = 0;
            r_out_sum = radiusPlus1 * (pr = pixels[yi]);
            g_out_sum = radiusPlus1 * (pg = pixels[yi + 1]);
            b_out_sum = radiusPlus1 * (pb = pixels[yi + 2]);
            r_sum += sumFactor * pr;
            g_sum += sumFactor * pg;
            b_sum += sumFactor * pb;
            effect = effectStart;
            for (i = 0; i < radiusPlus1; i++) {
                effect.r = pr;
                effect.g = pg;
                effect.b = pb;
                effect = effect.next;
            }
            for (i = 1; i < radiusPlus1; i++) {
                p = yi + ((widthMinus1 < i ? widthMinus1 : i) << 2);
                r_sum += (effect.r = (pr = pixels[p])) * (rbs = radiusPlus1 - i);
                g_sum += (effect.g = (pg = pixels[p + 1])) * rbs;
                b_sum += (effect.b = (pb = pixels[p + 2])) * rbs;
                r_in_sum += pr;
                g_in_sum += pg;
                b_in_sum += pb;
                effect = effect.next;
            }
            effectIn = effectStart;
            effectOut = effectEnd;
            for (x = 0; x < width; x++) {
                pixels[yi] = (r_sum * mul_sum) >> shg_sum;
                pixels[yi + 1] = (g_sum * mul_sum) >> shg_sum;
                pixels[yi + 2] = (b_sum * mul_sum) >> shg_sum;
                r_sum -= r_out_sum;
                g_sum -= g_out_sum;
                b_sum -= b_out_sum;
                r_out_sum -= effectIn.r;
                g_out_sum -= effectIn.g;
                b_out_sum -= effectIn.b;
                p = (yw + ((p = x + radiusPlus1) < widthMinus1 ? p : widthMinus1)) << 2;
                r_in_sum += (effectIn.r = pixels[p]);
                g_in_sum += (effectIn.g = pixels[p + 1]);
                b_in_sum += (effectIn.b = pixels[p + 2]);
                r_sum += r_in_sum;
                g_sum += g_in_sum;
                b_sum += b_in_sum;
                effectIn = effectIn.next;
                r_out_sum += (pr = effectOut.r);
                g_out_sum += (pg = effectOut.g);
                b_out_sum += (pb = effectOut.b);
                r_in_sum -= pr;
                g_in_sum -= pg;
                b_in_sum -= pb;
                effectOut = effectOut.next;
                yi += 4;
            }
            yw += width;
        }
        for (x = 0; x < width; x++) {
            g_in_sum = b_in_sum = r_in_sum = g_sum = b_sum = r_sum = 0;
            yi = x << 2;
            r_out_sum = radiusPlus1 * (pr = pixels[yi]);
            g_out_sum = radiusPlus1 * (pg = pixels[yi + 1]);
            b_out_sum = radiusPlus1 * (pb = pixels[yi + 2]);
            r_sum += sumFactor * pr;
            g_sum += sumFactor * pg;
            b_sum += sumFactor * pb;
            effect = effectStart;
            for (i = 0; i < radiusPlus1; i++) {
                effect.r = pr;
                effect.g = pg;
                effect.b = pb;
                effect = effect.next;
            }
            yp = width;
            for (i = 1; i < radiusPlus1; i++) {
                yi = (yp + x) << 2;
                r_sum += (effect.r = (pr = pixels[yi])) * (rbs = radiusPlus1 - i);
                g_sum += (effect.g = (pg = pixels[yi + 1])) * rbs;
                b_sum += (effect.b = (pb = pixels[yi + 2])) * rbs;
                r_in_sum += pr;
                g_in_sum += pg;
                b_in_sum += pb;
                effect = effect.next;
                if (i < heightMinus1) {
                    yp += width;
                }
            }
            yi = x;
            effectIn = effectStart;
            effectOut = effectEnd;
            for (y = 0; y < height; y++) {
                p = yi << 2;
                pixels[p] = (r_sum * mul_sum) >> shg_sum;
                pixels[p + 1] = (g_sum * mul_sum) >> shg_sum;
                pixels[p + 2] = (b_sum * mul_sum) >> shg_sum;
                r_sum -= r_out_sum;
                g_sum -= g_out_sum;
                b_sum -= b_out_sum;
                r_out_sum -= effectIn.r;
                g_out_sum -= effectIn.g;
                b_out_sum -= effectIn.b;
                p = (x + (((p = y + radiusPlus1) < heightMinus1 ? p : heightMinus1) * width)) << 2;
                r_sum += (r_in_sum += (effectIn.r = pixels[p]));
                g_sum += (g_in_sum += (effectIn.g = pixels[p + 1]));
                b_sum += (b_in_sum += (effectIn.b = pixels[p + 2]));
                effectIn = effectIn.next;
                r_out_sum += (pr = effectOut.r);
                g_out_sum += (pg = effectOut.g);
                b_out_sum += (pb = effectOut.b);
                r_in_sum -= pr;
                g_in_sum -= pg;
                b_in_sum -= pb;
                effectOut = effectOut.next;
                yi += width;
            }
        }
        radius *= increaseFactor;
        for (i = wh; --i > -1;) {
            var idx = i << 2;
            var lookupValue = (radiusPixels[idx + 2] & 0xff) / 255.0 * blurLevels;
            var index = lookupValue | 0;
            if (index == currentIndex) {
                var blend = 256.0 * (lookupValue - (lookupValue | 0));
                var iblend = 256 - blend;
                imagePixels[idx] = (imagePixels[idx] * iblend + pixels[idx] * blend) >> 8;
                imagePixels[idx + 1] = (imagePixels[idx + 1] * iblend + pixels[idx + 1] * blend) >> 8;
                imagePixels[idx + 2] = (imagePixels[idx + 2] * iblend + pixels[idx + 2] * blend) >> 8;
            } else if (index == currentIndex + 1) {
                imagePixels[idx] = pixels[idx];
                imagePixels[idx + 1] = pixels[idx + 1];
                imagePixels[idx + 2] = pixels[idx + 2];
            }
        }
        currentIndex++;
    }
}
function renderCompundBlurRGBA(imageData, radiusData, width, height, radius, increaseFactor, blurLevels) {
    var x,
        y,
        i,
        p,
        yp,
        yi,
        yw,
        r_sum,
        g_sum,
        b_sum,
        a_sum,
        r_out_sum,
        g_out_sum,
        b_out_sum,
        a_out_sum,
        r_in_sum,
        g_in_sum,
        b_in_sum,
        a_in_sum,
        pa,
        pr,
        pg,
        pb,
        rbs;
    var imagePixels = imageData.data;
    var radiusPixels = radiusData.data;
    var wh = width * height;
    var wh4 = wh << 2;
    var pixels = [];
    for (var i = 0; i < wh4; i++) {
        pixels[i] = imagePixels[i];
    }
    var currentIndex = 0;
    var steps = blurLevels;
    blurLevels -= 1;
    while (steps-- >= 0) {
        var iradius = (radius + 0.5) | 0;
        if (iradius == 0)
            continue;
        if (iradius > 256)
            iradius = 256;
        var div = iradius + iradius + 1;
        var w4 = width << 2;
        var widthMinus1 = width - 1;
        var heightMinus1 = height - 1;
        var radiusPlus1 = iradius + 1;
        var sumFactor = radiusPlus1 * (radiusPlus1 + 1) / 2;
        var effectStart = new Blureffect();
        var effect = effectStart;
        for (i = 1; i < div; i++) {
            effect = effect.next = new Blureffect();
            if (i == radiusPlus1)
                var effectEnd = effect;
        }
        effect.next = effectStart;
        var effectIn = null;
        var effectOut = null;
        yw = yi = 0;
        var mul_sum = mul_table[iradius];
        var shg_sum = shg_table[iradius];
        for (y = 0; y < height; y++) {
            r_in_sum = g_in_sum = b_in_sum = a_in_sum = r_sum = g_sum = b_sum = a_sum = 0;
            r_out_sum = radiusPlus1 * (pr = pixels[yi]);
            g_out_sum = radiusPlus1 * (pg = pixels[yi + 1]);
            b_out_sum = radiusPlus1 * (pb = pixels[yi + 2]);
            a_out_sum = radiusPlus1 * (pa = pixels[yi + 3]);
            r_sum += sumFactor * pr;
            g_sum += sumFactor * pg;
            b_sum += sumFactor * pb;
            a_sum += sumFactor * pa;
            effect = effectStart;
            for (i = 0; i < radiusPlus1; i++) {
                effect.r = pr;
                effect.g = pg;
                effect.b = pb;
                effect.a = pa;
                effect = effect.next;
            }
            for (i = 1; i < radiusPlus1; i++) {
                p = yi + ((widthMinus1 < i ? widthMinus1 : i) << 2);
                r_sum += (effect.r = (pr = pixels[p])) * (rbs = radiusPlus1 - i);
                g_sum += (effect.g = (pg = pixels[p + 1])) * rbs;
                b_sum += (effect.b = (pb = pixels[p + 2])) * rbs;
                a_sum += (effect.a = (pa = pixels[p + 3])) * rbs;
                r_in_sum += pr;
                g_in_sum += pg;
                b_in_sum += pb;
                a_in_sum += pa;
                effect = effect.next;
            }
            effectIn = effectStart;
            effectOut = effectEnd;
            for (x = 0; x < width; x++) {
                pixels[yi + 3] = pa = (a_sum * mul_sum) >> shg_sum;
                if (pa != 0) {
                    pa = 255 / pa;
                    pixels[yi] = ((r_sum * mul_sum) >> shg_sum) * pa;
                    pixels[yi + 1] = ((g_sum * mul_sum) >> shg_sum) * pa;
                    pixels[yi + 2] = ((b_sum * mul_sum) >> shg_sum) * pa;
                } else {
                    pixels[yi] = pixels[yi + 1] = pixels[yi + 2] = 0;
                }
                r_sum -= r_out_sum;
                g_sum -= g_out_sum;
                b_sum -= b_out_sum;
                a_sum -= a_out_sum;
                r_out_sum -= effectIn.r;
                g_out_sum -= effectIn.g;
                b_out_sum -= effectIn.b;
                a_out_sum -= effectIn.a;
                p = (yw + ((p = x + radiusPlus1) < widthMinus1 ? p : widthMinus1)) << 2;
                r_in_sum += (effectIn.r = pixels[p]);
                g_in_sum += (effectIn.g = pixels[p + 1]);
                b_in_sum += (effectIn.b = pixels[p + 2]);
                a_in_sum += (effectIn.a = pixels[p + 3]);
                r_sum += r_in_sum;
                g_sum += g_in_sum;
                b_sum += b_in_sum;
                a_sum += a_in_sum;
                effectIn = effectIn.next;
                r_out_sum += (pr = effectOut.r);
                g_out_sum += (pg = effectOut.g);
                b_out_sum += (pb = effectOut.b);
                a_out_sum += (pa = effectOut.a);
                r_in_sum -= pr;
                g_in_sum -= pg;
                b_in_sum -= pb;
                a_in_sum -= pa;
                effectOut = effectOut.next;
                yi += 4;
            }
            yw += width;
        }
        for (x = 0; x < width; x++) {
            g_in_sum = b_in_sum = a_in_sum = r_in_sum = g_sum = b_sum = a_sum = r_sum = 0;
            yi = x << 2;
            r_out_sum = radiusPlus1 * (pr = pixels[yi]);
            g_out_sum = radiusPlus1 * (pg = pixels[yi + 1]);
            b_out_sum = radiusPlus1 * (pb = pixels[yi + 2]);
            a_out_sum = radiusPlus1 * (pa = pixels[yi + 3]);
            r_sum += sumFactor * pr;
            g_sum += sumFactor * pg;
            b_sum += sumFactor * pb;
            a_sum += sumFactor * pa;
            effect = effectStart;
            for (i = 0; i < radiusPlus1; i++) {
                effect.r = pr;
                effect.g = pg;
                effect.b = pb;
                effect.a = pa;
                effect = effect.next;
            }
            yp = width;
            for (i = 1; i < radiusPlus1; i++) {
                yi = (yp + x) << 2;
                r_sum += (effect.r = (pr = pixels[yi])) * (rbs = radiusPlus1 - i);
                g_sum += (effect.g = (pg = pixels[yi + 1])) * rbs;
                b_sum += (effect.b = (pb = pixels[yi + 2])) * rbs;
                a_sum += (effect.a = (pa = pixels[yi + 3])) * rbs;
                r_in_sum += pr;
                g_in_sum += pg;
                b_in_sum += pb;
                a_in_sum += pa;
                effect = effect.next;
                if (i < heightMinus1) {
                    yp += width;
                }
            }
            yi = x;
            effectIn = effectStart;
            effectOut = effectEnd;
            for (y = 0; y < height; y++) {
                p = yi << 2;
                pixels[p + 3] = pa = (a_sum * mul_sum) >> shg_sum;
                if (pa > 0) {
                    pa = 255 / pa;
                    pixels[p] = ((r_sum * mul_sum) >> shg_sum) * pa;
                    pixels[p + 1] = ((g_sum * mul_sum) >> shg_sum) * pa;
                    pixels[p + 2] = ((b_sum * mul_sum) >> shg_sum) * pa;
                } else {
                    pixels[p] = pixels[p + 1] = pixels[p + 2] = 0;
                }
                r_sum -= r_out_sum;
                g_sum -= g_out_sum;
                b_sum -= b_out_sum;
                a_sum -= a_out_sum;
                r_out_sum -= effectIn.r;
                g_out_sum -= effectIn.g;
                b_out_sum -= effectIn.b;
                a_out_sum -= effectIn.a;
                p = (x + (((p = y + radiusPlus1) < heightMinus1 ? p : heightMinus1) * width)) << 2;
                r_sum += (r_in_sum += (effectIn.r = pixels[p]));
                g_sum += (g_in_sum += (effectIn.g = pixels[p + 1]));
                b_sum += (b_in_sum += (effectIn.b = pixels[p + 2]));
                a_sum += (a_in_sum += (effectIn.a = pixels[p + 3]));
                effectIn = effectIn.next;
                r_out_sum += (pr = effectOut.r);
                g_out_sum += (pg = effectOut.g);
                b_out_sum += (pb = effectOut.b);
                a_out_sum += (pa = effectOut.a);
                r_in_sum -= pr;
                g_in_sum -= pg;
                b_in_sum -= pb;
                a_in_sum -= pa;
                effectOut = effectOut.next;
                yi += width;
            }
        }
        radius *= increaseFactor;
        for (i = wh; --i > -1;) {
            var idx = i << 2;
            var lookupValue = (radiusPixels[idx + 2] & 0xff) / 255.0 * blurLevels;
            var index = lookupValue | 0;
            if (index == currentIndex) {
                var blend = 256.0 * (lookupValue - (lookupValue | 0));
                var iblend = 256 - blend;
                imagePixels[idx] = (imagePixels[idx] * iblend + pixels[idx] * blend) >> 8;
                imagePixels[idx + 1] = (imagePixels[idx + 1] * iblend + pixels[idx + 1] * blend) >> 8;
                imagePixels[idx + 2] = (imagePixels[idx + 2] * iblend + pixels[idx + 2] * blend) >> 8;
                imagePixels[idx + 3] = (imagePixels[idx + 3] * iblend + pixels[idx + 3] * blend) >> 8;
            } else if (index == currentIndex + 1) {
                imagePixels[idx] = pixels[idx];
                imagePixels[idx + 1] = pixels[idx + 1];
                imagePixels[idx + 2] = pixels[idx + 2];
                imagePixels[idx + 3] = pixels[idx + 3];
            }
        }
        currentIndex++;
    }
}
