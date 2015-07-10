<?php

class Maestrano_Connec_Helper_Observerlockhelper extends Mage_Core_Helper_Abstract {
    private $entitesLocks = null;

    public function __construct() {
        $this->entitesLocks = Mage::getSingleton('admin/session')->getEntitiesObserversLock();
        if (is_null($this->entitesLocks)) {
            $this->entitesLocks = array();
            Mage::getSingleton('admin/session')->setEntitiesObserversLock($this->entitesLocks);
        }
    }

    public function lockEntity($id) {
        $this->entitesLocks = Mage::getSingleton('admin/session')->getEntitiesObserversLock();
        $this->entitesLocks[] = $id;
        Mage::getSingleton('admin/session')->setEntitiesObserversLock($this->entitesLocks);
    }

    public function isLockedEntity($id) {
        $this->entitesLocks = Mage::getSingleton('admin/session')->getEntitiesObserversLock();
        if (in_array($id, $this->entitesLocks)) {
            return true;
        } else {
            return false;
        }
    }

    public function unlockEntity($id) {
        $this->entitesLocks = Mage::getSingleton('admin/session')->getEntitiesObserversLock();
        if(($key = array_search($id, $this->entitesLocks)) !== false) {
            unset($this->entitesLocks[$key]);
        }
        Mage::getSingleton('admin/session')->setEntitiesObserversLock($this->entitesLocks);
    }

    public function lockGlobally() {
        Mage::getSingleton('admin/session')->setGlobalObserversLock(true);
    }

    public function isLockedGlobally() {
        if (Mage::getSingleton('admin/session')->getGlobalObserversLock()) {
            return true;
        } else {
            return false;
        }
    }

    public function unlockGlobally() {
        Mage::getSingleton('admin/session')->unsGlobalObserversLock();
    }

}