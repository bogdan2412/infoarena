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

    // execute diff
    exec("diff -au ".$name[0]." ".$name[1], $lines);

    // delete temporary files
    for ($i = 0; $i < 2; ++$i) {
        if (!unlink($name[$i])) {
            return null;
        }
    }

    // parse diff output 
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
function lcs($a, $b) {
    $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w"),
    );

    // run lcs process
    $process = proc_open("iconv -f utf8 -t utf32 | " . IA_ROOT_DIR.
        "/common/lcs" . " | iconv -f utf32 -t utf8", $descriptorspec, $pipes);
    log_assert(is_resource($process), "Could not create process.");

    // feed script to pipe
    list($lcs_in, $lcs_out, $lcs_err) = $pipes;

    fwrite($lcs_in, $a."\n");
    fwrite($lcs_in, $b."\n");
    fclose($lcs_in);

    $result = fread($lcs_out, 10000);
    fclose($lcs_out);

    // check for errors
    $errors = fread($lcs_err, 1000);
    if ($errors) {
        log_error($errors);
    }
    fclose($lcs_err);

    // clean-up
    proc_close($process);

    $result = trim($result, "\n");

    return $result;
}

function split_string($string, $substring, $op_name) {
    // sentinel character
    $string .= "\n";
    $substring .= "\n";
    $N = mb_strlen($string);
    $M = mb_strlen($substring);

    $result = array();
    for ($i = 0, $j = -1; $i < $M; ++$i) {
        for ($prev = $j++; $j < $N && mb_substr($string, $j, 1) != mb_substr($substring, $i, 1); ++$j);
        if ($j-$prev-1 > 0) {
            $result[] = array('type' => $op_name, 'string' => mb_substr($string, $prev+1, $j-$prev-1));
        }
        if ($i < $M-1) {
            $result[] = array('type' => 'normal', 'string' => mb_substr($string, $j, 1));
        }
    }

    return $result;
}

// does inline diff on strings unsing <ins> ans <del> HTML tags
function diff_inline($string, $op_name = array("del", "ins")) {
    $diff = diff_string($string);

    $extensions = get_loaded_extensions();
    if (array_search('mbstring', $extensions) === false) {
        return $diff;
    }
    mb_internal_encoding("utf-8");

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
