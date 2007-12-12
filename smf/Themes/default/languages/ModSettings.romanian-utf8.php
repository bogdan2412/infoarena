<?php
// Version: 1.1; ModSettings

// Versiunea în limba română cu diacritice www.smf.ro

$txt['smf3'] = 'Această pagină îţi oferă posibilitatea să schimbi setările, unele caracteristici şi căteva opţiuni de bază în forum. Vezi <a href="' . $scripturl . '?action=theme;sa=settings;th=' . $settings['theme_id'] . ';sesc=' . $context['session_id'] . '">setări pentru teme</a> pentru mai multe opţiuni. Click iconul de ajutor pentru mai multe informaţii despre o setare anume.';

$txt['mods_cat_features'] = 'Caracteristici de bază';
$txt['pollMode'] = 'Mod sondaj';
$txt['smf34'] = 'Dezactivează sondaj';
$txt['smf32'] = 'Activează sondaj';
$txt['smf33'] = 'Afişează sondajele existente ca subiecte';
$txt['allow_guestAccess'] = 'Permite vizitatorilor să vadă forumul';
$txt['userLanguage'] = 'Activează selectarea limbii de către utilizatori';
$txt['allow_editDisplayName'] = 'Permite utilizatorilor să editeze numele afişat?';
$txt['allow_hideOnline'] = 'Permite altora decât admin să ascundă starea online?';
$txt['allow_hideEmail'] = 'Permite utilizatorilor să ascundă email propriu celorlalţi în afară de admin?';
$txt['guest_hideContacts'] = 'Nu afişa detaliile despre membri oaspeţilor?';
$txt['titlesEnable'] = 'Activează titlurile personalizate';
$txt['enable_buddylist'] = 'Activează lista de amici';
$txt['default_personalText'] = 'Text personal implicit';
$txt['max_signatureLength'] = 'Numarul maxim de caractere în semnătura<div class="smalltext">(0 nelimitat)</div>';
$txt['number_format'] = 'Format implicit pentru numere';
$txt['time_format'] = 'Format implicit pentru timp';
$txt['time_offset'] = 'Diferenţa de timp implicită<div class="smalltext">(adăugată la opţiunile utilizatorilor.)</div>';
$txt['failed_login_threshold'] = 'Durata între două tentative de autentificare nereuşite';
$txt['lastActive'] = 'Perioada de contorizare a utilizatorilor online';
$txt['trackStats'] = 'Inregistrează statistici zilnice';
$txt['hitStats'] = 'Inregistrează numărul de pagini afişate zilnic (trebuie să ai statisticile activate)';
$txt['enableCompressedOutput'] = 'Activează iesirea comprimată';
$txt['databaseSession_enable'] = 'Foloseste înregistrarea în baza de date a sesiunilor (Use database driven sessions)';
$txt['databaseSession_loose'] = 'Permite utilizatorilor să folosească back pentru pagini în cache';
$txt['databaseSession_lifetime'] = 'Secunde înainte ca o sesiune nefolosită să expire';
$txt['enableErrorLogging'] = 'Activează logul de erori';
$txt['cookieTime'] = 'Durata implicita pentru cookies (în minute)';
$txt['localCookies'] = 'Activează stocarea locala a cookies<div class="smalltext">(SSI nu va lucra bine dacă aceasta opţiune este activata.)</div>';
$txt['globalCookies'] = 'Foloseşte coockies independente pentru subdomenii<div class="smalltext">(dezactivează local cookies mai întăi!)</div>';
$txt['securityDisable'] = 'Dezactivează securitatea suplimentară pentru adminstrator';
$txt['send_validation_onChange'] = 'Necesită reactivare la schimbarea de email';
$txt['approveAccountDeletion'] = 'Necesită aprobare de la admin pentru a şterge contul personal';
$txt['autoOptDatabase'] = 'Optimizează tabelele periodic la urmatorul interval de zile?<div class="smalltext">(0 pentru a dezactiva.)</div>';
$txt['autoOptMaxOnline'] = 'Numărul maxim de useri online când execuţi optimizarea<div class="smalltext">(0 nelimitat.)</div>';
$txt['autoFixDatabase'] = 'Corectează tabelele defecte in mod automat';
$txt['allow_disableAnnounce'] = 'Permite utilizatorilor să dezactiveze anunţurile';
$txt['disallow_sendBody'] = 'Nu permite să se trimite text în anunţuri?';
$txt['modlog_enabled'] = 'Activează logul de moderare';
$txt['queryless_urls'] = 'Search engine friendly URLs<div class="smalltext"><b>Doar pentru Apache!</b></div>';
$txt['max_image_width'] = 'Dimensiunea maximă  - latime - a pozelor afişate (0 = dezactivează)';
$txt['max_image_height'] = 'Dimensiunea maximă - înaltime - a pozelor afişate (0 = dezactivează)';
$txt['mail_type'] = 'Facilitatea Mail';
$txt['mail_type_default'] = '(PHP implicit)';
$txt['smtp_host'] = 'server SMTP';
$txt['smtp_port'] = 'port SMTP';
$txt['smtp_username'] = 'utilizatorul SMTP';
$txt['smtp_password'] = 'parola SMTP';
$txt['pm_posts_verification'] = 'Numărul de mesaje sub care utilizatorul trebuie să introducă codul vizual atunci când trimiste mesaje personale.<div class="smalltext">(0 pentru nelimitat, administratorii sunt exceptaţi)</div>';
$txt['pm_posts_per_hour'] = 'Numărul de mesaje personale pe care un utilizator le poate trimite în interval de o oră. <div class="smalltext">(0 pentru nelimitat, moderatorii sunt exceptaţi)</div>';

$txt['enableReportPM'] = 'Activează raportarea mesajelor personale';
$txt['max_pm_recipients'] = 'Numărul maxim de destinatari permişi pentru un mesaj personal.<div class="smalltext">(0 pentru nelimitat, administratorii sunt exclusi)</div>';

$txt['mods_cat_layout'] = 'Layout şi Opţiuni';
$txt['compactTopicPagesEnable'] = 'Limitează numărul de legături către paginile afişate';
$txt['smf235'] = 'Pagini continue pentru afişare:';
$txt['smf236'] = 'pentru afişare';
$txt['todayMod'] = 'Activează caracteristica &quot;Azi&quot; ';
$txt['smf290'] = 'Dezactivat';
$txt['smf291'] = 'Doar Azi';
$txt['smf292'] = 'Azi &amp; Ieri';
$txt['topbottomEnable'] = 'Activează butoanele Mergi Sus/Mergi Jos';
$txt['onlineEnable'] = 'Afişează starea online/offline în mesaje şi PM';
$txt['enableVBStyleLogin'] = 'Afişează autentificarea rapida în fiecare pagină';
$txt['defaultMaxMembers'] = 'Utilizatori pe pagină în lista de utilizatori';
$txt['timeLoadPageEnable'] = 'Afişează timpul trecut pentru a crea fiecare pagină';
$txt['disableHostnameLookup'] = 'Dezactivează hostname lookups?';
$txt['who_enabled'] = 'Activează lista Cine e online';

$txt['smf293'] = 'Popularitate';
$txt['karmaMode'] = 'Mod popularitate';
$txt['smf64'] = 'Dezactivează karma|Activează karma per total|Activează karma pozitiv/negativ';
$txt['karmaMinPosts'] = 'Setează numărul minim de mesaje necesare pentru a putea modifica popularitatea altora';
$txt['karmaWaitTime'] = 'Setează timpul de aşteptare în ore';
$txt['karmaTimeRestrictAdmins'] = 'Restrictionează administratorii la timpul de aşteptare';
$txt['karmaLabel'] = 'Eticheta popularitate';
$txt['karmaApplaudLabel'] = 'Eticheta pentru aplauze';
$txt['karmaSmiteLabel'] = 'Eticheta pentru dezaprobare';

$txt['caching_information'] = '<div align="center"><b><u>Important! Citeşte indicaţiile următoare înainte de a activa aceasta facilitate.</b></u></div><br />
	SMF are suport pentru caching folosind acceleratorii de proces. Acceleratorii suportaţi în acest moment sunt:<br />
	<ul>
		<li>APC</li>
		<li>eAccelerator</li>
		<li>Turck MMCache</li>
		<li>Memcached</li>
		<li>Zend Platform/Performance Suite (Nu Zend Optimizer)</li>
	</ul>
	Caching va functiona pe serverul tău dacă PHP a fost compilat cu unul din optimizatorii de mai sus sau are memcache
	disponibilă. <br /><br />
	SMF executa caching la o mare varietate de nivele. Cu căt nivelul de caching ales este mai ridicat cu atat mai mult timp de CPU va fi folosit
	pentru a cauta informatiile. Dacă caching este disponibil pe masina ta este recomandat să încerci primul nivel mai întăi.
	<br /><br />
	Fii atent: dacă folosesti memcached va trebui să completezi detaliile despre server în setările de mai jos. Acestea vor fi introduse sub forma de lista separate prin virgula
	asa cum se observa în exemplul de mai jos:<br />
	&quot;server1,server2,server3:port,server4&quot;<br /><br />
	Atenţie: dacă nu vei specifica nici un port atunci SMF va folosi portul implicit 11211. SMF va încerca să utilizeze serverele căt mai echilibrat în privinţa încărcării acestora.
	<br /><br />
	%s
	<hr />';

$txt['detected_no_caching'] = '<b style="color: red;">SMF nu a reuşit să găseasca un accelerator compatibil pe serverul tău.</b>';
$txt['detected_APC'] = '<b style="color: green">SMF a detectat că APC este instalat pe serverul tău.';
$txt['detected_eAccelerator'] = '<b style="color: green">SMF a detectat ca eAccelerator este instalat pe serverul tău.';
$txt['detected_MMCache'] = '<b style="color: green">SMF a detectat ca MMCache este instalat pe serverul tău.';
$txt['detected_Zend'] = '<b style="color: green">SMF a detectat ca Zend este instalat pe serverul tău.';

$txt['cache_enable'] = 'Nivel Caching';
$txt['cache_off'] = 'Fără caching';
$txt['cache_level1'] = 'Nivel 1 Caching';
$txt['cache_level2'] = 'Nivel 2 Caching (Nerecomandat)';
$txt['cache_level3'] = 'Nivel 3 Caching (Nerecomandat)';
$txt['cache_memcached'] = 'Setări pentru Memcache';

?>