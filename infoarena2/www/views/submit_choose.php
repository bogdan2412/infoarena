<?php include('header.php'); ?>

<script type="text/javascript">

function RoundSelector_Submit(selector) {
    if (!selector.value) {
        return;
    }
    window.location = selector.form.action + selector.value;
    return false;
}

</script>

<h1><?= htmlentities($title)  ?></h1>

<form action="<?= url('submit/') ?>" method="post">
<ul class="form">
    <li>
        <label for="form_round">Runda</label>
        <select name="round_id" id="form_round" onchange="RoundSelector_Submit(this)">
            <option value="">[ Alegeti runda ]</option>
<?php foreach ($rounds as $round) {  ?>
            <option value="<?= htmlentities($round['id']) ?>"><?= htmlentities($round['title']) ?></option>
<?php } ?>
        </select>
    </li>
</ul>
</form>

<?php include('footer.php'); ?>
