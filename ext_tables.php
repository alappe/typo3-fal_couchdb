<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['registeredDrivers']['fal_couchdb'] = array(
	'class' => 'Tx_FalCouchdb_Driver_CouchDB',
	'shortName' => 'FAL CouchDB',
	'flexFormDS' => 'FILE:EXT:fal_couchdb/Configuration/FlexForms/CouchDB.xml',
	'label' => 'CouchDB'
);

if (!is_array($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['tx_falcouchdb_cache'])) {
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['tx_falcouchdb_cache'] = array();
}
$TYPO3_CONF_VARS['FE']['eID_include']['falCouchdbClearRegistry'] = 'EXT:fal_couchdb/Classes/Ajax/ClearRegistry.php';

?>