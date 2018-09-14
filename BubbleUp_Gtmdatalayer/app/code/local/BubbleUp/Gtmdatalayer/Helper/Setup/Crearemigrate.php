<?php

/*
    This helper is run during an upgrade to migrate from using CreareSEO's GTM Container code to the one that is part of this module.
    It works by duplicating all Creare GTM configs with a new "path" for the BubbleUp module. This process maintains the respective scopes of those configs, so it's multi-store-safe.
    After duplicating those entries, it disables GTM in scopes where it has been copied to the BubbleUp module. It leaves the container ID itself just in case.
    All of this also gets logged -- just in case something goes wrong and a rollback is needed.
*/
class BubbleUp_Gtmdatalayer_Helper_Setup_Crearemigrate extends Mage_Core_Helper_Abstract
{
    const LOG_FILE = 'bubbleup_gtmdatalayer_setup.log';
    
    public $configMap = [
        'enable' => [
            'old' => "creareseocore/google_tag_manager/enabled",
            'new' => BubbleUp_Gtmdatalayer_Helper_Config::XML_PATH_ENABLE_TAG, // Probably: google/gtmdatalayer/enable
        ],
        'container_id' => [
            'old' => "creareseocore/google_tag_manager/gtm_id",
            'new' => BubbleUp_Gtmdatalayer_Helper_Config::XML_PATH_CONTAINER_IDS // google/gtmdatalayer/gtm_container_ids
        ],
        'backwards_compat_webmasterei' => [ // Preserving backwards compatibility for the github contributor who was nice enough to add the container snippet itself to this module :)
            'old' => "google/gtmdatalayer/gtm_container_tag",
            'new' => BubbleUp_Gtmdatalayer_Helper_Config::XML_PATH_CONTAINER_IDS // google/gtmdatalayer/gtm_container_ids
        ]
    ];

    public function migrateFromCreareSeo()
    {
        // First we will duplicate the configs for all stores that are set to use Creare...
        foreach ($this->configMap as $type=>$map) {
            $this->log("Migrating the configuration value for the {$type} config.");
            try {
                $this->duplicateConfigs($map['old'], $map['new']);
            } catch (Exception $e) {
                $this->log("Failed to duplicate configs. Exception was:{$e->getMessage()}");
            }
        }

        // Then we must disable creare_seo in scopes that now use the BubbleUp_Gtmdatalayer implementation.
        $enable = $this->configMap['enable']; // Which configs are used to indicate the enable/disable for the container
        $this->disableCreareGtmWhereMigrated($enable['old'], $enable['new']);
    }

    public function disableCreareGtmWhereMigrated($oldPath, $newPath)
    {
        $queryParams = [
            'newPath' => $newPath,
            'oldPath' => $oldPath
        ];

        $r = Mage::getSingleton('core/resource');
        $db = $r->getConnection('core_write');
        $core_config_data = $r->getTableName('core/config_data');


        $this->log("Preparing to disable CreareSEO GTM implementation.");
        $sql = <<<SQL
UPDATE `$core_config_data` d
INNER JOIN (SELECT scope, scope_id FROM `$core_config_data` WHERE path=:newPath AND value=1) as enabled_scopes
ON d.scope=enabled_scopes.scope AND d.scope_id=enabled_scopes.scope_id
AND d.path=:oldPath
SET d.value=0
SQL;
        
        $result = $db->query($sql, $queryParams);

        $this->log("{$result->rowCount()} rows were affected by the query: {$sql} with params".print_r($queryParams, true));
    }

    public function duplicateConfigs($oldPath, $newPath)
    {
        $queryParams = [
            'newPath' => $newPath,
            'oldPath' => $oldPath
        ];

        $r = Mage::getSingleton('core/resource');
        $db = $r->getConnection('core_write');

        $core_config_data = $r->getTableName('core/config_data');

        $selectQuery = "SELECT scope, scope_id, :newPath, value FROM core_config_data WHERE path=:oldPath";

        $toCopy = $db->fetchAll($selectQuery, $queryParams);
        $this->log("Preparing to duplicate ".count($toCopy)." config values from {$oldPath} to apply to path {$newPath}:".print_r($toCopy, true));

        $sql = "INSERT IGNORE INTO $core_config_data (scope, scope_id, path, value) {$selectQuery}";

        $result = $db->query($sql, $queryParams);

        $this->log("{$result->rowCount()} rows were affected by the query: {$sql} with params".print_r($queryParams, true));
    }

    public function log($msg)
    {
        return Mage::log($msg, null, static::LOG_FILE, true);
    }
}
