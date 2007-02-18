<?php
include('views/header.php');
?>

<h1><?= htmlentities(getattr($view, 'title')) ?></h1>

<?php wiki_include('template/inscriere', array('round' => $round['id'])) ?>

<form action="<?= htmlentities(getattr($view, 'action')) ?>" method="post" class="register">
    <ul class="form">
        <li id="form_submit">
            <input type="submit" value="Confirma inscrierea" id="form_submit" class="button important" />
        </li>
    </ul>
</form>

<?php include('footer.php'); ?>
