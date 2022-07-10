var ThemeSwitcher = (function() {
    /**
     * @type {string[]}
     */
    var modes = ['auto', 'dark', 'light'];

    /**
     * @type {number}
     */
    var currentModeIndex = -1;

    /**
     * @type {boolean|null}
     */
    var systemState = null;

    /**
     * @returns {boolean}
     */
    function isSystemModeSupported() {
        try {
            // crashes on:
            // Mozilla/5.0 (Windows NT 6.2; ARM; Trident/7.0; Touch; rv:11.0; WPDesktop; Lumia 630 Dual SIM) like Gecko
            // Mozilla/5.0 (iPhone; CPU iPhone OS 13_5_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Mobile/15E148 Safari/604.1
            // Mozilla/5.0 (iPad; CPU OS 12_5_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.2 Mobile/15E148 Safari/604.1
            //
            // error examples:
            // - window.matchMedia("(prefers-color-scheme: dark)").addEventListener is not a function. (In 'window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change",this.onSystemSettingChange.bind(this))', 'window.matchMedia("(prefers-color-scheme: dark)").addEventListener' is undefined)
            // - Object [object MediaQueryList] has no method 'addEventListener'
            return !!window['matchMedia']
                && typeof window.matchMedia("(prefers-color-scheme: dark)").addEventListener === 'function';
        } catch (e) {
            return false
        }
    }

    /**
     * @returns {boolean}
     */
    function isDarkModeApplied() {
        var st = StaticManager.loadedStyles;
        for (var i = 0; i < st.length; i++) {
            var name = st[i];
            if (ge('style_'+name+'_dark'))
                return true;
        }
        return false;
    }

    /**
     * @returns {string}
     */
    function getSavedMode() {
        var val = getCookie('theme');
        if (!val)
            return modes[0];
        if (modes.indexOf(val) === -1) {
            console.error('[ThemeSwitcher getSavedMode] invalid cookie value')
            unsetCookie('theme')
            return modes[0]
        }
        return val
    }

    /**
     * @param {boolean} dark
     */
    function changeTheme(dark) {
        addClass(document.body, 'theme-changing');

        var onDone = function() {
            window.requestAnimationFrame(function() {
                removeClass(document.body, 'theme-changing');
            })
        };

        window.requestAnimationFrame(function() {
            if (dark)
                enableDark(onDone);
            else
                disableDark(onDone);
        })
    }

    /**
     * @param {function} callback
     */
    function enableDark(callback) {
        var names = [];
        StaticManager.loadedStyles.forEach(function(name) {
            var el = ge('style_'+name+'_dark');
            if (el)
                return;
            names.push(name);
        });

        var left = names.length;
        names.forEach(function(name) {
            StaticManager.loadStyle(name, 'dark', once(function(e) {
                left--;
                if (left === 0)
                    callback();
            }));
        })
    }

    /**
     * @param {function} callback
     */
    function disableDark(callback) {
        StaticManager.loadedStyles.forEach(function(name) {
            var el = ge('style_'+name+'_dark');
            if (el)
                el.remove();
        })
        callback();
    }

    /**
     * @param {string} mode
     */
    function setLabel(mode) {
        var labelEl = ge('theme-switcher-label');
        labelEl.innerHTML = escape(lang('theme_'+mode));
    }

    return {
        init: function() {
            var cur = getSavedMode();
            currentModeIndex = modes.indexOf(cur);

            var systemSupported = isSystemModeSupported();
            if (!systemSupported) {
                if (currentModeIndex === 0) {
                    modes.shift(); // remove 'auto' from the list
                    currentModeIndex = 1; // set to 'light'
                    if (isDarkModeApplied())
                        disableDark();
                }
            } else {
                /**
                 * @param {boolean} dark
                 */
                var onSystemChange = function(dark) {
                    var prevSystemState = systemState;
                    systemState = dark;

                    if (modes[currentModeIndex] !== 'auto')
                        return;

                    if (systemState !== prevSystemState)
                        changeTheme(systemState);
                };

                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                    onSystemChange(e.matches === true)
                });

                onSystemChange(window.matchMedia('(prefers-color-scheme: dark)').matches === true);
            }

            setLabel(modes[currentModeIndex]);
        },

        next: function(e) {
            if (hasClass(document.body, 'theme-changing')) {
                console.log('next: theme changing is in progress, ignoring...')
                return;
            }

            currentModeIndex = (currentModeIndex + 1) % modes.length;
            switch (modes[currentModeIndex]) {
                case 'auto':
                    if (systemState !== null)
                        changeTheme(systemState);
                    break;

                case 'light':
                    if (isDarkModeApplied())
                        changeTheme(false);
                    break;

                case 'dark':
                    if (!isDarkModeApplied())
                        changeTheme(true);
                    break;
            }

            setLabel(modes[currentModeIndex]);
            setCookie('theme', modes[currentModeIndex]);

            return cancelEvent(e);
        }
    };
})();