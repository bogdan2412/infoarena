/*
 * DHTML for RemoteBox macro
 * (c) infoarena
 */

var RemoteBox_Url = '';
var RemoteBox_Display = 'hide';
var RemoteBox_BeginComm = 1;
var RemoteBox_MaxComm = 10;

function RemoteBox_Load() {
    var container = $('remotebox');
    if (!container || !RemoteBox_Url) {
        // no remotebox in this page
        return;
    }

    // visual clue to indicate that remotebox is loading
    container.innerHTML = '<div class="loading"> <img src="/static/images/indicator.gif" />Se incarca ...</div>';

    var d = doSimpleXMLHttpRequest(RemoteBox_Url + "&display=" + RemoteBox_Display +
            "&begin_comm=" + RemoteBox_BeginComm +
            "&max_comm=" + RemoteBox_MaxComm);

    var ready = function(data) {
        if (data) {
            container.innerHTML = data.responseText;
        }
    }

    var error = function(error) {
        container.innerHTML = '<div class="macro_error">Continutul nu a putut fi descarcat. Incercati din nou.</div>';
    }

    d.addCallbacks(ready, error);
}

connect(window, 'onload', RemoteBox_Load);

