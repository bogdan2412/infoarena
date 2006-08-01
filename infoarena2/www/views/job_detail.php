<?php include('header.php'); ?>

<h1><?= htmlentities($view['title']) ?></h1>

<div class="job_detail" ?>
<ul>
<?php   foreach ($job as $key => $val) { ?>
    <li>
        <strong><div class="desc"><?=$key?></div></strong>
        <div class="val"><pre><?=$val?></pre></div>
    </li>
<?php    } ?>
</ul>
</div>
<?php include('footer.php'); ?>