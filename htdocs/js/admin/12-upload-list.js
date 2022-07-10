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
