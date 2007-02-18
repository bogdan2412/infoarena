<?php include('header.php'); ?>

    <h1>Atasamente pentru pagina <a href="<?= htmlentities(url_textblock($view['page_name'])) ?>">
        <?= htmlentities($view['page_name']) ?></a></h1>

    <? if (count($attach_list) > 0) { ?>
        <ul class="attach">
        <?php foreach ($attach_list as $v) { ?>
        <li>
            <?= format_link(url_attachment($page_name, $v['name']), $v['name']) ?>
            atasat de
            <?= format_user_tiny($v['username'], $v['user_fullname']) ?>
            la data de
            <?= format_date($v['timestamp']) ?>
            , 
            <a href="<?= htmlentities(url_attachment_delete($page_name, $v['name'])) ?>" onclick="return confirm('Aceasta actiune este ireversibila! Doresti sa continui?')">Sterge</a>
        </li>
        <?php } ?>
        </ul>
    <? } ?>

    <p><?= format_link(url_attachment_new($page_name), 'Ataseaza un alt fisier') ?></p>

<?php include('footer.php'); ?>
