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

function timestamp() {
    return Math.floor(Date.now() / 1000)
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

function parseUrl(uri) {
    var parser = document.createElement('a');
    parser.href = uri;

    return {
        protocol: parser.protocol, // => "http:"
        host: parser.host,         // => "example.com:3000"
        hostname: parser.hostname, // => "example.com"
        port: parser.port,         // => "3000"
        pathname: parser.pathname, // => "/pathname/"
        hash: parser.hash,         // => "#hash"
        search: parser.search,     // => "?search=test"
        origin: parser.origin,     // => "http://example.com:3000"
        path: (parser.pathname || '') + (parser.search || '')
    }
}

function once(fn, context) {
    var result;
    return function() {
        if (fn) {
            result = fn.apply(context || this, arguments);
            fn = null;
        }
        return result;
    };
}