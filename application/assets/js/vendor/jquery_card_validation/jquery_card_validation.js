!function(e) {
    if ("object" == typeof exports && "undefined" != typeof module)
        module.exports = e();
    else if ("function" == typeof define && define.amd)
        define([], e);
    else {
        var n;
        "undefined" != typeof window ? n = window : "undefined" != typeof global ? n = global : "undefined" != typeof self && (n = self), (n.payment || (n.payment = {})).js = e()
    }
}(function() {
    var define, module, exports;
    return (function e(t, n, r) {
        function s(o, u) {
            if (!n[o]) {
                if (!t[o]) {
                    var a = typeof require == "function" && require;
                    if (!u && a)
                        return a(o, !0);
                    if (i)
                        return i(o, !0);
                    throw new Error("Cannot find module '" + o + "'")
                }
                var f = n[o] = {
                    exports: {}
                };
                t[o][0].call(f.exports, function(e) {
                    var n = t[o][1][e];
                    return s(n ? n : e)
                }, f, f.exports, e, t, n, r)
            }
            return n[o].exports
        }
        var i = typeof require == "function" && require;
        for (var o = 0; o < r.length; o++)
            s(r[o]);
        return s
    })({
        1: [function(_dereq_, module, exports) {
            (function (global) {
                var J, Payment, cardFromNumber, cardFromType, cards, defaultFormat, formatBackCardNumber, formatBackExpiry, formatCardNumber, formatExpiry, formatForwardExpiry, formatForwardSlash, hasTextSelected, luhnCheck, reFormatCardNumber, restrictCVC, restrictCardNumber, restrictExpiry, restrictNumeric, setCardType,
                __indexOf = [].indexOf || function(item) {
                    for (var i = 0, l = this.length; i < l; i++) {
                        if (i in this && this[i] === item) 
                            return i;
                    }
                    return - 1;
                };

                J = _dereq_('./utils');

                defaultFormat = /(\d{1,4})/g;

                cards = [
                {
                    type: 'maestro',
                    pattern: /^(5018|5020|5038|6304|6759|676[1-3])/,
                    format: defaultFormat,
                    length: [12, 13, 14, 15, 16, 17, 18, 19],
                    cvcLength: [3],
                    luhn: true
                }, {
                    type: 'dinersclub',
                    pattern: /^(36|38|30[0-5])/,
                    format: defaultFormat,
                    length: [14],
                    cvcLength: [3],
                    luhn: true
                }, {
                    type: 'laser',
                    pattern: /^(6706|6771|6709)/,
                    format: defaultFormat,
                    length: [16, 17, 18, 19],
                    cvcLength: [3],
                    luhn: true
                }, {
                    type: 'jcb',
                    pattern: /^35/,
                    format: defaultFormat,
                    length: [16],
                    cvcLength: [3],
                    luhn: true
                }, {
                    type: 'unionpay',
                    pattern: /^62/,
                    format: defaultFormat,
                    length: [16, 17, 18, 19],
                    cvcLength: [3],
                    luhn: false
                }, {
                    type: 'discover',
                    pattern: /^(6011|65|64[4-9]|622)/,
                    format: defaultFormat,
                    length: [16],
                    cvcLength: [3],
                    luhn: true
                }, {
                    type: 'mastercard',
                    pattern: /^5[1-5]/,
                    format: defaultFormat,
                    length: [16],
                    cvcLength: [3],
                    luhn: true
                }, {
                    type: 'amex',
                    pattern: /^3[47]/,
                    format: /(\d{1,4})(\d{1,6})?(\d{1,5})?/,
                    length: [15],
                    cvcLength: [3, 4],
                    luhn: true
                }, {
                    type: 'visa',
                    pattern: /^4/,
                    format: defaultFormat,
                    length: [13, 14, 15, 16],
                    cvcLength: [3],
                    luhn: true
                }
                ];

                cardFromNumber = function(num) {
                    var card, _i, _len;
                    num = (num + '').replace(/\D/g, '');
                    for (_i = 0, _len = cards.length; _i < _len; _i++) {
                        card = cards[_i];
                        if (card.pattern.test(num)) {
                            return card;
                        }
                    }
                };

                cardFromType = function(type) {
                    var card, _i, _len;
                    for (_i = 0, _len = cards.length; _i < _len; _i++) {
                        card = cards[_i];
                        if (card.type === type) {
                            return card;
                        }
                    }
                };

                luhnCheck = function(num) {
                    var digit, digits, odd, sum, _i, _len;
                    odd = true;
                    sum = 0;
                    digits = (num + '').split('').reverse();
                    for (_i = 0, _len = digits.length; _i < _len; _i++) {
                        digit = digits[_i];
                        digit = parseInt(digit, 10);
                        if ((odd = !odd)) {
                            digit *= 2;
                        }
                        if (digit > 9) {
                            digit -= 9;
                        }
                        sum += digit;
                    }
                    return sum % 10 === 0;
                };

                hasTextSelected = function(target) {
                    var _ref;
                    if ((target.selectionStart != null) && target.selectionStart !== target.selectionEnd) {
                        return true;
                    }
                    if (typeof document !== "undefined" && document !== null ? (_ref = document.selection) != null ? typeof _ref.createRange === "function" ? _ref.createRange().text : void 0 : void 0 : void 0) {
                        return true;
                    }
                    return false;
                };

                reFormatCardNumber = function(e) {
                    return setTimeout((function(_this) {
                        return function() {
                            var target, value;
                            target = e.currentTarget;
                            value = J.val(target);
                            value = Payment.fns.formatCardNumber(value);
                            return J.val(target, value);
                        };
                    })(this));
                };

                formatCardNumber = function(e) {
                    var card, digit, length, re, target, upperLength, value;
                    digit = String.fromCharCode(e.which);
                    if (!/^\d+$/.test(digit)) {
                        return;
                    }
                    target = e.currentTarget;
                    value = J.val(target);
                    card = cardFromNumber(value + digit);
                    length = (value.replace(/\D/g, '') + digit).length;
                    upperLength = 16;
                    if (card) {
                        upperLength = card.length[card.length.length - 1];
                    }
                    if (length >= upperLength) {
                        return;
                    }
                    if ((target.selectionStart != null) && target.selectionStart !== value.length) {
                        return;
                    }
                    if (card && card.type === 'amex') {
                        re = /^(\d{4}|\d{4}\s\d{6})$/;
                    } else {
                        re = /(?:^|\s)(\d{4})$/;
                    }
                    if (re.test(value)) {
                        e.preventDefault();
                        return J.val(target, value + ' ' + digit);
                    } else if (re.test(value + digit)) {
                        e.preventDefault();
                        return J.val(target, value + digit + ' ');
                    }
                };

                formatBackCardNumber = function(e) {
                    var target, value;
                    target = e.currentTarget;
                    value = J.val(target);
                    if (e.meta) {
                        return;
                    }
                    if (e.which !== 8) {
                        return;
                    }
                    if ((target.selectionStart != null) && target.selectionStart !== value.length) {
                        return;
                    }
                    if (/\d\s$/.test(value)) {
                        e.preventDefault();
                        return J.val(target, value.replace(/\d\s$/, ''));
                    } else if (/\s\d?$/.test(value)) {
                        e.preventDefault();
                        return J.val(target, value.replace(/\s\d?$/, ''));
                    }
                };

                formatExpiry = function(e) {
                    var digit, target, val;
                    digit = String.fromCharCode(e.which);
                    if (!/^\d+$/.test(digit)) {
                        return;
                    }
                    target = e.currentTarget;
                    val = J.val(target) + digit;
                    if (/^\d$/.test(val) && (val !== '0' && val !== '1')) {
                        e.preventDefault();
                        return J.val(target, "0" + val + " / ");
                    } else if (/^\d\d$/.test(val)) {
                        e.preventDefault();
                        return J.val(target, "" + val + " / ");
                    }
                };

                formatForwardExpiry = function(e) {
                    var digit, target, val;
                    digit = String.fromCharCode(e.which);
                    if (!/^\d+$/.test(digit)) {
                        return;
                    }
                    target = e.currentTarget;
                    val = J.val(target);
                    if (/^\d\d$/.test(val)) {
                        return J.val(target, "" + val + " / ");
                    }
                };

                formatForwardSlash = function(e) {
                    var slash, target, val;
                    slash = String.fromCharCode(e.which);
                    if (slash !== '/') {
                        return;
                    }
                    target = e.currentTarget;
                    val = J.val(target);
                    if (/^\d$/.test(val) && val !== '0') {
                        return J.val(target, "0" + val + " / ");
                    }
                };

                formatBackExpiry = function(e) {
                    var target, value;
                    if (e.metaKey) {
                        return;
                    }
                    target = e.currentTarget;
                    value = J.val(target);
                    if (e.which !== 8) {
                        return;
                    }
                    if ((target.selectionStart != null) && target.selectionStart !== value.length) {
                        return;
                    }
                    if (/\d(\s|\/)+$/.test(value)) {
                        e.preventDefault();
                        return J.val(target, value.replace(/\d(\s|\/)*$/, ''));
                    } else if (/\s\/\s?\d?$/.test(value)) {
                        e.preventDefault();
                        return J.val(target, value.replace(/\s\/\s?\d?$/, ''));
                    }
                };

                restrictNumeric = function(e) {
                    var input;
                    if (e.metaKey || e.ctrlKey) {
                        return true;
                    }
                    if (e.which === 32) {
                        return e.preventDefault();
                    }
                    if (e.which === 0) {
                        return true;
                    }
                    if (e.which < 33) {
                        return true;
                    }
                    input = String.fromCharCode(e.which);
                    if (!/[\d\s]/.test(input)) {
                        return e.preventDefault();
                    }
                };

                restrictCardNumber = function(e) {
                    var card, digit, target, value;
                    target = e.currentTarget;
                    digit = String.fromCharCode(e.which);
                    if (!/^\d+$/.test(digit)) {
                        return;
                    }
                    if (hasTextSelected(target)) {
                        return;
                    }
                    value = (J.val(target) + digit).replace(/\D/g, '');
                    card = cardFromNumber(value);
                    if (card) {
                        if (!(value.length <= card.length[card.length.length - 1])) {
                            return e.preventDefault();
                        }
                    } else {
                        if (!(value.length <= 16)) {
                            return e.preventDefault();
                        }
                    }
                };

                restrictExpiry = function(e) {
                    var digit, target, value;
                    target = e.currentTarget;
                    digit = String.fromCharCode(e.which);
                    if (!/^\d+$/.test(digit)) {
                        return;
                    }
                    if (hasTextSelected(target)) {
                        return;
                    }
                    value = J.val(target) + digit;
                    value = value.replace(/\D/g, '');
                    if (value.length > 6) {
                        return e.preventDefault();
                    }
                };

                restrictCVC = function(e) {
                    var digit, target, val;
                    target = e.currentTarget;
                    digit = String.fromCharCode(e.which);
                    if (!/^\d+$/.test(digit)) {
                        return;
                    }
                    val = J.val(target) + digit;
                    if (!(val.length <= 4)) {
                        return e.preventDefault();
                    }
                };

                setCardType = function(e) {
                    var allTypes, card, cardType, target, val;
                    target = e.currentTarget;
                    val = J.val(target);
                    cardType = Payment.fns.cardType(val) || 'unknown';
                    if (!J.hasClass(target, cardType)) {
                        allTypes = (function() {
                            var _i, _len, _results;
                            _results = [];
                            for (_i = 0, _len = cards.length; _i < _len; _i++) {
                                card = cards[_i];
                                _results.push(card.type);
                            }
                            return _results;
                        })();
                        J.removeClass(target, 'unknown');
                        J.removeClass(target, allTypes.join(' '));
                        J.addClass(target, cardType);
                        J.toggleClass(target, 'identified', cardType !== 'unknown');
                        return J.trigger(target, 'payment.cardType', cardType);
                    }
                };

                Payment = (function() {
                    function Payment() {}

                    Payment.fns = {
                        cardExpiryVal: function(value) {
                            var month, prefix, year, _ref;
                            value = value.replace(/\s/g, '');
                            _ref = value.split('/', 2), month = _ref[0], year = _ref[1];
                            if ((year != null ? year.length : void 0) === 2 && /^\d+$/.test(year)) {
                                prefix = (new Date).getFullYear();
                                prefix = prefix.toString().slice(0, 2);
                                year = prefix + year;
                            }
                            month = parseInt(month, 10);
                            year = parseInt(year, 10);
                            return {
                                month: month,
                                year: year
                            };
                        },
                        validateCardNumber: function(num) {
                            var card, _ref;
                            num = (num + '').replace(/\s+|-/g, '');
                            if (!/^\d+$/.test(num)) {
                                return false;
                            }
                            card = cardFromNumber(num);
                            if (!card) {
                                return false;
                            }
                            return (_ref = num.length, __indexOf.call(card.length, _ref) >= 0) && (card.luhn === false || luhnCheck(num));
                        },
                        validateCardExpiry: function(month, year) {
                            var currentTime, expiry, prefix, _ref;
                            if (typeof month === 'object' && 'month' in month) {
                                _ref = month, month = _ref.month, year = _ref.year;
                            }
                            if (!(month && year)) {
                                return false;
                            }
                            month = J.trim(month);
                            year = J.trim(year);
                            if (!/^\d+$/.test(month)) {
                                return false;
                            }
                            if (!/^\d+$/.test(year)) {
                                return false;
                            }
                            if (!(parseInt(month, 10) <= 12)) {
                                return false;
                            }
                            if (year.length === 2) {
                                prefix = (new Date).getFullYear();
                                prefix = prefix.toString().slice(0, 2);
                                year = prefix + year;
                            }
                            expiry = new Date(year, month);
                            currentTime = new Date;
                            expiry.setMonth(expiry.getMonth() - 1);
                            expiry.setMonth(expiry.getMonth() + 1, 1);
                            return expiry > currentTime;
                        },
                        validateCardCVC: function(cvc, type) {
                            var _ref, _ref1;
                            cvc = J.trim(cvc);
                            if (!/^\d+$/.test(cvc)) {
                                return false;
                            }
                            if (type) {
                                return _ref = cvc.length, __indexOf.call((_ref1 = cardFromType(type)) != null ? _ref1.cvcLength : void 0, _ref) >= 0;
                            } else {
                                return cvc.length >= 3 && cvc.length <= 4;
                            }
                        },
                        cardType: function(num) {
                            var _ref;
                            if (!num) {
                                return null;
                            }
                            return ((_ref = cardFromNumber(num)) != null ? _ref.type : void 0) || null;
                        },
                        formatCardNumber: function(num) {
                            var card, groups, upperLength, _ref;
                            card = cardFromNumber(num);
                            if (!card) {
                                return num;
                            }
                            upperLength = card.length[card.length.length - 1];
                            num = num.replace(/\D/g, '');
                            num = num.slice(0, + upperLength + 1 || 9e9);
                            if (card.format.global) {
                                return (_ref = num.match(card.format)) != null ? _ref.join(' ') : void 0;
                            } else {
                                groups = card.format.exec(num);
                                if (groups != null) {
                                    groups.shift();
                                }
                                return groups != null ? groups.join(' ') : void 0;
                            }
                        }
                    };

                    Payment.restrictNumeric = function(el) {
                        return J.on(el, 'keypress', restrictNumeric);
                    };

                    Payment.cardExpiryVal = function(el) {
                        return Payment.fns.cardExpiryVal(J.val(el));
                    };

                    Payment.formatCardCVC = function(el) {
                        Payment.restrictNumeric(el);
                        J.on(el, 'keypress', restrictCVC);
                        return el;
                    };

                    Payment.formatCardExpiry = function(el) {
                        Payment.restrictNumeric(el);
                        J.on(el, 'keypress', restrictExpiry);
                        J.on(el, 'keypress', formatExpiry);
                        J.on(el, 'keypress', formatForwardSlash);
                        J.on(el, 'keypress', formatForwardExpiry);
                        J.on(el, 'keydown', formatBackExpiry);
                        return el;
                    };

                    Payment.formatCardNumber = function(el) {
                        Payment.restrictNumeric(el);
                        J.on(el, 'keypress', restrictCardNumber);
                        J.on(el, 'keypress', formatCardNumber);
                        J.on(el, 'keydown', formatBackCardNumber);
                        J.on(el, 'keyup', setCardType);
                        J.on(el, 'paste', reFormatCardNumber);
                        return el;
                    };

                    return Payment;

                })();

                Payment.J = J;

                module.exports = Payment;

                global.Payment = Payment;


            }).call(this, typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
        }, {
            "./utils": 2
        }
        ],
        2: [function(_dereq_, module, exports) {
            var J, rreturn, rtrim;

            J = function(selector) {
                return document.querySelectorAll(selector);
            };

            rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;

            J.trim = function(text) {
                if (text === null) {
                    return "";
                } else {
                    return (text + "").replace(rtrim, "");
                }
            };

            rreturn = /\r/g;

            J.val = function(el, val) {
                var ret;
                if (arguments.length > 1) {
                    return el.value = val;
                } else {
                    ret = el.value;
                    if (typeof ret === "string") {
                        return ret.replace(rreturn, "");
                    } else {
                        if (ret === null) {
                            return "";
                        } else {
                            return ret;
                        }
                    }
                }
            };

            J.on = function(el, ev, fn) {
                el.addEventListener(ev, fn);
                return el;
            };

            J.addClass = function(el, className) {
                var e;
                if (el.length) {
                    return (function() {
                        var _i, _len, _results;
                        _results = [];
                        for (_i = 0, _len = el.length; _i < _len; _i++) {
                            e = el[_i];
                            _results.push(J.addClass(e, className));
                        }
                        return _results;
                    })();
                }
                if (el.classList) {
                    return el.classList.add(className);
                } else {
                    return el.className += ' ' + className;
                }
            };

            J.hasClass = function(el, className) {
                if (el.classList) {
                    return el.classList.contains(className);
                } else {
                    return new RegExp('(^| )' + className + '( |$)', 'gi').test(el.className);
                }
            };

            J.removeClass = function(el, className) {
                var cls, e, _i, _len, _ref, _results;
                if (el.length) {
                    return (function() {
                        var _i, _len, _results;
                        _results = [];
                        for (_i = 0, _len = el.length; _i < _len; _i++) {
                            e = el[_i];
                            _results.push(J.removeClass(e, className));
                        }
                        return _results;
                    })();
                }
                if (el.classList) {
                    _ref = className.split(' ');
                    _results = [];
                    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                        cls = _ref[_i];
                        _results.push(el.classList.remove(cls));
                    }
                    return _results;
                } else {
                    return el.className = el.className.replace(new RegExp('(^|\\b)' + className.split(' ').join('|') + '(\\b|$)', 'gi'), ' ');
                }
            };

            J.toggleClass = function(el, className, bool) {
                var e;
                if (el.length) {
                    return (function() {
                        var _i, _len, _results;
                        _results = [];
                        for (_i = 0, _len = el.length; _i < _len; _i++) {
                            e = el[_i];
                            _results.push(J.toggleClass(e, className, bool));
                        }
                        return _results;
                    })();
                }
                if (bool) {
                    if (!J.hasClass(el, className)) {
                        return J.addClass(el, className);
                    }
                } else {
                    return J.removeClass(el, className);
                }
            };

            J.trigger = function(el, name, data) {
                var ev;
                if (window.CustomEvent) {
                    ev = new CustomEvent(name, {
                        detail: data
                    });
                } else {
                    ev = document.createEvent('CustomEvent');
                    if (ev.initCustomEvent) {
                        ev.initCustomEvent(name, true, true, data);
                    } else {
                        ev.initEvent(name, true, true, data);
                    }
                }
                return el.dispatchEvent(ev);
            };

            module.exports = J;


        }, {}
        ]
    }, {}, [1])
    (1)
});

