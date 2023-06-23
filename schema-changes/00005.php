<?php

const TABLES = [ 'ia_textblock', 'ia_textblock_revision' ];

main_00005();

function main_00005(): void {
  foreach (TABLES as $table) {
    $query = "select * from {$table} order by name";
    $result = db_query($query);

    while ($row = db_next_row($result)) {
      process_row($table, $row);
    }
  }
}

function process_row(string $table, array $row): void {
  $orig_text = $row['text'];

  $changes = [
    // user pages
    [ 'about-me',
      "/_\(completeaza aici: studii, an de absolvire, institutie de invatamant, locatie, profesori pregatitori, site personal ...\)_/",
      '* _(completează aici: studii, an de absolvire, instituție de învățămînt, localitate, profesori pregătitori, site personal...)_' ],
    [ 'awards-1',
      "/h2. Distinctii primite/",
      'h2. Distincții primite' ],
    [ 'awards-2',
      "/\* _\(completeaza aici: locuri obtinute la concursuri de informatica\)_/",
      '* _(completează aici: locuri obținute la concursuri de informatică)_' ],
    [ 'infoarena-friends-1',
      "/h2. Prieteni pe infoarena/",
      'h2. Prieteni pe NerdArena' ],
    [ 'infoarena-friends-2',
      "/\* _\(completeaza aici: link-uri catre profilele altor utilizatori infoarena pe care ii cunosti\)_/",
      '* _(completează aici: legături către profilele altor utilizatori NerdArena pe care îi cunoști)_' ],

    // round pages
    [ 'round-1',
      '/Concursul incepe/',
      'Concursul începe' ],
    [ 'round-2',
      '/si dureaza/',
      'și durează' ],
    [ 'round-3',
      '/Aceasta lista va deveni vizibila doar in momentul inceperii concursului./',
      'Această listă va deveni vizibilă doar în momentul începerii concursului.' ],
    [ 'round-4',
      '/Felicitari primilor clasati!!!/',
      'Felicitări primilor clasați!' ],
  ];

  foreach ($changes as $rec) {
    update_regexp($table, $row, $rec[0], $rec[1], $rec[2]);
  }

  if ($row['text'] != $orig_text) {
    save_row($table, $row);
  }
}

function update_regexp(string $table, array& $row, string $description,
                       string $from, string $to): void {

  $row['text'] = preg_replace($from, $to, $row['text'], -1, $count);
  if ($count) {
    printf("  * %d %s updated in %s %s[%s]\n",
           $count, $description, $table, $row['name'], $row['timestamp']);
  }
}

function save_row(string $table, array& $row): void {
  printf("Saving %s %s\n", $table, $row['name']);

  $where = sprintf('name = %s and timestamp = %s',
                   db_quote($row['name']),
                   db_quote($row['timestamp']));

  db_update($table,
            [ 'text' => $row['text'] ],
            $where);
}
