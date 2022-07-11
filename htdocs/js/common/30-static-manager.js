var StaticManager = {
    /**
     * @type {string[]}
     */
    loadedStyles: [],

    /**
     * @type {object}
     */
    versions: {},

    /**
     * @param {string[]} loadedStyles
     * @param {object} versions
     */
    init: function(loadedStyles, versions) {
        this.loadedStyles = loadedStyles;
        this.versions = versions;
    },

    /**
     * @param {string} name
     * @param {string} theme
     * @param {function} callback
     */
    loadStyle: function(name, theme, callback) {
        var url, id;
        if (!window.appConfig.devMode) {
            if (theme === 'dark')
                name += '_dark';
            url = '/dist-css/'+name+'.css?'+this.versions.css[name];
            id = 'style_'+name;
        } else {
            url = '/sass.php?name='+name+'&theme='+theme+'&v='+timestamp();
            id = 'style_'+name+(theme === 'dark' ? '_dark' : '');
        }

        var el = document.createElement('link');
        el.onerror = callback;
        el.onload = callback;
        el.setAttribute('rel', 'stylesheet');
        el.setAttribute('type', 'text/css');
        el.setAttribute('id', id);
        el.setAttribute('href', url);

        document.getElementsByTagName('head')[0].appendChild(el);
    }
};
