<?php

include('header.php');
//
// Test request handler for wiki processing.
//
print('<form method="get" action="'.url('WikiTest').'">');
print('<textarea name="wikitext">'.getattr($_REQUEST, 'wikitext', "Please edit me").'</textarea>');
print('<br />');
print('<input type="submit" value="Process"></input>');
print('</form>');
print('<br />');

if (request('wikitext') !== null) {
    print('<p><b>Wiki formatted text:</b></p>');
    print('<span class="wikitext">');
    echo wiki_process_text(request('wikitext'), $view);
    print('</span>');
}
include('footer.php');
?>
