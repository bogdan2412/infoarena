<?php
include('views/header.php');
?>

<h1><?= htmlentities(getattr($view, 'title')) ?></h1>

<form action="<?= htmlentities(getattr($view, 'action')) ?>" method="post" class="register">
    <ul class="form">
        <li id="form_submit">
            <input type="submit" value="Confirma inregistrarea" id="form_submit" class="button important" />
        </li>
    </ul>
    <a href="<?= htmlentities(url_round_register_view($view['round_id']['id'])) ?>">Vezi cine s-a inregistrat pana acum.</a>
</form>

<?php include('footer.php'); ?>
