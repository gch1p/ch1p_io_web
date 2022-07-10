var StaticManager = {
    loadedStyles: [],
    versions: {},

    init: function(loadedStyles, versions) {
        this.loadedStyles = loadedStyles;
        this.versions = versions;
    },

    loadStyle: function(name, theme, callback) {
        var url;
        if (!window.appConfig.devMode) {
            if (theme === 'dark')
                name += '_dark';
            url = '/css/'+name+'.css?'+this.versions.css[name];
        } else {
            url = '/sass.php?name='+name+'&theme='+theme+'&v='+timestamp();
        }

        var el = document.createElement('link');
        el.onerror = callback;
        el.onload = callback;
        el.setAttribute('rel', 'stylesheet');
        el.setAttribute('type', 'text/css');
        el.setAttribute('id', 'style_'+name);
        el.setAttribute('href', url);

        document.getElementsByTagName('head')[0].appendChild(el);
    }
};
