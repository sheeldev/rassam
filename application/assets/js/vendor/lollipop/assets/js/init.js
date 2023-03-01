var canvas;
var context;
var tempCanvas;
var tempContext;
var checkPoint;
var image;
var video;
var vWidth;
var vHeight;
var streaming;
var streamInterval;
var isStreaming = false;
var current = "Original";
var brightnessV = 0;
var contrastV = 0;
var saturationV = 1;
var noiseV = 0;
var vignette = false;
var effect = 'none';
function initCanvas() {
        context = canvas.getContext('2d');
        canvas.width = image.width;
        canvas.height = image.height;
        context.drawImage(image, 0, 0, image.width, image.height, 0, 0, canvas.width, canvas.height);
        canvasPos();
        saveCheckPoint();
}
function initTempCanvas() {
        tempCanvas = document.createElement('canvas');
        tempCanvas.width = canvas.width;
        tempCanvas.height = canvas.height;
        tempContext = tempCanvas.getContext('2d');
        tempContext.drawImage(canvas, 0, 0);
}
function getUrlParam() {
        var results = new RegExp('[\?&]' + 'src' + '=([^&#]*)').exec(window.location.href);
        if (results == null)
        {
                return null;
        }
        else
        {
                return results[1] || 0;
        }
}
$(function() {
        canvas = document.getElementById('new');
        image = document.getElementById('old');
        var src = getUrlParam();
        if (src == undefined)
        {
                image.src = 'assets/img/pictures/Default2.jpg';
                image.onload = function() {
                        initCanvas();
                }
        }
        else if (src == "null") {
                ;
        }
        else {
                image.src = src;
                image.onload = function() {
                        initCanvas();
                }
        }
        video = document.getElementById('video');
        canvas.addEventListener("dragenter", dragenter, false);
        canvas.addEventListener("dragover", dragover, false);
        canvas.addEventListener("drop", drop, false);
        video.addEventListener('canplay', function(e) {
                if (!isStreaming) {
                        vWidth = video.videoWidth;
                        vHeight = video.videoHeight;
                        canvas.setAttribute('width', vWidth);
                        canvas.setAttribute('height', vHeight);
                        canvasPos();
                        context.translate(vWidth, 0);
                        context.scale(-1, 1);
                        $(".interaction").hide();
                        $("#undo").hide();
                        $("#check").hide();
                        $(".nav").hide();
                        $("#take").show();
                        $("#package-icon").show();
                        $("#filters").addClass("toright");
                        showCSettings();
                        isStreaming = true;
                }
        }, false);
        video.addEventListener('play', function() {
                streamInterval = setInterval(function() {
                        if (video.paused || video.ended)
                        {
                                return;
                        }
                        context.fillRect(0, 0, vWidth, vHeight);
                        context.drawImage(video, 0, 0, vWidth, vHeight);
                        applyFilter(current);
                        applyBrightness(brightnessV);
                        applyContrast(contrastV);
                        applySaturation(saturationV);
                        applyNoise(noiseV);
                        if (vignette)
                        {
                                applyVignette();
                        }
                        if (effect == 'Grayscale' && effect != 'none')
                        {
                                applyGrayscale();
                        }
                        if (effect == 'Sepia' && effect != 'none')
                        {
                                applySepia();
                        }
                        if (effect == 'Invert' && effect != 'none')
                        {
                                applyInvert();
                        }
                }, 33);
        }, false);
        $("#new").swipe({
                swipeLeft: function() {
                        if (selection.indexOf(current) < selection.length - 1) {
                                current = selection[selection.indexOf(current) + 1];
                                applyFilter(current);
                        } else if (selection.indexOf(current) == selection.length - 1) {
                                current = selection[0];
                                applyFilter(current);
                        }
                },
                swipeRight: function() {
                        if (selection.indexOf(current) > 0) {
                                current = selection[selection.indexOf(current) - 1];
                                applyFilter(current);
                        }
                        else if (selection.indexOf(current) == 0) {
                                current = selection[selection.length - 1];
                                applyFilter(current);
                        }
                },
                threshold: 0
        });
        $(".interaction").on('click', function() {
                $("." + "active").each(function() {
                        $(this).removeClass("active");
                })
                $(this).addClass("active");
                $("#back").hide();
        });
        $("#export-btn").on('click', function() {
                var dt = canvas.toDataURL('image/jpeg');
                $(this).attr('href', dt);
        });
        $("#save-btn").on('click', function() {
                if (window.Lollipop) {
                        var dt = canvas.toDataURL('image/jpeg');
                        window.Lollipop.save(dt, window.Lollipop.getParams().type, window.Lollipop.getParams().filename, window.Lollipop.getParams().itemid, window.Lollipop.getParams().hash);
                }
        });
        $('.setting').perfectScrollbar();
        $('.theme').perfectScrollbar();
        $("#filters-icon").addClass("active");
});
$(window).on("resize orientationchange", function() {
        $('.setting').perfectScrollbar('update');
        canvasPos();
});
