(function() {
        //'use strict';
        var Lollipop = function() {
                var instance = {
                        getParams: function() {
                                return _lollipop.params;
                        },
                        setOptions: function(options) {
                                _lollipop.setOptions(options);
                                return this;
                        },
                        close: function() {
                                _lollipop.close();
                        },
                        open: function(options, image) {
                                _lollipop.open(options);
                                this.image = image;
                        },
                        save: function(data, type, filename, itemid, hash) {
                                _lollipop.params.onSave(data, type, filename, itemid, hash);
                        },
                        image: undefined
                }
                return instance;
        }
        var _lollipop = {
                params: {
                        path: ".",
                        image_url: "",
                        thumb_container: "",
                        hash: "",
                        filename: "",
                        type: "",
                        itemid: "",
                        appendTo: "body",
                        enabledCORS: false,
                        gallery_icon: false,
                        camera_icon: false,
                        upload_icon: false,
                        save_icon: false,
                        onSave: function(data, type, filename, itemid, hash) {}
                },
                cache: {},
                getThumbContainer: function() {
                        return _lollipop.params.thumb_container;
                },
                setOptions: function(options) {
                        this.extend(_lollipop.params, options);
                },
                extend: function(out) {
                        out = out || {};
                        for (var i = 1; i < arguments.length; i++) {
                                var obj = arguments[i];
                                if (!obj)
                                continue;
                                for (var key in obj) {
                                        if (obj.hasOwnProperty(key)) {
                                                if (typeof obj[key] === 'object' && obj.constructor == Object) {
                                                        _lollipop.extend(out[key], obj[key]);
                                                }
                                                else {
                                                        out[key] = obj[key];
                                                }
                                        }
                                }
                        }
                        return out;
                },
                getPath: function() {
                        return _lollipop.params.path ? _lollipop.params.path + '/' : '';
                },
                close: function() {
                        _lollipop.opened = false;
                        _lollipop.container.style.display = 'none';
                        var frame = _lollipop.container.querySelector("#lollipop-frame");
                        _lollipop.container.querySelector("#lollipop-frame-container").removeChild(frame);
                        document.body.classList.remove('noscroll');
                },
                container: undefined,
                opened: false,
                open: function(options) {
                        if (_lollipop.opened === true) {
                                return;
                        }
                        _lollipop.opened = true;
                        _lollipop.extend(_lollipop.params, options);
                        document.body.classList.add('noscroll');
                        if (document.querySelector("#lollipop-editor-container") == undefined) {
                                /* integrate style */
                                var link = document.createElement("link");link.rel = "stylesheet";
                                link.href = _lollipop.getPath() + "assets/css/lib/integrate.css";
                                document.querySelector("head").appendChild(link);
                                /* integrate container */
                                var _code = '<section id="lollipop-editor-container"><div id="lollipop-frame-container"><div class="lollipop-close" onclick="Lollipop.close()">&times;</div></div><div class="uil-ripple-css" style="-webkit-transform:scale(0.6)"><div></div><div></div></div></section>';
                                var wrapper = document.createElement("div");
                                wrapper.innerHTML = _code;
                                _lollipop.container = document.querySelector(_lollipop.params.appendTo).appendChild(wrapper.firstChild);
                                _lollipop.container.querySelector("#lollipop-frame-container").style.display = 'none';
                        }
                        else {
                                _lollipop.container.style.display = 'block';
                                _lollipop.container.querySelector(".uil-ripple-css").style.display = 'block';
                                _lollipop.container.querySelector("#lollipop-frame-container").style.display = 'none';
                        }
                        /* integrate frame */
                        var finalImage_url;
                        var resp = _lollipop.getFinalImage(_lollipop.params.image_url);
                        if (typeof resp === 'object') {
                                resp.onload = function(e) {
                                        _lollipop.integrateFrame(e.target.responseText);
                                }
                        }
                        else if (typeof resp === 'string') {
                                _lollipop.integrateFrame(resp);
                        }
                },
                integrateFrame: function(finalImage_url) {
                        var frame = document.createElement("iframe");
                        frame.setAttribute("id", "lollipop-frame");
                        _lollipop.container.querySelector("#lollipop-frame-container").appendChild(frame);
                        frame.src = _lollipop.getPath() + "index.html?src=null";
                        frame.onload = function() {
                                _lollipop.container.querySelector("#lollipop-frame-container").style.display = 'block';
                                _lollipop.container.querySelector(".uil-ripple-css").style.display = 'none';
                                frame.contentWindow.jQuery('.setting').perfectScrollbar();
                                frame.contentWindow.jQuery('.theme').perfectScrollbar();
                                frame.contentWindow.image.src = finalImage_url;
                                frame.contentWindow.image.onload = function() {
                                        frame.contentWindow.initCanvas();
                                }
                                if (_lollipop.params.upload_icon === true) {
                                        frame.contentWindow.document.querySelector("#upload-btn").style.display = "block";
                                }
                                if (_lollipop.params.save_icon === true) {
                                        frame.contentWindow.document.querySelector("#save-btn").style.display = "block";
                                }
                                if (_lollipop.params.gallery_icon === false) {
                                        frame.contentWindow.document.querySelector("#gallery-btn").style.display = "none";
                                }
                                if (_lollipop.params.camera_icon === false) {
                                        frame.contentWindow.document.querySelector("#camera-btn").style.display = "none";
                                        frame.contentWindow.Lollipop = window.Lollipop;
                                }
                        }
                },
                getFinalImage: function(url) {
                        if (_lollipop.params.enabledCORS || _lollipop.urlInSameDomain(url) || url.indexOf("data:image") != -1) {
                                return url;
                        }
                        if (_lollipop.cache[url]) {
                                return _lollipop.cache[url];
                        }
                        var request = _lollipop.ajax(iL['AJAXURL'], 'do=lollipopgetimage&url=' + _lollipop.absoluteUrl(url));
                        request.onload = function(e) {
                                if (e.target.status >= 200 && e.target.status < 400) {
                                        _lollipop.cache[url] = e.target.responseText;
                                }
                        };
                        return request;
                },
                urlInSameDomain: function(url) {
                        return url.indexOf(document.domain) > -1 || url.indexOf('//') === -1;
                },
                absoluteUrl: function(url) {
                        if (url.indexOf('//') > -1) return url;
                        var a = document.createElement('a');
                        a.href = url;
                        return a.href;
                },
                ajax: function(url, data) {
                        var request = new XMLHttpRequest();
                        request.open('POST', url, true);
                        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        request.send(data);
                        return request;
                }
        }
        window.Lollipop = new Lollipop();
})();
var imageEditor = window.Lollipop.setOptions({
        path: iL['CDNJS'] + "vendor/lollipop",
        appendTo: "body",
        gallery_icon: false,
        camera_icon: false,
        save_icon: true,
        share_icon: false,
        onSave: function (data, type, filename, itemid, hash) {
                jQuery(".uil-ripple-css").show();
                jQuery("#save").css('opacity', '0.1');
                window.$.ajax({
                        type: 'POST',
                        url: iL['AJAXURL'],
                        data: {
                                'do': 'lollipopsaveimage',
                                'imgData': data,
                                'type': type,
                                'filename': filename,
                                'itemid': itemid,
                                'hash': hash
                        },
                        success: function(response) {
                                jQuery(".uil-ripple-css").hide();
                                jQuery("#save").css('opacity', '1');
                                if (response == 1) {
                                        csrc = jQuery("#fileuploader_iframe").contents().find("#" + window.Lollipop.getParams().thumb_container).attr('src');
                                        jQuery("#fileuploader_iframe").contents().find("#" + window.Lollipop.getParams().thumb_container).prop('src', csrc + '?' + new Date().getTime());
                                        alert('Image saved successfully!');
                                }
                                else {
                                        alert('Image was not saved!');
                                }
                        }
                });
        }
});
