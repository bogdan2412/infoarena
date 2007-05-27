#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once(IA_ROOT_DIR.'common/schema.php');

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
    for ($i = 0; $i < count($real_errors); ++$i) {
        if ($i >= count($expected_errors)) {
            log_print("ERROR: Unexpected error {$real_errors[$i]}.");
            $passed = false;
            continue;
        }
        if (count($expected_errors) > 0 && $expected_errors[$i][0] == '/') {
            if (!preg_match($expected_errors[$i], $real_errors[$i])) {
                log_print("ERROR: Resulting error `{$real_errors[$i]}` didn't match expected pattern `{$expected_errors[$i]}`.");
                $passed = false;
            }
        } else {
            if ($expected_errors[$i] !== $real_errors[$i]) {
                log_print("ERROR: Resulting error `{$real_errors[$i]}` different from `{$expected_errors[$i]}`.");
                $passed = false;
            }
        }
    }
    for (; $i < count($expected_errors); ++$i) {
        log_print("ERROR: Missing error with pattern {$expected_errors[$i]}.");
        $passwd = false;
    }
    if ($passed) {
        log_print("OK: Test passed.");
    }
    return $passed;
}


log_print("TEST: Sequence of string.");
$schema = array(
    'type' => 'seq',
    'sequence' => array('type' => 'str'),
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
        '[1]: Not a string.',
    )
);


log_print("TEST: mapping of scalar");
$schema = array(
    'type' => 'map',
    'mapping' => array(
        'name' => array('type' => 'str', 'required' => true),
        'email' => array('type' => 'str', 'pattern' => '/@/'),
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
        '.email: Doesn\'t match pattern /@/.',
        '.age: Not an integer.',
        '/\.birth: Not a date.*/',
    )
);


log_print("TEST: sequence of mapping, valid");
$schema = array(
    'type' => 'seq',
    'sequence' => array(
        'type' => 'map',
        'mapping' => array(
            'name' => array('type' => 'str', 'required' => true),
            'email' => array('type' => 'str'),
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
        '[1].naem: Key naem undefined.',
        '[1].name: Required key name missing.',
        '[2].mail: Key mail undefined.',
    )
);


log_print("TEST: mapping of sequence of mapping, valid");
$schema = array(
    'type' => 'map',
    'mapping' => array(
        'company' => array('type' => 'str', 'required' => true),
        'email' => array('type' => 'str'),
        'employees' => array(
            'type' => 'seq',
            'sequence' => array(
                'type' => 'map',
                'mapping' => array(
                    'code' => array('type' => 'int', 'required' => true),
                    'name' => array('type' => 'str', 'required' => true),
                    'email' => array('type' => 'str'),
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
        '.employees[0].code: Not an integer.',
        '.employees[1].mail: Key mail undefined.',
    )
);


log_print('TEST: Rule examples');
$schema = array(
    'type' => 'seq',
    'sequence' => array(
        'type' => 'map',
        'mapping' => array(
            'name' => array('type' => 'str', 'required' => true),
            'email' => array('type' => 'str', 'required' => true, 'pattern' => '/@/'),
            'password' => array(
                'type' => 'str',
                'length' => array('max' => 16, 'min' => 8),
            ),
            'age' => array(
                'type' => 'int',
                'range' => array('max' => 30, 'min' => 18),
            ),
            'blood' => array(
                'type' => 'str',
                'enum' => array('A', 'B', 'O', 'AB'),
            ),
            'birth' => array('type' => 'date'),
            'memo' => array('type' => 'any'),
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
    array()
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
        '[0].email: Doesn\'t match pattern /@/.',
        '[0].password: Length out of range, 6 < 8.',
        '[0].age: Not an integer.',
        '[0].blood: Invalid value \'a\'',
        "[1]['given-name']: Key given-name undefined.",
        "[1]['family-name']: Key family-name undefined.",
        "[1].age: Value out of range, 15 < 18.",
        "/\[1\]\.birth: Not a date/",
        "[1].name: Required key name missing.",
    )
);
