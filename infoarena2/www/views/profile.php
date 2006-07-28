<?php include('header.php'); ?>

<form action="<?= url('register/save') ?>" method="post">
<ul class="form">
    1. Date generale
    <li>
        <label for='form_username'>Nume utilizator</label>
        <input type="text" name="username" value="<?= fval('username') ?>" id="form_username" />
        <?php if (getattr($errors, 'username')) { ?>
        <span class="fieldError"><?= getattr($errors, 'username') ?></span>
        <?php } ?>
    </li>

    <?php if (!$register) { ?>
    <li>
        <label for='form_password_old'>Parola veche</label>
        <input type="password" name='password_old' id="form_password_old" />
        <?php if (getattr($errors, 'password_old')) { ?>
        <span class="fieldError"><?= getattr($errors, 'password_old') ?></span>
        <?php } ?>
        <span class="fieldHelp">Pentru modificarea parolei sau adresei de email</span>
    </li>
    <?php } ?>
    
    <li>
        <label for='form_password'>Parola</label>
        <input type="password" name='password' id="form_password" />
        <?php if (getattr($errors, 'password')) { ?>
        <span class="fieldError"><?= getattr($errors, 'password') ?></span>
        <?php } ?>
    </li>

    <li>
        <label for='form_password2'>Confirmare parola</label>
        <input type="password" name='password2' id="form_password2" />
        <?php if (getattr($errors, 'password2')) { ?>
        <span class="fieldError"><?= getattr($errors, 'password2') ?></span>
        <?php } ?>
    </li>
    
    <li>
        <label for="form_name">Nume complet</label>
        <input type="text" name="full_name" value="<?= fval('full_name') ?>" id="form_name" />
        <?php if (getattr($errors, 'full_name')) { ?>
        <span class="fieldError"><?= getattr($errors, 'full_name') ?></span>
        <?php } ?>
    </li>

    <li>
        <label for="form_email">Adresa e-mail</label>
        <input type="text" name="email" value="<?= fval('email') ?>" id="form_email" />
        <?php if (getattr($errors, 'email')) { ?>
        <span class="fieldError"><?= getattr($errors, 'email') ?></span>
        <?php } ?>
    </li>
    
    <li>
        <label for="form_country">Tara</label>
        <input type="text" name="country" value="<?= fval('country') ?>" id="form_country" />
        <?php if (getattr($errors, 'country')) { ?>
        <span class="fieldError"><?= getattr($errors, 'country') ?></span>
        <?php } ?>
    </li>
    
    <li>
        <label for="form_county">Judetul</label>
        <select name="county" id="form_county">
            <option selected="selected" value="TODO">TODO</option>
            <option value="Bucuresti">Bucuresti</option>
        </select>
        <?php if (getattr($errors, 'county')) { ?>
        <span class="fieldError"><?= getattr($errors, 'county') ?></span>
        <?php } ?>
    </li>
    
    2. Profil
    
    <?php if (!$register) { ?>
    <li>
        <label for="form_avatar">Avatar</label>
        !TODO!
    </li>
    <?php } ?>
    
    <li>
        <label for="form_quote">Citat</label>
        <textarea name="quote" id="form_quote"><?= fval('quote') ?></textarea>
        <?php if (getattr($errors, 'quote')) { ?>
        <span class="fieldError"><?= getattr($errors, 'quote') ?></span>
        <?php } ?>
    </li>
    
    <li>
        <label for="form_birthday">Data nasterii</label>
        <input type="text" name="birthday" value="<?= fval('birthday') ?>" id="form_birthday" />
        <?php if (getattr($errors, 'birthday')) { ?>
        <span class="fieldError"><?= getattr($errors, 'birthday') ?></span>
        <?php } ?>
        <span class="fieldHelp">Trebuie sa fie de forma ZZ-LL-AAAA</span>
    </li>
    
    3. Date personale
    
    <li>
        <label for="form_city">Oras</label>
        <input type="text" name="city" value="<?= fval('city') ?>" id="form_city" />
        <?php if (getattr($errors, 'city')) { ?>
        <span class="fieldError"><?= getattr($errors, 'city') ?></span>
        <?php } ?>
    </li>
    
    <li>
        <label for="form_workplace">Institurie de invatamant</label>
        <input type="text" name="workplace" value="<?= fval('workplace') ?>" id="form_workplace" />
        <?php if (getattr($errors, 'workplace')) { ?>
        <span class="fieldError"><?= getattr($errors, 'workplace') ?></span>
        <?php } ?>
        <span class="fieldHelp">(merge un assisted-input aici)</span>
    </li>
    
    <li>
        <label for="form_study_level">Nivel scolar</label>
        <select name="study_level" id="form_study_level">
            <option selected="selected" value="nespecificat">nespecificat</option>
            <option value="gimnaziu">gimnaziu</option>
            <option value="liceu">liceu</option>
            <option value="facultate">facultate</option>
            <option value="absolvent">absolvent</option>
        </select>
    </li>

    <li>
        <label for="form_abs_year">Anul de absolvire</label>
        <input type="text" size='4' maxlength='4' name="abs_year" value="<?= fval('abs_year') ?>" id="form_abs_year" />
        <?php if (getattr($errors, 'abs_year')) { ?>
        <span class="fieldError"><?= getattr($errors, 'abs_year') ?></span>
        <?php } ?>
    </li>
    
    <li>
        <label for="form_postal_address">Adresa postala</label>
        <textarea name="postaladdress" id="form_postal_address"><?= fval('postal_address') ?></textarea>
        <?php if (getattr($errors, 'postal_address')) { ?>
        <span class="fieldError"><?= getattr($errors, 'postal_address') ?></span>
        <?php } ?>
    </li>
    
    <li>
        <label for="form_phone">Numar telefon</label>
        <input type="text" name="phone" value="<?= fval('phone') ?>" id="form_phone" />
        <?php if (getattr($errors, 'phone')) { ?>
        <span class="fieldError"><?= getattr($errors, 'phone') ?></span>
        <?php } ?>
    </li>
    
    <li>
        <input type="submit" value="Inregistreaza-ma" id="form_submit" />
    </li>
</ul>
</form>

<?php include('footer.php'); ?>
