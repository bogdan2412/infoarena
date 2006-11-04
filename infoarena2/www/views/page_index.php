<?php include('header.php'); ?>
    <h1><?= htmlentities($view['title']) ?></h1>

    <ul class="page_index">
        <?php foreach ($view['page_list'] as $v) { ?>
        <li>
            <a href="<?= url($v['name']) ?>"><?= $v['title'] ? htmlentities($v['title']) : '<strong>FARA TITLU</strong>' ?></a>,
            <span class="details">modificat ultima data la <?= htmlentities($v['timestamp']) ?></span>
            <?php if (getattr($v, 'username')) { ?>
                de <a href="<?= url('user/'.$v['username']) ?>"><?= htmlentities($v['username']) ?></a>
            <?php } ?>
        </li>
        <?php } ?>
    </ul>

<?php include('footer.php'); ?>
