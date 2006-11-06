/**
 * (c) 2006 info-arena
 */

function Page_Init() {
    // fade away flash message
    var flash = $('flash');
    if (flash) {
        var callback = function() {
            hideElement(flash);
        }
        setTimeout(callback, 4000);
    } 
}

connect(window, 'onload', Page_Init);

