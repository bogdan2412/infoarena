<?php

// opens zip archive and scans for "valid" files (non-zero size and valid attachment name)
// returns FALSE if error occurs
// NOTE: the function ignores (flattens) the file hierarchy in the ZIP archive
function get_zipped_attachments($filename) {
    $attachments = array();
    $namehash = array();

    log_print('Exploring ZIP archive '.$filename);

    // open ZIP archive
    $zip = new ZipArchive();
    $res = $zip->open($filename);
    if ($res !== true) {
        log_print("[FAILED] to open ZIP archive {$zip_file}");
        return false;
    }
    else {
        for ($i = 0; false !== ($stat = $zip->statIndex($i)); $i++) {
            // skip directories and 0-bytes files
            if (!$stat['size']) {
                continue;
            }

            // validate file name and make sure there are no duplicates
            $aname = basename($stat['name']);
            if (isset($namehash[$aname]) || !is_attachment_name($aname)) {
                continue;
            }

            // append to list
            $attachments[] = array('name' => $aname, 'size' => $stat['size'], 'zipindex' => $i);
            $namehash[$aname] = true;
        }
        $zip->close();
    }
    return $attachments;
}

// given a ZIP archive file name, extract ZIP entry of index $zip_index
// to disk file $to_file
//
// returns FALSE upon error
function extract_zipped_attachment($zip_file, $zip_index, $to_file) {
    $zip = new ZipArchive();
    $res = $zip->open($zip_file);
    if (true !== $res) {
        log_print("[FAILED] to open ZIP archive {$zip_file}");
        return false;
    }

    // FIXME: This should be done with streaming (i.e. avoid the temporary storage)
    $buffer = $zip->getFromIndex($zip_index);
    if (false === $buffer) {
        log_print("[FAILED] to ZIP extract file index @{$zip_index} from archive {$zip_file}");
        return false;
    }

    log_print("[SUCCESS] ZIP extract file index @{$zip_index} from archive {$zip_file}");

    return file_put_contents($to_file, $buffer);
}

?>
