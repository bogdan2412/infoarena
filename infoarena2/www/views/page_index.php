<?php include('header.php'); ?>

<ul class="page_index">
    <?php if ($view['page']) { ?>
        <h1>Pagini din <a href="<?= url($view['page_name']) ?>"><?= $view['page_name'] ?></a></h1>
    <?php } else if ($view['page_name']) { ?>
            <h1>Pagini din <?= $view['page_name'] ?></h1>
    <?php } else { ?>
        <h1>Pagini</h1>
    <?php } ?>

    <ul class="list">
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
</ul>

<?php include('footer.php'); ?>