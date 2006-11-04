<?php

// link JS
$view['head'] = getattr($view, 'head') . "<script type=\"text/javascript\" src=\"" . url("static/js/wikiedit.js") . "\"></script>";

// mark-up necessary for displaying preview
$wikiedit['preview'] = '
<input type="hidden" id="form_page_name" value="' . $page_name . '" />
<div class="wiki_text_block" id="wiki_preview" style="display: none;"></div>
<div id="wiki_preview_toolbar" style="display: none;">
    <input type="button" class="button" id="preview_close" value="Ascunde Preview" />
</div>
';

// field for editing title
$wikiedit['title'] = '
    <li id="field_title">
        <label for="form_title">Titlu</label>
        <input type="text" name="title" value="' . fval('title') . '" id="form_title"/>
        ' . ferr_span('title') . '
    </li>
';

// field for editing content
$wikiedit['content'] = '
    <li id="field_content">
        <label for="form_content">Continut</label>
        <textarea name="content" id="form_content" rows="10" cols="50">' . fval('content') . '</textarea>
        '. ferr_span('content') . '
        <span class="fieldHelp"><a href="' . url('textile') . '">Cum formatez text?</a></span>
    </li>
';

// buttons
$wikiedit['submit'] = '
	<li id="form_submit">
		<input type="submit" value="Salveaza" id="form_submit" class="button important" />
		<input type="button" value="Preview" id="form_preview" class="button" />
	</li>
';

