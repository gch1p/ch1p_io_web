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

        opts = Object.assign({}, defaultOpts, opts);

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
