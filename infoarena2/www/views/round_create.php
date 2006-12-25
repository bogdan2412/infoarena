<?php include('header.php'); ?>

<h1><?= htmlentities($title) ?></h1>

<form action="<?= htmlentities(url_round_create()) ?>" method="post" class="round create clear">
    <ul class="form">
        <li id="field_id">
            <?= format_form_text_field('id', 'Id-ul rundei'); ?>
        </li>
<? /* FIXME: round types */ ?>
    </ul>
    <ul class="form clear">
        <li>
            <input type="submit" class="button important" value="Creaza runda" id="form_submit" />
        </li>
    </ul>
</form>

<?php include('footer.php'); ?>
