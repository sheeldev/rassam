function effectBlurCanvasRGBA(top_x, top_y, radius) {
    if (isNaN(radius) || radius < 1)
        return;
    radius |= 0;
    var width = canvas.width;
    var height = canvas.height;
    var imageData = context.getImageData(0, 0, width, height);
    var pixels = imageData.data;
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
        pr,
        pg,
        pb,
        pa,
        rbs;
    var div = radius + radius + 1;
    var w4 = width << 2;
    var widthMinus1 = width - 1;
    var heightMinus1 = height - 1;
    var radiusPlus1 = radius + 1;
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
    var mul_sum = mul_table[radius];
    var shg_sum = shg_table[radius];
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
            p = (yw + ((p = x + radius + 1) < widthMinus1 ? p : widthMinus1)) << 2;
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
        for (i = 1; i <= radius; i++) {
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
    imageData.data = pixels;
    context.putImageData(imageData, 0, 0);
}
function effectBlurCanvasRGB(top_x, top_y, radius) {
    if (isNaN(radius) || radius < 1)
        return;
    radius |= 0;
    var width = canvas.width;
    var height = canvas.height;
    var imageData = context.getImageData(0, 0, width, height);
    var pixels = imageData.data;
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
    var div = radius + radius + 1;
    var w4 = width << 2;
    var widthMinus1 = width - 1;
    var heightMinus1 = height - 1;
    var radiusPlus1 = radius + 1;
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
    var mul_sum = mul_table[radius];
    var shg_sum = shg_table[radius];
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
            p = (yw + ((p = x + radius + 1) < widthMinus1 ? p : widthMinus1)) << 2;
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
        for (i = 1; i <= radius; i++) {
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
    imageData.data = pixels;
    context.putImageData(imageData, 0, 0);
}
