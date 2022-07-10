function lang(key) {
    return __lang[key] !== undefined ? __lang[key] : '{'+key+'}';
}

window.__lang = {};