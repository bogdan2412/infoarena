#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once(IA_ROOT_DIR.'common/array_schema.php');
require_once(IA_ROOT_DIR.'common/array_path.php');

test_cleanup();
test_prepare();

// Test json_validate.
// $expected_errors contains expected errors. Errors are matched in order.
// If a string in $expected_errors starts with a / then it's interprested as a regular expression.
//
// FIXME: Error ordering logic is confusing.
function test_array_validate($data, $schema, $expected_errors)
{
    $passed = true;
    $real_errors = array_validate($data, $schema);
    $real_error_messages = array();
    for ($i = 0; $i < count($real_errors); ++$i) {
        $real_error_messages[$i] = $real_errors[$i]['message'];
        $real_errors[$i] = $real_errors[$i]['path'];
    }

    // PHP is retarded and needs a strictly positive number of elements.
    if (count($expected_errors)) {
        $matched = array_fill(0, count($expected_errors), false);
    } else {
        $matched = array();
    }

    for ($i = 0; $i < count($real_errors); ++$i) {
        $found = false;
        for ($j = 0; $j < count($expected_errors); ++$j) {
            if ($matched[$j]) {
                continue;
            }
            if (count($real_errors[$i]) == count($expected_errors[$j])) {
                for ($k = 0; $k < count($real_errors[$i]); ++$k) {
                    if ($real_errors[$i][$k] != $expected_errors[$j][$k]) {
                        break;
                    }
                }
                if ($k == count($real_errors[$i])) {
                    $matched[$j] = $found = true;
                    break;
                }
            }
        }
        if (!$found) {
            log_print("ERROR: Unmatched real error path " . array_path_join($real_errors[$i]) .
                    " Message: " . $real_error_messages[$i]);
            $passed = false;
        }
    }
    for ($j = 0; $j < count($expected_errors); ++$j) {
        if (!$matched[$j]) {
            log_print("ERROR: Unmatched expected error path " .
                    array_path_join($expected_errors[$j]));
            $passed = false;
        }
    }
    if ($passed) {
        log_print("OK: Test passed.");
    }
    return $passed;
}


log_print("TEST: Sequence of string.");
$schema = array(
    'type' => 'sequence',
    'values' => array('type' => 'string'),
);
test_array_validate(
    array(
        'foo', 'bar', 'baz'
    ),
    $schema,
    array(
    )
);
test_array_validate(
    array(
        'foo', 123, 'baz'
    ),
    $schema,
    array(
        array(1),
    )
);


log_print("TEST: mapping of scalar");
$schema = array(
    'type' => 'struct',
    'fields' => array(
        'name' => array('type' => 'string', 'null' => false),
        'email' => array('type' => 'string', 'pattern' => '/@/'),
        'age' => array('type' => 'int'),
        'birth' => array('type' => 'date'),
    ),
);
test_array_validate(
    array(
        'name' => 'foo',
        'email' => 'foo@mail.com',
        'age' => 20,
        'birth' => '1985-01-01',
    ),
    $schema,
    array(
    )
);
test_array_validate(
    array(
        'name' => 'foo',
        'email' => 'foo(at)mail.com',
        'age' => 'twenty',
        'birth' => 'Jun 01, 1985',
    ),
    $schema,
    array(
        array('email'),
        array('age'),
        array('birth'),
    )
);


log_print("TEST: sequence of mapping.");
$schema = array(
    'type' => 'sequence',
    'values' => array(
        'type' => 'struct',
        'sealed' => true,
        'fields' => array(
            'name' => array('type' => 'string', 'null' => false),
            'email' => array('type' => 'string', 'null' => true),
        ),
    ),
);
test_array_validate(
    array(
        array('name' => 'foo', 'email' => 'foo@mail.com'),
        array('name' => 'bar', 'email' => 'bar@mail.net'),
        array('name' => 'baz', 'email' => 'foo@mail.org'),
    ),
    $schema,
    array(
    )
);
test_array_validate(
    array(
        array('name' => 'foo', 'email' => 'foo@mail.com'),
        array('naem' => 'bar', 'email' => 'bar@mail.net'),
        array('name' => 'baz', 'mail' => 'foo@mail.org'),
    ),
    $schema,
    array(
        array(1, 'naem'),
        array(1, 'name'),
        array(2, 'mail'),
    )
);


log_print("TEST: mapping of sequence of mapping, valid");
$schema = array(
    'type' => 'struct',
    'fields' => array(
        'company' => array('type' => 'string', 'null' => false),
        'email' => array('type' => 'string'),
        'employees' => array(
            'type' => 'sequence',
            'values' => array(
                'type' => 'struct',
                'sealed' => 'true',
                'fields' => array(
                    'code' => array('type' => 'int', 'null' => false),
                    'name' => array('type' => 'string', 'null' => false),
                    'email' => array('type' => 'string', 'null' => true),
                ),
            ),
        ),
    ),
);
test_array_validate(
    array(
        'company' => 'Kuwata lab.',
        'email' => 'webmaster@kuwata-lab.com',
        'employees' => array(
            array('code' => 101, 'name' => 'foo', 'email' => 'foo@kuwata-lab.com'),
            array('code' => 102, 'name' => 'bar', 'email' => 'bar@kuwata-lab.com'),
        )
    ),
    $schema,
    array(
    )
);
test_array_validate(
    array(
        'company' => 'Kuwata lab.',
        'email' => 'webmaster@kuwata-lab.com',
        'employees' => array(
            array('code' => 'A101', 'name' => 'foo', 'email' => 'foo@kuwata-lab.com'),
            array('code' => 102, 'name' => 'bar', 'mail' => 'bar@kuwata-lab.com'),
        )
    ),
    $schema,
    array(
        array('employees', 0, 'code'),
        array('employees', 1, 'mail'),
    )
);


log_print('TEST: Rule examples');
$schema = array(
    'type' => 'sequence',
    'values' => array(
        'type' => 'struct',
        'sealed' => true,
        'fields' => array(
            'name' => array('type' => 'string', 'null' => false),
            'email' => array('type' => 'string', 'null' => false, 'pattern' => '/@/'),
            'password' => array(
                'type' => 'string',
                'length' => array('max' => 16, 'min' => 8),
            ),
            'age' => array(
                'type' => 'int',
                'range' => array('max' => 30, 'min' => 18),
            ),
            'blood' => array(
                'type' => 'string',
                'enum' => array('A', 'B', 'O', 'AB'),
            ),
            'birth' => array('type' => 'date'),
            'memo' => array('type' => 'any', 'null' => 'true'),
        ),
    ),
);
test_array_validate(
    array(
        array(
            'name' => 'foo',
            'email' => 'foo@mail.com',
            'password' => 'xxx123456',
            'age' => 20,
            'blood' => 'A',
            'birth' => '1985-01-01',
        ),
        array(
            'name' => 'bar',
            'email' => 'bar@mail.net',
            'password' => 'xxx123456',
            'age' => 25,
            'blood' => 'AB',
            'birth' => '1980-01-01',
        ),
    ),
    $schema,
    array(
    )
);
test_array_validate(
    array(
        array(
            'name' => 'foo',
            'email' => 'foo(at)mail.com',
            'password' => 'xxx123',
            'age' => 'twenty',
            'blood' => 'a',
            'birth' => '1985-01-01',
        ),
        array(
            'given-name' => 'bar',
            'family-name' => 'Bar',
            'email' => 'bar@mail.net',
            'password' => 'xxx123456',
            'age' => 15,
            'blood' => 'AB',
            'birth' => '1980/01/01',
        ),
    ),
    $schema,
    array(
        array(0, 'email'),
        array(0, 'password'),
        array(0, 'age'),
        array(0, 'blood'),
        array(1, 'given-name'),
        array(1, 'family-name'),
        array(1, 'age'),
        array(1, 'birth'),
        array(1, 'name'),
    )
);
