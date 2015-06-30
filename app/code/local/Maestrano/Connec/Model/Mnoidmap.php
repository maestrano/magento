<?php

class Maestrano_Connec_Model_Mnoidmap extends Mage_Core_Model_Abstract
{
    protected function _construct(){
        $this->_init("connec/mnoidmap");
    }

    public function addMnoIdMap($local_id, $local_entity_name, $mno_id, $mno_entity_name) {
        $this->getResource()->addMnoIdMap($local_id, $local_entity_name, $mno_id, $mno_entity_name);
    }

    public function findMnoIdMapByMnoIdAndEntityName($mno_id, $mno_entity_name, $local_entity_name=null) {
        $this->getResource()->findMnoIdMapByMnoIdAndEntityName($mno_id, $mno_entity_name, $local_entity_name=null);
    }

    public function findMnoIdMapByLocalIdAndEntityName($local_id, $local_entity_name) {
        $this->getResource()->findMnoIdMapByLocalIdAndEntityName($local_id, $local_entity_name);
    }

    public function deleteMnoIdMap($local_id, $local_entity_name) {
        $this->getResource()->deleteMnoIdMap($local_id, $local_entity_name);
    }

    public function hardDeleteMnoIdMap($local_id, $local_entity_name) {
        $this->getResource()->hardDeleteMnoIdMap($local_id, $local_entity_name);
    }

    public function updateIdMapEntry($current_mno_id, $new_mno_id, $mno_entity_name) {
        $this->getResource()->updateIdMapEntry($current_mno_id, $new_mno_id, $mno_entity_name);
    }
}