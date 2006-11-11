<?php
include('views/header.php'); 
?>

<h1><?= htmlentities(getattr($view, 'title')) ?></h1>

<form action="<?= getattr($view, 'action') ?>" method="post" class="register">
    <ul class="form">
        <li id="form_submit">
            <input type="submit" value="Confirma" id="form_submit" class="button important" />
        </li>
    </ul>
</form>

<?php include('footer.php'); ?>
