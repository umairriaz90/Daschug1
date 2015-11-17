<?php



/**
 * Takes care of plugin localisation
 */
class DaschugLocalisation {
    public static $languages = array("de_DE" => "Deutsch", 
        "en_US"=>"English");

    private $_language_file;
    private $_userID;
    private static $defaultLanguage = "en_US";

    /**
     * If no language is set, loads the user setting
     * Otherwise, loads the corresponding language file (currently, just German for "de_DE", English otherwise)
     * @param string $language
     */
    function __construct($userID, $language = "") {
        $this->_userID = $userID;
        if($language == "")
             $language = $this->getLanguage();
        $this->setLanguageFile($language);
//        var_dump($this->_language_file);
//        var_dump($language);
        $this->load_strings();
    }

    /**
     * Sets the name of a corresponding language file with strings used by plugin
     * If no language is set, loads the user setting
     * Otherwise, loads the corresponding language file (currently, just German for "de_DE", English otherwise)
     * @param string $language
     */
    private function setLanguageFile($language) {
        if ($language == 'de_DE')
            $this->_language_file = 'de.txt';
        else
            $this->_language_file = 'en.txt';
    }

    /**
     * Sets the language used in plugin by storing the language in user settings, determining the name of a corresponding language file and loading the strings from it
     * @param type $language
     */
    function setLanguage($language) {
        update_user_meta($this->_userID, 'datr_plugin_language', $language);
        $this->setLanguageFile($language);
        $this->load_strings();
    }

    /**
     * Loads the language from user settings
     * @return string
     */
    function getLanguage() {
        $language = get_user_meta($this->_userID, "datr_plugin_language", true);
        if ($language != "")
            return $language;
        else
            return DaschugLocalisation::$defaultLanguage;
    }

    /**
     * Loads the strings used in plugin from the external file
     * Use for localisation
     * @param string $file
     */
    public function load_strings() {
        $strings = explode(';', file_get_contents(plugin_dir_path(__FILE__) . '/' . $this->_language_file));
        foreach ($strings as $string) {
            if ($string == '')
                continue;
            $stringValues = explode(' = ', $string);
            if (count($stringValues) == 2)
                define(trim($stringValues[0]), trim($stringValues[1]));
        }
    }

}

?>
