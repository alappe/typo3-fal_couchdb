<?php
namespace Zimmer7\FalCouchdb\Driver;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Andreas Lappe <nd@kaeufli.ch>, kaeufli.ch
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 *
 * @author Andreas Lappe <nd@kaeufli.ch>
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class CouchDB extends \TYPO3\CMS\Core\Resource\Driver\AbstractDriver {

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Core\Registry
	 */
	protected $registry;

	/**
	 * @var \TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend
	 */
	protected $cache;

	/**
	 * @var \Zimmer7\FalCouchdb\CouchDB
	 */
	protected $couchDB;

	protected $settings = array();

	/**
     * @var \TYPO3\CMS\Core\Log\Logger
	 */
	protected $logger;   

	/**
	 * Initialize…
	 *
	 */
	public function initialize() {
		$this->capabilities = \TYPO3\CMS\Core\Resource\ResourceStorage::CAPABILITY_BROWSABLE
			+ \TYPO3\CMS\Core\Resource\ResourceStorage::CAPABILITY_PUBLIC
			+ \TYPO3\CMS\Core\Resource\ResourceStorage::CAPABILITY_WRITABLE;

		// FIXME TODO
		$this->cache = $GLOBALS['typo3CacheManager']->getCache('tx_falcouchdb_cache');
		$this->couchDB = new \Zimmer7\FalCouchdb\CouchDB($this->configuration['uri'], $this->configuration['database'], $this->configuration['name'], $this->configuration['password']);
		$this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
	}

	/**
	 * Generic handler method for directory listings - gluing together the
	 * listing items is done
	 *
	 * @param string $path
	 * @param integer $start
	 * @param integer $numberOfItems
	 * @param array $filterMethods The filter methods used to filter the directory items
	 * @param string $itemHandlerMethod
	 * @param array $itemRows
	 * @return array
	 */
	protected function getDirectoryItemList($path, $start, $numberOfItems, array $filterMethods, $itemHandlerMethod, $itemRows = array()) {
		$folders = array();
		$files = array();
		$info = $this->getMetaData($path);

		foreach ($info as $row) {
			$folder = array(
				'ctime' => time(),
				'mtime' => time(),
				'name' => trim($row->value->name, '/'),
				'identifier' => $row->id
			);
			$folders[] = $folder;
		}
		return $folders;

		/*
		foreach($info['contents'] as $entry) {
			if($entry['is_dir']) {
				$folder['ctime'] = time();
				$folder['mtime'] = time();
				$folder['name'] = trim($entry['path'], '/');
				$folder['identifier'] = $entry['path'] . '/';
				$folder['storage'] = $this->storage->getUid();

				$folders[] = $folder;
			} else {
				$file['ctime'] = time();
				$file['mtime'] = time();
				$file['name'] = trim($entry['path'], '/');
				$file['identifier'] = $entry['path'];
				$file['storage'] = $this->storage->getUid();

				$files[] = $file;
			}
		}

		if($itemHandlerMethod == 'getFileList_itemCallback') {
			return $files;
		}
		if($itemHandlerMethod == 'getFolderList_itemCallback') {
			return $folders;
		}
		return array();
		 */
	}

	/**
	 * get file or folder informations from cache or directly from dropbox
	 *
	 * @param string $path Path to receive information from
	 * @param bool $list When set to true, this method returns information from all files in a directory. When set to false it will only return infromation from the specified directory.
	 * @param string $hash If a hash is supplied, this method simply returns true if nothing has changed since the last request. Good for caching.
	 * @param int $fileLimit Maximum number of file-information to receive
	 * @param string $root Use this to override the default root path (sandbox/dropbox)
	 * @return array file or folder informations
	 */
	public function getMetaData($path, $list = true, $hash = null, $fileLimit = null, $root = null) {
		/*
		$cacheKey = $this->getCacheIdentifierForPath($path);
		$info = $this->cache->get($cacheKey);
		if (empty($info)) {
			try{
			} catch(Exception $e) {
				$info = array();
			}
			$this->cache->set($cacheKey, $info);
		}
		*/
		return $this->couchDB->getMetaData($path, $list, $hash, $fileLimit, $root);
	}

	/**
	 * Checks if a configuration is valid for this driver.
	 * Throws an exception if a configuration will not work.
	 *
	 * @param array $configuration
	 * @return void
	 */
	static public function verifyConfiguration(array $configuration) {
	}

	/**
	 * processes the configuration, should be overridden by subclasses
	 *
	 * @return void
	 */
	public function processConfiguration() {
	}

	/**
	 * Returns the public URL to a file.
	 *
	 * @abstract
	 * @param \TYPO3\CMS\Core\Resource\ResourceInterface $resource
	 * @param bool  $relativeToCurrentScript    Determines whether the URL returned should be relative to the current script, in case it is relative at all (only for the LocalDriver)
	 * @return string
	 */
	public function getPublicUrl(\TYPO3\CMS\Core\Resource\ResourceInterface $resource, $relativeToCurrentScript = FALSE) {
	}

	/**
	 * Creates a (cryptographic) hash for a file.
	 *
	 * @abstract
	 * @param \TYPO3\CMS\Core\Resource\FileInterface $file
	 * @param string $hashAlgorithm The hash algorithm to use
	 * @return string
	 */
	public function hash(\TYPO3\CMS\Core\Resource\FileInterface $file, $hashAlgorithm) {
	}

	/**
	 * Creates a new file and returns the matching file object for it.
	 *
	 * @abstract
	 * @param string $fileName
	 * @param \TYPO3\CMS\Core\Resource\Folder $parentFolder
	 * @return \TYPO3\CMS\Core\Resource\File
	 */
	public function createFile($fileName, \TYPO3\CMS\Core\Resource\Folder $parentFolder) {
	}

	/**
	 * Returns the contents of a file. Beware that this requires to load the
	 * complete file into memory and also may require fetching the file from an
	 * external location. So this might be an expensive operation (both in terms
	 * of processing resources and money) for large files.
	 *
	 * @param \TYPO3\CMS\Core\Resource\FileInterface $file
	 * @return string The file contents
	 */
	public function getFileContents(\TYPO3\CMS\Core\Resource\FileInterface $file) {
	}

	/**
	 * Sets the contents of a file to the specified value.
	 *
	 * @param \TYPO3\CMS\Core\Resource\FileInterface $file
	 * @param string $contents
	 * @return integer The number of bytes written to the file
	 * @throws \RuntimeException if the operation failed
	 */
	public function setFileContents(\TYPO3\CMS\Core\Resource\FileInterface $file, $contents) {
	}

	/**
	 * Adds a file from the local server hard disk to a given path in TYPO3s virtual file system.
	 *
	 * This assumes that the local file exists, so no further check is done here!
	 *
	 * @param string $localFilePath
	 * @param \TYPO3\CMS\Core\Resource\Folder $targetFolder
	 * @param string $fileName The name to add the file under
	 * @param \TYPO3\CMS\Core\Resource\AbstractFile $updateFileObject Optional file object to update (instead of creating a new object). With this parameter, this function can be used to "populate" a dummy file object with a real file underneath.
	 * @return \TYPO3\CMS\Core\Resource\FileInterface
	 */
	public function addFile($localFilePath, \TYPO3\CMS\Core\Resource\Folder $targetFolder, $fileName, \TYPO3\CMS\Core\Resource\AbstractFile $updateFileObject = NULL) {
	}

	/**
	 * Checks if a resource exists - does not care for the type (file or folder).
	 *
	 * @param $identifier
	 * @return boolean
	 */
	public function resourceExists($identifier) {
	}

	/**
	 * Checks if a file exists.
	 *
	 * @abstract
	 * @param string $identifier
	 * @return boolean
	 */
	public function fileExists($identifier) {
	}

	/**
	 * Checks if a file inside a storage folder exists.
	 *
	 * @abstract
	 * @param string $fileName
	 * @param \TYPO3\CMS\Core\Resource\Folder $folder
	 * @return boolean
	 */
	public function fileExistsInFolder($fileName, \TYPO3\CMS\Core\Resource\Folder $folder) {
	}

	/**
	 * Returns a (local copy of) a file for processing it. When changing the
	 * file, you have to take care of replacing the current version yourself!
	 *
	 * @abstract
	 * @param \TYPO3\CMS\Core\Resource\FileInterface $file
	 * @param bool $writable Set this to FALSE if you only need the file for read operations. This might speed up things, e.g. by using a cached local version. Never modify the file if you have set this flag!
	 * @return string The path to the file on the local disk
	 */
	// TODO decide if this should return a file handle object
	public function getFileForLocalProcessing(\TYPO3\CMS\Core\Resource\FileInterface $file, $writable = TRUE) {
	}

	/**
	 * Returns the permissions of a file as an array (keys r, w) of boolean flags
	 *
	 * @abstract
	 * @param \TYPO3\CMS\Core\Resource\FileInterface $file
	 * @return array
	 */
	public function getFilePermissions(\TYPO3\CMS\Core\Resource\FileInterface $file) {
		return array('r' => TRUE, 'w' => TRUE);
	}

	/**
	 * Returns the permissions of a folder as an array (keys r, w) of boolean flags
	 *
	 * @abstract
	 * @param \TYPO3\CMS\Core\Resource\Folder $folder
	 * @return array
	 */
	public function getFolderPermissions(\TYPO3\CMS\Core\Resource\Folder $folder) {
		return array('r' => TRUE, 'w' => TRUE);
	}

	/**
	 * Renames a file
	 *
	 * @abstract
	 * @param \TYPO3\CMS\Core\Resource\FileInterface $file
	 * @param string $newName
	 * @return string The new identifier of the file if the operation succeeds
	 * @throws \RuntimeException if renaming the file failed
	 */
	public function renameFile(\TYPO3\CMS\Core\Resource\FileInterface $file, $newName) {
	}

	/**
	 * Replaces the contents (and file-specific metadata) of a file object with a local file.
	 *
	 * @abstract
	 * @param \TYPO3\CMS\Core\Resource\AbstractFile $file
	 * @param string $localFilePath
	 * @return boolean
	 */
	public function replaceFile(\TYPO3\CMS\Core\Resource\AbstractFile $file, $localFilePath) {
	}

	/**
	 * Returns information about a file for a given file identifier.
	 *
	 * @param string $identifier The (relative) path to the file.
	 * @return array
	 */
	public function getFileInfoByIdentifier($identifier) {
	}

	/**
	 * Returns a folder within the given folder. Use this method instead of doing your own string manipulation magic
	 * on the identifiers because non-hierarchical storages might fail otherwise.
	 *
	 * @param $name
	 * @param \TYPO3\CMS\Core\Resource\Folder $parentFolder
	 * @return \TYPO3\CMS\Core\Resource\Folder
	 */
	public function getFolderInFolder($name, \TYPO3\CMS\Core\Resource\Folder $parentFolder) {
	}

	/**
	 * Copies a file to a temporary path and returns that path.
	 *
	 * @abstract
	 * @param \TYPO3\CMS\Core\Resource\FileInterface $file
	 * @return string The temporary path
	 */
	public function copyFileToTemporaryPath(\TYPO3\CMS\Core\Resource\FileInterface $file) {
	}

	/**
	 * Moves a file *within* the current storage.
	 * Note that this is only about an intra-storage move action, where a file is just
	 * moved to another folder in the same storage.
	 *
	 * @param \TYPO3\CMS\Core\Resource\FileInterface $file
	 * @param \TYPO3\CMS\Core\Resource\Folder $targetFolder
	 * @param string $fileName
	 * @return string The new identifier of the file
	 */
	public function moveFileWithinStorage(\TYPO3\CMS\Core\Resource\FileInterface $file, \TYPO3\CMS\Core\Resource\Folder $targetFolder, $fileName) {
	}

	/**
	 * Copies a file *within* the current storage.
	 * Note that this is only about an intra-storage copy action, where a file is just
	 * copied to another folder in the same storage.
	 *
	 * @param \TYPO3\CMS\Core\Resource\FileInterface $file
	 * @param \TYPO3\CMS\Core\Resource\Folder $targetFolder
	 * @param string $fileName
	 * @return \TYPO3\CMS\Core\Resource\FileInterface The new (copied) file object.
	 */
	public function copyFileWithinStorage(\TYPO3\CMS\Core\Resource\FileInterface $file, \TYPO3\CMS\Core\Resource\Folder $targetFolder, $fileName) {
	}

	/**
	 * Folder equivalent to moveFileWithinStorage().
	 *
	 * @param \TYPO3\CMS\Core\Resource\Folder $folderToMove
	 * @param \TYPO3\CMS\Core\Resource\Folder $targetFolder
	 * @param string $newFolderName
	 * @return array A map of old to new file identifiers
	 */
	public function moveFolderWithinStorage(\TYPO3\CMS\Core\Resource\Folder $folderToMove, \TYPO3\CMS\Core\Resource\Folder $targetFolder, $newFolderName) {
	}

	/**
	 * Folder equivalent to copyFileWithinStorage().
	 *
	 * @param \TYPO3\CMS\Core\Resource\Folder $folderToCopy
	 * @param \TYPO3\CMS\Core\Resource\Folder $targetFolder
	 * @param string $newFileName
	 * @return boolean
	 */
	public function copyFolderWithinStorage(\TYPO3\CMS\Core\Resource\Folder $folderToCopy, \TYPO3\CMS\Core\Resource\Folder $targetFolder, $newFileName) {
	}

	/**
	 * Removes a file from this storage. This does not check if the file is
	 * still used or if it is a bad idea to delete it for some other reason
	 * this has to be taken care of in the upper layers (e.g. the Storage)!
	 *
	 * @abstract
	 * @param \TYPO3\CMS\Core\Resource\FileInterface $file
	 * @return boolean TRUE if deleting the file succeeded
	 */
	public function deleteFile(\TYPO3\CMS\Core\Resource\FileInterface $file) {
	}

	/**
	 * Removes a folder from this storage.
	 *
	 * @param \TYPO3\CMS\Core\Resource\Folder $folder
	 * @param boolean $deleteRecursively
	 * @return boolean
	 */
	public function deleteFolder(\TYPO3\CMS\Core\Resource\Folder $folder, $deleteRecursively = FALSE) {
	}

	/**
	 * Adds a file at the specified location. This should only be used internally.
	 *
	 * @abstract
	 * @param string $localFilePath
	 * @param \TYPO3\CMS\Core\Resource\Folder $targetFolder
	 * @param string $targetFileName
	 * @return string The new identifier of the file
	 */
	// TODO check if this is still necessary if we move more logic to the storage
	public function addFileRaw($localFilePath, \TYPO3\CMS\Core\Resource\Folder $targetFolder, $targetFileName) {
	}

	/**
	 * Deletes a file without access and usage checks.
	 * This should only be used internally.
	 *
	 * This accepts an identifier instead of an object because we might want to
	 * delete files that have no object associated with (or we don't want to
	 * create an object for) them - e.g. when moving a file to another storage.
	 *
	 * @abstract
	 * @param string $identifier
	 * @return boolean TRUE if removing the file succeeded
	 */
	public function deleteFileRaw($identifier) {
	}

	/*******************
	 * FOLDER FUNCTIONS
	 *******************/
	/**
	 * Returns the root level folder of the storage.
	 *
	 * @abstract
	 * @return \TYPO3\CMS\Core\Resource\Folder
	 */
	public function getRootLevelFolder() {
		return $this->getFolder('/');
	}

	/**
	 * Returns the default folder new files should be put into.
	 *
	 * @abstract
	 * @return \TYPO3\CMS\Core\Resource\Folder
	 */
	public function getDefaultFolder() {
	}

	/**
	 * Creates a folder.
	 *
	 * @param string $newFolderName
	 * @param \TYPO3\CMS\Core\Resource\Folder $parentFolder
	 * @return \TYPO3\CMS\Core\Resource\Folder The new (created) folder object
	 */
	public function createFolder($newFolderName, \TYPO3\CMS\Core\Resource\Folder $parentFolder) {
	}

	/**
	 * Checks if a folder exists
	 *
	 * @abstract
	 * @param string $identifier
	 * @return boolean
	 */
	public function folderExists($identifier) {
	}

	/**
	 * Checks if a file inside a storage folder exists.
	 *
	 * @abstract
	 * @param string $folderName
	 * @param \TYPO3\CMS\Core\Resource\Folder $folder
	 * @return boolean
	 */
	public function folderExistsInFolder($folderName, \TYPO3\CMS\Core\Resource\Folder $folder) {
	}

	/**
	 * Renames a folder in this storage.
	 *
	 * @param \TYPO3\CMS\Core\Resource\Folder $folder
	 * @param string $newName The target path (including the file name!)
	 * @return array A map of old to new file identifiers
	 * @throws \RuntimeException if renaming the folder failed
	 */
	public function renameFolder(\TYPO3\CMS\Core\Resource\Folder $folder, $newName) {
	}

	/**
	 * Checks if a given object or identifier is within a container, e.g. if
	 * a file or folder is within another folder.
	 * This can e.g. be used to check for webmounts.
	 *
	 * @abstract
	 * @param \TYPO3\CMS\Core\Resource\Folder $container
	 * @param mixed $content An object or an identifier to check
	 * @return boolean TRUE if $content is within $container
	 */
	public function isWithin(\TYPO3\CMS\Core\Resource\Folder $container, $content) {
	}

	/**
	 * Checks if a folder contains files and (if supported) other folders.
	 *
	 * @param \TYPO3\CMS\Core\Resource\Folder $folder
	 * @return boolean TRUE if there are no files and folders within $folder
	 */
	public function isFolderEmpty(\TYPO3\CMS\Core\Resource\Folder $folder) {
	}
}
?>