<?php
// Version: 1.1; PersonalMessage

// Versiunea în limba română cu diacritice www.smf.ro
$txt[143] = 'Secţiunea cu mesaje personale';
$txt[148] = 'Trimite mesajul';
$txt[150] = 'Către';
$txt[1502] = 'Şi către';
$txt[316] = 'Mesaje primite';
$txt[320] = 'Mesaje trimise';
$txt[321] = 'Mesaj nou';
$txt[411] = 'Şterge mesajul';
// Don't translate "PMBOX" în this string.
$txt[412] = 'Şterge toate mesajele din PMBOX';
$txt[413] = 'Eşti sigur că vrei să ştergi toate mesajele?';
$txt[535] = 'Către';
// Don't translate the word "SUBJECT" here, as it is used to format the message - use numeric entities as well.
$txt[561] = 'Mesaj personal nou: SUBJECT';
// Don't translate SENDER or MESSAGE în this language string; they are replaced with the corresponding text - use numeric entities too.
$txt[562] = 'Ai primit un mesaj personal de la SENDER în ' . $context['forum_name'] . '.' . "\n\n" . 'IMPORTANT: ATENŢIE, aceasta este doar un anunţ, vă rugăm nu răspundeti la acest email.' . "\n\n" . 'Mesajul care ţi-a fost trimis este: ' . "\n\n" . 'MESSAGE';
$txt[748] = '(Mai mulţi destinatari: \'name1, name2\')';
// Use numeric entities în the below string.
$txt['instant_reply'] = 'Răspunde la acest mesaj personal aici:';

$txt['smf249'] = 'Eşti sigur ca vrei să ştergi toate mesajele personale selectate?';

$txt['sent_to'] = 'Trimis către';
$txt['reply_to_all'] = 'Răspunde tuturor';

$txt['pm_capacity'] = 'Capacitate';
$txt['pm_currently_using'] = '%s mesaje, %s%% plin.';

$txt['pm_error_user_not_found'] = 'Nu găsesc utilizatorul cu numele \'%s\'.';
$txt['pm_error_ignored_by_user'] = 'Utilizatorul \'%s\' a blocat mesajele personale de la tine.';
$txt['pm_error_data_limit_reached'] = 'Mesajul nu a putut fi trimis către \'%s\' deoarece acesta are casuţa plină!';
$txt['pm_successfully_sent'] = 'Mesajul a fost trimis cu succes către \'%s\'.';
$txt['pm_too_many_recipients'] = 'Nu poţi trimite un mesaj personal la mai mult de %d destinatari în acelaşi timp.';
$txt['pm_too_many_per_hour'] = 'Ai depăşit numărul maxim de %d mesaje personale pe oră.';
$txt['pm_send_report'] = 'Trimite raportul';
$txt['pm_save_outbox'] = 'Salvează o copie în Mesaje trimise';
$txt['pm_undisclosed_recipients'] = 'Destinatari neprecizaţi';

$txt['pm_read'] = 'Citit';
$txt['pm_replied'] = 'Răspuns către';

// Message Pruning.
$txt['pm_prune'] = 'Curaţa mesajele';
$txt['pm_prune_desc1'] = 'Şterge toate mesajele personale mai vechi de';
$txt['pm_prune_desc2'] = 'zile.';
$txt['pm_prune_warning'] = 'Eşti sigur că vrei să-ţi cureţi mesajele personale?';

// Actions Drop Down.
$txt['pm_actions_title'] = 'Acţiuni viitoare';
$txt['pm_actions_delete_selected'] = 'Şterge mesajele selectate';
$txt['pm_actions_filter_by_label'] = 'Aranjează după eticheta';
$txt['pm_actions_go'] = 'Trimite';

// Manage Labels Screen.
$txt['pm_apply'] = 'Aplică';
$txt['pm_manage_labels'] = 'Aranjează etichetele';
$txt['pm_labels_delete'] = 'Eşti sigur ca vrei să ştergi etichetele selectate?';
$txt['pm_labels_desc'] = 'Aici poţi adauga, edita şi şterge etichetele folosite în casuţa de mesaje personale.';
$txt['pm_label_add_new'] = 'Adaugă o eticheta';
$txt['pm_label_name'] = 'Numele etichetei';
$txt['pm_labels_no_exist'] = 'In acest moment nu ai setată nici o etichetă!';

// Labeling Drop Down.
$txt['pm_current_label'] = 'Eticheta';
$txt['pm_msg_label_title'] = 'Etichetează mesajul';
$txt['pm_msg_label_apply'] = 'Adaugă eticheta';
$txt['pm_msg_label_remove'] = 'Şterge eticheta';
$txt['pm_msg_label_inbox'] = 'Primite';
$txt['pm_sel_label_title'] = 'Selectează eticheta';
$txt['labels_too_many'] = 'Ne pare rău, %s acest mesaj are deja numărul maxim de etichete permis!';

// Sidebar Headings.
$txt['pm_labels'] = 'Etichete';
$txt['pm_messages'] = 'Mesage';
$txt['pm_preferences'] = 'Preferinte';

$txt['pm_is_replied_to'] = 'Ai trimis mai departe sau ai răspuns la acest mesaj.';

// Reporting messages.
$txt['pm_report_to_admin'] = 'Reclamă la administrator';
$txt['pm_report_title'] = 'Reclamă Mesajul Personal';
$txt['pm_report_desc'] = 'Din aceasta pagină poţi reclama un mesaj personal primit de tine la administratorii forumului. Te rugăm să scrii motivele pentru care reclami acest mesaj, deoarece acestea vor fi ataşate la transcrierea indentica a mesajului.';
$txt['pm_report_admins'] = 'Administratori la care le trimiti mesajul';
$txt['pm_report_all_admins'] = 'Trimite la toti administratorii forumului';
$txt['pm_report_reason'] = 'Motivul pentru care reclami acest mesaj';
$txt['pm_report_message'] = 'Reclamă mesajul';

// Important - The following strings should use numeric entities.
$txt['pm_report_pm_subject'] = '[RECLAMATIE] ';
// In the below string, do not translate "{REPORTER}" or "{SENDER}".
$txt['pm_report_pm_user_sent'] = '{REPORTER} a raportat următorul mesaj personal, trimis de către {SENDER}, pentru următoarele motive:';
$txt['pm_report_pm_other_recipients'] = 'Alţi destinatari ai mesajului ataşat:';
$txt['pm_report_pm_hidden'] = '%d destinatar(i) invizibil(i)';
$txt['pm_report_pm_unedited_below'] = 'In continuare este transcrierea originală a mesajului raportat:';
$txt['pm_report_pm_sent'] = 'Trimis:';

$txt['pm_report_done'] = 'Mulţumim pentru ca ai raportat aceste mesaje personal. Vei avea un răspuns de la echipa de adminstratori căt mai curănd.';
$txt['pm_report_return'] = 'Intoarce-te la Mesaje primite';

$txt['pm_search_title'] = 'Caută în mesajele personale';
$txt['pm_search_bar_title'] = 'Caută mesajele';
$txt['pm_search_text'] = 'Caută după';
$txt['pm_search_go'] = 'Caută';
$txt['pm_search_advanced'] = 'Cautăre avansată';
$txt['pm_search_user'] = 'de către userul';
$txt['pm_search_match_all'] = 'Caută toate cuvintele';
$txt['pm_search_match_any'] = 'Caută orice cuvânt';
$txt['pm_search_options'] = 'Opţiuni';
$txt['pm_search_post_age'] = 'Vârsta';
$txt['pm_search_show_complete'] = 'Afişează mesajul integral în rezultate.';
$txt['pm_search_subject_only'] = 'Caută doar după subiect şi autor.';
$txt['pm_search_between'] = 'Intre';
$txt['pm_search_between_and'] = 'şi';
$txt['pm_search_between_days'] = 'zile';
$txt['pm_search_order'] = 'Aranjează rezultatele după';
$txt['pm_search_choose_label'] = 'Alege eticheta după care să cauţi sau caută în toate';

$txt['pm_search_results'] = 'Rezultatele căutării';
$txt['pm_search_none_found'] = 'Nu am găsit mesaje';

$txt['pm_search_orderby_relevant_first'] = 'Cele mai relevante întăi';
$txt['pm_search_orderby_recent_first'] = 'Cele mai recente întăi';
$txt['pm_search_orderby_old_first'] = 'Cele mai vechi întăi';

$txt['pm_visual_verification_label'] = 'Verificare';
$txt['pm_visual_verification_desc'] = 'Pentru a trimite acest mesaj personal trebuie sa introduci codul vizual din imaginea de mai sus.';
$txt['pm_visual_verification_listen'] = 'Ascultă literele';

?>