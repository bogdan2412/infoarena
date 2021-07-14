<?php include(CUSTOM_THEME . 'header.php'); ?>

<h1><?= html_escape($view['title']) ?></h1>
<div>
    <p>
        Introduceți numele problemei în câmpul de mai jos. Apoi apăsați butonul
        de căutare. Va fi afișată o listă cu toate problemele care conțin în
        titlu sau în textul problemei cuvântul cheie căutat.
    </p>
</div>

<script async src="https://cse.google.com/cse.js?cx=<?= GOOGLE_CSE_TOKEN ?>"></script>
<div class="gcse-searchbox-only"></div>
<div class="gcse-searchresults-only"></div>

<?php include('footer.php'); ?>
