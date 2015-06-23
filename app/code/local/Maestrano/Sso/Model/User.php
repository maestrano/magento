<?php

class Maestrano_Sso_Model_User extends Mage_Admin_Model_User
{
    /**
     * Find or Create a user based on the SAML response parameter and Add the user to current session
     */
    public function findOrCreate() {
        echo '<br/>Resource class: ' . get_class($this->getResource());
    }
}
