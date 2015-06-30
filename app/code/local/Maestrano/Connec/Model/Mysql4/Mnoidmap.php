<?php
class Maestrano_Connec_Model_Mysql4_Mnoidmap extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("connec/mnoidmap", "mnoidmap_id");
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
        //$query = "SELECT * from mno_id_map WHERE app_entity_id = '".$local_id."' AND app_entity_name = '".strtoupper($local_entity_name)."' ORDER BY deleted_flag";
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

}