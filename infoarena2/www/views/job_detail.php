<?php include('header.php'); ?>

<h1><?= htmlentities($view['title']) ?></h1>

<div class="job_details" ?>
<ul>
<?php   foreach ($job as $key => $val) { ?>
    <li>
        <div class="desc"><?=$key?></div>
        <div class="val"><?=$val?></div>
    </li>
<?php    } ?>
</ul>
</div>
<?php include('footer.php'); ?>