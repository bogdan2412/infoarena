<?php
include('views/textblock_edit_parts.php'); 
include('views/header.php'); 
?>

<?php if (TEXTBLOCK_TASK==$page_class) { ?>
<a href="<?= url('task/'.$page_id, array('action' => 'details')) ?>">Editeaza detalii / parametri pentru problema</a>
<?php } ?>

<?php if (TEXTBLOCK_ROUND==$page_class) { ?>
<a href="<?= url('round/'.$page_id, array('action' => 'details')) ?>">Editeaza detalii / parametri pentru runda</a>
<?php } ?>


<form action="<?= getattr($view, 'action') ?>" method="post" id="form_wikiedit">

<?= $wikiedit['preview'] ?>

<ul class="form">
    <?= $wikiedit['title'] ?>
    <?= $wikiedit['content'] ?>
    <?= $wikiedit['submit'] ?>
</ul>

</form>

<?php include('footer.php'); ?>
