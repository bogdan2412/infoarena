<?php
// Version: 1.1; ManagePermissions

// Versiunea în limba română cu diacritice www.smf.ro
$txt['permissions_title'] = 'Organizează Permisiunile';
$txt['permissions_modify'] = 'Modifică';
$txt['permissions_access'] = 'Acces';
$txt['permissions_allowed'] = 'Permis';
$txt['permissions_denied'] = 'Interzis';

$txt['permissions_switch'] = 'Schimbă in';
$txt['permissions_global'] = 'Global';
$txt['permissions_local'] = 'Local';

$txt['permissions_groups'] = 'Permisiuni pe grupuri de utilizatori';
$txt['permissions_all'] = 'toate';
$txt['permissions_none'] = 'nici una';
$txt['permissions_set_permissions'] = 'Setează permisiunile';

$txt['permissions_with_selection'] = 'Cu cele selectate';
$txt['permissions_apply_pre_defined'] = 'Aplică un profil de permisiuni predefinite';
$txt['permissions_select_pre_defined'] = 'Selectează un profil de permisiuni predefinite';
$txt['permissions_copy_from_board'] = 'Copie permisiunile de la acest forum';
$txt['permissions_select_board'] = 'Selectează un forum';
$txt['permissions_like_group'] = 'Setează permisiunile ca la acest grup';
$txt['permissions_select_membergroup'] = 'Selectează un grup';
$txt['permissions_add'] = 'Adaugă permisiuni';
$txt['permissions_remove'] = 'Şterge permisiune';
$txt['permissions_deny'] = 'Interzice permisiune';
$txt['permissions_select_permission'] = 'Selectează o permisiune';

// All of the following block of strings should not use entities, instead use \\" for &quot; etc.
$txt['permissions_only_one_option'] = 'Poti selecta doar o acţiune pentru a modifica permisiunile';
$txt['permissions_no_action'] = 'Nici o acţiune selectată';
$txt['permissions_deny_dangerous'] = 'Eşti pe cale să interzici una sau mai multe permisiuni.\\nAceasta poate fi periculos şi poate duce la rezultate neasteptate dacă există utilizatori \\"accidental\\" alocaţi la grupul sau grupurile pentru care le interzici permisiunile.\\nEşti sigur ca vrei să continui?';

$txt['permissions_boards'] = 'Permisiuni pe forum';

$txt['permissions_modify_group'] = 'Modifică Grup';
$txt['permissions_general'] = 'Permisiuni generale';
$txt['permissions_board'] = 'Permisiuni globale pe forum';
$txt['permissions_commit'] = 'Salvează schimbarea';
$txt['permissions_modify_local'] = 'Modifică permisiuni locale';
$txt['permissions_on'] = 'în forumul';
$txt['permissions_local_for'] = 'Permisiuni locale pentru grupul';
$txt['permissions_option_on'] = 'P';
$txt['permissions_option_off'] = 'X';
$txt['permissions_option_deny'] = 'I';
$txt['permissions_option_desc'] = 'Pentru fiecare permisiune poţi alege fie \'Permite\' (P), \'Nu permite\' (X), sau <span style="color: red;">\'Interzice\' (I)</span>.<br /><br />Fii atent: dacă interzici o permisiune, orice membru - fie moderator sau nu - care este în acel grup va fi interzis în acest fel.<br />Pentru acest motiv, ar trebui să folosesti Interzice cu grija, doar când este absolut <b>necesar</b>. Nu permite, pe de alta parte, interzice un lucru pâna când nu este specificat Permis în altă parte.';

$txt['permissiongroup_general'] = 'General';
$txt['permissionname_view_stats'] = 'Afişează statisticile forumului';
$txt['permissionhelp_view_stats'] = 'Statisticile forumului din acesta pagină, sunt un sumar al tuturor statisticilor, cum ar fi, număr de utilizatori, media zilnică de mesaje şi alte câteva clasamente de genul Cele mai multe 10. Activând aceasta permisiune, adaugă o legatură în josul paginii principale (\'[Mai multe statistici]\').';
$txt['permissionname_view_mlist'] = 'Afişează lista de utilizatori';
$txt['permissionhelp_view_mlist'] = 'Lista de utilizatori afişează toţi utilizatorii înregistraţi în forum. Această listă poate fi sortată şi aranjată şi deasemeni se poate căuta în ea. Lista de utilizatori este o legatură din josul paginii principale şi din statistici făcând click pe numărul de utilizatori.';
$txt['permissionname_who_view'] = 'Afişează Cine este Online';
$txt['permissionhelp_who_view'] = 'Cine este Online afişează toţi utilizatorii online în acest moment şi ceea ce fac ei la momentul afişării. Această permisiune va funcţiona doar dacă este activată din secţiunea \'Opţiuni şi Facilităţi\'. Poţi accesa \'Cine este Online\' făcând click pe legătura din secţiunea \'Utilizatori Online\' în pagina principală a forumului. Chiar dacă aceasta este Interzis, utilizatorii vor vedea cine este Online, însă nu vor vedea unde sunt şi ce fac acestia.';
$txt['permissionname_search_posts'] = 'Caută după subiecte şi mesaje';
$txt['permissionhelp_search_posts'] = 'Permisiunea Caută permite utilizatorilor să caute în toate forumurile în care au acces. Când permisunea de Cautăre este activată, un buton \'Caută\' va fi adaugat în bara de butoane a forumului.';
$txt['permissionname_karma_edit'] = 'Schimbă popularitatea altor utilizatori.';
$txt['permissionhelp_karma_edit'] = 'Karma este o funcţie care afişează popularitatea unui utilizator. Pentru a activa aceasta funcţie, trebuie să fie activată în \'Optiuni şi Facilităţi\'. Această permisiune permite unui grup de utilizatori să voteze o dată. Nu are efect pentru Vizitatori.';

$txt['permissiongroup_pm'] = 'Mesagerie personală';
$txt['permissionname_pm_read'] = 'Citeşte mesaje personale';
$txt['permissionhelp_pm_read'] = 'Acestă permisiune dă dreptul utilizatorilor să acceseze secţiunea Mesaje personale şi să îşi citească mesajele primite. Fără această permisiune, un utilizator nu este capabil să trimită mesaje.';
$txt['permissionname_pm_send'] = 'Trimite mesaje personale';
$txt['permissionhelp_pm_send'] = 'Trimite mesaje personale către alţi utilizatori înregistraţi. Necesită permisiunea \'Citeşte Mesaje personale\'.';

$txt['permissiongroup_calendar'] = 'Calendar';
$txt['permissionname_calendar_view'] = 'Afişează Calendarul';
$txt['permissionhelp_calendar_view'] = 'Calendarul afişează pentru fiecare lună aniversarile, evenimentele şi sărbătorile. Acesta permisiune conferă accesul la Calendar. Când este activată, un buton va fi adăugat la bara de butoane de sus şi o listă va fi afişată în josul paginii principale a forumului cu evenimentele, aniversarile şi sărbătorile imediat urmatoare. Calendarul trebuie să fie activat în \'Editează Facilităţi şi Opţiuni\'.';
$txt['permissionname_calendar_post'] = 'Creează evenimente în calendar';
$txt['permissionhelp_calendar_post'] = 'Un eveniment este un subiect legat la o anumită dată sau perioadă. Crearea de evenimente poate fi facută din Calendar. Un eveniment poate fi creat doar dacă acel utilizator are permisiunea de a scrie noi subiecte.';
$txt['permissionname_calendar_edit'] = 'Editează evenimente în calendar';
$txt['permissionhelp_calendar_edit'] = 'Un eveniment este un subiect legat la o anumită dată sau perioadă. Evenimentele pot fi editate făcând click pe asteriskul rosu (*) de langă eveniment când se afişează calendarul. Pentru a edita un eveniment, un utilizator trebuie să aibă permisiuni suficiente pentru a edita primul mesaj dintr-un subiect legat la acel eveniment.';
$txt['permissionname_calendar_edit_own'] = 'Evenimente proprii';
$txt['permissionname_calendar_edit_any'] = 'Orice eveniment';

$txt['permissiongroup_maintenance'] = 'Administrarea Forumului';
$txt['permissionname_admin_forum'] = 'Administrează forumul şi baza de date';
$txt['permissionhelp_admin_forum'] = 'Această permisiune da unui utilizator dreptul de a:<ul><li>schimba forumurile, baza de date şi setările pentru teme</li><li>organiza pachete de modificări</li><li>să folosească uneltele pentru întreţinere a forumului şi bazei de date</li><li>să vadă erorile şi logul de moderare</li></ul> Foloseşte această permisiune cu grijă deoarece este foarte puternică.';
$txt['permissionname_manage_boards'] = 'Organizează forumurile şi  categoriile';
$txt['permissionhelp_manage_boards'] = 'Această permisiune permite crearea, editarea şi eliminarea forumurilor şi categoriilor.';
$txt['permissionname_manage_attachments'] = 'Organizează fişiere ataşate şi avataruri';
$txt['permissionhelp_manage_attachments'] = 'Această permisiune conferă acces la centrul de administrare al fişierelor ataşate, unde toate fişierele ataşate şi avatarurile sunt listate şi pot fi şterse.';
$txt['permissionname_manage_smileys'] = 'Organizează zămbetele';
$txt['permissionhelp_manage_smileys'] = 'Aceasta permite accesul la centrul de administrare al zămbetelor. Aici se pot crea, adauga şi elimina seturi de zămbete.';
$txt['permissionname_edit_news'] = 'Editează ştiri';
$txt['permissionhelp_edit_news'] = 'Funcţia Ştiri permite ca o ştire aleatorie să fie afişată pe fiecare pagină. Pentru a putea utiliza această funcţie, ea trebuie să fie activată în setările forumului.';

$txt['permissiongroup_member_admin'] = 'Administrarea utilizatorilor';
$txt['permissionname_moderate_forum'] = 'Moderează utilizatorii forumului';
$txt['permissionhelp_moderate_forum'] = 'Această permisiune include toate funcţiile de moderarea a utilizatorilor:<ul><li>acces la organizarea înregistrărilor</li><li>acces la ecranul de vizualizat/şters utilizatori</li><li>profil extins, inclusiv urmărirea după IP, după utilizator şi starea ascuns/online</li><li>activarea conturilor</li><li>primeşte notificări de aprobare şi aprobă conturi</li><li>imunitate la ignorarea de mesaje personale</li><li>alte căteva mici lucruri</li></ul>';
$txt['permissionname_manage_membergroups'] = 'Organizează şi atribuie grupuri de utilizatori';
$txt['permissionhelp_manage_membergroups'] = 'Această permisiune dă dreptul unui utilizator să editeze grupuri de utilizatori şi să atribuie utilizatorii la grupuri.';
$txt['permissionname_manage_permissions'] = 'Organizează permisiuni';
$txt['permissionhelp_manage_permissions'] = 'Acestă permisiune dă dreptul unui utilizator să editeze toate permisiunile unui grup, global sau individual pe forumuri.';
$txt['permissionname_manage_bans'] = 'Organizează lista de ban';
$txt['permissionhelp_manage_bans'] = 'Acestă permisiune dă dreptul unui utilizator să adauge sau să elimine un ban în lista, un utilizator sau o adresa IP, hostname şi adresa email. De asemenea îi este permis să vizualizeze logul de utilizatori banaţi care încearcă să se autentifice.';
$txt['permissionname_send_mail'] = 'Trimite un email către toţi utilizatorii forumului';
$txt['permissionhelp_send_mail'] = 'Mass email către toţi utilizatorii forumului sau doar la un grup de utilizatori, prin email sau mesaj personal (ultima necesită permisunea  \'Trimite mesaje personale\' ).';

$txt['permissiongroup_profile'] = 'Profilul utilizatorilor';
$txt['permissionname_profile_view'] = 'Vizualizează sumarul şi statisticile din profil';
$txt['permissionhelp_profile_view'] = 'Acestă permisiune dă dreptul utilizatorilor care fac click pe un nume de utilizator să vadă un sumar al setărilor din profil, câteva statistici şi toate mesajele utilizatorului.';
$txt['permissionname_profile_view_own'] = 'Profil personal';
$txt['permissionname_profile_view_any'] = 'Orice profil';
$txt['permissionname_profile_identity'] = 'Editează setările contului';
$txt['permissionhelp_profile_identity'] = 'Setările contului sunt setări de bază din profil, cum ar fi parola, adresa email, grup de utilizatori şi limba preferată.';
$txt['permissionname_profile_identity_own'] = 'Profil personal';
$txt['permissionname_profile_identity_any'] = 'Orice profil';
$txt['permissionname_profile_extra'] = 'Editează setări suplimentare în profil';
$txt['permissionhelp_profile_extra'] = 'Setările suplimentare din profil includ avatar, preferinţe de temă, notificări şi mesaje personale.';
$txt['permissionname_profile_extra_own'] = 'Profil personal';
$txt['permissionname_profile_extra_any'] = 'Orice profil';
$txt['permissionname_profile_title'] = 'Editează titlul personalizat';
$txt['permissionhelp_profile_title'] = 'Titlul personalizat este afişat pe pagina de subiecte sub profilul fiecărui utilizator care are un titlu personalizat.';
$txt['permissionname_profile_title_own'] = 'Profil personal';
$txt['permissionname_profile_title_any'] = 'Orice profil';
$txt['permissionname_profile_remove'] = 'Şterge contul';
$txt['permissionhelp_profile_remove'] = 'Aceasta permite unui utilizator să şteargă contul personal, când este setat pe \'Cont propriu\'.';
$txt['permissionname_profile_remove_own'] = 'Cont propriu';
$txt['permissionname_profile_remove_any'] = 'Orice cont';
$txt['permissionname_profile_server_avatar'] = 'Alege un avatar de pe server';
$txt['permissionhelp_profile_server_avatar'] = 'Dacă este permis, aceasta va da posibilitatea utilizatorilor să aleagă un avatar din colecţia de pe server.';
$txt['permissionname_profile_upload_avatar'] = 'Incarcă un avatar pe server';
$txt['permissionhelp_profile_upload_avatar'] = 'Acestă permisiune va da posibilitatea unui utilizator să încarce (upload) un avatar personal pe server.';
$txt['permissionname_profile_remote_avatar'] = 'Alege un avatar remote';
$txt['permissionhelp_profile_remote_avatar'] = 'Deoarece avatarul influenţează durata de creare a unei pagini, este posibil să nu permiţi unor utilizatori să foloseasca avataruri de pe servere externe.';

$txt['permissiongroup_general_board'] = 'General';
$txt['permissionname_moderate_board'] = 'Moderează Forum';
$txt['permissionhelp_moderate_board'] = 'Permisiunea de a modera forumuri adaugă câteva mici permisiuni care fac dintr-un moderator un moderator adevărat. Permisiunea include răspunsul la subiecte blocate, schimbarea duratei de timp până la expirarea sondajelor şi vizualizarea rezultatelor sondajelor.';

$txt['permissiongroup_topic'] = 'Subiecte';
$txt['permissionname_post_new'] = 'Scrie noi subiecte';
$txt['permissionhelp_post_new'] = 'Acestă permisiune dă dreptul utilizatorilor să scrie subiecte noi. Nu permite să răspundă la subiecte.';
$txt['permissionname_merge_any'] = 'Lipeşte orice subiect';
$txt['permissionhelp_merge_any'] = 'Lipeşte două sau mai multe subiecte în unul singur. Ordinea mesajelor în subiectul rezultat va fi în ordinea datei şi orei la care au fost create. Un utilizator poate lipi mesaje doar din forumuri unde îi este permis să citească. Pentru a lipi mesajele, utilizatorul trebuie să-şi activeze moderare rapidă în setările din profilul personal.';
$txt['permissionname_split_any'] = 'Imparte orice subiect';
$txt['permissionhelp_split_any'] = 'Imparte un subiect în două subiecte separate.';
$txt['permissionname_send_topic'] = 'Trimite subiecte la un prieten';
$txt['permissionhelp_send_topic'] = 'Acestă permisiune dă dreptul unui utilizator să trimită un subiect pe email la un prieten, prin introducerea adresei de email şi permisiunea de a adăuga un mesaj.';
$txt['permissionname_make_sticky'] = 'Marchează subiecte drept important (sticky)';
$txt['permissionhelp_make_sticky'] = 'Subiectele importante (sticky) sunt subiecte care rămân întotdeauna în primele poziţii. Pot fi folositoare în cazul de anunţuri şi mesaje importante.';
$txt['permissionname_move'] = 'Mută subiecte';
$txt['permissionhelp_move'] = 'Mută un subiect dintr-un forum în altul. Utilizatorii pot selecta drept destinaţie doar forumurile unde au acces.';
$txt['permissionname_move_own'] = 'Mesaje proprii';
$txt['permissionname_move_any'] = 'Orice mesaj';
$txt['permissionname_lock'] = 'Blochează subiecte';
$txt['permissionhelp_lock'] = 'Acestă permisiune conferă unui utilizator dreptul de a bloca un subiect. Aceasta actiune poate fi facută pentru ca nimeni să nu mai poata răspunde la un subiect. Doar utilizatorii cu \'Moderează forum\' au posibilitatea de a scrie mesaje în subiecte blocate.';
$txt['permissionname_lock_own'] = 'Mesaje proprii';
$txt['permissionname_lock_any'] = 'Orice mesaj';
$txt['permissionname_remove'] = 'Elimină subiecte';
$txt['permissionhelp_remove'] = 'Şterge subiectele cu totul. Atenţie: acestă permisiune nu permite ştergerea de mesaje într-un subiect anume!';
$txt['permissionname_remove_own'] = 'Mesaje proprii';
$txt['permissionname_remove_any'] = 'Orice subiecte';
$txt['permissionname_post_reply'] = 'Scrie răspunsuri la subiecte';
$txt['permissionhelp_post_reply'] = 'Acesta permisiune permite rasunsul la subiecte.';
$txt['permissionname_post_reply_own'] = 'Mesaje proprii';
$txt['permissionname_post_reply_any'] = 'Orice mesaj';
$txt['permissionname_modify_replies'] = 'Modifică răspunsuri la subiectele proprii';
$txt['permissionhelp_modify_replies'] = 'Această permisiune dă dreptul unui utilizator care a pornit un subiect să editeze toate răspunsurile la acel subiect.';
$txt['permissionname_delete_replies'] = 'Şterge răspunsuri la subiectele proprii';
$txt['permissionhelp_delete_replies'] = 'Această permisune dă dreptul unui utilizator care a pornit un subiect să şteargă toate răspunsurile la subiectul său.';
$txt['permissionname_announce_topic'] = 'Anunţă subiectul';
$txt['permissionhelp_announce_topic'] = 'Aceasta permite unui utilizator să trimită un email de anunţ despre un subiect la toţi utilizatorii sau doar la un grup de utilizatori selectat.';

$txt['permissiongroup_post'] = 'Mesaje';
$txt['permissionname_delete'] = 'Şterge mesajele';
$txt['permissionhelp_delete'] = 'Elimină mesajele. Aceasta nu permite unui utilizator să ştearga primul mesaj dintr-un subiect.';
$txt['permissionname_delete_own'] = 'Mesaje proprii';
$txt['permissionname_delete_any'] = 'Orice mesaje';
$txt['permissionname_modify'] = 'Modifică mesaje';
$txt['permissionhelp_modify'] = 'Editează mesaje';
$txt['permissionname_modify_own'] = 'Mesaje proprii';
$txt['permissionname_modify_any'] = 'Orice mesaje';
$txt['permissionname_report_any'] = 'Raportează mesaje la moderator';
$txt['permissionhelp_report_any'] = 'Acestă permisiune adaugă o legatură la fiecare mesaj, permiţând unui utilizator să raporteze un mesaj la moderator(i). Astfel, toţi moderatorii acelui forum vor primi un mail cu o legatură către mesajul rapotat şi o descriere a problemei (aşa cum este dată de utilizatorul care raportează).';

$txt['permissiongroup_poll'] = 'Sondaje';
$txt['permissionname_poll_view'] = 'Vizualizează sondaje';
$txt['permissionhelp_poll_view'] = 'Aceasta permite unui utilizator să vadă sondajele dintr-un subiect. Fără acesta el va vedea doar subiectul.';
$txt['permissionname_poll_vote'] = 'Votează în sondaje';
$txt['permissionhelp_poll_vote'] = 'Această permisiune dă dreptul unui utilizator înregistrat să voteze o dată. Nu se aplica la vizitatori.';
$txt['permissionname_poll_post'] = 'Porneşte sondaje';
$txt['permissionhelp_poll_post'] = 'Acestă permisiune dă dreptul unui utilizator să iniţieze un sondaj nou.';
$txt['permissionname_poll_add'] = 'Adaugă sondaj la subiecte';
$txt['permissionhelp_poll_add'] = 'Aceasta permite adăugarea unui sondaj după ce subiectul a fost scris. Necesită drepturi suficiente de a edita primul mesaj dintr-un subiect.';
$txt['permissionname_poll_add_own'] = 'Subiecte proprii';
$txt['permissionname_poll_add_any'] = 'Orice subiecte';
$txt['permissionname_poll_edit'] = 'Editează sondaj';
$txt['permissionhelp_poll_edit'] = 'Această permisiune dă dreptul să se editeze opţiunile dintr-un sondaj sau să se reseteze acel sondaj. Pentru a modifica numărul maxim de voturi şi data expirării, utilizatorul necesită permisiunea \'Moderează forum\' .';
$txt['permissionname_poll_edit_own'] = 'Sondaje proprii';
$txt['permissionname_poll_edit_any'] = 'Orice sondaj';
$txt['permissionname_poll_lock'] = 'Blochează sondaj';
$txt['permissionhelp_poll_lock'] = 'Blocarea unui sondaj duce la închiderea acestuia, nu se mai acceptă noi voturi.';
$txt['permissionname_poll_lock_own'] = 'Sondaje proprii';
$txt['permissionname_poll_lock_any'] = 'Orice sondaj';
$txt['permissionname_poll_remove'] = 'Elimină sondaj';
$txt['permissionhelp_poll_remove'] = 'Acestă permisiune permite eliminarea de sondaje.';
$txt['permissionname_poll_remove_own'] = 'Sondaje proprii';
$txt['permissionname_poll_remove_any'] = 'Orice sondaj';

$txt['permissiongroup_notification'] = 'Notificări';
$txt['permissionname_mark_any_notify'] = 'Cere notificări la răspunsuri';
$txt['permissionhelp_mark_any_notify'] = 'Această facilitate permite utilizatorilor să primeasca anunţuri despre eventualele răspunsuri la un mesaj la care au subscris.';
$txt['permissionname_mark_notify'] = 'Cere notificări la subiecte noi';
$txt['permissionhelp_mark_notify'] = 'Notificări la subiecte noi este o facilitate care permite unui utilizator să primeasca email de fiecare dată când un nou subiect este creat la un forum unde sunt inscrisi.';

$txt['permissiongroup_attachment'] = 'Fisiere ataşate';
$txt['permissionname_view_attachments'] = 'Afişează fişiere ataşate';
$txt['permissionhelp_view_attachments'] = 'Fişierele ataşate sunt fişiere care sunt alipite la un mesaj scris. Această facilitate poate fi activată şi configurată din \'Editează Facilităţi şi Opţiuni\'. Deoarece fişierele ataşate nu sunt accesate direct, ele sunt protejate împotriva accesului direct şi download de către oricine care nu are această permisiune.';
$txt['permissionname_post_attachment'] = 'Atasează fişiere';
$txt['permissionhelp_post_attachment'] = 'Fişierele ataşate sunt fişiere care sunt alipite la un mesaj scris. Un mesaj poate avea mai multe fişiere ataşate.';

$txt['permissionicon'] = '';

$txt['permission_settings_title'] = 'Setarea permisiunilor';
$txt['groups_manage_permissions'] = 'Grupuri de utilizatori care au permisiunea de a vizualiza/modifica permisiunile';
$txt['permission_settings_submit'] = 'Salvează';
$txt['permission_settings_enable_deny'] = 'Activează opţiunea de a interzice permisiuni';
// Escape any single quotes în here twice.. 'it\'s' -> 'it\\\'s'.
$txt['permission_disable_deny_warning'] = 'Dezactivând aceasta va modifica permisiunile \\\'Interzis\\\'- în permisiuni \\\'Nepermis\\\'.';
$txt['permission_by_membergroup_desc'] = 'Aici poţi seta toate permisiunile globale pentru fiecare grup de utilizatori. Aceste permisiuni sunt valabile în forumuri care nu au fost schimbate către permisiuni locale în secţiunea \'Permisiuni pe forum\'.';
$txt['permission_by_board_desc'] = 'Aici poţi seta dacă un forum foloseşte permisiunile globale sau are un regim special. Folosind permisiuni locale pentru un forum, poţi seta permisiuni pentru fiecare grup de utilizatori.';
$txt['permission_settings_desc'] = 'Aici poţi seta cine are dreptul de a altera permisiunile celorlalţi, atât cât de sofisticat este sistemul de permisiuni.';
$txt['permission_settings_enable_postgroups'] = 'Activează permisiunile bazate pe numărul de mesaje scrise';
// Escape any single quotes în here twice.. 'it\'s' -> 'it\\\'s'.
$txt['permission_disable_postgroups_warning'] = 'Dezactivând aceasta vei elimina permisiunile acordate în acest moment la grupuri pe baza numărului de mesaje scrise.';
$txt['permission_settings_enable_by_board'] = 'Activează permisiunile avansate per-forum';
// Escape any single quotes în here twice.. 'it\'s' -> 'it\\\'s'.
$txt['permission_disable_by_board_warning'] = 'Dezactivând aceasta vei elimina toate permisiunile setate la nivel de forum.';

?>