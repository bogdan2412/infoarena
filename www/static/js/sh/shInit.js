
function Sh_Init() {
    if (!dp) {
        return;
    }
    dp.SyntaxHighlighter.ClipboardSwf = '../static/js/sh/clipboard.swf';
    dp.SyntaxHighlighter.HighlightAll('code');
}

connect(window, 'onload', Sh_Init);

