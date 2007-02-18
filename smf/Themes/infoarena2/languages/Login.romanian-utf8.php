<?php
// Version: 1.1 RC3; Login

$txt[37] = 'Nu ai completat câmpul Numele Utilizatorului.';
$txt[38] = 'Nu ai introdus nici o parolă.';
$txt[39] = 'Parola incorectă';
$txt[98] = 'Alege un nume de utilizator';
$txt[155] = 'In întreţinere';
$txt[245] = 'Inregistrare reuşită';
$txt[431] = 'Bun venit! Acum eşti utilizator al forumului.';
// Use numeric entities în the below string.
$txt[492] = 'şi parola ta este';
$txt[500] = 'te rugăm să introduci o adresă de email validă, %s.';
$txt[517] = 'Informaţii necesare';
$txt[520] = 'Folosit doar pentru autentificare la SMF.';
$txt[585] = 'De acord';
$txt[586] = 'Nu accept!';
$txt[633] = 'Avertisment!';
$txt[634] = 'Doar utilizatorii înregistraţi au permis accesul în acestă secţiune.';
$txt[635] = 'te rugăm să te autentifici mai jos sau';
$txt[636] = 'sa te înregistrezi';
$txt[637] = 'la ' . $context['forum_name'] . '.';
// Use numeric entities în the below two strings.
$txt[701] = 'Poţi schimba aceasta după ce pe autentifici mergând în profilul personal, sau vizitează acesta pagină după autentificare:';
$txt[719] = 'Numele de utilizator este: ';
$txt[730] = 'Această adresa de email (%s) este utilizată de un alt utilizator înregistrat. Dacă crezi că este o greşeală, mergi la pagina de autentificare şi foloseşte legatura de reamintire a parolei cu aceasta adresă.';

$txt['login_hash_error'] = 'Securitatea parolei a fost crescută.  Te rugăm să introduci parola din nou.';

$txt['register_age_confirmation'] = 'Am cel putin %d ani';

// Use numeric entities în the below six strings.
$txt['register_subject'] = 'Bun venit la '. $context['forum_name'] ;

// For the below three messages, %1$s is the display name, %2$s is the username, %3$s is the password, %4$s is the activation code, and %5$s is the activation link (the last two are only for activation.)
$txt['register_immediate_message'] = 'In acest moment ai un cont înregistrat în ' . $context['forum_name'] . ', %1$s!' . "\n\n" . 'Numele de \'s utilizator este %2$s şi parola ta este %3$s.' . "\n\n" . 'Poţi schimba parola după autentificare mergând în profilul personal sau vizitând pagina următoare după autentificare:' . "\n\n" . $scripturl . '?action=profile' . "\n\n" . $txt[130];
$txt['register_activate_message'] = 'In acest moment ai înregistrat un cont în ' . $context['forum_name'] . ', %1$s!' . "\n\n" . 'Numele de utilizator este %2$s şi parola asociată este %3$s (care poate fi schimbată oricând mai târziu)' . "\n\n" . 'Inainte de a putea să te autentifici, trebuie să-ţi activezi contul tău. Pentru aceasta te rugăm să mergi la legatura urmatoare:' . "\n\n" . '%5$s' . "\n\n" . 'Dacă cumva ai probleme cu activarea, foloseşte codul următor "%4$s".' . "\n\n" . $txt[130];
$txt['register_pending_message'] = 'Cererea de înregistrare în ' . $context['forum_name'] . ' a fost recepţionată, %1$s.' . "\n\n" . 'Numele de utilizator ales este %2$s şi parola este %3$s.' . "\n\n" . 'Inainte de a te autentifica şi a începe să utilizezi forumul,  cererea ta va fi examinată şi aprobată.  Când aceasta se va întampla, vei primi un alt mesaj la adresa ta de email de la administratorii forumului.' . "\n\n" . $txt[130];

// For the below two messages, %1$s is the user's display name, %2$s is their username, %3$s is the activation code, and %4$s is the activation link (the last two are only for activation.)
$txt['resend_activate_message'] = 'Ai un cont înregistrat în ' . $context['forum_name'] . ', %1$s!' . "\n\n" . 'Numele de utilizator este "%2$s".' . "\n\n" . 'Inainte de a te autentifica trebuie să-ţi activezi contul. Pentru acesta te rugăm să mergi la următoarea pagină:' . "\n\n" . '%4$s' . "\n\n" . 'Dacă cumva ai probleme la activare, foloseşte codul "%3$s".' . "\n\n" . $txt[130];
$txt['resend_pending_message'] = 'Cererea ta de înregistrare în ' . $context['forum_name'] . ' a fost recepţionata, %1$s.' . "\n\n" . 'Ai ales următorul nume de utilizator %2$s.' . "\n\n" . 'Inainte de a te autentifica şi a începe să utilizezi forumul, cererea ta va fi revizuită şi aprobată.  Când acest lucru se va întampla, vei primi un alt mesaj email de la administratori.' . "\n\n" . $txt[130];

$txt['ban_register_prohibited'] = 'Ne pare rău, nu este permis să te înregistrezi în acest forum.';
$txt['under_age_registration_prohibited'] = 'Ne pare rău, dar utilizatorilor sub %d ani, nu le este permis să se înregistreze în acest forum.';

$txt['activate_account'] = 'Activarea contului';
$txt['activate_success'] = 'Contul tău a fost activat cu succes! Acum poţi să te autentifici.';
$txt['activate_not_completed1'] = 'Adresa ta de email trebuie să fie verificată înainte de a te autentifica.';
$txt['activate_not_completed2'] = 'Vrei un alt email pentru activare?';
$txt['activate_after_registration'] = 'Îţi mulţumim pentru înregistrare. Imediat vei primi un email cu o legatură către o pagina de activare a contului. Dacă nu ai primit acest email în cateva minute, te rugăm să verifici directorul de SPAM, BULK sau JUNK deoarece cei mai mulţi provideri de email gratuit (în special Yahoo! şi  MSN Hotmail) tratează aceste email-uri drept SPAM (JUNK).';
$txt['invalid_userid'] = 'Utilizatorul nu există';
$txt['invalid_activation_code'] = 'Cod de activare incorect';
$txt['invalid_activation_username'] = 'Numele de utilizator sau adresa email';
$txt['invalid_activation_new'] = 'Dacă te-ai înregistrat cu o adresă greşită de email scrie una nouă şi parola mai jos.';
$txt['invalid_activation_new_email'] = 'Noua adresă de email';
$txt['invalid_activation_password'] = 'Parola veche';
$txt['invalid_activation_resend'] = 'Retrimite codul de activare';
$txt['invalid_activation_known'] = 'Dacă deja ştii codul de activare, scrie-l aici.';
$txt['invalid_activation_retry'] = 'Cod de activare';
$txt['invalid_activation_submit'] = 'Activează';

$txt['coppa_not_completed1'] = 'Administratorul nu a primit încă consimţământul parinţilor/tutorilor pentru contul tău.';
$txt['coppa_not_completed2'] = 'Doreşti mai multe detalii?';

$txt['awaiting_delete_account'] = 'Contul tău a fost marcat pentru ştergere!<br />Dacă doreşti să-ţi restabilesti contul te rugăm să vizitezi &quot;Reactivarea contului&quot; şi apoi să te autentifici din nou.';
$txt['undelete_account'] = 'Reactivează contul meu';

$txt['change_email_success'] = 'Adresa ta de email a fost schimbată şi astfel un nou email de activare a fost trimis la ea.';
$txt['resend_email_success'] = 'Un nou email pentru activare a fost trimis.';
// Use numeric entities în the below three strings.
$txt['change_password'] = 'Parola nouă';
$txt['change_password_1'] = 'Detaliile pentru autentificare în';
$txt['change_password_2'] = 'au fost schimbate şi parola resetată. Mai jos ai noile detalii necesare la autentificare.';

$txt['maintenance3'] = 'Acest forum este în întreţinere.';

// These two are used as a javascript alert; please use international characters directly, not as entities.
$txt['register_agree'] = 'Te rugăm să citeţti şi să accepţi condiţiile de mai jos înainte de a te înregistra.';
$txt['register_passwords_differ_js'] = 'Cele doua parole introduse nu sunt identice!';

$txt['approval_after_registration'] = 'Îţi multumim pentru înregistrare. Administratorul trebuie să aprobe înregistrarea ta înainte de a putea utiliza contul. Vei primi un mesaj email curând cu privire la decizia administratorului.';

$txt['admin_settings_desc'] = 'Aici poţi schimba setările cu privire la înregistrarea de noi membri.';

$txt['admin_setting_registration_method'] = 'Metoda de înregistrare pentru noi membri';
$txt['admin_setting_registration_disabled'] = 'Inregistrarea dezactivată';
$txt['admin_setting_registration_standard'] = 'Inregistrare imediată';
$txt['admin_setting_registration_activate'] = 'Activare de către utilizator';
$txt['admin_setting_registration_approval'] = 'Aprobare de către administrator';
$txt['admin_setting_notify_new_registration'] = 'Anunţă administratorul când un nou utilizator se înregistrează';
$txt['admin_setting_send_welcomeEmail'] = 'Trimite mesajul de bun venit la noii utilizatori';

$txt['admin_setting_password_strength'] = 'Dificultate necesara pentru parola de utilizator';
$txt['admin_setting_password_strength_low'] = 'Scazută - 4 caractere minim';
$txt['admin_setting_password_strength_medium'] = 'Medie - nu poate conţine numele de utilizator';
$txt['admin_setting_password_strength_high'] = 'Mare - combinaţie de diferite caractere';

$txt['admin_setting_disable_visual_verification'] = 'Dezactivează verificarea vizuală la înregistrare';

$txt['admin_setting_coppaAge'] = 'Vârsta sub care se aplică restricţiile la înregistrare';
$txt['admin_setting_coppaAge_desc'] = '(Setează 0 pentru dezactivare)';
$txt['admin_setting_coppaType'] = 'Acţiune în cazul unui utilizator sub vârsta minima care se înregistrează';
$txt['admin_setting_coppaType_reject'] = 'Nu permite înregistrarea!';
$txt['admin_setting_coppaType_approval'] = 'Cere aprobarea părintelui/tutorelui';
$txt['admin_setting_coppaPost'] = 'Adresa poştala unde să fie trimis acordul parinţilor';
$txt['admin_setting_coppaPost_desc'] = 'Se aplică doar dacă restricţiile pe bază de vârstă sunt în funcţiune';
$txt['admin_setting_coppaFax'] = 'Numărul de fax unde să fie trimis acordul părinţilor';
$txt['admin_setting_coppaPhone'] = 'Număr de contact pentru parinţii care doresc mai multe detalii';
$txt['admin_setting_coppa_require_contact'] = 'Trebuie să introduci un cod postal sau un număr de fax dacă este necesar acordul parinţilor/tutorelui.';

$txt['admin_register'] = 'Inregistrarea de noi membri';
$txt['admin_register_desc'] = 'Aici poţi înregistra noi utilizatori în forum şi dacă doreşti poţi să le trimiţi detaliile pe email.';
$txt['admin_register_username'] = 'Noul nume de utilizator';
$txt['admin_register_email'] = 'Adresa de email';
$txt['admin_register_password'] = 'Parola';
$txt['admin_register_username_desc'] = 'Nume de utilizator pentru noul membru';
$txt['admin_register_email_desc'] = 'Adresa de email a noului utilizator';
$txt['admin_register_password_desc'] = 'Parola pentru noul utilizator';
$txt['admin_register_email_detail'] = 'Trimite pe email noua parola la utilizator';
$txt['admin_register_email_detail_desc'] = 'Adresa de email este necesară chiar dacă este nebifată';
$txt['admin_register_email_activate'] = 'Cere utilizatorului să-şi activeze contul';
$txt['admin_register_group'] = 'Grupul de utilizatori primar';
$txt['admin_register_group_desc'] = 'Grupul de utilizatori de care vor aparţine noii utilizatori';
$txt['admin_register_group_none'] = '(nici un grup primar)';
$txt['admin_register_done'] = 'Utilizatorul %s a fost înregistrat cu succes!';

$txt['admin_browse_register_new'] = 'Inregistrează un utilizator nou';

// Use numeric entities în the below three strings.
$txt['admin_notify_subject'] = 'Un nou utilizator s-a înregistrat';
$txt['admin_notify_profile'] = '%s tocmai s-a înregistrat ca nou membru al forumului tău. Click pe legătura de mai jos pentru a vedea profilul său.';
$txt['admin_notify_approval'] = 'Inainte ca acest membru să poata scrie mesaje el trebuie să aiba contul aprobat. Click pe legatura de mai jos pentru a merge la interfaţa de aprobare.';

$txt['coppa_title'] = 'Forum cu restricţii de vârstă';
$txt['coppa_after_registration'] = 'Îţi mulţumim pentru înregistrarea în ' . $context['forum_name'] . '.<br /><br />Deoarece tu eşti sub limita de vârsta pentru acest forum, limită care este de {MINIMUM_AGE}, este necesar legal
	sa obţii aprobarea parinţilor sau tutorelui legal înainte de a putea să foloseşti contul.  Pentru a regla activarea contului te rugăm să tipareşti formularul de mai jos:';
$txt['coppa_form_link_popup'] = 'Incarcă formularul într-o fereastră nouă';
$txt['coppa_form_link_download'] = 'Download formularul ca fişier text';
$txt['coppa_send_to_one_option'] = 'Apoi vorbeşte cu parintele/tutorele să trimită formularul completat prin:';
$txt['coppa_send_to_two_options'] = 'Apoi vorbeşte cu parintele/tutorele să trimită formularul completat prin:';
$txt['coppa_send_by_post'] = 'Poştă, la adresa următoare:';
$txt['coppa_send_by_fax'] = 'Fax, la numărul următor:';
$txt['coppa_send_by_phone'] = 'Sau poate vorbi direct cu administratorul la numărul de telefon {PHONE_NUMBER}.';

$txt['coppa_form_title'] = 'Formular de acord pentru înregistrarea în ' . $context['forum_name'];
$txt['coppa_form_address'] = 'Adresa';
$txt['coppa_form_date'] = 'Data';
$txt['coppa_form_body'] = 'Subsemnatul {PARENT_NAME},<br /><br />îmi dau acordul pentru ca {CHILD_NAME} (numele copilului) să devină membru înregistrat al forumului: ' . $context['forum_name'] . ', cu urmatorul nume de utilizator: {USER_NAME}.<br /><br />Inţeleg şi accept ca unele informaţii personale introduse de {USER_NAME} pot fi văzute de către ceilalţi utilizatori ai forumului.<br /><br />Semnătura:<br />{PARENT_NAME} (Părinte/Tutore).';

$txt['visual_verification_label'] = 'Verificare vizuală';
$txt['visual_verification_description'] = 'Introdu literele afişate in fotografie';
$txt['visual_verification_sound'] = 'Ascută literele';
$txt['visual_verification_sound_again'] = 'Ascultă din nou';
$txt['visual_verification_sound_close'] = 'Inchide fereastra';
$txt['visual_verification_request_new'] = 'Cere o altă fotografie';
$txt['visual_verification_sound_direct'] = 'Ai probleme în a auzi asta? Incearcă link-ul direct către ea.';

?>