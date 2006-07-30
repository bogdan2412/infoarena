<?php
$view['head'] = "<script type=\"text/javascript\" src=\"" . url("static/js/wikiedit.js") . "\"></script>";
?>

<?php
include('header.php'); 
?>

<div class="wiki_text_block" id="wiki_preview" style="display: none;"></div>
<div id="wiki_preview_toolbar" style="display: none;">
    <input type="button" class="button" id="preview_close" value="Ascunde Preview" />
    <input type="button" class="button" id="preview_reload" value="Re-incarca" />
</div>

<form action="<?= getattr($view, 'action') ?>" method="post">
<ul class="form">
    <li>
        <label for="form_title">Titlu</label>
        <input type="text" name="title" value="<?= fval('title') ?>" id="form_title"/>
        <?php if (ferr('title')) { ?>
        <span class="fieldError"><?= ferr('title') ?></span>
        <?php } ?>        
    </li>
    
    <li>
        <label for="form_content">Continut</label>
        <?php if (ferr('content')) { ?>
        <span class="fieldError"><?= ferr('content') ?></span>
        <?php } ?>        
        <textarea name="content" id="form_content" rows="10" cols="50"><?= fval('content') ?></textarea>
        <span class="fieldHelp"><a href="<?= url('textile') ?>">Cum formatez text?</a></span>
    </li>
    
	<li>
		<input type="submit" value="Salveaza" id="form_submit" class="button important" />
		<input type="button" value="Preview" id="form_preview" class="button" />
	</li>
</ul>
</form>
<?php include('footer.php'); ?>
