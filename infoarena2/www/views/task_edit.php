<?php
$view['head'] = "<script type=\"text/javascript\" src=\"" . url("static/js/wikiedit.js") . "\"></script>";
?>

<?php
include('header.php'); 
?>

<h1><?= getattr($view, 'title') ?></h1>

<div class="wiki_text_block" id="wiki_preview" style="display: none;"></div>
<div id="wiki_preview_toolbar" style="display: none;">
    <input type="button" class="button" id="preview_close" value="Ascunde Preview" />
    <input type="button" class="button" id="preview_reload" value="Re-incarca" />
</div>

<form action="<?= getattr($view, 'action') ?>" method="post" class="task">
<div class="tabber">
    <div class="tabbertab<?= 'statement' == $active_tab ? ' tabbertabdefault' : '' ?> statement">
        <h3>Enunt</h3>
        <ul class="form">
            <li id="field_title">
                <label for="form_title">Titlu</label>
                <input type="text" name="title" value="<?= fval('title') ?>" id="form_title"/>
                <?= ferr_span('title') ?>
            </li>
            
            <li id="field_author">
                <label for="form_author">Autor(i)</label>
                <input type="text" name="author" value="<?= fval('author') ?>" id="form_author"/>
                <?= ferr_span('author') ?>
            </li>
            
            <li id="field_source">
                <label for="form_source">Sursa</label>
                <input type="text" name="source" value="<?= fval('source') ?>" id="form_source"/>
                <?= ferr_span('source') ?>
            </li>
            
            <li id="field_content">
                <label for="form_content">Enunt</label>
                <textarea name="text" id="form_content" rows="10" cols="50"><?= fval('text') ?></textarea>
                <?= ferr_span('text') ?>
            </li>    
        </ul>
    </div>

    <div class="tabbertab<?= 'parameters' == $active_tab ? ' tabbertabdefault' : '' ?> parameters">
        <h2>Parametri</h2>

<ul class="form">
    <li id="field_type">
        <label for="form_type">Tip task</label>
            <select name="type" id="form_type">
                <option value=""<?= '' == fval('type') ? ' selected="selected"' : '' ?>>[ Alege ]</option>
                <option value="classic"<?= 'classic' == fval('type') ? ' selected="selected"' : '' ?>>Clasic</option>
                <option value="debug"<?= 'debug' == fval('type') ? ' selected="selected"' : '' ?>>Debug</option>
                <option value="speed"<?= 'speed' == fval('type') ? ' selected="selected"' : '' ?>>Speed</option>
                <option value="output-only"<?= 'output-only' == fval('type') ? ' selected="selected"' : '' ?>>Output Only</option>
            </select>
            <?= ferr_span('type')?>
    </li>
</ul>

<? include('views/param_edit.php') ?>

    </div>

</div>

<div class="submit">
    <ul class="form">
        <li id="field_buttons">
            <input type="submit" value="Salveaza" id="form_submit" class="button important" />
            <input type="button" value="Preview" id="form_preview" class="button" />
        </li>
    </ul>
</div>

</form>
<?php include('footer.php'); ?>
