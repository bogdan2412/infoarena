<?php include('header.php'); ?>

    <h1>Atasamente pentru pagina <a href="<?= htmlentities(url($view['page_name'])) ?>">
        <?= htmlentities($view['page_name']) ?></a></h1>

    <ul class="attach">
    <?php foreach ($view['attach_list'] as $v) { ?>
    <li>
        <?= format_link(url($view['page_name'], array('action' => 'download', 'file' => $v['name'])), $v['name']) ?>
        atasat de
        <?= format_link(url(TB_USER_PREFIX.$v['username']), $v['username']) ?>

        <?= " la ".htmlentities($v['timestamp']).", " ?></span>

        <a href="<?= htmlentities(url($view['page_name'], array('action' => 'attach-del', 'file'=> $v['name']))) ?>" onclick="return confirm('Aceasta actiune este ireversibila! Doresti sa continui?')">Sterge</a>

    </li>
    <?php } ?>
    </ul>

    <p><?= format_link(url($page_name, array('action' => 'attach')), 'Ataseaza un alt fisier') ?></p>

<?php include('footer.php'); ?>
