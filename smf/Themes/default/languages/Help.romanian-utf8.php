<?php
// Version: 1.1; Help

global $helptxt;

$helptxt = array();

$txt[1006] = 'Închide fereastra';

$helptxt['manage_boards'] = '
	<b>Editare Secţiuni</b><br />
	În acest meniu poţi crea/reordona/elimina secţiuni şi categoriile de deasupra lor. De exemplu, dacă aţi avea un site care ar oferi informaţii cu privire la &quot;Sport&quot; şi &quot;Maşini&quot; şi &quot;Muzică&quot;, acestea ar fi Categoriile principale pe care le-aţi crea. Sub fiecare dintre aceste categorii veţi dori probabil să creaţi în mod ierarhic &quot;sub-categorii&quot;,
	sau &quot;Secţiuni&quot; pentru subiecte în fiecare dintre ele. Este o simplă ierarhie cu această structură: <br />
	<ul>
		<li>
			<b>Sport</b>
			&nbsp;- O &quot;categorie&quot;
		</li>
		<ul>
			<li>
				<b>Baseball</b>
				&nbsp;- O secţiune în categoria &quot;Sport&quot;
			</li>
			<ul>
				<li>
					<b>Statistici</b>
					&nbsp;- O subsecţiune în secţiunea &quot;Baseball&quot;
				</li>
			</ul>
			<li><b>Fotbal</b>
			&nbsp;- O secţiune în categoria &quot;Sport&quot;</li>
		</ul>
	</ul>
	Categoriile vă permit să spargeţi secţiunea în subiecte vaste (&quot;Maşini,
	Sport&quot;), şi &quot;Secţiunile&quot; din ele sunt de fapt subiectele în care membrii pot posta. Un utilizator interesat în Pintos ar posta un mesaj în &quot;Maşini->Pinto&quot;. Categoriile le permit oamenilor să găsească rapid ceea ce îi interesează: În loc de &quot;Magazin&quot; aveţi magazinele &quot;Hardware&quot; şi &quot;Articole de îmbrăcăminte&quot; la care puteţi merge.  Aceasta simplifică căutarea dumneavoastră pentru &quot;pipe joint compound&quot; pentru că puteţi merge la Magazinul Hardware &quot;categorie&quot; în loc de a merge la Magazinul cu Articole de îmbrăcăminte (unde e puţin probabil să găsiţi
	pipe joint compound).<br />
	După cum s-a menţionat mai sus o secţiune este un subiect-cheie dintr-o categorie vastă. 
	Dacă vreţi să discutaţi despre &quot;Pintos&quot; va trebui să mergeţi la categoria &quot;Auto&quot; şi să
	săriţi în secţiunea &quot;Pinto&quot; pentru a posta gândurile dumneavoastră în acea secţiune.<br />
	Funcţiile administrative pentru acest element de meniu sunt de a crea noi secţiuni sub fiecare categorie, de a le reordona (pune &quot;Pinto&quot; după &quot;Chevy&quot;), sau de a şterge secţiunile în întregime.';

$helptxt['edit_news'] = '<b>Editează Ştiri Forum</b><br />
	Acest lucru vă permite să setaţi textul pentru ştirile afişate pe pagina de Index a Secţiunii (Board Index page).
	Adăugaţi orice element doriţi (e.g., &quot;Nu pierdeţi conferinţa de marţi&quot;). Fiecare element de ştiri ar trebui să
meargă într-o casetă separată şi este afişat aleator.';

$helptxt['view_members'] = '
	<ul>
		<li>
			<b>Vizualizaţi toţi Membrii</b><br />
			Vizualizaţi toţi membrii în secţiune. Vă este prezentată o listă cu hiperlegături de nume de membri.  Este posibil să faceţi click pe oricare nume pentru a afla detalii despre membri (homepage, vârstă, etc.) şi ca Administrator
puteţi modifica aceşti parametri. Aveţi control complet asupra membrilor, inclusiv capacitatea de a-i şterge de pe forum.<br /><br />
		</li>
		<li>
			<b>Aşteaptă Aprobarea</b><br />
			Această secţiune este afişată numai dacă aţi activat aprobarea de către admin a tuturor înregistrărilor noi. Oricine se înregistrează pentru a se alătura forumului dvs va deveni membru cu drepturi depline numai după ce va fi aprobat de către admin. Secţiunea afişează toţi acei membri care sunt încă în aşteptarea aprobării, împreună cu adresa de e-mail şi adresa IP. Puteţi alege fie să acceptaţi, fie să respingeţi (ştergeţi) orice membru de pe listă, bifând caseta de lângă membru şi alegând acţiunea din caseta derulantă (drop-down) din partea de jos a ecranului. Când respingeţi un membru puteţi să alegeţi să-l ştergeţi cu sau fără a-l notifica asupra deciziei dumneavoastră.<br /><br />
		</li>
		<li>
			<b>Aşteaptă Activarea</b><br />
			Această secţiune va fi vizibilă doar dacă aveţi activarea conturilor de membru activată pe forum. Această secţiune va lista toţi membrii care nu şi-au activat conturile încă. Din acest ecran puteţi alege să acceptaţi, să respingeţi sau să le reamintiţi membrilor de înregistrările restante. 	Ca mai sus, puteţi, de asemenea, alege să trimiteţi e-mail membrilor pentru a-i informa de acţiunile pe care le-aţi luat.<br /><br />
		</li>
	</ul>';

$helptxt['ban_members'] = '<b>Banează Membrii</b><br />
	SMF dispune de capacitatea de a &quot;bana&quot; utilizatorii, pentru a preveni persoanele care au încălcat încrederea acordată în secţiune prin spamming, trolling, etc. Acest lucru vă permite să banaţi utilizatorii care sunt în detrimentul dumneavoastră pe forum. Când vizualizaţi mesajele ca admin puteţi vedea adresa de IP a fiecărui utilizator folosită pentru postare la momentul respectiv. Tastaţi pur şi simplu acea adresă de IP în lista de interdicţie, salvaţi şi ei nu mai pot posta de la acea locaţie.<br />Puteţi, de asemenea, bana oamenii prin adresa lor de e-mail.';

$helptxt['modsettings'] = '<b>Editare Facilităţi şi Opţiuni</b><br />
	Există mai multe facilităţi în această secţiune care pot fi modificate în funcţie de preferinţele dumneavoastră.
Opţiunile pentru modificările instalate vor apărea, de asemenea, în general, aici.';

$helptxt['number_format'] = '<b>Formatare Număr</b><br />
		Aveţi posibilitatea să utilizaţi această setare pentru a formata modul în care numerele de pe forum vor fi afişate pentru utilizator. Formatul aceastei setări este:<br />
	<div style="margin-left: 2ex;">1,234.00</div><br />
	Unde \',\' este caracterul utilizat pentru a separa grupurile de mii, \'.\' este caracterul utilizat ca punct zecimal şi numărul de zerouri dictate de acurateţea rotunjirilor.';

$helptxt['time_format'] = '<b>Formatare Timp</b><br />
	Ai puterea de a ajusta modul în care ora şi data sunt afişate pentru tine. Sunt o mulţime de litere mici, dar e foarte simplu.
	Convenţiile urmăresc funcţia PHP strftime  şi sunt descrise ca mai jos (mai multe detalii pot fi găsite la <a href="http://www.php.net/manual/function.strftime.php" target="_blank">php.net</a>).<br />
	<br />
	Următoarele caractere sunt recunoscute în formatul şir (string): <br />
	<span class="smalltext">
	&nbsp;&nbsp;%a - nume zi abreviat<br />
	&nbsp;&nbsp;%A - nume zi complet<br />
	&nbsp;&nbsp;%b - nume lună abreviat<br />
	&nbsp;&nbsp;%B - nume lună complet<br />
	&nbsp;&nbsp;%d - zi a lunii (de la 01 la 31) <br />
	&nbsp;&nbsp;%D<b>*</b> - la fel ca %m/%d/%y <br />
	&nbsp;&nbsp;%e<b>*</b> - zi a lunii (de la 1 la 31) <br />
	&nbsp;&nbsp;%H - ora folosind un ceas cu 24 de ore (de la 00 la 23) <br />
	&nbsp;&nbsp;%I - ora folosind un ceas cu 12 ore (de la 01 la 12) <br />
	&nbsp;&nbsp;%m - luna ca număr (de la 01 la 12) <br />
	&nbsp;&nbsp;%M - minutul ca număr <br />
	&nbsp;&nbsp;%p - fie &quot;am&quot; sau &quot;pm&quot; în funcţie de timpul dat<br />
	&nbsp;&nbsp;%R<b>*</b> - timp în notaţie de 24 ore<br />
	&nbsp;&nbsp;%S - secunda ca număr zecimal<br />
	&nbsp;&nbsp;%T<b>*</b> - timp actual, egal cu %H:%M:%S <br />
	&nbsp;&nbsp;%y - an cu 2 caractere (de la 00 la 99) <br />
	&nbsp;&nbsp;%Y - an cu 4 caractere<br />
	&nbsp;&nbsp;%Z - fus orar sau nume sau abreviere <br />
	&nbsp;&nbsp;%% - un \'%\' caracter literă <br />
	<br />
	<i>* Nu funcţionează pe serverele bazate pe Windows.</i></span>';

$helptxt['live_news'] = '<b>Anunţuri live</b><br />
	Această casetă arată anunţurile recent actualizate de la <a href="http://www.simplemachines.org/" target="_blank">www.simplemachines.org</a>.
	Ar trebui să verificaţi aici din când în când pentru actualizări, noi versiuni, informaţii importante de la Simple Machines.';

$helptxt['registrations'] = '<b>Management Înregistrare</b><br />
	Această secţiune conţine toate funcţiile care ar putea fi necesare în administrarea noilor înregistrări de pe forum. Ea conţine până la patru secţiuni care sunt vizibile în funcţie de setările forumului dumneavoastră. Acestea sunt:<br /><br />
	<ul>
		<li>
			<b>Înregistrează Un Membru Nou</b><br />		
Din acest ecran puteţi alege să se înregistreze conturi de noi membri în numele lor. Acest lucru poate fi util în cazul în care înregistrarea în forum este închisă sau în cazurile în care administratorul doreşte să creeze un cont de test. 	
Dacă opţiunea de a solicita activarea contului este selectată, membrul va primi pe e-mail un link de activare pe care trebuie să facă click înainte de a putea utiliza contul de membru.
			Puteţi selecta în mod similar să le trimiteti pe e-mail utilizatorilor noua parolă la adresa de e-mail declarată.<br /><br />
		</li>
		<li>
			<b>Editează Acordul de Înregistrare</b><br />
			Acest lucru vă permite să setaţi textul acordului de înregistrare afişat atunci când membrii se înscriu în forumul dumneavoastră.
			Puteţi adăuga sau elimina orice din acordul de înregistrare implicit care este inclus în SMF.<br /><br />
		</li>
		<li>
			<b>Setează Numele Rezervate</b><br />
			Folosind această interfaţă puteţi specifica numele sau cuvintele care nu pot fi utilizate de către utilizatorii dvs.<br /><br />
		</li>
		<li>
			<b>Setări</b><br />
			Această secţiune va fi vizibilă doar dacă aveţi permisiunea de a administra forumul. Din acest ecran puteţi decide cu privire la metoda de înregistrare care este folosită în forumul dumneavoastră, precum şi alte setări legate de înregistrare.
		</li>
	</ul>';

$helptxt['modlog'] = '<b>Moderation Log</b><br />
		Această secţiune permite membrilor din echipa de admini să urmărească toate acţiunile de moderare pe care le-au efectuat moderatorii forumului. Pentru a te asigura că moderatorii nu pot elimina referirile la acţiunile pe care le-au efectuat, intrările nu pot fi şterse până la 24 de ore după ce acţiunea a fost luată.
	Coloana \'objects\' afişează orice variabile asociate cu acţiunea.';
$helptxt['error_log'] = '<b>Error Log</b><br />
	Jurnalul de erori urmăreşte fiecare eroare serioasă întâlnită de utilizatori în utilizarea forumului dumneavoastră. El afişează toate aceste erori după dată, dată care poate fi ordonată prin click pe săgeata neagră de lângă fiecare dată. În plus, puteţi filtra erorile făcând click pe imaginea de lângă fiecare eroare statistică. Aceasta vă permite să filtraţi, de exemplu, după membru. Când un filtru este activ singurele rezultate care vor fi afişate sunt acelea care corespund acelui filtru.';
$helptxt['theme_settings'] = '<b>Setări Temă</b><br />
	Ecranul de setări vă permite să schimbaţi setările specifice unei teme. Aceste setări includ opţiuni cum ar fi directorul de teme şi informaţii despre URL, dar şi opţiuni care pot afecta aspectul (layout-ul) unei teme de pe forum. Cele mai multe teme vor avea o varietate de opţiuni configurabile de către utilizator, permiţându-vă să adaptaţi o temă pentru a se potrivi nevoilor individuale ale forumului dumneavoastră.';
$helptxt['smileys'] = '<b>Centrul de Zâmbete</b><br />
		Aici puteţi adăuga şi elimina Zâmbete şi Seturi de Zâmbete.  Notează importanţa faptului că dacă un zâmbet este într-un set, el trebuie sa fie în toate seturile - altfel s-ar putea crea confuzie printre utilizatorii care folosesc diferite seturi.<br /><br />

		În plus, puteţi să editaţi iconiţele asociate mesajelor de aici, dacă le-aţi activat de pe pagina de setări. ';
$helptxt['calendar'] = '<b>Administrează Calendarul</b><br />
	Aici puteţi modifica setările calendarului curent, precum şi adăuga sau şterge sărbătorile care apar în calendar.';

$helptxt['serversettings'] = '<b>Setări Server</b><br />
	Aici puteţi efectua configurarea de bază pentru forumul dumneavoastră. Aceasta secţiune include setări ale bazei de date si url-ului, precum şi alte elemente de configurare importante, cum ar fi setările de mail şi de stocare în cache. Gândeşte-te cu atenţie ori de câte ori editezi aceste setări deoarece o eroare poate face forumul inaccesibil.';

$helptxt['topicSummaryPosts'] = 'Aceasta îţi permite să setezi numărul mesajelor precedente vizualizate în rezumatul topicului în ecranul de răspuns.';
$helptxt['enableAllMessages'] = 'Setează aceasta la numărul <em>maxim</em> de mesaje postate pe care le poate avea un subiect pentru a afişa link-ul toate.  Setarea acesteia mai jos de &quot;Maximul de mesaje de afişat în pagina unui subiect&quot; va însemna pur şi simplu ca nu va fi afişat niciodată, iar setarea ei prea sus ar putea încetini forumul dvs.';
$helptxt['enableStickyTopics'] = 'Subiectele importante (Stickies) sunt subiecte care ramân în fruntea listei de subiecte. Ele sunt utilizate în cea mai mare măsură pentru mesaje. Deşi puteţi schimba acest lucru cu permisiunile, în mod implicit numai moderatorii şi administratorii pot face subiectele importante (sticky).';
$helptxt['allow_guestAccess'] = 'Debifarea acestei casete va opri vizitatorii să facă orice altceva decât acţiunile de bază - logare,  înregistrare, reamintire parolă etc. - pe forumul dvs.  Aceasta nu este acelaşi lucru cu a refuza accesul vizitatorilor la forumuri.';
$helptxt['userLanguage'] = 'Activarea acestei opţiuni va permite utilizatorilor să selecteze ce fişier de limbă vor folosi. Nu va afecta selecţia implicită.';
$helptxt['trackStats'] = 'Statistici:<br />Aceasta va permite utilizatorilor să vadă cele mai noi mesaje postate şi cele mai populare subiecte de pe forum.
		De asemenea, va arăta mai multe statistici, cum ar fi cei mai mulţi membri online, noii membri şi subiectele noi.<hr />
		Vizualizări pagină:<br />Adaugă o altă coloană la pagina de statistici cu numărul de vizualizări de pagini pe forumul dvs.';
$helptxt['titlesEnable'] = 'Activarea Titlurilor Personalizate le va permite membrilor cu permisiuni relevante să creeze un titlu special pentru ei înşişi.
		Acesta va fi afişat sub nume.<br /><i>De exemplu:</i><br />Jeff<br />Grozav Tip';
$helptxt['topbottomEnable'] = 'Aceasta va adăuga butoanele Mergi Sus şi Mergi Jos, în aşa fel încât membrul poate merge în partea de sus sau de jos a forumului fără a derula.';
$helptxt['onlineEnable'] = 'Aceasta va afişa o imagine pentru a indica dacă membrul este online sau offline';
$helptxt['todayMod'] = 'Aceasta va arăta &quot;Astăzi&quot;, sau &quot;Ieri&quot;, în loc de dată.';
$helptxt['enablePreviousNext'] = 'Aceasta va arăta un link către topicul următor şi precedent.';
$helptxt['pollMode'] = 'Aceasta selectează dacă sondajele sunt sau nu activate. Dacă sondajele sunt dezactivate, orice sondaje existente vor fi ascunse din listarea subiectului. Puteţi opta să arătaţi în continuare subiectele obişnuite fără sondaje prin selectarea &quot;Afişează Sondajele Existente ca Subiecte&quot;.<br /><br />Pentru a alege cine poate posta sondaje, vizualiza sondaje şi altele similare, puteţi permite şi interzice aceste permisiuni. Amintiţi-vă acest lucru dacă sondajele nu funcţionează.';
$helptxt['enableVBStyleLogin'] = 'Aceasta va afişa un login mai compact pentru vizitatori pe fiecare pagină a forumului.';
$helptxt['enableCompressedOutput'] = 'Această opţiune va comprima ieşirea pentru a diminua consumul de lăţime de bandă (bandwidth), dar necesită ca zlib  să fie instalat.';
$helptxt['databaseSession_enable'] = 'Această opţiune face uz de baza de date pentru sesiunea de stocare - este cel mai bine pentru servere cu încărcare echilibrată, dar ajută cu toate problemele de expirare de timp şi poate face forumul mai rapid.';
$helptxt['databaseSession_loose'] = 'Această setare va diminua lăţimea de bandă consumată de forum, dar la folosirea butonului înapoi din browser pagina nu se va reîncărca - minusul acestei setări este că icoanele (noi) şi alte lucruri nu se vor reîmprospăta automat. (doar dacă se face click pe link-ul paginii respective în loc de folosirea butonului înapoi.)';
$helptxt['databaseSession_lifetime'] = 'Acesta este numărul de secunde în care mai pot dura sesiunile după ce nu au mai fost accesate.  Dacă o sesiune nu este accesată prea mult timp, se spune că a &quot;expirat&quot;.  Orice număr mai mare decât 2400 este recomandat.';
$helptxt['enableErrorLogging'] = 'Aceasta va loga orice erori, cum ar fi o logare nereuşită, ca să puteţi vedea ce a mers rău.';
$helptxt['allow_disableAnnounce'] = 'Aceasta le va permite utilizatorilor să opteze să nu primească notificări despre subiectele pe care tu le anunţi bifând căsuţa &quot;anunţă subiect&quot; la postare.';
$helptxt['disallow_sendBody'] = 'Această opţiune înlătură opţiunea de a primi textul răspunsurilor şi mesajelor în email-urile de notificare.<br /><br />Deseori membrii răspund notificării prin email, ceea ce în cele mai multe cazuri înseamnă că webmaster-ul primeşte răspunsul.';
$helptxt['compactTopicPagesEnable'] = 'Aceasta va afişa doar o selecţie a numărului de pagini.<br /><i>Exemplu:</i>
		&quot;3&quot; pentru a afişa: 1 ... 4 [5] 6 ... 9 <br />
		&quot;5&quot; pentru a afişa: 1 ... 3 4 [5] 6 7 ... 9';
$helptxt['timeLoadPageEnable'] = 'Aceasta va arăta timpul în secunde necesar SMF pentru a crea această pagină în partea de jos a forumului.';
$helptxt['removeNestedQuotes'] = 'Aceasta va arăta citatul mesajului în cauză, nu orice mesaje citate din acel mesaj.';
$helptxt['simpleSearch'] = 'Aceasta va arăta un formular simplu de căutare şi un link către un formular mai avansat.';
$helptxt['max_image_width'] = 'Aceasta îţi permite să setezi mărimea măximă admisă pentru imaginile postate. Imaginile mai mici decât maximul setat nu vor fi afectate.';
$helptxt['mail_type'] = 'Aceasta setare îţi permite să alegi fie setările implicite ale PHP, fie să suprascrii acele setări cu SMTP.  PHP nu sprijină folosirea autentificării cu SMTP (cerute de multe gazde acum), deci dacă vrei aceasta ar trebui să selectezi SMTP.  Vă rugăm să reţineţi că SMTP poate fi mai lent şi unele servere nu vor lua în calcul numele de utilizator şi parolele.<br /><br />Nu trebuie să completaţi setările SMTP, dacă aceasta este setată pentru PHP implicit.';
$helptxt['attachmentEnable'] = 'Ataşamentele sunt fişiere pe care membrii le pot încărca şi ataşa la un post.<br /><br />
		<b>Verificaţi extensia fişierului ataşat </b>:<br />Doriţi să verificaţi extensia fişierelor?<br />
		<b>Extensii fişier ataşat permise</b>:<br /> Poţi seta extensiile permise ale fişierelor ataşate.<br />
		<b>Directorul de ataşamente</b>:<br /> Calea către folderul dvs. de ataşament<br />(exemplu: /home/sites/siteuldvs/www/forum/attachments)<br />
		<b>Spaţiul maxim al folderului de ataşamente</b> (în KB):<br /> Selectaţi cât de mare poate fi folderul de ataşamente incluzând toate fişierele din el.<br />
		<b>Mărimea maximă a ataşamentului per post</b> (în KB):<br /> Selectaţi dimensiunea maximă a fişierelor din toate ataşamentele făcute per post.  Daca aceasta este mai mică decât limita per ataşament, atunci aceasta va fi limita.<br />
		<b>Mărimea maximă per ataşament</b> (în KB):<br /> Selectaţi dimensiunea maximă a fişierelor din fiecare ataşament separat.<br />
		<b>Numărul maxim de ataşamente per post</b>:<br /> Selectaţi numărul maxim de ataşamente pe care le poate face o persoană per post.<br />
		<b>Afişează ataşamentul ca imagine în mesajele postate</b>:<br /> Dacă fişierul încărcat este o imagine, va fi arătat sub mesajul postat.<br />
		<b>Redimensionează imaginile când sunt afişate sub mesajele postate</b>:<br /> Dacă este selectată opţiunea de mai sus, aceasta va salva un ataşament (mai mic) separat pentru imaginea în miniatură (thumbnail) pentru a reduce lăţimea de bandă.<br />
		<b>Lăţimea şi înălţimea maximă a imaginilor în miniatură </b>:<br /> Utilizat numai cu opţiunea &quot;Redimensionează imaginile atunci când sunt afişate sub mesajele postate&quot; , lăţimea şi înălţimea maximă de la care se porneşte pentru a redimensiona în jos ataşamentele .  Ele vor fi redimensionate proporţional.';
$helptxt['karmaMode'] = 'Karma este o caracteristică care arată popularitatea unui membru. Membrii, dacă li se permite, pot
		\'aplauda\' sau \'plezni\' alţi membri, acesta fiind modul în care popularitatea lor este calculată. Aveţi posibilitatea să modificaţi numărul de postări necesare pentru a avea &quot;karma&quot;, timpul dintre lovituri sau aplauze şi dacă administratorii trebuie să aştepte tot acest timp şi ei.<br /><br />Posibilitatea ca grupurile de membri să îi poată plezni sau nu pe alţii este controlată printr-o permisiune. Dacă aveţi probleme în a face această facilitate să funcţioneze pentru toată lumea verificaţi-vă din nou permisiunile.';
// !!! This should be resused or removed.
$helptxt['cal_enabled'] = 'Calendarul poate fi folosit pentru a afişa zilele de naştere sau pentru a afişa momentele importante ce se întâmplă în comunitatea ta.<br /><br /> <b>Afişează zilele ca legătura către \'Post Event\'</b>:<br />Aceasta le va permite membrilor să posteze evenimente pentru acea zi când vor face click pe acea dată<br /> <b>Afişează numărul săptămânilor</b>:<br />Arată ce săptămână este aceasta.<br /> <b>Numărul maxim de zile în avans în index-ul forumului</b>:<br />Dacă acesta este setat la 7 vor fi afişate evenimentele din săptămâna viitoare.<br /> <b>Afişează sărbătorile în index-ul forumului.<br /> <b>Arată sărbătoririle zilei de astăzi într-o bară a calendarului în index-ul forumului</b><b>Afişează zilele de naştere în index-ul forumului</b>:<br />Arată zilele de naştere (sărbătoriţii) de astăzi într-o bară a calendarului în index-ul forumului.<br /> <b>Afişează evenimentele în index-ul forumului</b>:<br />Arată evenimentele zilei de astăzi într-o bară a calendarului în index-ul forumului.<br /> <b>Secţiunea implicită pentru a posta în</b>:<br />Care este secţiunea implicită pentru a posta evenimente?<br /> <b>Permite evenimentele fără legătură către mesajele postate</b>:<br />Permite-le membrilor să posteze evenimente fără a le solicita să facă legătura cu un mesaj postat într-o sectiune.<br /> <b>Anul de început</b>:<br />Selectează &quot;primul&quot; an din lista calendarului.<br /> <b>Anul de sfârşit</b>:<br />Selectează &quot;ultimult&quot; an din lista calendarului<br /> <b>Culoarea zilei de naştere</b>:<br />Selectează culoarea textului zilei de naştere<br /> <b>Culoarea evenimentului</b>:<br />Selectează culoarea textului evenimentului<br /> <b>Culoarea Sărbătorii</b>:<br />Selectează culoarea textului Sărbătorii<br /> <b>Permite evenimentelor să se întindă pe mai multe zile</b>:<br />Bifaţi pentru a permite evenimentelor să se întindă pe mai multe zile.<br /> <b>Numărul maxim de zile în care un eveniment se poate întinde</b>:<br />Selectează numărul maxim de zile în care un evenimnet se poate întinde.<br /><br /> Reţine că utilizarea calendarului (postarea de evenimente, vizualizarea evenimentelor, etc.) este controlată prin permisiunile setate în ecranul de permisiuni.';
$helptxt['localCookies'] = 'SMF foloseşte module cookie pentru a stoca informaţiile de login pe computerul clientului. Cookie-urile pot fi stocate la nivel global (myserver.com) sau local (myserver.com/path/to/forum).<br />Bifaţi această opţiune dacă vă confruntaţi cu probleme de utilizatori deconectaţi automat.<hr />Cookie-urile stocate la nivel global sunt mai puţin sigure atunci când sunt utilizate pe un server de web partajat (cum ar fi Tripod).<hr />Cookie-urile locale nu funcţionează în afara directorului forumului, dacă forumul tău forum se afla la www.myserver.com/forum, pagini ca www.myserver.com/index.php nu pot accesa informaţiile de cont. Cookie-urile globale sunt recomandate în special când se foloseşte SSI.php.
';
$helptxt['enableBBC'] = 'Selectarea acestei opţiuni le va permite utilizatorilor tăi să utilizeze Bulletin Board Code (BBC) in forum, permiţându-le utilizatorilor să-şi formateze mesajele cu imagini, tipuri de formatare şi multe altele.';
$helptxt['time_offset'] = 'Nu toţi administratorii de forum vor ca forumul lor să folosească acelaşi timp ca al zonei serverului pe care sunt găzduiţi. Foloseşte această opţiune pentru a specifica o diferenţă de timp (în ore) de la care forumul ar trebui sa opereze faţă de timpul serverului. Valorile negative şi zecimale sunt permise.';
$helptxt['spamWaitTime'] = 'Aici puteţi selecta timpul care trebuie să treacă între postări. Acest lucru poate fi folosit pentru a opri oamenii de la "spam" pe forumul dvs prin limitarea a cât de des se poate posta.';

$helptxt['enablePostHTML'] = 'Acest lucru va permite postarea unor tag-uri HTML de bază :
<ul style="margin-bottom: 0;"> <li>&lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;em&gt;, &lt;ins&gt;, &lt;del&gt;</li> <li>&lt;a href=&quot;&quot;&gt;</li> <li>&lt;img src=&quot;&quot; alt=&quot;&quot; /&gt;</li> <li>&lt;br /&gt;, &lt;hr /&gt;</li> <li>&lt;pre&gt;, &lt;blockquote&gt;</li> </ul>';

$helptxt['themes'] = 'Aici puteţi selecta dacă tema implicită poate fi aleasă, ce temă vor utiliza vizitatorii, precum şi alte opţiuni. Faceţi click în dreapta pe o temă pentru a modifica setările pentru ea.';
$helptxt['theme_install'] = 'Acest lucru vă permite să instalaţi teme noi.  Puteţi face acest lucru dintr-un director deja creat prin încărcarea unei arhive a temei sau prin copierea temei implicite.<br /><br />Reţineţi că arhiva sau directorul trebuie să aibă un fişier de definiţie <tt>theme_info.xml</tt>.';
$helptxt['enableEmbeddedFlash'] = 'Această opţiune le va permite utilizatorilor dvs să utilizeze Flash direct în mesajle postate, la fel ca la imagini.  Acest lucru ar putea ridica un risc de securitate, cu toate că puţini l-au exploatat cu succes.	UTILIZAŢI PE PROPRIUL RISC!';
// !!! Add more information about how to use them here.
$helptxt['xmlnews_enable'] = 'Permite oamenilor să facă legatura către <a href="' . $scripturl . '?action=.xml;sa=news">Ştiri Recente</a> şi date similare.  Este, de asemenea, recomandat să limitaţi dimensiunea mesajelor recent postate/ştirilor deoarece, atunci când datele rss sunt afişate la unii clienţi, cum ar fi Trillian, este de aşteptat să fie trunchiate.';
$helptxt['hotTopicPosts'] = 'Schimbă numărul de postări pentru ca un subiect să ajungă la starea de subiect  &quot;hot&quot; sau &quot;very hot&quot; .';
$helptxt['globalCookies'] = 'Face disponibilă intrarea în modulele cookies pe subdomenii.  De exemplu, dacă...<br /> Site-ul dvs este la http://www.simplemachines.org/,<br /> Şi forumul dvs este la http://forum.simplemachines.org/,<br /> Utilizarea acestei opţiuni vă va permite să accesaţi cockie-ul forumului dvs pe site-ul dvs. Nu permite acest lucru, dacă există şi alte subdomenii (cum ar fi hacker.simplemachines.org) care nu sunt controlate de către dumneavoastră';
$helptxt['securityDisable'] = 'Aceasta <i>dezactivează</i> verificarea suplimentară a parolei pentru secţiunea de administrare. Acest lucru nu este recomandat!';
$helptxt['securityDisable_why'] = 'Aceasta este parola ta actuală. (aceeaşi cu cea pe care o folosiţi pentru logare.)<br /><br />Tastarea acesteia ajută în asigurarea faptului că tu vrei să faci orice sarcină de administraţie ai face şi că eşti <b>tu</b> cel care o face.';
$helptxt['emailmembers'] = 'În acest mesaj puteţi utiliza câteva &quot;variabile&quot;.  Acestea sunt:<br />
	{$board_url} - URL-ul forumul dvs.<br />
	{$current_time} - Timpul actual.<br />
	{$member.email} - Adresa de e-mail actuală a membrului.<br />
	{$member.link} - Link-ul actual al membrului.<br />
	{$member.id} - ID-ul actual al membrului.<br />
	{$member.name} - Numele actual al membrului.  (pentru personalizare.)<br />
	{$latest_member.link} - Link-ul celui mai recent membru înregistrat.<br />
	{$latest_member.id} -  ID-ul elui mai recent membru înregistrat.<br />
	{$latest_member.name} - Numele celui mai recent membru înregistrat.';
$helptxt['attachmentEncryptFilenames'] = 'Incriptarea numelor fişierelor ataşate vă permite să aveţi mai mult de un ataşament cu acelaşi nume, să utilizaţi în condiţii de siguranţă fişiere .php pentru ataşamente şi sporeşte securitatea. Acest lucru, totuşi, ar putea face reconstruirea bazei dvs de date mult mai dificilă dacă s-ar întâmpla ceva drastic.';

$helptxt['failed_login_threshold'] = 'Setaţi numărul de încercări de conectare eşuate înainte de direcţionarea utilizatorului către ecranul de reamintire a parolei.';
$helptxt['oldTopicDays'] = 'Dacă această opţiune este activată o avertizare va fi afişată pentru utilizator, atunci când acesta încearcă să răspundă la un subiect care nu a avut niciun răspuns nou pentru timpul, în zile, prevăzut de această setare. Setaţi această setare la 0 pentru a dezactiva facilitatea.';
$helptxt['edit_wait_time'] = 'Numărul de secunde permis pentru ca un mesaj postat să fie editat înainte de a loga data ultimei editări.';
$helptxt['edit_disable_time'] = 'Numărul de minute permis să treacă înainte ca un utilizator să nu mai poată edita un mesaj pe care l-a făcut. Setează la 0 pentru a dezactiva. <br /><br /><i> Reţine: Acest lucru nu va afecta orice utilizator care are permisiunea de a edita mesajele psotate de către alte popoare.</i>';
$helptxt['enableSpellChecking'] = 'Permite verificarea ortografiei. TREBUIE să aveţi biblioteca pspell instalată pe serverul dvs şi configuraţia PHP  setată pentru a utiliza biblioteca pspell. Serverul dvs ' . (function_exists('pspell_new') ? 'ARE' : 'NU ARE') . ' acest lucru setat.';
$helptxt['lastActive'] = 'Setaţi numărul de minute pentru a arata că oamenii sunt activi în numărul X de minute pe indexul forumului. Implicit este de 15 minute.';

$helptxt['autoOptDatabase'] = 'Această opţiune optimizează baza de date la intervalul de zile setat. Setaţi-o la 1 pentru a face o optimizare zilnică. Puteţi, de asemenea, să specificaţi un număr maxim de utilizatori online, astfel încât nu veţi supraîncărca serverul dvs. sau crea neplăceri prea multor utilizatori.';
$helptxt['autoFixDatabase'] = 'Aceasta va stabiliza automat tabelele defecte şi va relua activitatea ca şi cum nimic nu s-a întâmplat. Acest lucru poate fi util pentru că singura cale de a o stabiliza este de a REPARA tabelul şi în acest fel forumul dvs nu va cădea înainte ca dvs să vă daţi seama. Vă trimite un email atunci când acest lucru se întâmplă.';

$helptxt['enableParticipation'] = 'Aceasta arată o mică pictogramă în subiectele în care a postat utilizatorul.';

$helptxt['db_persist'] = 'Păstrează conexiunea activă pentru creşterea performanţei. Dacă nu sunteţi pe un server dedicat, aceasta vă poate cauza probleme cu gazda.';

$helptxt['queryless_urls'] = 'Acest lucru schimbă un pic formatul adreselor URL astfel încât motoarele de cautare le vor plăcea mai mult.  Ele vor arăta ca index.php/topic,1.html.<br /><br /> 
Această facilitate va ' . (strpos(php_sapi_name(), 'apache') !== false ? '' : 'not') . ' funcţiona pe serverul dvs.';
$helptxt['countChildPosts'] = 'Bifarea acestei opţiuni va însemna că mesajele postate şi subiectele dintr-o subsecţiune a secţiunii vor fi luate în calcul în totalul de pe pagina de index.<br /><br />Aceasta va face lucrurile considerabil mai lente, dar înseamnă că o secţiune părinte fără mesaje postate în ea nu va afişa "0".';
$helptxt['fixLongWords'] = 'Această opţiune împarte cuvintele mai mari de o anumită lungime în bucăţi astfel încât ele nu afectează aspectul forumului. (la fel de mult...)  Această opţiune nu ar trebui să fie setată la o valoare sub 40.';

$helptxt['who_enabled'] = 'Această opţiune vă permite să activaţi sau să dezactivaţi abilitatea utilizatorilor de a vedea cine sunt cei care navighează pe forum şi ceea ce fac ei.';

$helptxt['recycle_enable'] = '&quot;Reciclează&quot; subiectele şi mesajele şterse în secţiunea specificată.';

$helptxt['enableReportPM'] = 'Această opţiune permite utilizatorilor dvs. să raporteze mesajele personale pe care le primesc de la echipa de administrare. Acest lucru poate fi util în a ajuta să urmărim orice abuz al sistemului de mesaje personale.

 

		';
$helptxt['max_pm_recipients'] = 'Această opţiune vă permite să setaţi valoarea maximă permisă de destinatari într-un mesaj personal trimis de către un membru al forumului. Acest lucru poate fi utilizat pentru a vă ajuta să opriţi abuzul de spam în sistemul de PM. Reţineţi că utilizatorii cu permisiunea de a trimite buletine de ştiri sunt exceptaţi de la această restricţie. Setaţi la zero pentru nicio limită.';
$helptxt['pm_posts_verification'] = 'Această setare va forţa utilizatorii să introducă un cod de verificare afişat într-o imagine de fiecare dată când aceştia trimit un mesaj personal. Numai utilizatorii cu un număr de mesaje postate sub numărul setat vor fi nevoiţi să introducă codul - acest lucru ar trebui să ajute la combaterea scripturilor automate de spam.';
$helptxt['pm_posts_per_hour'] = 'Aceasta va limita numărul de mesaje personale care pot fi trimise de către un utilizator într-o oră. Aceasta nu îi afectează pe administratori şi moderatori.';

$helptxt['default_personalText'] = 'Setează textul implicit pe care un utilizator îl poate avea ca &quot;text personal.&quot;';

$helptxt['modlog_enabled'] = 'Loghează toate acţiunile de moderare.';

$helptxt['guest_hideContacts'] = 'Dacă este selectată această opţiune veţi ascunde adresele de e-mail şi detalii de contact messenger ale tuturor membrilor faţă de orice vizitatori ai forumului dvs.';

$helptxt['registration_method'] = 'Această opţiune stabileşte metoda de înregistrare care este utilizată pentru persoanele care doresc să se alăture forumului dvs.. Puteţi să selectaţi dintre:<br /><br />
	<ul>
		<li>
			<b>Înregistrare Dezactivată:</b><br />
				Dezactivează procesul de înregistrare, ceea ce înseamnă că nu se pot înregistra noi membri pentru a alătura forumului dvs. .<br />
		</li><li>
			<b>Înregistrare Imediată</b><br />
			        Membrii noi pot intra şi posta imediat după înregistrare pe forumul dvs. .<br />
		</li><li>
			<b>Activare Membri</b><br />
				Când această opţiune este activată orice membrii care se înregistrează pe forumul dvs vor primi un link de activare pe e-mail pe care trebuie să facă click înainte de a putea deveni membri deplini.<br />
		</li><li>
			<b>Aprobare Membri</b><br />
			Această opţiune va face ca toţi membrii care se înregistrează pe forumul dvs. să necesite aprobarea administratorului înainte de a deveni membri.
		</li>
	</ul>';
$helptxt['send_validation_onChange'] = 'Când această opţiune este bifată cei care îşi vor schimba adresa de email în profilul lor vor trebui să-şi reactiveze contul dintr-un e-mail trimis la acea adresă.';
$helptxt['send_welcomeEmail'] = 'Când această opţiune este activată tuturor membrilor noi le va fi trimis un e-mail urându-le bun venit în comunitatea ta';
$helptxt['password_strength'] = 'Această setare determină puterea necesară pentru parolele selectate de catre utilizatorii forumului dvs. . Cu cât va fi mai puternică parola, cu atât vor fi mai greu de compromis conturile membrilor.
	Opţiunile posibile sunt
	<ul>
		<li><b>Scăzut:</b>Parola trebuie să fie de cel puţin patru caractere.</li>
		<li><b>Mediu:</b> Parola trebuie să fie cel puţin opt caractere şi nu poate fi parte a unui nume de utilizatori sau adresa de e-mail.</li>
		<li><b>Ridicat:</b> Ca şi pentru mediu, cu excepţia faptului că parola trebuie de asemenea să conţină un amestec de litere mari şi mici şi cel puţin un număr.</li>
	</ul>';

$helptxt['coppaAge'] = 'Valoarea specificată în această casetă va stabili vârsta minimă pe care membrii noi trebuie să o aibă pentru a li se acorda acces imediat la forum.
	La înregistrare li se va solicita să confirme dacă sunt peste această vârstă şi, dacă nu, cererea lor va fi fie respinsă, fie suspendată în aşteapteptarea aprobării părintelui - depinde de tipul de restricţie ales.
	Dacă o valoare de 0 este aleasă pentru această setare atunci toate celelalte setări de restricţie de vârstă trebuie să fie ignorate.';
$helptxt['coppaType'] = 'Dacă sunt activate restricţiile de vârstă, atunci această setare va defini ce se întâmplă atunci când un utilizator sub vârsta minimă încearcă să se înregistreze pe forumul dvs. Există două opţiuni: <ul>  <li>  <b>Respinge Înregistrarea Lui:</b><br /> Înregistrarea oricărui membru nou sub vârsta minimă va fi respinsă imediat.<br />  </li><li>Obţine Aprobarea Părintelui / Tutorelui</b><br /> Orice nou membru care încearcă să se înregistreze şi este sub vârsta minimă permisă va avea contul marcat ca fiind în aşteaptarea aprobării şi i se va prezenta un formular prin intermediul căruia părinţii săi trebuie să îi acorde permisiunea de a deveni membru al forumului. Lor li se vor oferi, de asemenea, detaliile de contact ale forumului introduse în pagina de setări, astfel încât să poată trimite formularul către administrator prin mail sau fax.</li>  </ul>  ';
$helptxt['coppaPost'] = 'Casetele de contact sunt necesare pentru ca formularele de acordare a permisiunii de înregistrare pentru minori să poată să fie trimise către administratorul forumului. 	
Aceste detalii vor fi afişate tuturor minorilor şi sunt solicitate la aprobarea părintelui/tutorelui. Cel puţin o adresă poştală sau numărul de fax trebuie să fie furnizate.';

$helptxt['allow_hideOnline'] = 'Cu această opţiune activată toţi utilizatorii vor putea să-şi ascundă statusul online faţă de ceilalţi utilizatori (cu excepţia administratorilor). Dacă este dezactivată atunci numai utilizatorii care pot modera forumul îşi pot ascunde prezenţa. De notat că dezactivarea acestei opţiuni nu va modifica statusul curent al utilizatorilor - îi va opri doar să se mai ascundă în viitor.';
$helptxt['allow_hideEmail'] = 'Cu această opţiune activată membrii pot alege să-şi ascundă adresa de e-mail faţă de alţi membri. Cu toate acestea administratorii pot vedea întotdeauna adresele de e-mail ale tuturor.';

$helptxt['latest_support'] = 'Acest panou vă arată câteva din cele mai comune probleme şi întrebări despre configurarea serverului dvs. Nu vă faceţi griji, această informaţie nu este logată sau aşa ceva.<br /><br />	
Dacă aceasta rămâne ca &quot;Preluare de informaţii de asistenţă...&quot;, 	
computerul dvs nu se poate conecta probabil la <a href="http://www.simplemachines.org/" target="_blank">www.simplemachines.org</a>.';
$helptxt['latest_packages'] = 'Aici puteţi vedea unele din cele mai populare pachete, precum şi câteva pachete aleatoare sau modificări, cu instalări rapide şi uşoare.<br /><br />Dacă această secţiune nu va apărea, probabil computerul dvs nu se poate conecta la <a href="http://www.simplemachines.org/" target="_blank">www.simplemachines.org</a>.';
$helptxt['latest_themes'] = 'Această zonă arată câteva din cele mai recente şi cele mai populare teme de la <a href="http://www.simplemachines.org/" target="_blank">www.simplemachines.org</a>. Aceasta s-ar putea să nu apară în mod corespunzător în cazul în care computerul nu poate găsi <a href="http://www.simplemachines.org/" target="_blank">www.simplemachines.org</a>, totuşi.';

$helptxt['secret_why_blank'] = 'Pentru securitatea ta, răspunsul la întrebarea ta (precum şi parola ta) este criptat în aşa fel incât SMF poate doar să-ţi spună dacă este corect, deci nu îţi poate spune niciodată ţie (ori, important, nimănui altcuiva) care este răspunsul sau parola. ';
$helptxt['moderator_why_missing'] = 'Deoarece moderarea se face la nivel de secţiune cu secţiune, trebuie să faceţi membrii moderatori din <a href="javascript:window.open(\'' . $scripturl . '?action=manageboards\'); self.close();">interfaţa de administrare secţiune</a>.';

$helptxt['permissions'] = 'Permisiunile sunt după cum fie permiţi, fie interzici grupurilor să facă anumite lucruri.<br /><br />Puteţi modifica secţiuni multiple de-odată cu casetele de selectare sau puteţi vizualiza permisiunile pentru un anumit grup dând click pe "Modifică" ';
$helptxt['permissions_board'] = 'Dacă o secţiune este setată la nivel "Global", aceasta înseamnă că secţiunea nu va avea nicio permisiune specială. "Local"  înseamnă că ea va avea propriile permisiuni - separate de cele de la nivel global. Acest lucru vă permite să aveţi o secţiune care are mai multe sau mai puţine permisiuni decât alta, fără să fie nevoie să le setaţi pentru fiecare secţiune în parte.';
$helptxt['permissions_quickgroups'] = 'Aceste lucru vă permite să utilizaţi setările de permisiuni &quot;implicite&quot; - standard înseamnă \'nimic special\', restrictiv înseamnă \'ca un vizitator\', moderator înseamnă \'ca un moderator\', şi în cele din urmă \'mentenanţă\' înseamnă permisiuni foarte aproape de cele ale unui administrator.';
$helptxt['permissions_deny'] = 'Negarea permisiunilor pot fi utilă atunci când doriţi să luaţi permisiunile anumitor membri. Puteţi adăuga un membergroup cu permisiunea de \'deny\' pentru membrii cărora doriţi să le negaţi o permisiune. <br /><br />Folosiţi cu atenţie, o permisiune refuzată va rămâne refuzată indiferent de grupurile de membri în care se află acel membru. ';
$helptxt['permissions_postgroups'] = 'Activarea permisiunilor pentru grupurile bazate pe numărul de mesaje postate vă va permite să atribuiţi permisiuni membrilor care au postat un anumit număr de mesaje. Permisiunile pentru grupurile bazate pe numărul de mesaje postate sunt <em>adăugate</em> la permisiunile grupurilor obişnuite de membri.';
$helptxt['permissions_by_board'] = 'Activarea acestei opţiuni va permite să setaţi permisiuni diferite pentru fiecare sec pentru fiecare secţiune pentru fiecare grup de membri. În mod implicit o secţiune utilizează permisiunile la nivel global, dar cu această opţiune activată puteţi comuta o secţiune la setările de permisiuni locale. Acest lucru oferă un mod foarte sofisticat de a vă gestiona permisiunile.';
$helptxt['membergroup_guests'] = 'În grupul de membri Vizitatori sunt toţi utilizatorii care nu sunt autentificaţi.';
$helptxt['membergroup_regular_members'] = 'Membrii Obişnuiţi sunt toţi membri care sunt autentificaţi, dar care nu au niciun membergroup primar asignat.';
$helptxt['membergroup_administrator'] = 'Administratorul poate, prin definiţie, să facă orice şi să vadă orice secţiune. Nu există setări de permisiune pentru administrator.';
$helptxt['membergroup_moderator'] = 'Grupul de membri Moderator este un grup de membri special. Permisiunile şi setările asignate acestui grup se aplică moderatorilor dar numai <em>în secţiunile pe care le moderează</em>. În afara acestor secţiuni ei sunt la fel ca orice alt membru.';
$helptxt['membergroups'] = 'În SMF există două tipuri de grupuri din care membrii pot face parte. Acestea sunt:
	<ul>
		<li><b>Grupuri Obişnuite:</b>Un grup obişnuit este un grup în care membrii nu sunt puşi automat. Pentru a aloca un membru într-un grup pur şi simplu mergeţi la profilul său şi faceţi click pe &quot;Setări Cont&quot;. De aici puteţi desemna orice număr de grupuri obişnuite din care vor lua parte..</li>
		<li><b>Grupuri de Postări :</b> Spre deosebire de grupurile obişnuite, grupurile bazate pe numărul de postări nu pot fi desemnate. În schimb, membrii sunt asignaţi automat unui grup bazat pe numărul de postari atunci când ajung  la numărul minim de mesaje postate necesar pentru a fi în acel grup.</li>
	</ul>';

$helptxt['calendar_how_edit'] = 'Aveţi posibilitatea să editaţi aceste evenimente făcând click pe asteriscul roşu (*) de lângă numele lor.';

$helptxt['maintenance_general'] = 'De aici, veţi putea să vă optimizaţi toate tabelele (le face mai mici şi mai rapide!), să vă asiguraţi că aveţi cele mai noi versiuni, să găsiţi orice erori care ar putea să facă dezordine în forumul dvs, să recalculaţi totalurile şi să goliţi jurnalele.<br /><br /> Ultimele două ar trebui să fie evitate, cu excepţia cazului în care ceva este greşit, dar nu strică nimic.';
$helptxt['maintenance_backup'] = 'Această zonă vă permite să salvaţi o copie a tuturor mesajelor postate, setărilor, membrilor, precum şi alte informaţii din forumul dvs într-un fişier foarte mare.<br /><br />Este recomandat să faceţi asta de multe ori, poate săptămânal, pentru siguranţă şi securitate.';
$helptxt['maintenance_rot'] = 'Acest lucru vă permite să eliminaţi <b>complet</b> şi <b>irevocabil</b> subiectele vechi. Este recomandat să încercaţi să faceţi o copie de rezervă mai întâi, pentru eventualitatea în care ai elimina ceva ce nu ai vrut să elimini.<br /><br />Folosiţi această opţiune cu grijă.';

$helptxt['avatar_allow_server_stored'] = 'Acest lucru le permite utilizatorilor dvs. să selecteze dintre avatarele stocate chiar pe serverul dvs. Ele sunt, în general, în acelaşi loc ca SMF, în folderul avatarelor.<br />Ca un pont, în cazul în care creaţi directoarele din acel folder, puteţi face &quot;categorii&quot; de avatare.';
$helptxt['avatar_allow_external_url'] = 'Cu această opţiune activată, membrii dvs. pot tasta un URL către propriul lor avatar. Dezavantajul este că, în unele cazuri, ei pot folosi avatare care sunt excesiv de mari sau imagini pe care nu le doreşti pe forumul tău.';
$helptxt['avatar_download_external'] = 'Cu această opţiune activată, URL-ul dat de utilizator este accesat pentru a descărca avatarul de la acea locaţie. În caz de succes, avatarul va fi tratat ca avatar ce poate fi încărcat.';
$helptxt['avatar_allow_upload'] = 'Această opţiune este asemănătoare cu &quot;Permite membrilor să selecteze un avatar extern&quot;, numai că aveţi mai bun control asupra avatarelor, un timp mai bun de redimensionare şi membrii dvs nu trebuie să aibă un alt loc unde să-şi pună avatarele.<br /><br />Cu toate acestea dezavantajul este ca poate ocupa o mulţime de spaţiu pe serverul dvs.';
$helptxt['avatar_download_png'] = 'Fişierele PNG sunt mai mari, dar oferă o calitate mai bună de compresie. Dacă acest lucru este nebifat, JPEG va fi folosit în loc - care este de multe ori mai mic, dar de asemenea, de o calitate mai mică sau neclară.';

$helptxt['disableHostnameLookup'] = 'Aceasta dezactivează hostname lookups care, la unele servere sunt foarte lente. Reţine că acest lucru va face banarea mai puţin eficientă.';

$helptxt['search_weight_frequency'] = 'Factorii de greutate sunt utilizaţi pentru a determina relevanţa unui rezultat al căutării. Schimbă acesti factori de greutate pentru a se potrivi cu lucrurile care sunt în mod special importante pentru forumul dvs. De exemplu, un forum al unui site de ştiri ar putea dori o valoare relativ ridicată pentru  \'vechimea ultimelor mesaje compatibile\'. Toate valorile sunt relative între ele şi ar trebui să fie numere întregi pozitive.<br /><br />Acest factor ia în calcul numărul mesajelor compatibile şi îl împarte la numărul total de mesaje din cadrul unui subiect.';
$helptxt['search_weight_age'] = 'Factorii de greutate sunt utilizaţi pentru a determina relevanţa unui rezultat al căutării. Schimbă aceşti factori de greutate pentru a se potrivi cu lucrurile care sunt în mod special importante pentru forumul dvs. De exemplu, un forum al unui site de ştiri ar putea dori o valoare relativ ridicată pentru  \'vechimea ultimelor mesaje compatibile\'. Toate valorile sunt relative între ele şi ar trebui să fie numere întregi pozitive..<br /><br />Acest factor indică vechimea ultimului mesaj compatibil dintr-un subiect. Cu cât acest mesaj este mai recent, cu atât scorul este mai ridicat.';
$helptxt['search_weight_length'] = 'Factorii de greutate sunt utilizaţi pentru a determina relevanţa unui rezultat al căutării. Schimbă aceşti factori de greutate pentru a se potrivi cu lucrurile care sunt în mod special importante pentru forumul dvs. De exemplu, un forum al unui site de ştiri ar putea dori o valoare relativ ridicată pentru  \'vechimea ultimelor mesaje compatibile\'. Toate valorile sunt relative între ele şi ar trebui să fie numere întregi pozitive..<br /><br />Acest factor se bazează pe mărimea subiectului. Cu cât sunt mai multe mesaje într-un subiect, cu atât este mai mare scorul.';
$helptxt['search_weight_subject'] = 'Factorii de greutate sunt utilizaţi pentru a determina relevanţa unui rezultat al căutării. Schimbă aceşti factori de greutate pentru a se potrivi cu lucrurile care sunt în mod special importante pentru forumul dvs. De exemplu, un forum al unui site de ştiri ar putea dori o valoare relativ ridicată pentru  \'vechimea ultimelor mesaje compatibile\'. Toate valorile sunt relative între ele şi ar trebui să fie numere întregi pozitive..<br /><br />Acest factor arată dacă un termen de căutare pot fi găsit în cadrul subiectului unui topic.';
$helptxt['search_weight_first_message'] = 'Factorii de greutate sunt utilizaţi pentru a determina relevanţa unui rezultat al căutării Schimbă aceşti factori de greutate pentru a se potrivi cu lucrurile care sunt în mod special importante pentru forumul dvs. De exemplu, un forum al unui site de ştiri ar putea dori o valoare relativ ridicată pentru  \'vechimea ultimelor mesaje compatibile\'. Toate valorile sunt relative între ele şi ar trebui să fie numere întregi pozitive.<br /><br />Acest factor arată dacă poate fi găsită o similaritate în primul mesaj al unui subiect.';
$helptxt['search_weight_sticky'] = 'Factorii de greutate sunt utilizaţi pentru a determina relevanţa unui rezultat al căutării. Schimbă aceşti factori de greutate pentru a se potrivi cu lucrurile care sunt în mod special importante pentru forumul dvs. De exemplu, un forum al unui site de ştiri ar putea dori o valoare relativ ridicată pentru  \'vechimea ultimelor mesaje compatibile\'. Toate valorile sunt relative între ele şi ar trebui să fie numere întregi pozitive..<br /><br /> Acest factor arată dacă un subiect este important (sticky) şi creşte scorul de relevanţă în cazul în care este.';
$helptxt['search'] = 'Reglaţi toate setările pentru funcţia de căutare aici.';
$helptxt['search_why_use_index'] = 'Un index de căutare poate îmbunătăţi foarte mult performanţa căutărilor de pe forumul dvs. Mai ales atunci când numărul de mesaje pe un forum creşte mai mare căutarea fără un index poate dura o perioadă mai lungă de timp şi poate creşte presiunea asupra bazei dvs de date. Dacă forumul dvs este mai mare de 50.000 de mesaje, aţi putea lua în considerare crearea unui index de căutare pentru a asigura o performanţă de vârf a forumului dvs.<br /><br />Reţineţi că un index de căutare poate ocupa destul spaţiu. Un index fulltext este cel construit în indexul de MySQL. Este relativ compact (aproximativ aceeaşi mărime ca tabela de mesaje), dar o mulţime de cuvinte nu sunt indexate şi este posibil ca unele interogări de căutare să se dovedească a fi foarte lente. Indexul personalizat este deseori mai mare (în funcţie de configuraţia dvs. poate fi de până la 3 ori mai mare decât tabela de mesaje) dar performanţa sa este mai bună decât cel fulltext şi este relativ stabilă.';

$helptxt['see_admin_ip'] = 'Adresele de IP sunt prezentate administratorilor şi moderatorilor pentru a facilita moderarea şi  	
pentru a face mai uşor urmărirea oamenilor ce nu au intenţii bune..  Amintiţi-vă că adresele IP nu pot fi întotdeauna de identificare şi adresele IP ale majorităţii oamenilor se schimbă periodic..<br /><br />Membrilor le este de asemenea permis să-şi vizualizeze propriile IP-uri.';
$helptxt['see_member_ip'] = 'Adresa dvs. IP este afişată numai la tine şi moderatorilor. Amintiţi-vă că această informaţie nu este de identificare şi de cele mai multe IP-urile se schimbă periodic.<br /><br />Nu puteţi vedea adresele IP ale altor membri şi ei nu o pot vedea pe a ta.';

$helptxt['ban_cannot_post'] = 'Restricţia \'cannot post\' transformă forumul în modul read-only (doar citire) pentru utilizatorii cu interdicţii (banaţi). Utilizatorul nu poate crea subiecte noi sau răspunde la cele existente, nu poate trimite mesaje personale sau vota în sondaje. Utilizatorul banat poate citi totuşi mesajele personale sau subiectele.<br /><br />Un mesaj de avertizare este prezentat utilizatorilor care sunt banaţi în acest fel.';

$helptxt['posts_and_topics'] = '
	<ul>
		<li>
			<b>Setări Postare</b><br />
			Modifică setările legate de postarea de mesaje şi de modul în care sunt afişate mesajele.  Puteţi, de asemenea, permite verificarea ortografiei aici.
		</li><li>
			<b>Bulletin Board Code</b><br />
			Activaţi codul care afişează mesajele pe forum în layout-ul corect. De asemenea, reglează care coduri sunt permise şi care nu.
		</li><li>
			<b>Cuvinte Cenzurate</b>
				Puteţi să cenzuraţi anumite cuvinte în scopul de a păstra sub control limba dvs. pe forum. Această funcţie vă permite să transformaţi cuvinte interzise în versiuni inocente.
		</li><li>
			<b>Setări Subiect</b>
			Modifică setările legate de subiecte. Numărul de subiecte pe pagină, dacă subiectele importante (sticky) sunt activate sau nu, numărul de mesaje necesare pentru a fi un subiect fierbinte, etc.
		</li>
	</ul>';

?>