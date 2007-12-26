<?php
require_once(IA_ROOT_DIR."common/log.php");

// puts the contents of a string into a temporary files 
// returns the temporary file name
function file_put_string($string) {
    $name = tempnam(IA_ROOT_DIR.'attach/', "ia");
    $fp = fopen($name, "w");
    if (!$fp) {
        return "";
    }
    $string .= "\n";
    fputs($fp, $string);
    fclose($fp);
    return $name;
}

// this function returns an array describing the differences between two strings
// output array format is:
// array(
//      ...
//      block_index => array( 
//                      ...
//                      op_index => array(
//                          type  => added | deleted | normal
//                          lines => array of strings
//                      )
//                      ...
//      ...
// );
function diff_string($string) {
    log_assert(count($string) == 2);
    // put string contents into files
    $name = array();
    for ($i = 0; $i < 2; ++$i) {
        $name[$i] = file_put_string($string[$i]);
        if (!$name[$i]) {
            return null;
        }
    }

    // capture diff output
    ob_start();
    system("diff -au ".$name[0]." ".$name[1]);
    $diff = ob_get_contents();
    ob_end_clean();

    // delete temporary files
    for ($i = 0; $i < 2; ++$i) {
        if (!unlink($name[$i])) {
            return null;
        }
    }

    // parse diff output 
    $lines = explode("\n", $diff);
    $result = array();
    $block_cnt = 0; $op_cnt = -1; 
    foreach ($lines as $line) {
        if (strlen($line) == 0 || preg_match("/^(---|\+\+\+)/", $line)) {
            continue;
        }
        if (preg_match("/^(@@)/", $line)) {
            if (isset($result[$block_cnt])) {
                ++$block_cnt;
                $op_cnt = -1;
            }
            continue;
        }

        // what type of operation is this?
        if (strlen($line) > 0 && $line[0] == '+') {
            $type = 'added';
        } elseif (strlen($line) > 0 && $line[0] == '-') {
            $type = 'deleted';
        } else {
            $type = 'normal';
        }

        $line = substr($line, 1);
        if ($op_cnt >= 0 && $result[$block_cnt][$op_cnt]['type'] == $type) {
            $result[$block_cnt][$op_cnt]['lines'][] = $line;
        } else {
            ++$op_cnt;
            $result[$block_cnt][$op_cnt]['type'] = $type;
            $result[$block_cnt][$op_cnt]['lines'] = array($line);
        }
    }

    return $result;
}

// compute longest common subsequence using dynamic programming
// FIXME: both time and memory complexity can be reduced
function lcs($a, $b) {
    $N = strlen($a);
    $M = strlen($b);
    $C = array(array($N+1), array($M+1));

    for ($i = 0; $i <= $N; ++$i) {
        $C[$i][0] = "";
    }
    for ($i = 0; $i <= $M; ++$i) {
        $C[0][$i] = "";
    }
    for ($i = 1; $i <= $N; ++$i) {
        for ($j = 1; $j <= $M; ++$j) {
            if ($a[$i-1] == $b[$j-1]) {
                $C[$i][$j] = $C[$i-1][$j-1].$a[$i-1];
            } else if ($C[$i-1][$j] > $C[$i][$j-1]) {
                $C[$i][$j] = $C[$i-1][$j];
            } else {
                $C[$i][$j] = $C[$i][$j-1];
            }
        }
    }
    return $C[$N][$M];
}

function split_string($string, $substring, $op_name) {
    // sentinel character
    $string .= "#";
    $substring .= "#";
    $N = strlen($string);
    $M = strlen($substring);

    $result = array();
    for ($i = 0, $j = -1; $i < $M; ++$i) {
        for ($prev = $j++; $j < $N && $string[$j] != $substring[$i]; ++$j);
        log_assert($j < $N);
        if ($i == $M-1) {
            --$j;
        }
        if ($j-$prev-1 > 0) {
            $result[] = array('type' => $op_name, 'string' => substr($string, $prev+1, $j-$prev-1));
        }
        $result[] = array('type' => 'normal', 'string' => $string[$j]);
    }

    return $result;
}

// does inline diff on strings unsing <ins> ans <del> HTML tags
function diff_inline($string, $op_name = array("del", "ins")) {
    $diff = diff_string($string);

    foreach ($diff as &$block) {
        for ($i = 0; $i+1 < count($block); ++$i) {
            if ($block[$i]['type'] != 'deleted' || $block[$i+1]['type'] != 'added' ||
                count($block[$i]['lines']) != count($block[$i+1]['lines'])) {
                continue;
            }

            for ($j = 0; $j < count($block[$i]['lines']); ++$j) {
                $line = array();
                for ($k = 0; $k < 2; ++$k) {
                    $line[$k] = $block[$i+$k]['lines'][$j];
                }
                $lcs = lcs($line[0], $line[1]);
                if ($lcs == "") {
                    continue;
                }
                for ($k = 0; $k < 2; ++$k) {
                    $line[$k] = split_string($line[$k], $lcs, $op_name[$k]);
                    $block[$i+$k]['lines'][$j] = $line[$k];
                }
            }
        }
    }

    return $diff;
}


?>

