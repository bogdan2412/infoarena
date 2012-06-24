/*
 * DHTML for RemoteBox macro
 * (c) infoarena
 */

var RemoteBox_Url = '';

function RemoteBox_Load(remotebox_function ) {
    var container = $('#remotebox');

    if (container.length == 0 || !RemoteBox_Url) {
        // no remotebox in this page
        return;
    }

    // visual clue to indicate that remotebox is loading
    container.html('<div class="loading"> <img src="/static/images/indicator.gif" />Se incarca ...</div>');

    container.load(RemoteBox_Url, {},
        function(responseText, textStatus, req) {
            if (textStatus == 'error') {
                 container.html('<div class="macro_error">Continutul nu a putut fi descarcat. Incercati din nou.</div>');
            }
            if (typeof remotebox_function === 'function') {
                remotebox_function();
            }
        });
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

$(document).ready(RemoteBox_Load);
