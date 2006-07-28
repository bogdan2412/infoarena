<?php
$view['head'] = "<script type=\"text/javascript\" src=\"" . url("static/js/wikiedit.js") . "\"></script>";
?>

<?php include('header.php'); ?>

<div id="wiki_preview" style="display: none;"></div>
<div id="wiki_preview_toolbar" style="display: none;">
    <input type="button" class="button" id="preview_close" value="Ascunde Preview" />
    <input type="button" class="button" id="preview_reload" value="Re-incarca" />
</div>

<form action="<?= getattr($view, 'action') ?>" method="post">
<ul class="form">
    <li>
        <label for="form_content">Continut</label>
        <textarea name="content" id="form_content" rows="10" cols="50"><?= fval('content') ?></textarea>
    </li>
    
	<li>
		<input type="submit" value="Salveaza" id="form_submit" class="button important" />
		<input type="button" value="Preview" id="form_preview" class="button" />
	</li>
</ul>
</form>

<?php include('footer.php'); ?>

