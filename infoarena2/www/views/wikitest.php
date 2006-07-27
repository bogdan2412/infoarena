<?php

include('header.php');

//
// Test request handler for wiki processing.
//

$wikitext = getattr($_REQUEST, 'wikitext', "Please edit me");
print('<form method="post" action="'.url('WikiTest').'">');
print('<textarea name="wikitext" rows="20" cols="75">'.$wikitext.'</textarea>');
print('<br />');
print('<input type="submit" value="Process"></input>');
print('</form>');
print('<br />');

if (isset($_REQUEST['wikitext'])) {
    print('<p><b>Wiki formatted text:</b></p>');
    print('<span class="wikitext">');
    echo wiki_process_text(request('wikitext'), $view);
    print('</span>');
}
include('footer.php');
?>
