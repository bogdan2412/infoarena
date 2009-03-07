/*
 * DHTML for RemoteBox macro
 * (c) infoarena
 */

var RemoteBox_Url = '';

function RemoteBox_Load(remotebox_function) {
    var container = $('remotebox');
    if (!container || !RemoteBox_Url) {
        // no remotebox in this page
        return;
    }

    // visual clue to indicate that remotebox is loading
    container.innerHTML = '<div class="loading"> <img src="/static/images/indicator.gif" />Se incarca ...</div>';

    var d = doSimpleXMLHttpRequest(RemoteBox_Url);

    var ready = function(data) {
        if (data) {
            container.innerHTML = data.responseText;
        }
    }

    var error = function(error) {
        container.innerHTML = '<div class="macro_error">Continutul nu a putut fi descarcat. Incercati din nou.</div>';
    }

    d.addCallbacks(ready, error);
    d.addCallbacks(remotebox_function, null);
}

function RemoteBox_Comments(begin_comm, max_comm, focus_on_comments, display) {
    var RemoteBox_Base_Url = RemoteBox_Url;

    RemoteBox_Url = RemoteBox_Url + "&display=" + display +
            "&begin_comm=" + begin_comm +
            "&max_comm=" + max_comm;
    if (focus_on_comments == true) {
        var remotebox_function = function() {
            // set the anchor to the "comentarii" element
            window.location.hash = "comentarii";
        }
        RemoteBox_Load(remotebox_function);
    } else {
        RemoteBox_Load(null);
    }

    RemoteBox_Url = RemoteBox_Base_Url;
}

connect(window, 'onload', RemoteBox_Load);

