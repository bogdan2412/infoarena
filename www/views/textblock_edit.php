<?php

// link JS
$view['head'] = getattr($view, 'head')."<script type=\"text/javascript\" src=\"" . html_escape(url_static("js/wikiedit.js")) . "\"></script>";

include('views/header.php');
include('views/tags_header.php');

// insert task edit tabs
if (($task_id = textblock_security_is_task($page['security'])) &&
    (identity_can('task-edit', task_get($task_id)))) {
    require_once(IA_ROOT_DIR."www/views/task_edit_header.php");
    echo task_edit_tabs($task_id, request("action"));
?>
<h1>Editare enunț <a href="<?= html_escape(url_task($task_id)) ?>">
<?= html_escape($task_id) ?></a></h1>
<?php }

// insert round edit tabs
if (($round_id = textblock_security_is_round($page['security'])) &&
    (identity_can('round-edit', $round = round_get($round_id)))) {
    require_once(IA_ROOT_DIR."www/views/round_edit_header.php");
    echo round_edit_tabs($round_id, request("action"));
?>
<h1>Editare pagină <a href="<?= html_escape(url_round($round_id)) ?>">
<?= html_escape($round['title']) ?></a></h1>
<?php } ?>

<script type="text/javascript">
    function toggleSpecial() {
        var select = document.getElementById("security_select");
        var option = select.options[select.selectedIndex].value;
        var special = document.getElementById("special");

        if (option == "round" || option == "task") {
            special.className = "security_show";
        } else {
            special.className = "security_hide";
        }
    }
</script>

<form accept-charset="utf-8" action="<?= html_escape(url_textblock_edit($page_name)) ?>" method="post" id="form_wikiedit" <?= tag_form_event() ?>>
<input type="hidden" id="form_page_name" value="<?= html_escape(isset($page_name) ? $page_name : '') ?>" />
<input type="hidden" name="last_revision" value="<?=html_escape($last_revision)?>" />

<?php if (ferr('was_modified', false)) { ?>
<div class="wiki_was_modified"><?= ferr('was_modified', false); ?></div>
<?php } ?>

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

    <?php if (array_key_exists('forum_topic', $form_values)) { ?>
    <li id="field_forum_topic">
        <label for="form_forum_topic">Forum Topic</label>
        <input type="text" name="forum_topic" value="<?= fval('forum_topic') ?>" id="form_forum_topic" />
        <?= ferr_span('forum_topic') ?>
    </li>
    <?php } ?>

    <?php if (identity_can('textblock-tag', $view['page'])) { ?>
       <?= tag_format_input_box(array("label" => "Tag-uri", "name" => "tags"), fval('tags')) ?>
    <?php } ?>

    <?php if (array_key_exists('security', $form_values)) { ?>
    <li id="field_security">
        <label for="form_security">Nivel de securitate al paginii
        <a href="<?= html_escape(url_textblock('documentatie/securitate')) ?>">(?)</a></label>

        <select id="security_select" name="security" onchange="toggleSpecial()">
        <?php
            $options = array(
                array('value' => 'public', 'html' => 'Public'),
                array('value' => 'protected', 'html' => 'Protected'),
                array('value' => 'private', 'html' => 'Private'),
                array('value' => 'round', 'html' => 'Round'),
                array('value' => 'task', 'html' => 'Task')
            );

            foreach ($options as $option) {
                if ($option['value'] == fval('security')) {
                    echo sprintf('<option value="%s" selected="selected">%s
                                 </option>', $option['value'], $option['html']);
                } else {
                    echo sprintf('<option value="%s">%s</option>',
                                 $option['value'], $option['html']);
                }
            }
        ?>
        </select>

        <?php
            if (in_array(fval('security'), array('round', 'task'))) {
                $css_class = "security_show";
            } else {
                $css_class = "security_hide";
            }

            echo sprintf('<input name="security_special" id="special"
                          type="text" value="%s" class="%s">',
                          fval('security_special'), $css_class);
        ?>

        <?= ferr_span('security') ?>
    </li>
    <?php } ?>

    <li id="field_submit">
        <input type="submit" value="Salveaza" id="form_submit" class="button important" />
        <input type="submit" value="Salveaza si Editeaza" name="form_save_and_edit" class="button" />
        <input type="button" value="Preview" id="form_preview" class="button" />
    </li>
</ul>

</form>

<?php include('footer.php'); ?>
