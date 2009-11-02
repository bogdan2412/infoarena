<?php
// Version: 1.1; Search

$txt[183] = 'Setează parametrii pentru căutare';
$txt[189] = 'Alege un forum în care să cauţi sau selectează-le pe toate';
$txt[343] = 'Toate cuvintele să existe în rezultat';
$txt[344] = 'Oricare cuvânt să se regăsească în rezultat';
$txt[583] = 'de către utilizatorul';

$txt['search_post_age'] = 'Vechimea mesajului';
$txt['search_between'] = 'Între';
$txt['search_and'] = 'şi';
$txt['search_options'] = 'Opţiuni';
$txt['search_show_complete_messages'] = 'Afişează rezultatele ca mesaje';
$txt['search_subject_only'] = 'Caută doar în titlul subiectului';
$txt['search_relevance'] = 'Relevanţa';
$txt['search_date_posted'] = 'Data mesajului';
$txt['search_order'] = 'Ordinea de căutare';
$txt['search_orderby_relevant_first'] = 'Cele mai relevante întâi';
$txt['search_orderby_large_first'] = 'Cele mai mari subiecte întâi';
$txt['search_orderby_small_first'] = 'Cele mai mici subiecte întâi';
$txt['search_orderby_recent_first'] = 'Cele mai recente subiecte întâi';
$txt['search_orderby_old_first'] = 'Cele mai vechi subiecte întâi';

$txt['search_specific_topic'] = 'Caută doar mesajele într-un subiect';

$txt['mods_cat_search'] = 'Caută';
$txt['groups_search_posts'] = 'Grupuri de membri cu acces la funcţia de căutare';
$txt['simpleSearch'] = 'Activează căutarea simplă';
$txt['search_results_per_page'] = 'Numărul de rezultate ale căutarii pe pagină';
$txt['search_weight_frequency'] = 'Importanţa căutării raportată la numărul de coincidenţe dintr-un subiect';
$txt['search_weight_age'] = 'Importanţa căutării raportată la vechimea ultimului mesaj care se potriveşte';
$txt['search_weight_length'] = 'Importanţa căutarii raportată la lungimea subiectului';
$txt['search_weight_subject'] = 'Importanţa căutării raportată la gradul de coincidenţă a subiectului';
$txt['search_weight_first_message'] = 'Importanţa căutării raportată la gradul de potrivire al primului mesaj';
$txt['search_weight_sticky'] = 'Importanţa căutării raportată la un subiect important (sticky)';

$txt['search_settings_desc'] = 'Aici poţi schimba setările de bază pentru funcţia de căutare în forum.';
$txt['search_settings_title'] = 'Funcţia de căutare - setări';
$txt['search_settings_save'] = 'Salvează';

$txt['search_weights'] = 'Relevanţa';
$txt['search_weights_desc'] = 'Aici poţi seta nivelul diferitelor componente pentru relevanţa rezultatelor. ';
$txt['search_weights_title'] = 'Căutare - relevanţa';
$txt['search_weights_total'] = 'Total';
$txt['search_weights_save'] = 'Salvează';

$txt['search_method'] = 'Metoda de căutare';
$txt['search_method_desc'] = 'Aici poţi seta modul de căutare.';
$txt['search_method_title'] = 'Caută - metoda';
$txt['search_method_save'] = 'Salvează';
$txt['search_method_messages_table_space'] = 'Spaţiu ocupat de către mesajele din forum în baza de date';
$txt['search_method_messages_index_space'] = 'Spaţiu ocupat de indexul mesajelor în baza de date';
$txt['search_method_kilobytes'] = 'KB';
$txt['search_method_fulltext_index'] = 'index fulltext';
$txt['search_method_no_index_exists'] = 'în acest moment nu există';
$txt['search_method_fulltext_create'] = 'creează un index fulltext';
$txt['search_method_fulltext_cannot_create'] = 'nu poate fi creată deoarece lungimea maximă este peste 65,535 sau tabela nu este de tip MyISAM';
$txt['search_method_index_already_exsits'] = 'deja creat';
$txt['search_method_fulltext_remove'] = 'şterge indexul fulltext';
$txt['search_method_index_partial'] = 'creat parţial';
$txt['search_index_custom_resume'] = 'reia procesarea';
// This string is used in a javascript confirmation popup; don't use entities.
$txt['search_method_fulltext_warning'] = 'Înainte de a fi capabil să utilizezi căutarea la parametrii maximi trebuie să creezi un index integral pentru text!';

$txt['search_index'] = 'Index pentru căutare';
$txt['search_index_none'] = 'Fără index';
$txt['search_index_custom'] = 'Index personalizat';
$txt['search_index_label'] = 'Index';
$txt['search_index_size'] = 'Dimensiune';
$txt['search_index_create_custom'] = 'crează indexul personalizat';
$txt['search_index_custom_remove'] = 'şterge indexul personalizat';
// This string is used in a javascript confirmation popup; don't use entities.
$txt['search_index_custom_warning'] = 'Pentru a putea utiliza un index de căutare personalizat trebuie mai întâi să-l creezi!';

$txt['search_force_index'] = 'Obligă-i pe toti să utilizeze indexul de căutare';
$txt['search_match_words'] = 'Caută doar cuvinte întregi';
$txt['search_max_results'] = 'Numărul maxim de rezultate afişate';
$txt['search_max_results_disable'] = '(0: fără limită)';

$txt['search_create_index'] = 'Creează index';
$txt['search_create_index_why'] = 'De ce să creez un index?';
$txt['search_create_index_start'] = 'Creează';
$txt['search_predefined'] = 'Profil Predefinit';
$txt['search_predefined_small'] = 'Index de dimensiune mică';
$txt['search_predefined_moderate'] = 'Index de dimensiune medie';
$txt['search_predefined_large'] = 'Index de dimensiune mare';
$txt['search_create_index_continue'] = 'Continuă';
$txt['search_create_index_not_ready'] = 'SMF creează în acest moment un index de căutare prin mesaje. Pentru a evita supraîncărcarea serverului procesul a fost momentan întrerupt. Ar trebui să continue automat în câteva secunde. Dacă nu se reia apasă click mai jos.';
$txt['search_create_index_progress'] = 'Progres';
$txt['search_create_index_done'] = 'Indexul personalizat a fost creat';
$txt['search_create_index_done_link'] = 'Continuă';
$txt['search_double_index'] = 'Ai creat două indexuri diferite pentru mesajele din baza de date. Pentru performanţe maxime este indicat să ştergi unul din ele.';

$txt['search_error_indexed_chars'] = 'Număr de caractere invalide pentru a crea indexul. Minim 3 caractere sunt necesare pentru un index utilizabil.';
$txt['search_error_max_percentage'] = 'Procentaj de cuvinte care pot fi sărite prea mic. Foloseşte o valoare de minim 5%.';

$txt['search_adjust_query'] = 'Revizuieşte parametrii căutării';
$txt['search_adjust_submit'] = 'Revizuieşte căutarea';
$txt['search_did_you_mean'] = 'Poate ai vrut să cauţi după';

$txt['search_example'] = '<i>e.g.</i> Probleme la instalare, eroare mysql 9999';

?>