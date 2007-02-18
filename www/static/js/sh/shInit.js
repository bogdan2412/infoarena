
function Sh_Init() {
    if (!dp) {
        return;
    }
    dp.SyntaxHighlighter.HighlightAll('code');
}

connect(window, 'onload', Sh_Init);

