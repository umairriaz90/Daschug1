<?php

//if (get_locale() == 'de_DE')
//    $language_file = 'de.txt';
//else
    $language_file = 'de.txt';

load_strings(plugin_dir_path( __FILE__ ).'/'.$language_file);
//var_dump(ABSPATH.PLUGIN_PATH);
//var_dump(ABSPATH_)
function load_strings($file) {
    $strings = explode(';', file_get_contents($file));
    foreach ($strings as $string) {
        if ($string == '')
            continue;
        $stringValues = explode(' = ', $string);
        if (count($stringValues) == 2)
            define(trim($stringValues[0]), trim($stringValues[1]));
    }
}
?>
