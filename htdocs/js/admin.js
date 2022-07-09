var LS = window.localStorage;

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

var AdminWriteForm = {
    form: null,
    previewTimeout: null,
    previewRequest: null,

    init: function(opts) {
        opts = opts || {};

        this.opts = opts;
        this.form = document.forms[opts.pages ? 'pageForm' : 'postForm'];
        this.isFixed = false;

        addEvent(this.form, 'submit', this.onSubmit);
        if (!opts.pages)
            addEvent(this.form.title, 'input', this.onInput);

        addEvent(this.form.text, 'input', this.onInput);
        addEvent(ge('toggle_wrap'), 'click', this.onToggleWrapClick);

        if (this.form.text.value !== '')
            this.showPreview();

        // TODO make it more clever and context-aware
        /*var draft = Draft.get();
        if (draft.title)
            this.form.title.value = draft.title;
        if (draft.text)
            this.form.text.value = draft.text;*/

        addEvent(window, 'scroll', this.onScroll);
        addEvent(window, 'resize', this.onResize);
    },

    showPreview: function() {
        if (this.previewRequest !== null) {
            this.previewRequest.abort();
        }
        this.previewRequest = ajax.post('/admin/markdown-preview.ajax', {
            title: this.form.elements.title.value,
            md: this.form.elements.text.value,
            use_image_previews: this.opts.pages ? 1 : 0
        }, function(err, response) {
            if (err)
                return console.error(err);
            ge('preview_html').innerHTML = response.html;
        });
    },

    onSubmit: function(event) {
        try {
            var fields = ['title', 'text'];
            if (!this.opts.pages)
                fields.push('tags');
            if (this.opts.edit) {
                fields.push('new_short_name');
            } else {
                fields.push('short_name');
            }
            for (var i = 0; i < fields.length; i++) {
                var field = fields[i];
                if (event.target.elements[field].value.trim() === '')
                    throw 'no_'+field
            }

            // Draft.reset();
        } catch (e) {
            var error = typeof e == 'string' ? lang((this.opts.pages ? 'err_pages_' : 'err_blog_')+e) : e.message;
            alert(error);
            console.error(e);
            return cancelEvent(event);
        }
    },

    onToggleWrapClick: function(e) {
        var textarea = this.form.elements.text;
        if (!hasClass(textarea, 'nowrap')) {
            addClass(textarea, 'nowrap');
        } else {
            removeClass(textarea, 'nowrap');
        }
        return cancelEvent(e);
    },

    onInput: function(e) {
        if (this.previewTimeout !== null) {
            clearTimeout(this.previewTimeout);
        }
        this.previewTimeout = setTimeout(function() {
            this.previewTimeout = null;
            this.showPreview();

            // Draft[e.target.name === 'title' ? 'setTitle' : 'setText'](e.target.value);
        }.bind(this), 300);
    },

    onScroll: function() {
        var ANCHOR_TOP = 10;

        var y    = window.pageYOffset;
        var form = this.form;
        var td   = ge('form_first_cell');
        var ph   = ge('form_placeholder');

        var rect = td.getBoundingClientRect();

        if (rect.top <= ANCHOR_TOP && !this.isFixed) {
            ph.style.height = form.getBoundingClientRect().height+'px';

            var w = (rect.width - (parseInt(getComputedStyle(td).paddingRight, 10) || 0));
            form.style.display = 'block';
            form.style.width = w+'px';
            form.style.position = 'fixed';
            form.style.top = ANCHOR_TOP+'px';

            this.isFixed = true;
        } else if (rect.top > ANCHOR_TOP && this.isFixed) {
            form.style.display = '';
            form.style.width = '';
            form.style.position = '';
            form.style.position = '';
            ph.style.height = '';

            this.isFixed = false;
        }
    },

    onResize: function() {
        if (this.isFixed) {
            var form = this.form;
            var td   = ge('form_first_cell');
            var ph   = ge('form_placeholder');

            var rect = td.getBoundingClientRect();
            var pr   = parseInt(getComputedStyle(td).paddingRight, 10) || 0;

            ph.style.height = form.getBoundingClientRect().height+'px';
            form.style.width = (rect.width - pr) + 'px';
        }
    }
};
bindEventHandlers(AdminWriteForm);

var BlogUploadList = {
    submitNoteEdit: function(action, note) {
        if (note === null)
            return;

        var form = document.createElement('form');
        form.setAttribute('method', 'post');
        form.setAttribute('action', action);

        var input = document.createElement('input');
        input.setAttribute('type', 'hidden');
        input.setAttribute('name', 'note');
        input.setAttribute('value', note);

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
};
