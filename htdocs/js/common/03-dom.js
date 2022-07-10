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
    document.cookie = name + "=" + (value || "") + expires + "; domain=" + window.appConfig.cookieHost + "; path=/";
}

function unsetCookie(name) {
    document.cookie = name + '=; Max-Age=-99999999; domain=' + window.appConfig.cookieHost + "; path=/";
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
