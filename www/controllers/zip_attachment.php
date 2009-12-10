<?php

// opens zip archive and scans for "valid" files (non-zero size and valid attachment name)
// returns FALSE if error occurs
// NOTE: the function ignores (flattens) the file hierarchy in the ZIP archive
function get_zipped_attachments($filename) {
    $attachments = array();
    $namehash = array();
    $total_files = 0;

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

            $total_files++;
            // Skip big files
            if ($stat['size'] > IA_ATTACH_MAXSIZE) {
                continue;
            }

            // validate file name and make sure there are no duplicates
            $aname = basename($stat['name']);
            if (isset($namehash[$aname]) || !is_attachment_name($aname)) {
                continue;
            }

            // append to list
            $attachments[] = array('name' => $aname, 'size' => $stat['size'],
                                   'zipindex' => $i);
            $namehash[$aname] = true;
        }
        $zip->close();
    }
    return array('total_files' => $total_files, 'attachments' => $attachments);
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

    $from_stream = $zip->getStream($zip->getNameIndex($zip_index));
    if (!$from_stream) {
        log_print("[FAILED] to open ZIP stream at file index @{$zip_index} ".
                  "from archive {$zip_file}");
        return false;
    }

    log_print("[SUCCESS] ZIP extract file index @{$zip_index} ".
              "from archive {$zip_file}");

    return file_put_contents($to_file, $from_stream);
}

?>
