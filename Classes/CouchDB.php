<?php
namespace Zimmer7\FalCouchdb;

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
 * @author Stefan Isak <mail@stefanisak.com>
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class CouchDB {

	/**
	 * @var \string 
	 */
	protected $uri;

	/**
	 * @var \string
	 */
	protected $database;

	/**
	 * @var \string
	 */
	protected $name;

	/**
	 * @var \string
	 */
	protected $password;

	/**
	 * Constructor
	 */
	public function __construct($uri, $database, $name, $password) {
		$this->uri = $uri;
		$this->database = $database;
		$this->password = $password;
		$this->name = $name;
	}

	
	/**
	 * Get meta data
	 *
	 * @param string $path Path to receive information from
	 * @param bool $list When set to true, this method returns information from all files in a directory. When set to false it will only return information from the specified directory.
	 * @param string $hash If a hash is supplied, this method simply returns true if nothing has changed since the last request. Good for caching.
	 * @param int $fileLimit Maximum number of file-information to receive
	 * @param string $root Use this to override the default root path (sandbox/dropbox)
	 * @return array file or folder informations
	 */
	public function getMetaData($path, $list = true, $hash = null, $fileLimit = null, $root = null) {
		$folders = array();
		$files = array();
		try {
			$response = Utility\Http::get($this->getUrl($path));
			$response = json_decode($response);
		} catch (Exception $e) {
			throw $e;
		}

		return $response->rows;
	}

	/**
	 * Get url
	 *
	 * @param \string $path
	 * @return \string
	 */
	private function getUrl($path) {
		$url = '';

		$url .= $this->uri . '/' . $this->database . '/_design/directories/_view/list3?key="' . $path . '"';

		if (!empty($this->name) && !empty($this->password)) {
			$url .= str_replace('://', '://' . $this->name . ':' . $this->password . '@');
		}

		return $url;
	}

}
?>