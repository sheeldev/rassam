function upload() {
    var imageUp = document.getElementById("imageUp");
    if (imageUp) {
        imageUp.click();
    }
}
function getImage(files) {
    if (files.length) {
        var file = files[0];
        image.src = window.URL.createObjectURL(file);
        image.onload = function() {
            restore();
        }
    }
}
function dragenter(e) {
    e.stopPropagation();
    e.preventDefault();
}
function dragover(e) {
    e.stopPropagation();
    e.preventDefault();
}
function drop(e) {
    e.stopPropagation();
    e.preventDefault();
    var dt = e.dataTransfer;
    var files = dt.files;
    getImage(files);
}
function getCamera() {
    navigator.getMedia = (navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia);
    navigator.getMedia({
        video: true,
        audio: false
    }, function(stream) {
        if (navigator.mozGetUserMedia) {
            video.mozSrcObject = stream;
        } else {
            var vendorURL = window.URL || window.webkitURL;
            video.src = vendorURL.createObjectURL(stream);
        }
        video.play();
        streaming = stream;
    }, function(err) {
        console.log("An error occured! " + err);
    });
    brightnessV = 0;
    contrastV = 0;
    saturationV = 1;
    noiseV = 0;
    vignette = false;
    effect = 'none';
    current = 'Original';
}
function importImage() {
    clearInterval(streamInterval);
    streaming.getTracks()[0].stop();
    isStreaming = false;
    image.src = canvas.toDataURL();
    image.onload = function() {
        restore();
    }
}
