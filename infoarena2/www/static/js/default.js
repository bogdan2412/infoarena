/**
 * sitewide JS
 * (c) infoarena
 */

function Page_Init() {
    // fade away flash message
    var flash = $('flash');
    if (flash && !hasElementClass(flash, 'flashError')) {
        var callback = function() {
            hideElement(flash);
        }
        setTimeout(callback, 4000);
    }
}

connect(window, 'onload', Page_Init);

