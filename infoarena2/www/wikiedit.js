/**
 * (c) 2006 info-arena
 */ 
function Page_Init() {
    // flash fade away
    var flash = $('flash');
    var callback = function() {
        flash.style.display = 'none';
    }
    setTimeout(callback, 4000);
}

connect(window, 'onload', Page_Init);

