<?php

const TABLES = [ 'ia_textblock', 'ia_textblock_revision' ];

main();
exit; // FIXME

function main(): void {
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
    [ 'links', "/'([^']+)':/", '"$1":' ],
    [ 'round htabs', "/\(htabs\)\*\(active\)/", "*(htabs).\n*(active)" ],
    [ 'ranking htabs', "/\(htabs\)\* /", "*(htabs).\n* " ],
    [ 'tables', "/^(table.*\.) \|/m", "$1\n|" ],
    [ 'images-styled-aligned', "/!(\{[^}]+\})([<>])([^!]+)!/", '!$2$1$3!' ],
    [ 'curly-star', "/\{\*([^*}]+)\*\}/", '[*$1*]' ],
    [ 'curly-tilde', "/\{~([^~}]+)~\}/", '[~$1~]' ],
    [ 'curly-undescore', "/\{_([^_}]+)_\}/", '[_$1_]' ],
    [ 'curly-dollar', "/\{\\$([^$}]+)\\$\}/", '[$$1$]' ],
    [ 'exponent-left', "/(?<![ \t\n\[])\\^([a-z0-9]+)\\^/i", '[^$1^]' ],
    [ 'exponent-right', "/\\^([a-z0-9]+)\\^(?=[^ \t\r\n\]])/i", '[^$1^]' ],
    [ 'tilde-left', "/(?<![ \t\n\[])~([a-z0-9]+)~/i", '[~$1~]' ],
    [ 'tilde-right', "/~([a-z0-9]+)~(?=[^ \t\r\n\]])/i", '[~$1~]' ],
    [ 'dollar-left', "/(?<![ \t\n\[])\\$([a-z0-9]+)\\$/i", '[$$1$]' ],
    [ 'dollar-right', "/\\$([a-z0-9]+)\\$(?=[^ \t\r\n\]])/i", '[$$1$]' ],
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
