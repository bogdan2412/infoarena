<?php

// link JS
$view['head'] = getattr($view, 'head')."<script type=\"text/javascript\" src=\"" . htmlentities(url_static("js/wikiedit.js")) . "\"></script>";

include('views/header.php');
include('views/tags_header.php');
?>

<form action="<?= htmlentities(url_textblock_edit($page_name)) ?>" method="post" id="form_wikiedit" <?= tag_form_event() ?>>
<input type="hidden" id="form_page_name" value="<?= htmlentities(isset($page_name) ? $page_name : '') ?>" />
<input type="hidden" name="last_revision" value="<?=htmlentities($last_revision)?>" />

<? if (ferr('was_modified', false)) { ?>
<div class="wiki_was_modified"><?= ferr('was_modified', false); ?></div>
<? } ?>

<div class="wiki_text_block" id="wiki_preview" style="display: none;"></div>
<div id="wiki_preview_toolbar" style="display: none;">
    <input type="button" class="button" id="preview_close" value="Ascunde Preview" />
</div>

<ul class="form">
    <li id="field_title">
        <label for="form_title">Titlu</label>
        <input type="text" name="title" value="<?= fval('title') ?>" id="form_title"/>
        <?= ferr_span('title') ?>
    </li>


    <li id="field_content">
        <label for="form_text">Continut</label>
        <textarea name="text" id="form_text" rows="10" cols="50"><?= fval('text') ?></textarea>
        <?= ferr_span('text') ?>
        <?= format_link(url_textblock('documentatie/wiki'), "Cum formatez text?") ?>
    </li>

    <? if (identity_can('textblock-tag', $view['page'])) { ?>
       <?= tag_format_input_box(fval('tags')) ?>
    <? } ?>

    <?php if (array_key_exists('security', $form_values)) { ?>
    <li id="field_security">
        <label for="form_security">Nivel de securitate al paginii
        <a href="<?= htmlentities(url_textblock('documentatie/securitate')) ?>">(?)</a></label> 
        <input type="text" name="security" value="<?= fval('security') ?>" id="form_security"/>
        <?= ferr_span('security') ?>
    </li>
    <?php } ?>

    <li id="field_submit">
        <input type="submit" value="Salveaza" id="form_submit" class="button important" />
        <input type="button" value="Preview" id="form_preview" class="button" />
    </li>
</ul>

</form>

<?php include('footer.php'); ?>
