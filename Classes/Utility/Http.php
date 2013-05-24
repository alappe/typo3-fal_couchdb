<?php
namespace Zimmer7\FalCouchdb\Utility;

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
 * Utility to handle HTTP methods…
 */
class Http {

	/**
	 * Issue a GET-Request and return the response or throw
	 * an exception.
	 *
	 * @param string $url
	 * @return string
	 */
	public static function get($url) {
		$curlHandler = curl_init($url);
		curl_setopt_array($curlHandler, array(
			CURLOPT_TIMEOUT => 5,
			CURLOPT_RETURNTRANSFER => TRUE
		));

		$response = curl_exec($curlHandler);
		$error = curl_error($curlHandler);

		if ($error !== '') {
			throw new \Exception($error);
		}

		return $response;
	}

	/**
	 * Issue a PUT-Request to the given URL with the given data
	 *
	 * @param string $url
	 * @param string $data
	 * @return string
	 */
	public static function put($url, $data) {
		$curlHandler = curl_init($url);
		curl_setopt_array($curlHandler, array(
			CURLOPT_TIMEOUT => 5,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_CUSTOMREQUEST => 'PUT',
			CURLOPT_HTTPHEADER => array('Content-type: application/json'),
			CURLOPT_POSTFIELDS => $data,
		));

		$response = curl_exec($curlHandler);
		$error = curl_error($curlHandler);

		if ($error !== '') {
			throw new \Exception($error);
		}

		return $response;
	}

	/**
	 * Issue a POST-Request to the given URL with the given data
	 *
	 * @param string $url
	 * @param string $data json-encoded
	 * @return string
	 */
	public static function post($url, $data) {
		$curlHandler = curl_init($url);
		curl_setopt_array($curlHandler, array(
			CURLOPT_TIMEOUT => 5,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_POST => TRUE,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => array('Content-type: application/json')
		));

		$response = curl_exec($curlHandler);
		$error = curl_error($curlHandler);

		if ($error !== '') {
			throw new \Exception($error);
		}

		return $response;
	}
}
?>