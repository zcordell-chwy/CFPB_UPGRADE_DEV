<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// handy debugging function that allows debug output to go to different destinations
// depending on setting. Also handles non-scalar values as needed
function debug_log($s, $label = '')
{
    $debug_type = 1;

    if (!is_scalar($s))
    {
        $s = print_r($s, true);
    }

    switch ($debug_type)
    {
        case 0 :
            echo("<pre> " . date("Y-m-d H:i:s") . " $label $s </pre><br />\n");
            break;

        case 1 :
            $logfile = fopen("/tmp/debug_log_out_" . date("Y-m-d") . ".txt", 'a') or exit("couldn't open debug output file!");
            fwrite($logfile, date("Y-m-d H:i:s") . " $label $s \n");
            fclose($logfile);
            break;

    }
}
