/**
 * DHTML for wiki editing page.
 * (c) infoarena
 */

var WikiEdit_Saved = false;

function WikiEdit_Init() {
    // wiki preview
    $('#form_preview').on('click', WikiEdit_Preview);
    $('#preview_close').on('click', WikiEdit_ClosePreview);
    $('#form_wikiedit').on('submit', WikiEdit_ObserveSave);
    $('#form_text').one('keypress', WikiEdit_ObserveChange);
    $(window).on("beforeunload", WikiEdit_Leave);

    WikiEdit_Saved = true;
}

function WikiEdit_Preview() {
    var container = $('#wiki_preview');
    var toolbar = $('#wiki_preview_toolbar');
    var content = $('#form_text');
    var page_name = $('#form_page_name');
    if (container.length == 0 || content.length == 0) return;

    // visual clue to indicate that preview is loading
    container.html('<div class="loading"> <img src="'
        + BASE_HREF + 'static/images/indicator.gif" />Se încarcă...</div>');
    container.show();

    // request preview
    $.ajax({url:BASE_HREF + 'json/wiki-preview?page_name=' + escape(page_name.val()), type:'POST', dataType: 'json', data: content.val(), success:
        function(data, textStatus, req) {
            if (textStatus == 'error') {
                alert('Eroare! Nu pot face preview. Încercați din nou.');
                WikiEdit_ClosePreview();
                return;
            }
            container.html(data['html']);
            container.show();
            toolbar.show();
        }});
}

function WikiEdit_ClosePreview() {
    var container = $('#wiki_preview');
    var toolbar = $('#wiki_preview_toolbar');

    if (container.length == 0 || toolbar.length == 0) return;

    container.hide();
    toolbar.hide();
}

function WikiEdit_Leave(event) {
    if (WikiEdit_Saved) {
        return;
    }

    var message = "Ați făcut modificări pe care nu le-ați salvat. Doriți să părăsiți pagina?";
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
}

$(document).ready(WikiEdit_Init);
