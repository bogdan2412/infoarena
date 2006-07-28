/**
 * DHTML for wiki editing page.
 * (c) 2006 info-arena
 */ 
function WikiEdit_Init() {
    // wiki preview
    var b1 = $('form_preview');
    var b2 = $('preview_close');
    var b3 = $('preview_reload');
    connect(b1, 'onclick', WikiEdit_Preview);
    connect(b2, 'onclick', WikiEdit_ClosePreview);
    connect(b3, 'onclick', WikiEdit_Preview);
}

function WikiEdit_Preview() {
    var container = $('wiki_preview');
    var toolbar = $('wiki_preview_toolbar');
    var content = $('form_content');
    if (!container || !content) return;

    // visual clue to indicate that preview is loading
    container.innerHTML = '<div class="loading"> <img src="' + BASE_HREF + 'static/images/indicator.gif" />Se incarca ...</div>';

    // request preview
    // :TODO: some web servers and proxies limit GET requests to a maximum
    // size. This should really be POST-ed. Perhaps when MochiKit starts
    // supporting loadJSONDoc via POST?
    var d = loadJSONDoc(BASE_HREF + 'json/wiki-preview?content=' + escape(content.value));

    var ready = function(data) {
        container.innerHTML = data['html'];
        container.style.display = '';
        toolbar.style.display = '';
    }

    var error = function(error) {
        window.alert('Eroare! Nu pot face preview. Incearcati din nou.');
    }

    d.addCallbacks(ready, error);
}

function WikiEdit_ClosePreview() {
    var container = $('wiki_preview');
    var toolbar = $('wiki_preview_toolbar');

    if (!container || !toolbar) return;

    container.style.display = 'none';
    toolbar.style.display = 'none';
}

connect(window, 'onload', WikiEdit_Init);

