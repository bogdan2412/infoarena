<?php

function db_variable_get_by_name(string $name): ?array {
    $query = sprintf("select * from ia_variable where name = '%s'",
                     db_escape($name));
    return db_fetch($query);
}

function db_variable_insert(string $name, string $value): void {
    $rec = [
        'name' => $name,
        'value' => $value,
    ];
    db_insert('ia_variable', $rec);
}

function db_variable_update(string $name, string $value): void {
    db_update('ia_variable',
              [ 'value' => $value ],
              "name='{$name}'");
}

function db_variable_peek(string $name, string $default = ''): string {
    $rec = db_variable_get_by_name($name);
    return $rec ? $rec['value'] : $default;
}

function db_variable_poke(string $name, string $value): void {
    $rec = db_variable_get_by_name($name);
    if ($rec) {
        db_variable_update($name, $value);
    } else {
        db_variable_insert($name, $value);
    }
}
