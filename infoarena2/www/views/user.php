<?php include('header.php'); ?>

<h1><?= getattr($view, 'title'); ?></h1>
<ul>
    <li>
        TODO: avatar goes here
    </li>
    <li>
        <span name="desc_full_name" class="user-desc">Numele utilizatorului</span>
        <span name="full_name" class="user-field"><?=
            getattr($user_info, 'full_name');
        ?></span>
    </li>
    <?php if ($detail_view) { ?>
    <li>
        <span name="desc_email" class="user-desc">Email</span>
        <span name="email" class="user-field"><?=
            getattr($user_info, 'email');
        ?></span>
    </li>
    <li>
        <span name="desc_security_level" class="user-desc">User security level</span>
        <span name="security_level" class="user-field"><?=
            getattr($user_info, 'security_level');
        ?></span>
    </li>
    <?php } ?>
    
</ul>

<?php include('footer.php'); ?>