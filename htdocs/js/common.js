if (!String.prototype.startsWith) {
    String.prototype.startsWith = function(search, pos) {
        pos = !pos || pos < 0 ? 0 : +pos;
        return this.substring(pos, pos + search.length) === search;
    };
}

if (!String.prototype.endsWith) {
    String.prototype.endsWith = function(search, this_len) {
        if (this_len === undefined || this_len > this.length) {
            this_len = this.length;
        }
        return this.substring(this_len - search.length, this_len) === search;
    };
}

//
// AJAX
//
(function() {

    var defaultOpts = {
        json: true
    };

    function createXMLHttpRequest() {
        if (window.XMLHttpRequest) {
            return new XMLHttpRequest();
        }

        var xhr;
        try {
            xhr = new ActiveXObject('Msxml2.XMLHTTP');
        } catch (e) {
            try {
                xhr = new ActiveXObject('Microsoft.XMLHTTP');
            } catch (e) {}
        }
        if (!xhr) {
            console.error('Your browser doesn\'t support XMLHttpRequest.');
        }
        return xhr;
    }

    function request(method, url, data, optarg1, optarg2) {
        data = data || null;

        var opts, callback;
        if (optarg2 !== undefined) {
            opts = optarg1;
            callback = optarg2;
        } else {
            callback = optarg1;
        }

        opts = opts || {};

        if (typeof callback != 'function') {
            throw new Error('callback must be a function');
        }

        if (!url) {
            throw new Error('no url specified');
        }

        switch (method) {
            case 'GET':
                if (isObject(data)) {
                    for (var k in data) {
                        if (data.hasOwnProperty(k)) {
                            url += (url.indexOf('?') == -1 ? '?' : '&')+encodeURIComponent(k)+'='+encodeURIComponent(data[k])
                        }
                    }
                }
                break;

            case 'POST':
                if (isObject(data)) {
                    var sdata = [];
                    for (var k in data) {
                        if (data.hasOwnProperty(k)) {
                            sdata.push(encodeURIComponent(k)+'='+encodeURIComponent(data[k]));
                        }
                    }
                    data = sdata.join('&');
                }
                break;
        }

        opts = extend({}, defaultOpts, opts);

        var xhr = createXMLHttpRequest();
        xhr.open(method, url);

        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        if (method == 'POST') {
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        }

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if ('status' in xhr && !/^2|1223/.test(xhr.status)) {
                    throw new Error('http code '+xhr.status)
                }
                if (opts.json) {
                    var resp = JSON.parse(xhr.responseText)
                    if (!isObject(resp)) {
                        throw new Error('ajax: object expected')
                    }
                    if (resp.error) {
                        throw new Error(resp.error)
                    }
                    callback(null, resp.response);
                } else {
                    callback(null, xhr.responseText);
                }
            }
        };

        xhr.onerror = function(e) {
            callback(e);
        };

        xhr.send(method == 'GET' ? null : data);

        return xhr;
    }

    window.ajax = {
        get: request.bind(request, 'GET'),
        post: request.bind(request, 'POST')
    }

})();

function bindEventHandlers(obj) {
    for (var k in obj) {
        if (obj.hasOwnProperty(k)
            && typeof obj[k] == 'function'
            && k.length > 2
            && k.startsWith('on')
            && k[2].charCodeAt(0) >= 65
            && k[2].charCodeAt(0) <= 90) {
            obj[k] = obj[k].bind(obj)
        }
    }
}

//
// DOM helpers
//
function ge(id) {
    return document.getElementById(id)
}

function hasClass(el, name) {
    return el && el.nodeType === 1 && (" " + el.className + " ").replace(/[\t\r\n\f]/g, " ").indexOf(" " + name + " ") >= 0
}

function addClass(el, name) {
    if (!el) {
        return console.warn('addClass: el is', el)
    }
    if (!hasClass(el, name)) {
        el.className = (el.className ? el.className + ' ' : '') + name
    }
}

function removeClass(el, name) {
    if (!el) {
        return console.warn('removeClass: el is', el)
    }
    if (isArray(name)) {
        for (var i = 0; i < name.length; i++) {
            removeClass(el, name[i]);
        }
        return;
    }
    el.className = ((el.className || '').replace((new RegExp('(\\s|^)' + name + '(\\s|$)')), ' ')).trim()
}

function addEvent(el, type, f, useCapture) {
    if (!el) {
        return console.warn('addEvent: el is', el, stackTrace())
    }

    if (isArray(type)) {
        for (var i = 0; i < type.length; i++) {
            addEvent(el, type[i], f, useCapture);
        }
        return;
    }

    if (el.addEventListener) {
        el.addEventListener(type, f, useCapture || false);
        return true;
    } else if (el.attachEvent) {
        return el.attachEvent('on' + type, f);
    }

    return false;
}

function removeEvent(el, type, f, useCapture) {
    if (isArray(type)) {
        for (var i = 0; i < type.length; i++) {
            var t = type[i];
            removeEvent(el, type[i], f, useCapture);
        }
        return;
    }

    if (el.removeEventListener) {
        el.removeEventListener(type, f, useCapture || false);
    } else if (el.detachEvent) {
        return el.detachEvent('on' + type, f);
    }

    return false;
}

function cancelEvent(evt) {
    if (!evt) {
        return console.warn('cancelEvent: event is', evt)
    }

    if (evt.preventDefault) evt.preventDefault();
    if (evt.stopPropagation) evt.stopPropagation();

    evt.cancelBubble = true;
    evt.returnValue = false;

    return false;
}


//
// Cookies
//
function setCookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function unsetCookie(name) {
    document.cookie = name+'=; Max-Age=-99999999;';
}

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ')
            c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0)
            return c.substring(nameEQ.length, c.length);
    }
    return null;
}

//
// Misc
//
function isObject(o) {
    return Object.prototype.toString.call(o) === '[object Object]';
}

function isArray(a) {
    return Object.prototype.toString.call(a) === '[object Array]';
}

function extend(dst, src) {
    if (!isObject(dst)) {
        return console.error('extend: dst is not an object');
    }
    if (!isObject(src)) {
        return console.error('extend: src is not an object');
    }
    for (var key in src) {
        dst[key] = src[key];
    }
    return dst;
}

function stackTrace(split) {
    if (split === undefined) {
        split = true;
    }
    try {
        o.lo.lo += 0;
    } catch(e) {
        if (e.stack) {
            var stack = split ? e.stack.split('\n') : e.stack;
            stack.shift();
            stack.shift();
            return stack.join('\n');
        }
    }
    return null;
}

function escape(str) {
    var pre = document.createElement('pre');
    var text = document.createTextNode(str);
    pre.appendChild(text);
    return pre.innerHTML;
}

//
//

function lang(key) {
    return __lang[key] !== undefined ? __lang[key] : '{'+key+'}';
}

var DynamicLogo = {
    dynLink: null,
    afr: null,
    afrUrl: null,

    init: function() {
        this.dynLink = ge('head_dyn_link');
        this.cdText = ge('head_cd_text');

        if (!this.dynLink) {
            return console.warn('DynamicLogo.init: !this.dynLink');
        }

        var spans = this.dynLink.querySelectorAll('span.head-logo-path');
        for (var i = 0; i < spans.length; i++) {
            var span = spans[i];
            addEvent(span, 'mouseover', this.onSpanOver);
            addEvent(span, 'mouseout', this.onSpanOut);
        }
    },

    setUrl: function(url) {
        if (this.afr !== null) {
            cancelAnimationFrame(this.afr);
        }
        this.afrUrl = url;
        this.afr = requestAnimationFrame(this.onAnimationFrame);
    },

    onAnimationFrame: function() {
        var url = this.afrUrl;

        // update link
        this.dynLink.setAttribute('href', url);

        // update console text
        if (this.afrUrl === '/') {
            url = '~';
        } else {
            url = '~'+url.replace(/\/$/, '');
        }
        this.cdText.innerHTML = escape(url);

        this.afr = null;
    },

    onSpanOver: function() {
        var span = event.target;
        this.setUrl(span.getAttribute('data-url'));
        cancelEvent(event);
    },

    onSpanOut: function() {
        var span = event.target;
        this.setUrl('/');
        cancelEvent(event);
    }
};
bindEventHandlers(DynamicLogo);

window.__lang = {};

// set/remove retina cookie
(function() {
    var isRetina = window.devicePixelRatio >= 1.5;
    if (isRetina) {
        setCookie('is_retina', 1, 365);
    } else {
        unsetCookie('is_retina');
    }
})();
