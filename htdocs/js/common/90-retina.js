// set/remove retina cookie
(function() {
    var isRetina = window.devicePixelRatio >= 1.5;
    if (isRetina) {
        setCookie('is_retina', 1, 365);
    } else {
        unsetCookie('is_retina');
    }
})();