<?php

class Maestrano_Sso_Model_Resource_User extends Mage_Admin_Model_Resource_User {
    /**
     * Check if a user exists with this mno_uid
     *
     * @param Maestrano_Sso_User $user
     * @return array|false
     */
    public function getLocalUserByMnoUid(Maestrano_Sso_User $user)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select();

        $binds = array(
            'mno_uid' => $user->getUid()
        );

        $select->from($this->getMainTable())
            ->where('mno_uid = :mno_uid');

        return $adapter->fetchRow($select, $binds);
    }

    /**
     * Check if a user exists with this email
     *
     * @param Maestrano_Sso_User $user
     * @return array|false
     */
    public function getLocalUserByEmail(Maestrano_Sso_User $user)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select();

        $binds = array(
            'email' => $user->getEmail()
        );

        $select->from($this->getMainTable())
            ->where('email = :email');

        return $adapter->fetchRow($select, $binds);
    }

    /**
     * Used by createLocalUserOrDenyAccess to create a local user
     * based on the sso user.
     * If the method returns null then access is denied
     *
     * @param Maestrano_Sso_User $mnoUser
     * @return the the user created, null otherwise
     * @throws Exception
     */
    public function createLocalUser(Maestrano_Sso_User $mnoUser) {
        $newUser = Mage::getModel('admin/user');
        $newUser->setMnoUid($mnoUser->getUid());
        // Username must be unique
        // format "Firstname (uid)" breaks the acl...
        $newUser->setUsername($mnoUser->getUid());
        $newUser->setFirstname($mnoUser->getFirstname());
        $newUser->setLastname($mnoUser->getLastName());
        $newUser->setEmail($mnoUser->getEmail());
        $newUser->setPassword($this->generatePassword());
        $newUser->save();

        //Assign Role Id
        $newUser->setRoleIds(array(1)) //Administrator role id is 1 ,Here you can assign other roles ids
            ->setRoleUserId($newUser->getUserId())
            ->saveRelations();

        return $newUser;
    }

    /**
     * Generate a random password.
     * Convenient to set dummy passwords on users
     *
     * @return string a random password
     */
    protected function generatePassword() {
        $length = 66;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}