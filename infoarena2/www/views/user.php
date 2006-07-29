<?php include('header.php'); ?>

<h1><?= getattr($view, 'title'); ?></h1>

<div class="tabber userProfile">
    <div class="tab generalData">
        <h2>Date Generale</h2>

        <ul>
            <li>
                TODO: avatar goes here
            </li>
            <li id="profile_name">
                <span class="desc">Numele utilizatorului</span>
                <span class="field"><?=
                    getattr($user_info, 'full_name');
                ?></span>
            </li>
            <?php if ($detail_view) { ?>
            <li id="profile_email">
                <span class="desc">Email</span>
                <span class="field"><?=
                    getattr($user_info, 'email');
                ?></span>
            </li>
            <li id="security_level">
                <span class="desc">User security level</span>
                <span class="field"><?=
                    getattr($user_info, 'security_level');
                ?></span>
            </li>
            <?php } ?>
            
        </ul>
    </div>
</div>
<?php include('footer.php'); ?>
