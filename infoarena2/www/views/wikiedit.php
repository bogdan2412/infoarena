<?php include('header.php'); ?>


<form action="<?= getattr($view, 'action') ?>" method="post">
<ul class="form">
    <li>
        <label for="form_content">Continut</label>
        <textarea name="content" id="form_content" rows="10" cols="50"><?= fval('content') ?></textarea>
    </li>
    
	<li>
		<input type="submit" value="Salveaza" id="form_submit" />
	</li>
</ul>
</form>

<?php include('footer.php'); ?>

