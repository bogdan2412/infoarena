<?php include('header.php'); ?>

<ul class="attach">
   <h1>Atasamente pentru <?php echo htmlentities(getattr($view, 'page_title')) ?></h1>
    <?php foreach ($view['attach_list'] as $v) { ?>
    <li>
        <a href="<?= url($view['page_name'],array ('action' => 'download', 'file'=> $v['name'])) ?>">
        <?= $v['name'] ?></a><span class="details"><?= ", atasat de ".$v['username']." la ".$v['timestamp'].", " ?></span>
        <a href="<?= url($view['page_name'],array ('action' => 'delattach', 'file'=> $v['name'])) ?>">
        Sterge </a>
    </li>
    <?php } ?>
</ul>

<?php include('footer.php'); ?>
