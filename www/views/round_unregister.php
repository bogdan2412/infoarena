<?php
include('views/header.php');
?>

<h1>Deinregistrare de la <?= format_link(url_textblock($round['page_name']), $round['title']) ?></h1>

<?php wiki_include('template/deinscriere', array('round' => $round['id'])) ?>

<form action="<?= html_escape(getattr($view, 'action')) ?>" method="post" class="register">
    <ul class="form">
        <li id="form_submit">
            <input type="submit" value="Confirma deinscrierea" id="form_submit" class="button important" />
        </li>
    </ul>
</form>

<?php include('footer.php'); ?>
