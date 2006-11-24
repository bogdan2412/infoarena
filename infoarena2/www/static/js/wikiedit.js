/**
 * DHTML for wiki editing page.
 * (c) infoarena
 */

var WikiEdit_Saved = false;

function WikiEdit_Init() {
    // wiki preview
    var b1 = $('form_preview');
    var b2 = $('preview_close');
    var felem = $('form_wikiedit');
    var fc = $('form_content');
    connect(b1, 'onclick', WikiEdit_Preview);
    connect(b2, 'onclick', WikiEdit_ClosePreview);
    connect(felem, 'onsubmit', WikiEdit_ObserveSave);
    connect(fc, 'onkeypress', WikiEdit_ObserveChange);
    window.onbeforeunload = WikiEdit_Leave;

    WikiEdit_Saved = true;
}

function WikiEdit_Preview() {
    var container = $('wiki_preview');
    var toolbar = $('wiki_preview_toolbar');
    var content = $('form_content');
    var page_name = $('form_page_name');
    if (!container || !content) return;

    // visual clue to indicate that preview is loading
    container.innerHTML = '<div class="loading"> <img src="' + BASE_HREF + 'static/images/indicator.gif" />Se incarca ...</div>';
    container.style.display = '';

    // request preview
    var d = doXHR(BASE_HREF + 'json/wiki-preview?page_name=' + escape(page_name.value), {method: 'POST', sendContent: content.value});

    var ready = function(xhr) {
        var data = evalJSONRequest(xhr);
        container.innerHTML = data['html'];
        container.style.display = '';
        toolbar.style.display = '';
    }

    var error = function(error) {
        window.alert('Eroare! Nu pot face preview. Incearcati din nou.');
        WikiEdit_ClosePreview();
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

function WikiEdit_Leave(event) {
    if (WikiEdit_Saved) {
        return;
    }

    var message = "Ati facut modificari pe care nu le-ati salvat. Doriti sa parasiti pagina?";
    if (typeof event == 'undefined') {
        event = window.event;
    }
    if (event) {
        event.returnValue = message;
    }
    return message;
}

function WikiEdit_ObserveSave() {
    WikiEdit_Saved = true;
}

function WikiEdit_ObserveChange() {
    WikiEdit_Saved = false;

    var fc = $('form_content');
    fc.onkeypress = null;
}

connect(window, 'onload', WikiEdit_Init);

