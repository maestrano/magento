<?php

/**
 * Map Connec Resource representation to/from Magento Model
 * You need to extend this class an implement the following methods:
 * - loadModelById($local_id) Loads the Magento entity by its id
 * - mapConnecResourceToModel($resource_hash, $model) Maps the Connec resource to the Magento entity
 * - mapModelToConnecResource($model) Maps the Magento entity into a Connec resource
 * - matchLocalModel($resource_hash) (Optional) Returns an Magento entity matched by attributes
 */
abstract class Maestrano_Connec_Helper_BaseMappers extends Mage_Core_Helper_Abstract {
    protected $_connec_client;

    protected $connec_entity_name = 'Model';
    protected $local_entity_name = 'Model';

    protected $connec_resource_name = 'models';
    protected $connec_resource_endpoint = 'models';

    protected $_date_format = null;

    public function __construct() {
        Maestrano::configure('maestrano.json');
        $this->_connec_client = new Maestrano_Connec_Client();
        $this->_date_format = DateTime::ISO8601;
    }

    protected function isNewByConnecId($connec_id) {
        $mno_id_map = Mage::getModel('connec/mnoidmap')->findMnoIdMapByMnoIdAndEntityName($connec_id, $this->connec_entity_name, $this->local_entity_name);
        if($mno_id_map) {
            Mage::log("Maestrano_Connec_Helper_BaseMappers::isNewByConnecId entity_name=$this->connec_entity_name, connec_id=" . $connec_id . " is not new.");
            return false;
        } else {
            Mage::log("Maestrano_Connec_Helper_BaseMappers::isNewByConnecId entity_name=$this->connec_entity_name, connec_id=" . $connec_id . " is new.");
            return true;
        }
    }

    protected function isNewByLocalId($local_id) {
        $mno_id_map = Mage::getModel('connec/mnoidmap')->findMnoIdMapByLocalIdAndEntityName($local_id, $this->local_entity_name);
        if($mno_id_map) {
            Mage::log("Maestrano_Connec_Helper_BaseMappers::isNewByLocalId local_entity_name=$this->local_entity_name, local_id=" . $local_id . " is not new.");
            return false;
        } else {
            Mage::log("Maestrano_Connec_Helper_BaseMappers::isNewByLocalId local_entity_name=$this->local_entity_name, local_id=" . $local_id . " is new.");
            return true;
        }
    }

    protected function format_date_to_php($connec_date) {
        return date($this->_date_format, strtotime($connec_date));
    }

    protected function format_date_to_connec($php_date) {
        if(!$this->is_set($php_date)) { return ''; }
        $date = DateTime::createFromFormat($this->_date_format, $php_date);
        if(!$date) { $date = DateTime::createFromFormat('Y-m-d', $php_date); }
        return $date->format('c');
    }

    // Overwrite me!
    // Return a local Model by id
    abstract protected function loadModelById($local_id);

    // Overwrite me!
    // Return a new local Model
    abstract protected function getNewModel();

    // Overwrite me!
    // Map the Connec resource attributes onto the Magento model
    abstract protected function mapConnecResourceToModel($resource_hash, &$model);

    // Overwrite me!
    // Map the Magento model to a Connec resource hash
    abstract protected function mapModelToConnecResource($model);

    // Overwrite me!
    // Optional: Match a local Model from hash attributes
    protected function matchLocalModel($resource_hash) {
        return null;
    }

    // Overwrite me!
    // Optional: Check the hash is valid for mapping, if false, resource is skipped
    protected function validate($resource_hash) {
        return true;
    }

    // Overwrite me!
    // Optional: Triggered after the save and before the creation of the mno_id_map
    protected function afterSaveConnecResource($resource_hash, $model, $oberverLock)
    {
        return;
    }

    public function getConnecResourceName() {
        return $this->connec_resource_name;
    }

    public function getLocalResourceName() {
        return $this->local_entity_name;
    }

    // Load a local Model by its Connec! id. If it does not exist locally, it is fetched from Connec! first
    public function loadModelByConnecId($entity_id) {
        Mage::log("Maestrano_Connec_Helper_BaseMappers::loadModelByConnecId entity_name=$this->connec_entity_name, entity_id=$entity_id");

        if(is_null($entity_id)) { return null; }

        $mno_id_map = Mage::getModel('connec/mnoidmap')->findMnoIdMapByMnoIdAndEntityName($entity_id, $this->connec_entity_name, $this->local_entity_name);
        if(!$mno_id_map) {
            // Entity does not exist locally, fetch it from Connec!
            return $this->fetchConnecResource($entity_id);
        } else {
            // Load existing entity
            return $this->loadModelById($mno_id_map['app_entity_id']);
        }
    }

    // Fetch and persist a Connec! resounce by id
    public function fetchConnecResource($entity_id) {
        Mage::log("Maestrano_Connec_Helper_BaseMappers::fetchConnecResource entity_name=$this->connec_entity_name, entity_id=$entity_id");

        $msg = $this->_connec_client->get("$this->connec_resource_endpoint/$entity_id");
        $code = $msg['code'];

        if($code != 200) {
            Mage::log("Maestrano_Connec_Helper_BaseMappers::fetchConnecResource cannot fetch Connec! entity code=$code, entity_name=$this->connec_entity_name, entity_id=$entity_id");
        } else {
            $result = json_decode($msg['body'], true);
            Mage::log("Maestrano_Connec_Helper_BaseMappers::fetchConnecResource processing entity_name=$this->connec_entity_name entity=". json_encode($result));
            return $this->saveConnecResource($result[$this->connec_resource_name], true, null, true, true);
        }
        return false;
    }

    // Persist a list of Connec Resources as Magento Models
    public function persistAll($resources_hash, $oberverLock=false) {
        Mage::log("Maestrano_Connec_Helper_BaseMappers::persistAll count: " . count($resources_hash));
        if(!is_null($resources_hash)) {
            foreach($resources_hash as $resource_hash) {
                try {
                    $this->saveConnecResource($resource_hash, true, null, true, $oberverLock);
                } catch (Exception $e) {
                    Mage::log("Maestrano_Connec_Helper_BaseMappers::persistAll Error when processing entity=".$this->connec_entity_name.", id=".$resource_hash['id'].", message=" . $e->getMessage());
                }
            }
        }
    }

    // Map a Connec Resource to an Magento Model
    public function saveConnecResource($resource_hash, $persist=true, $model=null, $retry=true, $oberverLock=false) {
        if(!$this->validate($resource_hash)) { return null; }

        Mage::log("Maestrano_Connec_Helper_BaseMappers::saveConnecResource entity=$this->connec_entity_name, hash=" . print_r($resource_hash, 1));

        // Load existing Model or create a new instance
        try {
            if(is_null($model)) {
                $model = $this->findOrInitializeModel($resource_hash);
                if(is_null($model)) {
                    Mage::log("Maestrano_Connec_Helper_BaseMappers::saveConnecResource model cannot be initialized and will not be saved");
                    return null;
                }
            }

            // Update the model attributes
            Mage::log("Maestrano_Connec_Helper_BaseMappers::saveConnecResource mapConnecResourceToModel entity=$this->connec_entity_name");
            $this->mapConnecResourceToModel($resource_hash, $model);

            // Save maestrano id
            if($persist) {
                Mage::log("Maestrano_Connec_Helper_BaseMappers::saveConnecResource save entity: $this->connec_entity_name, local model id: " . $model->getId());
                $model->setObserverLock($oberverLock);
                $model->save();
                $this->afterSaveConnecResource($resource_hash, $model, $oberverLock);
                $this->findOrCreateIdMap($resource_hash, $model);
            }

            return $model;
        } catch (Exception $e) {
            Mage::log("Maestrano_Connec_Helper_BaseMappers::saveConnecResource Error when saving Connec resource entity=".$this->connec_entity_name.", error=" . $e->getMessage());
            if($retry) {
                // Can fail due to concurrent persists using the same PK, so give it another chance
                Mage::log("Maestrano_Connec_Helper_BaseMappers::saveConnecResource retrying saveConnecResource entity=$this->connec_entity_name");
                return $this->saveConnecResource($resource_hash, $persist, $model, false);
            }
        }
        return null;
    }

    // Map a Connec Resource to an Magento Model
    public function findOrCreateIdMap($resource_hash, $model) {
        $local_id = $model->getId();
        Mage::log("Maestrano_Connec_Helper_BaseMappers::findOrCreateIdMap entity=$this->connec_entity_name, local_id=$local_id, entity_id=" .$resource_hash['id']);

        /** @var Maestrano_Connec_Model_Mnoidmap $mnoIdMapModel */
        $mnoIdMapModel = Mage::getModel('connec/mnoidmap');

        if($local_id == 0 || is_null($resource_hash['id'])) { return null; }

        $mno_id_map = $mnoIdMapModel->findMnoIdMapByLocalIdAndEntityName($local_id, $this->local_entity_name);
        if(!$mno_id_map) {
            Mage::log("Maestrano_Connec_Helper_BaseMappers::findOrCreateIdMap create MnoIdMap entity=$this->connec_entity_name, mno_id=" . $resource_hash['id'] . ", local_id=$local_id");
            return $mnoIdMapModel->addMnoIdMap($local_id, $this->local_entity_name, $resource_hash['id'], $this->connec_entity_name);
        }

        Mage::log("Maestrano_Connec_Helper_BaseMappers::findOrCreateIdMap found MnoIdMap entity=$this->connec_entity_name, mno_id=" . $resource_hash['id'] . ", local_id=$local_id");
        return $mno_id_map;
    }

    // Process a Model update event
    // $pushToConnec: option to notify Connec! of the model update
    // $delete:       option to soft delete the local entity mapping amd ignore further Connec! updates
    public function processLocalUpdate($model, $pushToConnec=true, $delete=false) {
        $pushToConnec = $pushToConnec && Maestrano::param('connec.enabled');
        Mage::log("Maestrano_Connec_Helper_BaseMappers::processLocalUpdate entity=$this->connec_entity_name, local_id=" . $model->getId() . ", pushToConnec=$pushToConnec, delete=$delete");

        if($pushToConnec) {
            $this->pushToConnec($model);
        }

        if($delete) {
            $this->flagAsDeleted($model);
        }
    }

    // Find an Magento entity matching the Connec resource or initialize a new one
    protected function findOrInitializeModel($resource_hash) {
        /** @var Maestrano_Connec_Model_Mnoidmap $mnoIdMapModel */
        $mnoIdMapModel = Mage::getModel('connec/mnoidmap');

        // Find mnoIdMapModel if exists
        $mno_id = $resource_hash['id'];
        $mno_id_map = $mnoIdMapModel->findMnoIdMapByMnoIdAndEntityName($mno_id, $this->connec_entity_name, $this->local_entity_name);

        if($mno_id_map) {
            Mage::log("Maestrano_Connec_Helper_BaseMappers::findOrInitializeModel found mno_id_map=" . print_r($mno_id_map, 1));

            // Ignore updates for deleted Models
            if($mno_id_map['deleted_flag'] == 1) {
                Mage::log("Maestrano_Connec_Helper_BaseMappers::findOrInitializeModel ignore update for locally deleted entity=$this->connec_entity_name, mno_id=$mno_id");
                return null;
            }

            // Load the locally mapped Model
            $model = $this->loadModelById($mno_id_map['app_entity_id']);
        } else {
            Mage::log("Maestrano_Connec_Helper_BaseMappers::findOrInitializeModel mno_id_map not found: return a new entity");

            // New empty model
            $model = $this->getNewModel();
        }

        return $model;
    }

    // Transform an Magento Model into a Connec Resource and push it to Connec
    protected function pushToConnec($model) {
        // Find local id
        $local_id = $model->getId();
        Mage::log("Maestrano_Connec_Helper_BaseMappers::pushToConnec entity=$this->connec_entity_name, local_id=$local_id");

        // Transform the Model into a Connec hash
        $resource_hash = $this->mapModelToConnecResource($model);
        if (empty($resource_hash)) {
            return;
        }

        $hash = array($this->connec_resource_name => $resource_hash);

        // Find Connec resource id
        $mno_id_map = Mage::getModel('connec/mnoidmap')->findMnoIdMapByLocalIdAndEntityName($local_id, $this->local_entity_name);
        if($mno_id_map) {
            // Update resource in connect
            $url = $this->connec_resource_endpoint . '/' . $mno_id_map['mno_entity_guid'];
            Mage::log("Maestrano_Connec_Helper_BaseMappers::pushToConnec updating entity=$this->local_entity_name, url=$url, id=$local_id hash=" . json_encode($hash));
            $response = $this->_connec_client->put($url, $hash);
        } else {
            // Create resource in connect
            $url = $this->connec_resource_endpoint;
            Mage::log("Maestrano_Connec_Helper_BaseMappers::pushToConnec creating entity=$this->local_entity_name, url=$url, hash=" . json_encode($hash));
            $response = $this->_connec_client->post($url, $hash);
        }

        // Process Connec response
        $code = $response['code'];
        $body = $response['body'];
        if($code >= 300) {
            Mage::log("Maestrano_Connec_Helper_BaseMappers::pushToConnec Cannot push to Connec! entity_name=$this->local_entity_name, code=$code, body=$body");
            return false;
        } else {
            Mage::log("Maestrano_Connec_Helper_BaseMappers::pushToConnec Processing Connec! response code=$code, body=$body");
            $result = json_decode($response['body'], true);
            $this->findOrCreateIdMap($result[$this->connec_resource_name], $model);
            //return $this->saveConnecResource($result[$this->connec_resource_name], true, $model);
        }
    }

    // Flag the local Model mapping as deleted to ignore further updates
    protected function flagAsDeleted($model) {
        Mage::log("Maestrano_Connec_Helper_BaseMappers::flagAsDeleted local_entity_name=$this->local_entity_name, local_id=" . $model->getId());

        /** @var Maestrano_Connec_Model_Mnoidmap $mnoIdMapModel */
        $mnoIdMapModel = Mage::getModel('connec/mnoidmap');

        $mno_id_map = $mnoIdMapModel->findMnoIdMapByLocalIdAndEntityName($model->getId(), $this->local_entity_name);
        $mno_id_map = $mnoIdMapModel->load($mno_id_map['mnoidmap_id']);
        $mno_id_map->setDeletedFlag(1);
        $mno_id_map->save();
    }



}
