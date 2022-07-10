var Draft = {
    get: function() {
        if (!LS) return null;

        var title = LS.getItem('draft_title') || null;
        var text = LS.getItem('draft_text') || null;

        return {
            title: title,
            text: text
        };
    },

    setTitle: function(text) {
        if (!LS) return null;
        LS.setItem('draft_title', text);
    },

    setText: function(text) {
        if (!LS) return null;
        LS.setItem('draft_text', text);
    },

    reset: function() {
        if (!LS) return;
        LS.removeItem('draft_title');
        LS.removeItem('draft_text');
    }
};