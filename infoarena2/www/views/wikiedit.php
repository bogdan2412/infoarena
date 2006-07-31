<?php
include('views/wikiedit_parts.php'); 
include('views/header.php'); 
?>

<form action="<?= getattr($view, 'action') ?>" method="post">

<?= $wikiedit['preview'] ?>

<ul class="form">
    <?= $wikiedit['title'] ?>
    <?= $wikiedit['content'] ?>
    <?= $wikiedit['submit'] ?>
</ul>

</form>

<?php include('footer.php'); ?>
