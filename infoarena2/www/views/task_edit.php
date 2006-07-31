<?php
include('views/wikiedit_parts.php'); 
include('views/header.php'); 
?>

<h1><?= getattr($view, 'title') ?></h1>

<?= $wikiedit['preview'] ?>

<form action="<?= getattr($view, 'action') ?>" method="post" class="task">
<div class="tabber">
    <div class="tabbertab<?= 'statement' == $active_tab ? ' tabbertabdefault' : '' ?> statement">
        <h3>Enunt</h3>
        <ul class="form">
            <?= $wikiedit['title'] ?>
            
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
            
            <?= $wikiedit['content'] ?>
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
        <?= $wikiedit['submit'] ?>
    </ul>
</div>

</form>
<?php include('footer.php'); ?>
