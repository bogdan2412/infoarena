<?php

// This view uses gnuplot to render PNG image
//
// Expected view variables:
// $script  gnuplot script without `set terminal` declaration
// $data    gnuplot auxiliary data

log_assert(isset($script) && isset($data));

// FIXME Only fixed size plots are supported at this point.
// Getting PostScript & `convert` to render an image at a precise
// pixel resolution seems to be a nightmare.
$width = 510;
$height = 200;
$ratio = 0.36;

// compute gnuplot script
//  - gnuplot accepts image size as a ratio of hard-coded image size 640x480
$plot_script = "
set terminal postscript eps color enhanced 'Arial' 15
set terminal postscript eps landscape
set size ratio {$ratio}
set size 1,1
";
$plot_script .= $script;

// store auxiliary data in a temporary file
// NOTE: Don't worry about /tmp! If it doesn't exists, tempnam
// finds the right system temporary folder
$tmpfname = tempnam("/tmp/", "iagnuplot_");
log_assert($tmpfname);

$ftemp = fopen($tmpfname, "w");
// NOTE: the 0 (zero) at the beginning indicates octal constant value
chmod($tmpfname, 0666);
log_assert($ftemp);
fwrite($ftemp, $data);
fclose($ftemp);

// 'bind' plot script to data
$plot_script = str_replace("%data%", $tmpfname, $plot_script);

// open gnuplot pipe
$descriptorspec = array(
    0 => array("pipe", "r"),
    1 => array("pipe", "w"),
);

$process = proc_open("gnuplot | convert -rotate 90 -density 72 "
                     ."-resample 51x50 -crop {$width}x{$height}+0+0 "
                     ."-gravity South ps:- png:-",
                     $descriptorspec, $pipes);

log_assert(is_resource($process), "Could not create gnuplot process");

// feed script to pipe
list($plot_in, $plot_out) = $pipes;
fwrite($plot_in, $plot_script);
fclose($plot_in);

// render PNG
header("Content-type: image/png");
fpassthru($plot_out);
fclose($plot_out);

// clean-up
proc_close($process);
unlink($tmpfname);

?>
