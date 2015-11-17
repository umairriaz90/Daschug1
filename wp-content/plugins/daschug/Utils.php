<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Utils
 *
 * @author oxana
 */
class Utils {
    
    /**
     * Parses time string into time format used by plugin view
     * @param string $timeStr
     * @return string
     */
    public static function getTime($timeStr) {
        if ($timeStr != "" && substr($timeStr, 0, strlen("0000-00-00")) != "0000-00-00")
            return date("d.m.Y / H:i", strToTime($timeStr));
        else
            return "";
}
}

?>
