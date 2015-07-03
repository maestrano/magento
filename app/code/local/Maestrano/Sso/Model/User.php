<?php

/**
 * Admin user model
 *
 * @method Maestrano_Sso_Model_Resource_User _getResource()
 * @method Maestrano_Sso_Model_Resource_User getResource()
 */
class Maestrano_Sso_Model_User extends Mage_Admin_Model_User
{
    /**
     * Find or Create a user based on the SAML response parameter and Add the user to current session
     * @param Maestrano_Sso_User $mnoUser
     * @return Magento user
     */
    public function findOrCreate(Maestrano_Sso_User $mnoUser) {
        // Find user by uid or email
        $localUser = $this->getResource()->getLocalUserByMnoUid($mnoUser);
        if(!$localUser) {
            $localUser = $this->getResource()->getLocalUserByEmail($mnoUser);
        }

        // Create user if doesn't exists
        if(!$localUser) $localUser = $this->getResource()->createLocalUser($mnoUser);

        return $localUser;
    }
}
