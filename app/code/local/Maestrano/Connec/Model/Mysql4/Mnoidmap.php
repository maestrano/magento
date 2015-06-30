<?php
class Maestrano_Connec_Model_Mysql4_Mnoidmap extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("connec/mnoidmap", "mnoidmap_id");
    }

    public function addMnoIdMap($local_id, $local_entity_name, $mno_id, $mno_entity_name) {
        //$query = "INSERT INTO mno_id_map (mno_entity_guid, mno_entity_name, app_entity_id, app_entity_name, db_timestamp) VALUES ('".$mno_id."','".strtoupper($mno_entity_name)."','".$local_id."','".strtoupper($local_entity_name)."', LOCALTIMESTAMP(0))";
        $new = Mage::getModel('connec/mnoidmap');
        $new->setMnoEntityGuid($mno_id);
        $new->setMnoEntityName($mno_entity_name);
        $new->setAppEntityId($local_id);
        $new->setAppEntityName($local_entity_name);
        $new->save();

        return $new;
    }

    public function findMnoIdMapByMnoIdAndEntityName($mno_id, $mno_entity_name, $local_entity_name=null) {
        /*if(is_null($local_entity_name)) {
            $query = "SELECT * from mno_id_map WHERE mno_entity_guid = '$mno_id' AND mno_entity_name = '".strtoupper($mno_entity_name)."' ORDER BY deleted_flag";
        } else {
            $query = "SELECT * from mno_id_map WHERE mno_entity_guid = '$mno_id' AND mno_entity_name = '".strtoupper($mno_entity_name)."' AND app_entity_name = '".strtoupper($local_entity_name)."' ORDER BY deleted_flag";
        }*/
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select();

        $binds = array(
            'mno_entity_guid' => $mno_id,
            'mno_entity_name' => $mno_entity_name
        );

        $select->from($this->getMainTable())
            ->where('mno_entity_guid = :mno_entity_guid')
            ->where('mno_entity_name = :mno_entity_name');

        if (!is_null($local_entity_name)) {
            $binds[] = strtoupper($local_entity_name);
            $select->where('app_entity_name = :local_entity_name');
        }

        return $adapter->fetchRow($select, $binds);
    }

    public function findMnoIdMapByLocalIdAndEntityName($local_id, $local_entity_name) {
        //$result = $adb->pquery("SELECT * from mno_id_map WHERE app_entity_id = '".$local_id."' AND app_entity_name = '".strtoupper($local_entity_name)."' ORDER BY deleted_flag");
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select();

        $binds = array(
            'local_id' => $local_id,
            'local_entity_name' => strtoupper($local_entity_name)
        );

        $select->from($this->getMainTable())
            ->where('app_entity_id = :local_id')
            ->where('app_entity_name = :local_entity_name');

        return $adapter->fetchRow($select, $binds);
    }

    public function deleteMnoIdMap($local_id, $local_entity_name) {
        //$query = "UPDATE mno_id_map SET deleted_flag = 1 WHERE app_entity_id = '".$local_id."' AND app_entity_name = '".strtoupper($local_entity_name)."'";
        $mnoIdMap = $this->findMnoIdMapByLocalIdAndEntityName($local_id, $local_entity_name);
        $mnoIdMap->setDeletedFlag(1);
        $mnoIdMap->save();
    }

    public function hardDeleteMnoIdMap($local_id, $local_entity_name) {
        //$query = "DELETE FROM mno_id_map WHERE app_entity_id = '".$local_id."' AND app_entity_name = '".strtoupper($local_entity_name)."'";
        $mnoIdMap = $this->findMnoIdMapByLocalIdAndEntityName($local_id, $local_entity_name);
        $mnoIdMap->delete();
    }

    public function updateIdMapEntry($current_mno_id, $new_mno_id, $mno_entity_name) {
        //$query = "UPDATE mno_id_map SET mno_entity_guid = '".$new_mno_id."' WHERE mno_entity_guid = '".$current_mno_id."' AND mno_entity_name = '".strtoupper($mno_entity_name)."'";
        $mnoIdMap = $this->findMnoIdMapByMnoIdAndEntityName($current_mno_id, $mno_entity_name);
        $mnoIdMap->setMnoEntityGuid($new_mno_id);
        $mnoIdMap->save();
    }

}