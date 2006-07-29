<?php include('header.php'); ?>

<ul class="attach">
    <h1>Atasamente pentru pagina <a href="<?= url($view['page_name']) ?>">
        <?= $view['page_name'] ?></a></h1>

    <ul class="attachmentList">
    <?php foreach ($view['attach_list'] as $v) { ?>
    <li>
        <a href="<?= url($view['page_name'],array ('action' => 'download', 'file'=> $v['name'])) ?>">
        <?= $v['name'] ?></a><span class="details"><?= ", atasat de ".$v['username']." la ".$v['timestamp'].", " ?></span>
        <a href="<?= url($view['page_name'],array ('action' => 'attach-del', 'file'=> $v['name'])) ?>">
        Sterge </a>
    </li>
    <?php } ?>
    </ul>

    <?php
    $create_link = htmlentities(url($page_name, array('action' => 'attach')));
    ?>

    <p><a href="<?php echo $create_link ?>">Ataseaza un alt fisier</a></p>
</ul>

<?php include('footer.php'); ?>
