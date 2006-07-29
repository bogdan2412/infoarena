<?php include('header.php'); ?>

<ul class="history">
    <?php if ($view['page_name']) { ?>
        <h1>Istoria paginii <a href="<?= url($view['page_name']) ?>"><?= $view['page_name'] ?></a></h1>
    <?php } else { ?>
            <h1>Istoria paginii <?= $view['page_name'] ?></h1>
    <?php } ?>

    <ul class="list">
        <?php for ($idx = $view['count']-1; $idx >= 0; $idx--) { ?>
        <li>
            <em>Revizia #<?= htmlentities($idx)+1 ?>.</em>
            <?php $v = $view['page_list'][$idx]; ?>
            <a href="<?= url($v['name']).'?revision='.$idx ?>"><?= $v['title'] ? htmlentities($v['title']) : '<strong>FARA TITLU</strong>' ?></a>,
            <span class="details">modificat ultima data la <?= htmlentities($v['timestamp']) ?></span>
            <?php if (getattr($v, 'username')) { ?>
                de <a href="<?= url('user/'.$v['username']) ?>"><?= htmlentities($v['username']) ?></a>
            <?php } ?>
            
        </li>
        <?php } ?>
    </ul>
</ul>

<?php include('footer.php'); ?>