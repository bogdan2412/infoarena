/**
 * DHTML for newsletter preview
 * (c) 2009 and onwards, infoarena
 */

// Resize iframe element height to frame document height. This
// should make the iframe vertical scrollbar disappear.
function Newsletter_ResizePreviewFrame() {
    var iframe = window.frameElement;
    if (iframe) {
        var new_height = document.body.scrollHeight + 10;
        iframe.style.height = new_height + 'px';
    }
}

function Newsletter_Init() {
    if (!window.frameElement) {
        /* Only trigger inside the iframe. */
        return;
    }
    Newsletter_ResizePreviewFrame();
    Newsletter_HijackLinks();
}

// Hijack all anchor clicks to Newsletter_LinkClick.
// This only applies inside the iframe.
function Newsletter_HijackLinks() {
    map(function(anchor) { anchor.onclick=Newsletter_LinkClick; },
        MochiKit.DOM.getElementsByTagAndClassName('a'));
}

// Click handler for links inside the iframe.
// Instead of navigating inside the iframe, we change the location
// of the parent window.
function Newsletter_LinkClick(event) {
    if (window.frameElement) {
        window.parent.location = this.href;
    }
    return false;
}

connect(window, 'onload', Newsletter_Init);
