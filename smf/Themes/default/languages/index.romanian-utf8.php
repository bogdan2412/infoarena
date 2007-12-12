<?php
// Version: 1.1.2; index

global $forum_copyright, $forum_version, $webmaster_email;

// Versiunea în limba română cu diacritice www.smf.ro
// Locale (strftime, pspell_new) and spelling. (pspell_new, can be left as '' normally.)
// For more information see:
//   - http://www.php.net/function.pspell-new
//   - http://www.php.net/function.setlocale
// Again, SPELLING SHOULD BE '' 99% OF THE TIME!!  Please read this!
$txt['lang_locale'] = 'ro_RO.utf8';
$txt['lang_dictionary'] = 'ro';
$txt['lang_spelling'] = 'română';

// Character set and right to left?
$txt['lang_character_set'] = 'UTF-8';
$txt['lang_rtl'] = false;

$txt['days'] = array('Duminică', 'Luni', 'Marţi', 'Miercuri', 'Joi', 'Vineri', 'Sâmbătă');
$txt['days_short'] = array('Du', 'Lu', 'Ma', 'Mi', 'Jo', 'Vi', 'Sa');
// Months must start with 1 => 'Ianuarie'. (or translated, of course.)
$txt['months'] = array(1 => 'Ianuarie', 'Februarie', 'Martie', 'Aprile', 'Mai', 'Iunie', 'Iulie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie');
$txt['months_titles'] = array(1 => 'Ianuarie', 'Februarie', 'Martie', 'Aprile', 'Mai', 'Iunie', 'Iulie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie');
$txt['months_short'] = array(1 => 'Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun', 'Iul', 'Aug', 'Sep', 'Oct', 'Noi', 'Dec');

$txt['newmessages0'] = 'este nou';
$txt['newmessages1'] = 'sunt noi';
$txt['newmessages3'] = 'Nou';
$txt['newmessages4'] = ',';

$txt[2] = 'Administrare';

$txt[10] = 'Salvează';

$txt[17] = 'Modifică';
$txt[18] = $context['forum_name'] . ' - Index';
$txt[19] = 'Utilizatori';
$txt[20] = 'Numele secţiunii';
$txt[21] = 'Mesaje';
$txt[22] = 'Ultimul mesaj';

$txt[24] = '(Nici un subiect)';
$txt[26] = 'Mesaje';
$txt[27] = 'Vezi Profilul';
$txt[28] = 'Vizitator';
$txt[29] = 'Autor';
$txt[30] = ' ';
$txt[31] = 'Elimină';
$txt[33] = 'Creează un subiect nou';

$txt[34] = 'Autentificare';
// Use numeric entities în the below string.
$txt[35] = 'Nume de utilizator';
$txt[36] = 'Parola';

$txt[40] = 'Acest nume de utilizator nu există';

$txt[62] = 'Moderatorul forumului';
$txt[63] = 'Elimină subiectul';
$txt[64] = 'Subiecte';
$txt[66] = 'Modifică mesajul';
$txt[68] = 'Nume';
$txt[69] = 'Adresa de email';
$txt[70] = 'Titlul';
$txt[72] = 'Mesaj';

$txt[79] = 'Profil';

$txt[81] = 'Alege o parolă';
$txt[82] = 'Verifică parola';
$txt[87] = 'Rang';

$txt[92] = 'Vezi profilul utilizatorului';
$txt[94] = 'Total';
$txt[95] = 'Mesaje';
$txt[96] = 'Site personal';
$txt[97] = 'Creează un cont';

$txt[101] = 'Index-ul mesajului';
$txt[102] = 'Noutăţi';
$txt[103] = 'Pagina principală';

$txt[104] = 'Blochează/Deblochează subiectul';
$txt[105] = 'Mesaj';
$txt[106] = 'A apărut o eroare!';
$txt[107] = 'la';
$txt[108] = 'Ieşire';
$txt[109] = 'Creat de';
$txt[110] = 'Răspunsuri';
$txt[111] = 'Ultimul mesaj';
$txt[114] = 'Autentificare administrator';
// Use numeric entities în the below string.
$txt[118] = 'Subiect';
$txt[119] = 'Ajutor';
$txt[121] = 'Elimină mesajul';
$txt[125] = 'Avertizare';
$txt[126] = 'Vrei să fii avertizat printr-un email dacă apar răspunsuri la acest subiect?';
// Use numeric entities în the below string.
$txt[130] = " \nEchipa " . $context['forum_name'] . '';
$txt[131] = 'Avertizare la răspunsuri';
$txt[132] = 'Mută subiectul';
$txt[133] = 'Mută în';
$txt[139] = 'Pagini';
$txt[140] = 'Utilizatori activi în ultimele ' . $modSettings['lastActive'] . ' minute';
$txt[144] = 'Mesaje personale';
$txt[145] = 'Răspunde folosind un citat';
$txt[146] = 'Răspunde';

$txt[151] = 'Nu sunt mesaje...';
$txt[152] = 'ai';
$txt[153] = 'mesaje';
$txt[154] = 'Şterge acest mesaj';

$txt[158] = 'Utilizatori prezenţi';
$txt[159] = 'Mesaje personale';
$txt[160] = 'Schimbă forumul';
$txt[161] = 'Schimbă';
$txt[162] = 'Eşti sigur că vrei să elimini acest subiect?';
$txt[163] = 'Da!';
$txt[164] = 'Nu!';

$txt[166] = 'Rezultatele căutarii';
$txt[167] = 'Căutarea este terminată';
$txt[170] = 'Nu există nici un rezultat';
$txt[176] = 'din';

$txt[182] = 'Caută';
$txt[190] = 'Toate';

$txt[193] = 'Inapoi';
$txt[194] = 'Memorează parola';
$txt[195] = 'Subiect creat de';
$txt[196] = 'Titlul';
$txt[197] = 'Scris de';
$txt[200] = 'Diverse criterii de căutare în lista utilizatorilor înregistraţi';
$txt[201] = 'Bine ai venit!';
$txt[208] = 'Centrul de administrare';
$txt[211] = 'Ultima modificare';
$txt[212] = 'Vrei să nu mai fii avertizat prin email dacă apar răspunsuri la acest subiect?';

$txt[214] = 'Mesaje recente';

$txt[227] = 'Locaţia';
$txt[231] = 'Gen';
$txt[233] = 'Cont creat la';

$txt[234] = 'Vezi cele mai recente mesaje';
$txt[235] = 'este cel mai recent subiect';

$txt[238] = 'Bărbat';
$txt[239] = 'Femeie';

$txt[240] = 'Ai introdus caractere nepermise la numele de utilizator.';

$txt['welcome_guest'] = 'Bine ai venit, <b>' . $txt[28] . '</b>. Trebuie <a href="' . $scripturl . '?action=login">să te autentifici</a> sau <a href="' . $scripturl . '?action=register">să îţi creezi un cont</a>.';
$txt['welcome_guest_activate'] = '<br />Ai pierdut sau nu ai primit emailul care conţine <a href="' . $scripturl . '?action=activate">codul de activare al contului?</a>';
$txt['hello_member'] = 'Salut,';
// Use numeric entities în the below string.
$txt['hello_guest'] = 'Bine ai venit,';
$txt[247] = 'Salut,';
$txt[248] = 'Bine ai venit,';
$txt[249] = 'Te rog';
$txt[250] = 'Înapoi';
$txt[251] = 'Selectează o destinaţie';

// Escape any single quotes în here twice.. 'it\'s' -> 'it\\\'s'.
$txt[279] = 'Creat de';

$txt[287] = 'Smiley';
$txt[288] = 'Angry';
$txt[289] = 'Cheesy';
$txt[290] = 'Laugh';
$txt[291] = 'Sad';
$txt[292] = 'Wink';
$txt[293] = 'Grin';
$txt[294] = 'Shocked';
$txt[295] = 'Cool';
$txt[296] = 'Huh';
$txt[450] = 'Roll Eyes';
$txt[451] = 'Tongue';
$txt[526] = 'Embarrassed';
$txt[527] = 'Lips sealed';
$txt[528] = 'Undecided';
$txt[529] = 'Kiss';
$txt[530] = 'Cry';

$txt[298] = 'Moderator';
$txt[299] = 'Moderatori';

$txt[300] = 'Marchează subiectele din această secţiune ca fiind citite';
$txt[301] = 'Vizite';
$txt[302] = 'Nou';

$txt[303] = 'Afişează toţi utilizatorii';
$txt[305] = 'Vezi';
$txt[307] = 'Email';

$txt[308] = 'Afişează utilizatorii';
$txt[309] = 'din';
$txt[310] = 'numărul total de utilizatori';
$txt[311] = 'la';
$txt[315] = 'Ai uitat parola?';

$txt[317] = 'Data';
// Use numeric entities în the below string.
$txt[318] = 'De la';
$txt[319] = 'Subiect';
$txt[322] = 'Verifică noile mesaje';
$txt[324] = 'La';

$txt[330] = 'Subiecte';
$txt[331] = 'Membri';
$txt[332] = 'Lista utilizatorilor înregistraţi';
$txt[333] = 'Mesaje noi';
$txt[334] = 'Nu sunt mesaje noi';

$txt['sendtopic_send'] = 'Trimite!';

$txt[371] = 'Diferenţa de timp faţă de ora oficiala a forumului';
$txt[377] = 'sau';

$txt[398] = 'Nu există nici un rezultat';

$txt[418] = 'Avertizare';

$txt[430] = 'Utilizatorului %s i s-a interzis accesul pe forum!';

$txt[452] = 'Marchează toate subiecte ca fiind citite';

$txt[454] = 'Subiect interesant (Mai mult de ' . $modSettings['hotTopicPosts'] . ' răspunsuri)';
$txt[455] = 'Subiect FOARTE interesant (Mai mult de ' . $modSettings['hotTopicVeryPosts'] . ' răspunsuri)';
$txt[456] = 'Subiect închis';
$txt[457] = 'Subiect normal';
$txt['participation_caption'] = 'Subiect la care ai scris';

$txt[462] = 'Du-te!';

$txt[465] = 'Imprimă';
$txt[467] = 'Profil';
$txt[468] = 'Cuprinsul subiectului';
$txt[470] = 'N/A';
$txt[471] = 'mesaj';
$txt[473] = 'Acest nume de utilizator a fost deja utilizat';

$txt[488] = 'Utilizatori care au cont';
$txt[489] = 'Număr de mesaje';
$txt[490] = 'Număr de subiecte';

$txt[497] = 'Timp de conectare';

$txt[507] = 'Verificare';
$txt[508] = 'Autentificare permanentă';

$txt[511] = 'Memorat';
// Use numeric entities în the below string.
$txt[512] = 'IP';

$txt[513] = 'ICQ';
$txt[515] = 'WWW';

$txt[525] = 'de către';

$txt[578] = 'ore';
$txt[579] = 'zile';

$txt[581] = ', cel mai nou membru.';

$txt[582] = 'Caută dupa';

$txt[603] = 'AIM';
// In this string, please use +'s for spaces.
$txt['aim_default_message'] = 'Salut! Ce faci?';
$txt[604] = 'YM';

$txt[616] = 'Acest forum este în \'întreţinere\'.';

$txt[641] = 'Citit de';
$txt[642] = 'ori';

$txt[645] = 'Statistici forum';
$txt[656] = 'Ultimul cont creat';
$txt[658] = 'Totalul secţiunilor';
$txt[659] = 'Ultimul mesaj';

$txt[660] = 'Ai';
$txt[661] = 'Apasă';
$txt[662] = 'aici';
$txt[663] = 'pentru a le vedea.';

$txt[665] = 'Totalul secţiunilor';

$txt[668] = 'Imprimă pagina';

$txt[679] = 'Trebuie să fie o adresa de email corectă.';

$txt[683] = 'Sunt un fraier!!';
$txt[685] = $context['forum_name'] . ' - Informaţii forum';

$txt[707] = 'Trimite acest subiect';

$txt['sendtopic_title'] = 'Trimite subiectul &quot;%s&quot; unui prieten.';
// Use numeric entities în the below three strings.
$txt['sendtopic_dear'] = ' %s,';
$txt['sendtopic_this_topic'] = 'Îţi recomand să citeşti "%s" pe ' . $context['forum_name'] . '.  Pentru a vedea subiectul foloseşte adresa';
$txt['sendtopic_thanks'] = 'Mulţumesc';
$txt['sendtopic_sender_name'] = 'Numele tău';
$txt['sendtopic_sender_email'] = 'Adresa ta de email';
$txt['sendtopic_receiver_name'] = 'Numele destinatarului';
$txt['sendtopic_receiver_email'] = 'Adresa lui de email';
$txt['sendtopic_comment'] = 'Scrie şi un mic text drept comentariu';
// Use numeric entities în the below string.
$txt['sendtopic2'] = 'A fost adăugat urmatorul comentariu legat de acest subiect';

$txt[721] = 'Vrei ca adresa ta de email să nu fie publică?';

$txt[737] = 'Selectează toate';

// Use numeric entities în the below string.
$txt[1001] = 'Eroare în baza de date';
$txt[1002] = 'Dacă după o nouă încercare mai apare această eroare te rugăm să contactezi Administratorul.';
$txt[1003] = 'Fişier';
$txt[1004] = 'Linie';
// Use numeric entities în the below string.
$txt[1005] = 'SMF a detectat o eroare în baza de date şi a încercat să o remedieze. Dacă tot mai apar probleme sau primeşti mesaje pe email trebuie să contactezi administratorul.';
$txt['database_error_versions'] = '<b>Observaţie:</b> Baza de date are nevoie <em>may</em> de o înnoire. Momentan fişierele forumului sunt la versiunea ' . $forum_version . ', iar baza de date este la versiunea ' . $modSettings['smfVersion'] . 'Erorile precedente pot disparea dacă vei utiliza ultima versiune a fişierului upgrade.php.';
$txt['template_parse_error'] = 'Eroare la fişierul template - template parse error';
$txt['template_parse_error_message'] = 'Au apărut cateva disfuncţionalitati temporare legate de partea grafica a forumului. Dacă după o noua încercare acest mesaj mai apare atunci contactează imediat Administratorul.<br /><br />Mai încearcă şi <a href="javascript:location.reload();">apăsând aici</a>.';
$txt['template_parse_error_details'] = 'A apărut o problema legată de <tt><b>%1$s</b></tt> partea grafică sau de fişierul de limbă. Verifică ortografia şi mai încearcă. Nu uita: aceste semne (<tt>\'</tt>) au nevoie de ajutorul unei bare oblice (<tt>\\</tt>). Pentru a cunoaşte mai multe detalii despre aceste erori PHP, încearcă <a href="' . $boardurl . '%1$s">accesarea directă a fişierului</a>.<br /><br />Mai poţi încerca <a href="javascript:location.reload();">reîncărcând pagina</a> sau <a href="' . $scripturl . '?theme=1">în lipsa temei grafice</a>.';

$txt['smf10'] = '<b>Astăzi</b> la ';
$txt['smf10b'] = '<b>Ieri</b> la ';
$txt['smf20'] = 'Sondaj nou';
$txt['smf21'] = 'Întrebare';
$txt['smf23'] = 'Adaugă votul';
$txt['smf24'] = 'Numărul votanţilor';
$txt['smf25'] = 'Poţi folosi tastele Alt+S pentru a adăuga sau Alt+P pentru a verifica';
$txt['smf29'] = 'Vezi rezultatele';
$txt['smf30'] = 'Suspendă sondajul';
$txt['smf30b'] = 'Deblochează sondajul';
$txt['smf39'] = 'Modifică sondajul';
$txt['smf43'] = 'Sondaj';
$txt['smf47'] = 'o zi';
$txt['smf48'] = 'o săptamană';
$txt['smf49'] = 'o lună';
$txt['smf50'] = 'Timp nelimitat';
$txt['smf52'] = 'Autentifică-te cu numele de utilizator, parola şi precizează durata sesiunii.';
$txt['smf53'] = 'o ora';
$txt['smf56'] = 'Subiect MUTAT';
$txt['smf57'] = 'Scrie motivul pentru care<br />acest subiect a fost mutat.';
$txt['smf60'] = 'Nu ai un număr suficient de mesaje pentru a avea acces la aceasta opţiune. Ai nevoie de cel putin ';
$txt['smf62'] = 'Nu poţi modifica popularitatea aceluiaşi utilizator decât o data la ';
$txt['smf82'] = 'Secţiune';
$txt['smf88'] = 'în';
$txt['smf96'] = 'Subiect IMPORTANT';

$txt['smf138'] = 'Şterge';

$txt['smf199'] = 'Mesajele personale';

$txt['smf211'] = 'KB';

$txt['smf223'] = '[Statistici detaliate]';

// Use numeric entities în the below three strings.
$txt['smf238'] = 'Cod';
$txt['smf239'] = 'Citat din mesajul lui';
$txt['smf240'] = 'Citat';

$txt['smf251'] = 'Secţionează subiectul';
$txt['smf252'] = 'Lipeşte subiectele';
$txt['smf254'] = 'Titlul noului subiect';
$txt['smf255'] = 'Secţionează numai acest subiect.';
$txt['smf256'] = 'Secţionează subiectul după şi include acest mesaj.';
$txt['smf257'] = 'Alege mesajele destinate secţionării.';
$txt['smf258'] = 'Subiect nou';
$txt['smf259'] = 'Subiectul iniţial a fost secţionat în două noi subiecte.';
$txt['smf260'] = 'Subiectul original';
$txt['smf261'] = 'Selectează mesajele pe care vrei să le separi.';
$txt['smf264'] = 'Subiectele au fost lipite.';
$txt['smf265'] = 'Subiectul nou creat prin lipire';
$txt['smf266'] = 'Subiecte pe care vrei să le lipeşti';
$txt['smf267'] = 'Secţiunea unde vrei să îl muţi';
$txt['smf269'] = 'Subiectul la care vrei să îl ataşezi';
$txt['smf274'] = 'Eşti sigur că vrei să efectuezi lipirea';
$txt['smf275'] = 'cu';
$txt['smf276'] = 'Această acţiune va combina mesajele din două subiecte într-un singur subiect. Mesajele vor fi aranjate în funcţie de data la care au fost create şi cel mai vechi mesaj va deveni primul în subiectul nou apărut după operaţia de lipire.';

$txt['smf277'] = 'Marchează ca IMPORTANT';
$txt['smf278'] = 'Deselectează';
$txt['smf279'] = 'Blochează subiectul';
$txt['smf280'] = 'Deblochează subiectul';

$txt['smf298'] = 'Căutare detaliată';

$txt['smf299'] = 'RISC MAJOR DE SECURITATE:';
$txt['smf300'] = 'Nu ai şters ';

$txt['smf301'] = 'Pagină creată în ';
$txt['smf302'] = ' secunde cu ';
$txt['smf302b'] = ' cereri.';

$txt['smf315'] = 'Foloseşte această opţiune pentru a face o sesizare către Administrator/Moderatori avand ca subiect mesajele cu un conţinut contrar regulamentului.<br /><i>Fii atent că adresa ta de email va fi afişată la moderatori dacă utilizezi această opţiune.</i>';

$txt['online2'] = 'Conectat';
$txt['online3'] = 'Deconectat';
$txt['online4'] = 'Mesaj personal (Conectat)';
$txt['online5'] = 'Mesaj personal (Deconectat)';
$txt['online8'] = 'Statistică';

$txt['topbottom4'] = 'In sus';
$txt['topbottom5'] = 'In jos';

$forum_copyright = '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">Powered by ' . $forum_version . '</a> | 
<a href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software" target="_blank">SMF &copy; 2006-2007, Simple Machines LLC</a> <br /> <a href="http://www.smf.ro/" title="Traducerea în limba română" target="_blank"> Traducerea în limba română &copy;  2006-2007 www.smf.ro</a>';

$txt['calendar3'] = 'Zile de naştere:';
$txt['calendar4'] = 'Evenimente:';
$txt['calendar3b'] =  'Aniversările următoare:';
$txt['calendar4b'] = 'Evenimentele următoare:';
// Prompt for holidays în the calendar, leave blank to just display the holiday's name.
$txt['calendar5'] = '';
$txt['calendar9'] = 'Luna:';
$txt['calendar10'] = 'Anul:';
$txt['calendar11'] = 'Ziua:';
$txt['calendar12'] = 'Titlul evenimentului:';
$txt['calendar13'] = 'Scrie în:';
$txt['calendar20'] = 'Modifică evenimentul';
$txt['calendar21'] = 'Şterge acest eveniment?';
$txt['calendar22'] = 'Şterge evenimentul';
$txt['calendar23'] = 'Adaugă un eveniment';
$txt['calendar24'] = 'Calendar';
$txt['calendar37'] = 'Către calendar';
$txt['calendar43'] = 'Către evenimente';
$txt['calendar47'] = 'Evenimentele viitoare';
$txt['calendar47b'] = 'Evenimentele de azi';
$txt['calendar51'] = 'Săptămâna';
$txt['calendar54'] = 'Număr de zile:';
$txt['calendar_how_edit'] = 'cum modifici acest eveniment?';
$txt['calendar_link_event'] = 'Legătura între eveniment şi subiect:';
$txt['calendar_confirm_delete'] = 'Eşti sigur că vrei să ştergi acest eveniment?';
$txt['calendar_linked_events'] = 'Legături la rubrica de evenimente';

$txt['moveTopic1'] = 'Adaugă un mesaj de redirecţionare';
$txt['moveTopic2'] = 'Schimbă titlul subiectului';
$txt['moveTopic3'] = 'Subiect nou';
$txt['moveTopic4'] = 'Schimbă titlul tuturor mesajelor';

$txt['theme_template_error'] = 'Eroare la \'%s\' tema grafică.';
$txt['theme_language_error'] = 'Eroare la \'%s\' fişierul de limbă.';

$txt['parent_boards'] = 'Secţiuni';

$txt['smtp_no_connect'] = 'Legătura eşuată cu serverul SMTP';
$txt['smtp_port_ssl'] = 'Portul setat pentru SMTP nu este corect; ar trebui să fie 465 pentru server SSL.';
$txt['smtp_bad_response'] = 'Eroare în recepţia codurilor serverului de email';
$txt['smtp_error'] = 'Emailul nu a fost trimis. Eroarea este: ';
$txt['mail_send_unable'] = 'Este imposibil să se trimita un email la această adresă \'%s\'';

$txt['mlist_search'] = 'Caută după numele de utilizator';
$txt['mlist_search2'] = 'Caută din nou';
$txt['mlist_search_email'] = 'Caută după adresa de email';
$txt['mlist_search_messenger'] = 'Caută după numele de YM';
$txt['mlist_search_group'] = 'Caută după rang';
$txt['mlist_search_name'] = 'Caută după nume';
$txt['mlist_search_website'] = 'Caută după www';
$txt['mlist_search_results'] = 'Rezultatele cautării după';

$txt['attach_downloaded'] = 'descărcat';
$txt['attach_viewed'] = 'văzut';
$txt['attach_times'] = 'ori';

$txt['MSN'] = 'MSN';

$txt['settings'] = 'Setările opţiunilor';
$txt['never'] = 'Niciodată';
$txt['more'] = 'mai mult';

$txt['hostname'] = 'Gazda';
$txt['you_are_post_banned'] = 'Din pacate %s, acestui utilizator i-a fost retras dreptul de a scrie mesaje.';
$txt['ban_reason'] = 'Motiv';

$txt['tables_optimized'] = 'Baza de date este optimizată';

$txt['add_poll'] = 'Sondaj nou';
$txt['poll_options6'] = 'Nu poţi adauga mai mult de %s opţiuni.';
$txt['poll_remove'] = 'Elimină sondajul';
$txt['poll_remove_warn'] = 'Eşti sigur că doresti să elimini sondajul din acest subiect?';
$txt['poll_results_expire'] = 'Rezultatele vor fi afişate când va expira perioada de votare';
$txt['poll_expires_on'] = 'Perioada de votare expiră';
$txt['poll_expired_on'] = 'Votare încheiata';
$txt['poll_change_vote'] = 'Elimină votul';
$txt['poll_return_vote'] = 'Opţiunile votării';

$txt['quick_mod_remove'] = 'Şterge ceea ce e selectat';
$txt['quick_mod_lock'] = 'Blochează ceea ce e selectat';
$txt['quick_mod_sticky'] = 'Marchează ca important -sticky- ceea ce e selectat';
$txt['quick_mod_move'] = 'Mută ceea ce e selectat în';
$txt['quick_mod_merge'] = 'Lipeşte ceea ce e selectat';
$txt['quick_mod_markread'] = 'Marchează ca citit ceea ce e selectat';
$txt['quick_mod_go'] = 'Execută!';
$txt['quickmod_confirm'] = 'Eşti sigur ca vrei să efectuezi această operaţie???';

$txt['spell_check'] = 'Verifică ortografia';

$txt['quick_reply_1'] = 'Răspuns rapid';
$txt['quick_reply_2'] = 'Şi la opţiunea <i>răspuns rapid</i> poţi folosi coduri şi zămbete; avantajul acestei opţiuni este ca răspunsul la un subiect se poate trimite mai repede.';
$txt['quick_reply_warning'] = 'ATENŢIE: acest subiect este blocat!<br />Numai administratorul şi moderatorii pot scrie.';

$txt['notification_enable_board'] = 'Eşti sigur că vrei să fii avertizat când se scriu subiecte noi în acest forum?';
$txt['notification_disable_board'] = 'Eşti sigur că nu vrei să fii avertizat când se scriu subiecte noi în acest forum?';
$txt['notification_enable_topic'] = 'Eşti sigur că vrei să fii avertizat când se scriu mesaje noi în acest subiect?';
$txt['notification_disable_topic'] = 'Eşti sigur că nu vrei să fii avertizat când se scriu mesaje noi în acest subiect?';

$txt['rtm1'] = 'Raportează acest text unui moderator';

$txt['unread_topics_visit'] = 'Subiecte recente necitite';
$txt['unread_topics_visit_none'] = 'Nu există subiecte necitite de la ultima ta autentificare/vizită.  <a href="' . $scripturl . '?action=unread;all">Apasă aici pentru a vedea subiectele necitite</a>.';
$txt['unread_topics_all'] = 'Toate subiectele necitite';
$txt['unread_replies'] = 'Subiecte noi';

$txt['who_title'] = 'Utilizatori prezenţi';
$txt['who_and'] = ' şi ';
$txt['who_viewing_topic'] = ' pe acest subiect.';
$txt['who_viewing_board'] = ' pe aceasta categorie.';
$txt['who_member'] = 'Utilizator';

$txt['powered_by_php'] = 'Creat cu PHP';
$txt['powered_by_mysql'] = 'Creat cu MySQL';
$txt['valid_html'] = 'Validat cu HTML 4.01!';
$txt['valid_xhtml'] = 'Validat cu XHTML 1.0!';
$txt['valid_css'] = 'Validat cu CSS!';

$txt['guest'] = 'Vizitator';
$txt['guests'] = 'Vizitatori';
$txt['user'] = 'Utilizator';
$txt['users'] = 'Utilizatori';
$txt['hidden'] = 'Ascuns';
$txt['buddy'] = 'Amic';
$txt['buddies'] = 'Amici';
$txt['most_online_ever'] = 'Cei mai mulţi utilizatori online în total';
$txt['most_online_today'] = 'Cei mai mulţi utilizatori online astăzi';

$txt['merge_select_target_board'] = 'Selectează forumul destinaţie pentru subiectelor lipite';
$txt['merge_select_poll'] = 'Selectează sondajul pe care vrei să îl ataşezi subiectelor lipite';
$txt['merge_topic_list'] = 'Selectează subiectele pe care vrei să le lipeşti';
$txt['merge_select_subject'] = 'Alege titlul subiectelor lipite';
$txt['merge_custom_subject'] = 'Titlul preferat';
$txt['merge_enforce_subject'] = 'Schimbă titlul tuturor mesajelor';
$txt['merge_include_notifications'] = 'Include avertizările?';
$txt['merge_check'] = 'Lipesc?';
$txt['merge_no_poll'] = 'Fără sondaj';

$txt['response_prefix'] = 'Răspuns: ';
$txt['current_icon'] = 'Imagine actuală';

$txt['smileys_current'] = 'Setul actual de imagini';
$txt['smileys_none'] = 'Fără imagini/zămbete';
$txt['smileys_forum_board_default'] = 'Forum/Categorii lipsă';

$txt['search_results'] = 'Rezultatele căutării';
$txt['search_no_results'] = 'Nici un rezultat';

$txt['totalTimeLogged1'] = 'Ai fost conectat: ';
$txt['totalTimeLogged2'] = ' zile, ';
$txt['totalTimeLogged3'] = ' ore şi ';
$txt['totalTimeLogged4'] = ' minute.';
$txt['totalTimeLogged5'] = 'z ';
$txt['totalTimeLogged6'] = 'h ';
$txt['totalTimeLogged7'] = 'm';

$txt['approve_thereis'] = 'Este';
$txt['approve_thereare'] = 'Sunt';
$txt['approve_member'] = 'un utilizator înregistrat';
$txt['approve_members'] = 'utilizatori înregistraţi';
$txt['approve_members_waiting'] = 'aşteaptă aprobarea contului.';

$txt['notifyboard_turnon'] = 'Vrei să primeşti o avertizare prin email când se scriu subiecte noi în aceast forum?';
$txt['notifyboard_turnoff'] = 'Eşti sigur ca nu vrei să fii avertizat când se scriu subiecte noi în aceast forum?';

$txt['activate_code'] = 'Codul de activare al contului tău este';

$txt['find_members'] = 'Caută un utilizator';
$txt['find_username'] = 'Nume de utilizator sau adresa de email';
$txt['find_buddies'] = 'Afişarea numai a amicilor?';
$txt['find_wildcards'] = 'Inlocuitori permişi: *, ?';
$txt['find_no_results'] = 'Nu au fost găsite rezultate';
$txt['find_results'] = 'Rezultate';
$txt['find_close'] = 'Inchis';

$txt['unread_since_visit'] = 'Mesaje necitite de la ultima autentificare.';
$txt['show_unread_replies'] = 'Raspunsuri noi la mesajele mele.';

$txt['change_color'] = 'Schimbă culoarea';

$txt['quickmod_delete_selected'] = 'Şterge ceea ce e selectat';

// In this string, don't use entities. (&amp;, etc.)
$txt['show_personal_messages'] = 'Ai primit unul sau mai multe mesaje personale.\\nVrei să le citeşti acum (într-o nouă fereastră)?';

$txt['previous_next_back'] = '&laquo; mesajul precedent';
$txt['previous_next_forward'] = 'următorul mesaj &raquo;';

$txt['movetopic_auto_board'] = '[FORUM]';
$txt['movetopic_auto_topic'] = '[ADRESA SUBIECT]';
$txt['movetopic_default'] = 'Acest subiect a fost mutat în ' . $txt['movetopic_auto_board'] . ".\n\n" . $txt['movetopic_auto_topic'];

$txt['upshrink_description'] = 'Ascunde sau afişează antetul.';

$txt['mark_unread'] = 'Marchează ca necitit';

$txt['ssi_not_direct'] = 'Nu se poate accesa SSI.php prin URL direct; poţi folosi calea (%s) sau utilizând ?ssi_function=ceva.';
$txt['ssi_session_broken'] = 'SSI.php a fost incapabil să încarce o sesiune! Acest lucru poate crea probleme cu deconectarea şi alte opţiuni - verifica dacă SSI.php este inclus pe prima poziţie în toate scripturile din codul tău!';

// Escape any single quotes în here twice.. 'it\'s' -> 'it\\\'s'.
$txt['preview_title'] = 'Verifică mesajul';
$txt['preview_fetch'] = 'Afişează mesajul ...';
$txt['preview_new'] = 'Mesaj nou';
$txt['error_while_submitting'] = 'Eroarea sau erorile următoare au apărut în timpul trimiterii acestui mesaj:';

$txt['split_selected_posts'] = 'Mesaje selectate';
$txt['split_selected_posts_desc'] = 'Următoarele mesaje vor forma un nou subiect după secţionare.';
$txt['split_reset_selection'] = 'Resetează selecţia';

$txt['modify_cancel'] = 'Anulează';
$txt['mark_read_short'] = 'Marchează citite';

$txt['pm_short'] = 'Mesaje personale';
$txt['hello_member_ndt'] = 'Salut';

$txt['ajax_in_progress'] = ' Se încarcă ...';

?>