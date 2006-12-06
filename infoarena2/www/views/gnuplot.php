<?php

// This view uses gnuplot to render PNG image
//
// Expected view variables:
// $script  gnuplot script without `set terminal` declaration
// $data    gnuplot auxiliary data

// compute gnuplot script
$plot_script = "set terminal png notransparent small\n";
$plot_script .= "set size .85,.5\n\n";
$plot_script .= $script;

// store auxiliary data in a temporary file
// NOTE: Don't worry about /tmp! If it doesn't exists, tempnam
// finds the right system temporary folder
$tmpfname = tempnam("/tmp", "iagnuplot_");
log_assert($tmpfname);
$ftemp = fopen($tmpfname, "w");
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
$process = proc_open('gnuplot', $descriptorspec, $pipes);

log_assert(is_resource($process), "Could not create gnuplot process");

// feed script to pipe
list($plot_in, $plot_out) = $pipes;
fwrite($plot_in, $plot_script);
fclose($plot_in);

// render PNG
header("Content-type: image/png\n\n");
fpassthru($plot_out);
echo $data;
fclose($plot_out);

// clean-up
proc_close($process);
unlink($tmpfname);

?>
