<?php
// Version: 1.1; ManageMembers

// Versiunea în limba română cu diacritice www.smf.ro

$txt['membergroups_title'] = 'Organizează şi aranjează Grupurile de Utilizatori';
$txt['membergroups_description'] = 'Grupurile de utilizatori sunt utilizatori care au aceleaşi setări pentru permisiuni, mod de prezentare şi drepturi de acces. Unele grupuri de utilizatori sunt bazate pe numărul de mesaje scrise. Atribuirea unui utilizator la un anumit grup se face din panoul Profil personal al utilizatorului respectiv, modificănd setările contului.';
$txt['membergroups_modify'] = 'Modifică';

$txt['membergroups_add_group'] = 'Adaugă un grup';
$txt['membergroups_regular'] = 'Grupuri obişnuite';
$txt['membergroups_post'] = 'Grupuri bazate pe numărul de mesaje';

$txt['membergroups_new_group'] = 'Adaugă un nou grup de utilizatori';
$txt['membergroups_group_name'] = 'Numele grupului';
$txt['membergroups_new_board'] = 'Forumuri vizibile';
$txt['membergroups_new_board_desc'] = 'Forumuri vizibile pentru grupul de utilizatori';
$txt['membergroups_new_board_post_groups'] = '<em>Notă: în mod normal, grupurile bazate pe număr de mesaje nu au nevoie să le setezi permisiuni deoarece alte grupuri ale caror utilizatori sunt le dau utilizatorilor drepturi de acces sau le introduc restricţii după caz.</em>';
$txt['membergroups_new_as_type'] = 'după tip';
$txt['membergroups_new_as_copy'] = 'alege de bază';
$txt['membergroups_new_copy_none'] = '(nici una)';
$txt['membergroups_can_edit_later'] = 'Poţi modifica acestea mai tărziu.';

$txt['membergroups_edit_group'] = 'Modifică grupul de utilizatori';
$txt['membergroups_edit_name'] = 'Numele grupului';
$txt['membergroups_edit_post_group'] = 'Acest grup este bazat pe numărul de mesaje';
$txt['membergroups_min_posts'] = 'Numărul de mesaje necesare';
$txt['membergroups_online_color'] = 'Culoare în lista utilizatorilor online';
$txt['membergroups_star_count'] = 'Numărul de steluţe';
$txt['membergroups_star_image'] = 'Fişierul care conţine imaginea steluţei';
$txt['membergroups_star_image_note'] = 'poţi utiliza $language pentru a seta limba acestui utilizator';
$txt['membergroups_max_messages'] = 'Numărul maxim de mesaje personale';
$txt['membergroups_max_messages_note'] = '0 = nelimitat';
$txt['membergroups_edit_save'] = 'Salvează';
$txt['membergroups_delete'] = 'Şterge';
$txt['membergroups_confirm_delete'] = 'Eşti sigur că vrei să elimini acest grup?!';

$txt['membergroups_members_title'] = 'Afişează toţi utilizatorii acestui grup';
$txt['membergroups_members_no_members'] = 'Acest grup nu are nici un utilizator în acest moment';
$txt['membergroups_members_add_title'] = 'Adaugă un utilizator în acest grup';
$txt['membergroups_members_add_desc'] = 'Lista utilizatorilor de adăugat';
$txt['membergroups_members_add'] = 'Adaugă utilizator';
$txt['membergroups_members_remove'] = 'Elimină din grup';

$txt['membergroups_postgroups'] = 'Grupuri bazate pe numărul de mesaje scrise';

$txt['membergroups_edit_groups'] = 'Modifică grupurile de utilizatori';
$txt['membergroups_settings'] = 'Setări pentru grupul de utilizatori';
$txt['groups_manage_membergroups'] = 'Grupuri de utilizatori care pot modifica setările grupurilor de utilizatori';
$txt['membergroups_settings_submit'] = 'Salvează';
$txt['membergroups_select_permission_type'] = 'Selectează permisiunile în profil';
$txt['membergroups_images_url'] = '{theme URL}/images/';
$txt['membergroups_select_visible_boards'] = 'Afişează forumuri';

$txt['admin_browse_approve'] = 'Utilizatori care asteaptă aprobarea conturilor';
$txt['admin_browse_approve_desc'] = 'Aici poţi administra toţi utilizatorii care au conturi în asteptarea aprobării.';
$txt['admin_browse_activate'] = 'Utilizatori care nu şi-au activat conturile';
$txt['admin_browse_activate_desc'] = 'Aici sunt listaţi toţi utilizatorii care nu şi-au activat conturile.';
$txt['admin_browse_awaiting_approval'] = 'Asteaptă aprobarea  <span style="font-weight: normal">(%d)</span>';
$txt['admin_browse_awaiting_activate'] = 'Asteaptă activarea <span style="font-weight: normal">(%d)</span>';

$txt['admin_browse_username'] = 'Nume de utilizator';
$txt['admin_browse_email'] = 'Adresa de email';
$txt['admin_browse_ip'] = 'Adresa IP';
$txt['admin_browse_registered'] = 'Inregistrat';
$txt['admin_browse_id'] = 'ID';
$txt['admin_browse_with_selected'] = 'Cu cei selectaţi';
$txt['admin_browse_no_members_approval'] = 'Nici un membru nu aşteaptă aprobarea.';
$txt['admin_browse_no_members_activate'] = 'Nici un membru nu este neactivat.';

// Don't use entities în the below strings, except the main ones. (lt, gt, quot.)
$txt['admin_browse_warn'] = 'toţi utilizatorii selectaţi?';
$txt['admin_browse_outstanding_warn'] = 'toţi utilizatorii afectaţi?';
$txt['admin_browse_w_approve'] = 'Aprobă';
$txt['admin_browse_w_activate'] = 'Activează';
$txt['admin_browse_w_delete'] = 'Şterge';
$txt['admin_browse_w_reject'] = 'Refuză';
$txt['admin_browse_w_remind'] = 'Reaminteşte';
$txt['admin_browse_w_approve_deletion'] = 'Aprobă (Şterge contul)';
$txt['admin_browse_w_email'] = 'şi trimite email';
$txt['admin_browse_w_approve_require_activate'] = 'Aprobă şi asteaptă activarea';

$txt['admin_browse_filter_by'] = 'Filtrează după';
$txt['admin_browse_filter_show'] = 'Afişează';
$txt['admin_browse_filter_type_0'] = 'Conturi noi neactivate';
$txt['admin_browse_filter_type_2'] = 'Schimbări de email neactivate';
$txt['admin_browse_filter_type_3'] = 'Conturi noi neaprobate';
$txt['admin_browse_filter_type_4'] = 'Conturi şterse neaprobate';
$txt['admin_browse_filter_type_5'] = 'Conturi neaprobate pe motiv de "Vârstă prea mică"';

$txt['admin_browse_outstanding'] = 'Utilizatori neactivaţi';
$txt['admin_browse_outstanding_days_1'] = 'Pentru toţi utilizatorii neactivaţi care s-au înregistrat acum mai mult de';
$txt['admin_browse_outstanding_days_2'] = 'zile';
$txt['admin_browse_outstanding_perform'] = 'Efectuează următoarea acţiune';
$txt['admin_browse_outstanding_go'] = 'Efectuează!';

// Use numeric entities în the below nine strings.
$txt['admin_approve_reject'] = 'Inregistrare refuzată';
$txt['admin_approve_reject_desc'] = 'Ne pare rău, cererea ta pentru a te înscrie în ' . $context['forum_name'] . ' a fost refuzată.';
$txt['admin_approve_delete'] = 'Cont şters';
$txt['admin_approve_delete_desc'] = 'Contul tău în ' . $context['forum_name'] . ' a fost şters. Aceasta s-a întamplat deoarece nu ai activat niciodată contul; în acest caz poţi încerca să te înregistrezi din nou.';
$txt['admin_approve_remind'] = 'Reaminteşte înregistrarea';
$txt['admin_approve_remind_desc'] = 'Incă nu ai activat contul tău din ';
$txt['admin_approve_remind_desc2'] = 'Te rugăm să faci click pe link-ul de mai jos pentru a activa contul:';
$txt['admin_approve_accept_desc'] = 'Contul tău a fost activat de către administrator! Acum poţi să te autentifici şi să scrii mesaje.';
$txt['admin_approve_require_activation'] = 'Contul tău in ' . $context['forum_name'] . ' a fost aprobat de către administrator şi trebuie doar să-l activezi înainte de a începe să scrii mesaje.';

?>