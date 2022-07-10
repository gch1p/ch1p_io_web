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