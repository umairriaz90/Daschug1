<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Represents the data of an address in system
 *
 * @author oxana
 */
class Address {
    public static $addressParams = array(ADDRESS_OFFLINE => "address",
        MEETING_ROOM => "meeting_room", 
        LOCATION => "locationID", 
        INSTRUCTION_LINK => "instructionLink", 
        CONTENT_LINK => "contentLink");

    private $addressValues;
    
    private $addressID;

    function __construct($addressValues) {
        $this->addressValues = $addressValues;
    }

    /**
     * A string representing full offline address (address, meeting room)
     * @return string
     */
    private function getOfflineAddressValue() {
        return $this->addressValues['address'] . ", " . $this->addressValues['meeting_room'];
    }

    /**
     * A string representing online address (a link)
     * Returned as plain text, if $activeLink = FALSE, as link wrapped in HTML otherwise
     * @param boolean $activeLink
     * @return string
     */
    private function getOnlineAddressValue($activeLink = false) {
        if (!$activeLink)
            return $this->addressValues['contentLink'];
        else
            return "<a href = '".$this->addressValues['contentLink']."'>".CONTENT_LINK_TITLE."</a>";
    }

    /**
     * Returns TRUE if the address refers to online website
     * @return boolean
     */
    private function isOnline() {
        return $this->addressValues['name'] == 'Online' || 
//                strtolower($this->addressValues['location']) == 'webinar'||
                 strtolower($this->addressValues['name']) == 'elearning';
    }

    /**
     * Returns full address value (physical address, meeting 
     * In case address is offline: physical address, meeting room
     * In case address is online: link to the content
     * In case a link with the instructions to this address is set: the link is added to the address 
     * 
     * @param boolean $activeLinks determines whether the links are returned as plain text, or wrapped in HTML
     * @return string
     */
    function getAddressValue($activeLinks = false) {
        $value = $this->addressValues['name'] . ', ';
        if ($this->isOnline())
            $value .= $this->getOnlineAddressValue($activeLinks);
        else
            $value .= $this->getOfflineAddressValue();
        if (isset($this->addressValues['instructionLink']) && $this->addressValues['instructionLink'] != "") {
            if ($activeLinks)
                $link = "<a href = '".$this->addressValues['instructionLink']."'>".INSTRUCTION_LINK_TITLE."</a>";
            else
                $link = $this->addressValues['instructionLink'];
            
            $value .= ", <br />".INSTRUCTION_LINK.": $link";
        }
        return $value;
    }
    
    /**
     * Gets the physical address value
     * @return string
     */
    function getAddress() {
        return $this->addressValues['address'];
    }
    
    /**
     * Returns the ID of an address location
     * @return type
     */
    function getLocationID() {
        return $this->addressValues['locationID'];
    }
    
    function getLocationName() {
        return $this->addressValues['name'];
    }
    
    function getMeetingRoom() {
        return $this->addressValues['meeting_room'];
    }
    
    /**
     * Returns the instruction link set to an address.
     * An instruction link may point towards an instruction for using the website, in case address is online, or as an instruction for a specific event held in this place, route description etc. in case event is offline
     * @return string
     */
    function getInstructionLink() {
        return $this->addressValues['instructionLink'];
    }
    
    /**
     * For online addresses, returns the link of the website where the content is found
     * @return string
     */
    function getContentLink() {
        return $this->addressValues['contentLink'];
    }
    
    /**
     * Returns the ID of an address if read from database or set manually, null otherwise
     * @return int
     */
    function getID() {
        return $this->addressID;
    }
    
    /**
     * Sets the ID of an address
     * Does NOT change the corresponding entry in database
     * @param int $id
     */
    function setID($id) {
        $this->addressID = $id;
    }

}

?>
