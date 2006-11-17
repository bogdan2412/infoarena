<?php
// Version: 1.1 RC3; Install

// These should be the same as those în index.language.php.
$txt['lang_character_set'] = 'UTF-8';
$txt['lang_rtl'] = false;

$txt['smf_installer'] = 'SMF Interfaţa de Instalare';
$txt['installer_language'] = 'Limba';
$txt['installer_language_set'] = 'Setează';
$txt['congratulations'] = 'Felicitări, instalarea este terminată!';
$txt['congratulations_help'] = 'Dacă ai nevoie de ajutor sau forumul SMF nu mai funcţionează corect, adu-ţi aminte de paginile <a href="http://www.simplemachines.org/community/index.php" target="_blank">help in english</a>.';
$txt['still_writable'] = 'Directorul în care ai instalat forumul este în continuare writable. Este o bună idee să utilizezi comanda chmod în FTP şi să-i scoţi atributul writable din motive de securitate.';
$txt['delete_installer'] = 'Click aici pentru a şterge fişierul install.php acum.';
$txt['delete_installer_maybe'] = '<i>(nu funcţionează pe toate serverele.)</i>';
$txt['go_to_your_forum'] = 'Acum poţi vedea <a href="%s">forumul tău proaspăt instalat</a> şi să începi să-l utilizezi. Mai întăi asigură-te ca eşti autentificat, după care poţi accesa Centru de Administrare.';
$txt['good_luck'] = 'Baftă Maximă!<br />Simple Machines';

$txt['user_refresh_install'] = 'Forum Refresh';
$txt['user_refresh_install_desc'] = 'In timpul instalarii (folosind detaliile introduse de tine), s-a găsit că una sau mai multe dintre tabelele necesare a fi create deja există.<br />Orice tabelă care lipseşte va fi recreata cu datele implicite, însa nici o dată nu a fost ştearsă sau rescrisă în tabelele deja existente.';

$txt['default_topic_subject'] = 'Bine ai venit! SMF!';
$txt['default_topic_message'] = 'Salut! Ai instalat Simple Machines Forum cu interfaţa în limba română!<br /><br />Sperăm că vei gasi uşoară funcţionarea şi administrarea şi te vei bucura folosind acest forum.&nbsp; Pentru orice problemă [url=http://www.simplemachines.org/community/index.php]help in english[/url].<br /><br />Mulţumim!<br />Simple Machines Forum';
$txt['default_board_name'] = 'Discuţii Generale';
$txt['default_board_description'] = 'Eşti liber să vorbesti despre orice în aceast forum.';
$txt['default_category_name'] = 'Categoria Generală';
$txt['default_time_format'] = '%B %d, %Y, %I:%M:%S %p';
$txt['default_news'] = 'SMF - A fost instalat!';
$txt['default_karmaLabel'] = 'Popularitate:';
$txt['default_karmaSmiteLabel'] = '[dezaprobare]';
$txt['default_karmaApplaudLabel'] = '[aprobare]';
$txt['default_reserved_names'] = 'Admin\nWebmaster\nGuest\nroot\nAdministrator\nVizitator\nModerator\ntest\nsmf ';
$txt['default_smileyset_name'] = 'Implicit';
$txt['default_classic_smileyset_name'] = 'Clasic';
$txt['default_theme_name'] = 'SMF Tema Implicita - Tema de Bază';
$txt['default_classic_theme_name'] = 'Tema Clasică YaBB SE';
$txt['default_babylon_theme_name'] = 'Tema Babylon';

$txt['default_administrator_group'] = 'Administrator';
$txt['default_global_moderator_group'] = 'Moderator Global';
$txt['default_moderator_group'] = 'Moderator';
$txt['default_newbie_group'] = 'Incepător';
$txt['default_junior_group'] = 'Junior';
$txt['default_full_group'] = 'Membru plin';
$txt['default_senior_group'] = 'Senior';
$txt['default_hero_group'] = 'Erou';

$txt['default_smiley_smiley'] = 'Smiley';
$txt['default_wink_smiley'] = 'Wink';
$txt['default_cheesy_smiley'] = 'Cheesy';
$txt['default_grin_smiley'] = 'Grin';
$txt['default_angry_smiley'] = 'Angry';
$txt['default_sad_smiley'] = 'Sad';
$txt['default_shocked_smiley'] = 'Shocked';
$txt['default_cool_smiley'] = 'Cool';
$txt['default_huh_smiley'] = 'Huh?';
$txt['default_roll_eyes_smiley'] = 'Roll Eyes';
$txt['default_tongue_smiley'] = 'Tongue';
$txt['default_embarrassed_smiley'] = 'Embarrassed';
$txt['default_lips_sealed_smiley'] = 'Lips Sealed';
$txt['default_undecided_smiley'] = 'Undecided';
$txt['default_kiss_smiley'] = 'Kiss';
$txt['default_cry_smiley'] = 'Cry';
$txt['default_evil_smiley'] = 'Evil';
$txt['default_azn_smiley'] = 'Azn';
$txt['default_afro_smiley'] = 'Afro';

$txt['error_message_click'] = 'Click aici';
$txt['error_message_try_again'] = 'pentru a încerca aceasta etapă din nou.';
$txt['error_message_bad_try_again'] = 'pentru a încerca să instalezi oricum, dar atenţie: este <i>total</i> nerecomandat.';

$txt['install_settings'] = 'Setari de Bază';
$txt['install_settings_info'] = 'Câteva lucruri care trebuie setate de tine';
$txt['install_settings_name'] = 'Numele forumului';
$txt['install_settings_name_info'] = 'Acesta este numele forumului, ex. &quot;Forum de Test&quot;.';
$txt['install_settings_name_default'] = 'Comunitatea mea';
$txt['install_settings_url'] = 'Adresa URL a forumului';
$txt['install_settings_url_info'] = 'Acesta este adresa URL a forumului tău <b>fără  \'/\'!</b>.<br />In cele mai multe cazuri, poţi să laşi valorile implicite în această casuţă - de obicei este corect.';
$txt['install_settings_compress'] = 'Ieşire comprimată Gzip';
$txt['install_settings_compress_title'] = 'Comprimă paginile de ieşire pentru a salva din bandă.';
// In this string, you can translate the word "PASS" to change what it says when the test passes.
$txt['install_settings_compress_info'] = 'Aceasta funcţie nu rulează corect pe toate serverele, dar te poate ajuta să salvezi multă bandâ.<br />Click <a href="install.php?obgz=1&amp;pass_string=PASS" onclick="return reqWin(this.href, 200, 60);" target="_blank">aici</a> pentru a testa. (ar trebui doar să spună "PASS".)';
$txt['install_settings_dbsession'] = 'Inregistrează sesiunile în baza de date';
$txt['install_settings_dbsession_title'] = 'Foloseste baza de date pentru sesiuni în loc să folosesti fişiere.';
$txt['install_settings_dbsession_info1'] = 'Această proprietate este întotdeauna cea mai bună, deoarece face sesiunile mai dependente.';
$txt['install_settings_dbsession_info2'] = 'Această proprietate este în general o idee bună, dar s-ar putea să nu funcţioneze corect pe acest server.';
$txt['install_settings_utf8'] = 'Set de caractere UTF-8';
$txt['install_settings_utf8_title'] = 'Foloseşte UTF-8 ca set de caractere implicit';
$txt['install_settings_utf8_info'] = 'Acestş facilitate permite ca baza de date şi datele să folosească setul de caractere internaţional UTF-8. Aceasta poate fi folositoare atunci când se lucrează cu mai multe limbi care utilizează seturi de caractere diferite.';
$txt['install_settings_stats'] = 'Permite colectarea de statistici';
$txt['install_settings_stats_title'] = 'Permite Simple Machines să colecteze lunar statistici de bază';
$txt['install_settings_stats_info'] = 'Dacă este activată, va permite Simple Machines să viziteze siteul tău odata pe lună pentru a colecta statistici de bază. Aceasta va ajuta echipe de programatori să decidă asupra caror configuraţii să optimizeze programul. Pentru mai multe informaţii te rugăm să vizitezi <a href="http://www.simplemachines.org/about/stats.php" target="_blank">pagina oficială cu informaţii (în limba engleză)</a>.';
$txt['install_settings_proceed'] = 'Execută';

$txt['mysql_settings'] = 'Setări server MySQL';
$txt['mysql_settings_info'] = 'Acestea sunt setările pentru serverul MySQL. Dacă cumva nu stii, trebuie să întrebi hosting-ul care sunt.';
$txt['mysql_settings_server'] = 'Numele serverului MySQL';
$txt['mysql_settings_server_info'] = 'Acesta este aproape întotdeauna localhost - asa că, dacă nu ştii, poţi încerca localhost.';
$txt['mysql_settings_username'] = 'Utilizator MySQL';
$txt['mysql_settings_username_info'] = 'Completează aici numele de utilizator cu care te conectezi la baza de date MySQL.<br />Dacă nu îl stii, asta e, încearcă numele de utilizator de la contul FTP, în cele mai multe cazuri este acelaşi.';
$txt['mysql_settings_password'] = 'Parola utilizatorului MySQL';
$txt['mysql_settings_password_info'] = 'Aici scrie parola necesara pentru a te conecta la baza de date MySQL.<br />Dacă nu o stii, încearcă parola de la contul FTP.';
$txt['mysql_settings_database'] = 'Numele bazei de date MySQL';
$txt['mysql_settings_database_info'] = 'Completează numele bazei de date care urmează a fi folosită de către SMF să stocheze datele.<br />Dacă nu există, se va încerca crearea acesteia.';
$txt['mysql_settings_prefix'] = 'Prefixul tabelelor MySQL';
$txt['mysql_settings_prefix_info'] = 'Prefixul care urmează a fi utilizat pentru tabelele forumului SMF.  <b>Niciodata nu instala doua forumuri cu acelaşi prefix!</b><br />Această valoare permite multiple instalari într-o singură bază de date.';

$txt['user_settings'] = 'Creează contul tău';
$txt['user_settings_info'] = 'Interfata va crea acum un nou cont de administrator pentru tine!';
$txt['user_settings_username'] = 'Numele tău de utilizator';
$txt['user_settings_username_info'] = 'Alege numele cu care vrei să te autentifici.<br />Acesta nu poate fi schimbat mai tarziu, ănsa numele afişat poate fi.';
$txt['user_settings_password'] = 'Parola';
$txt['user_settings_password_info'] = 'Completează parola dorită şi memorează această parola corect.';
$txt['user_settings_again'] = 'Parola';
$txt['user_settings_again_info'] = '(doar pentru verificare.)';
$txt['user_settings_email'] = 'Adresa email';
$txt['user_settings_email_info'] = 'Trebuie să completezi adresa de Email. <b>Aceasta trebuie să fie o adresă validă!</b>';
$txt['user_settings_database'] = 'Parola de la baza de date MySQL';
$txt['user_settings_database_info'] = 'Interfaţa cere aceasta parolă pentru baza de date, pentru a crea contul de administrator, din motive de securitate.';
$txt['user_settings_proceed'] = 'Termină';

$txt['ftp_setup'] = 'Informaţii pentru conectarea FTP';
$txt['ftp_setup_info'] = 'Interfaţa se poate conecta prin FTP pentru a corecta fişierele care necesită să fie writable şi nu sunt.  Dacă nu funcţionează, va trebui să mergi şi să le modifici atributele manual in FTP folosind comanda chmod.  Atenţie: nu este suport SSL încă.';
$txt['ftp_server'] = 'Server FTP';
$txt['ftp_server_info'] = 'Acesta ar trebui să fie serverul FTP şi portul pe care te conectezi.';
$txt['ftp_port'] = 'Port FTP';
$txt['ftp_username'] = 'Utilizator FTP';
$txt['ftp_username_info'] = 'Numele de utilizator cu care te autentifici. <i>Acesta nu va fi salvat nicaieri.</i>';
$txt['ftp_password'] = 'Parola FTP';
$txt['ftp_password_info'] = 'Parola folosită la autentificarea FTP. <i>Aceasta nu va fi salvată nicăieri.</i>';
$txt['ftp_path'] = 'Calea unde este instalat forumul';
$txt['ftp_path_info'] = 'Aceasta este calea <i>relativă</i> pe care o foloseşti în serverul tău FTP.';
$txt['ftp_path_found_info'] = 'Calea de mai sus a fost detectată automat.';
$txt['ftp_connect'] = 'Conectează-te';
$txt['ftp_setup_why'] = 'Pentru ce este util acest pas?';
$txt['ftp_setup_why_info'] = 'Unele fişiere trebuie să aiba atributul writable pentru ca SMF să funcţioneze corect. Acest pas îţi permite să laşi interfaţa de instalare să le seteze acest atribut pentru tine. Oricum, în unele cazuri nu funcţionează, deci în acele cazuri trebuie să setezi manual atributul 777 (writable, 755 pe unele servere) pentru următoarele fişiere şi/sau directoare:';
$txt['ftp_setup_again'] = 'pentru a testa aceste fişiere din nou.';

$txt['error_php_too_low'] = 'Atenţie! Se pare ce versiunea PHP instalată pe serverul tău nu îndeplineşte <b>cerinţele minime de instalare</b> cerute de SMF.<br />Dacă nu eşti tu administratorul serverului, va trebuie să ceri un upgrade, sau să alegi un alt server gazda - în rest, te rugăm să instalezi o versiune mai recentă.<br /><br />Dacă eşti sigur că de fapt versiunea PHP este destul de recentă, poţi continua instalarea, oricum este total nerecomandat.';
$txt['error_missing_files'] = 'Nu am găsit fişierele esenţiale pentru instalare în acest director!<br /><br />Asigură-te că ai încărcat întregul pachet de la instalare, inclusiv fişierele sql, apoi încearcă din nou.';
$txt['error_session_save_path'] = 'Te rugăm să informezi administratorul serverului gazdă că variabila <b>session.save_path specificată în php.ini</b> nu este valabilă! Ea trebuie schimbată către un director care <b>există</b>, şi este <b>writable</b> de către userul sub care lucrează PHP.<br />';
$txt['error_windows_chmod'] = 'Tu folosesti un server windows şi unele din fişierele esenţiale nu sunt writable. Te rugăm să ceri gazdei să acorde  <b>permisiuni de scriere (write permissions)</b> utilizatorului sub care rulează PHP pentru fişierele folosite de către SMF la instalare. Trebuie să fie writable următoarele fişiere şi directoare:';
$txt['error_ftp_no_connect'] = 'Nu pot să mă conectez la serverul FTP folosind combinaţia de detalii indicată.';
$txt['error_mysql_connect'] = 'Nu pot să mă conectez la baza de date MySQL folosind informaţiile indicate.<br /><br />Dacă nu eşti sigur despre ceea ce trebuie să completezi, te rugăm să întrebi administratorul serverului gazda.';
$txt['error_mysql_too_low'] = 'Versiunea MySQL care este utilizată pe server este foarte veche şi nu indeplineşte condiţiile minime necesare de către SMF.<br /><br />Te rugăm să ceri gazdei fie să instaleze o versiune mai nouă, fie să-ţi acorde un alt server, dacă nu doreste încearcă un server gazda diferit.';
$txt['error_mysql_database'] = 'Interfaţa de instalare nu poate să acceseze baza de date. La unele servere este posibil să trebuiască să-ţi creezi baza de date în panoul de administrare (cpanel) înainte de a putea fi utilizată de SMF. La altele serverul adaugă prefixe - cum ar fi numele de utilizator - la numele bazei de date.';
$txt['error_mysql_queries'] = 'Unele cereri MySQL nu au fost executate corect.  Aceasta poate fi cauzată de către o versiune nesuportată (dezvoltare -beta- sau prea veche) de MySQL.<br /><br />Informaţii tehnice asupra acelor cereri:';
$txt['error_mysql_queries_line'] = 'Linia #';
$txt['error_mysql_missing'] = 'Interfaţa de instalare nu a putut găsi suport MySQL în PHP.  Te rugăm să ceri gazdei să compileze PHP cu suport MySQL, sau să se asigure că extensia necesară a fost încarcată.';
$txt['error_session_missing'] = 'Interfaţa de instalare a fost incapabilă să detecteze sessions support în instalarea PHP pe server. Te rugăm să anunţi administratorul serverului gazda că PHP a fost compilat cu session support (în fapt, trebuie să fie compilat în mod explicit fară.)';
$txt['error_user_settings_again_match'] = 'Ai scris doua parole complet diferite!';
$txt['error_user_settings_taken'] = 'Ne pare rău, un alt utilizator este deja înregistrat cu acest nume de utilizator şi/sau parolă.<br /><br />Contul nou nu a fost creat.';
$txt['error_user_settings_query'] = 'O eroare în baza de date a apărut când s-a încercat crearea contului de administrator.  Această eroare este:';
$txt['error_subs_missing'] = 'Nu gasesc fişierul /Sources/Subs.php. Asigură-te că a fost încărcat corect şi apoi încearcă din nou.';
$txt['error_mysql_alter_priv'] = 'Contul de MySQL specificat nu are permisiuni să ALTER, CREATE şi/sau DROP tabele în baza de date; este absolut necesar pentru ca SMF să funcţioneze corect.';
$txt['error_versions_do_not_match'] = 'Interfaţa de instalarea a detectat o altă versiune de SMF deja instalată cu informaţiile specificate.  Dacă încerci să faci un upgrade, atunci ar trebui să utilizezi interfaţa de upgrade (upgrade.php), nu cea de instalare.<br /><br />Altfel, poate vrei să folosesti alte informaţii, sau să crezi un backup şi să ştergi datele din acest moment din baza de date.';
$txt['error_mod_security'] = 'Interfaţa de instalare a detectat că modulul mod_security este instalat pe serverul web. Mod_security va bloca formularele completate înainte ca SMF să poata face ceva. SMF are built-in un security scanner care funcţionează mai eficient decat mod_security şi care nu va bloca formularele primite.<br /><br /><a href="http://www.simplemachines.org/redirect/mod_security">Mai multe informaţii despre cum poate fi dezactivat mod_security</a>';
$txt['error_utf8_mysql_version'] = 'Versiunea curentă a bazei de date nu suportă utilizarea implicită a setului de caractere UTF-8. Tu poţi totuşi instala SMF fără nicio problemă, insă doar cu suport UTF-8 dezactivat. Dacă doreşti în viitor să treci la UTF-8 (după ce serverul MySQL unde ţii baza de date a forumului a fost updatat la o versiunea >= 4.1), poţi converti forumul tău la UTF-8 din panoul de administrare.';

?>