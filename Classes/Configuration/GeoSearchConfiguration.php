<?php
namespace TYPO3\Solrgeo\Configuration;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Phuong Doan <phuong.doan@dkd.de>, dkd Internet Service GmbH
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
 * @package solrgeo
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */


class GeoSearchConfiguration {

	/**
	 * The Default Adapter
	 */
	const DEFAULT_ADAPTER = 'CurlHttpAdapter';

	/**
	 * The Default Provider
	 */
	const DEFAULT_PROVIDER = 'GoogleMapsProvider';

	/**
	 * @var \tx_solr_Site
	 */
	protected $site;

	/**
	 * @var array
	 */
	protected $siteConfiguration = array();

	/**
	 * @var array
	 * */
	private $supportedAdapter = array(
		'buzzhttpadapter',
		'curlhttpadapter',
		'guzzlehttpadapter',
		'sockethttpadapter',
		'zendhttpadapter'
	);

	/**
	 * @var array
	 * */
	private $supportedProvider = array(
		'googlemapsprovider'
	);

	/**
	 * @var \Geocoder\HttpAdapter\HttpAdapterInterface
	*/
	private $adapter;

	/**
	 * @var \Geocoder\Provider\ProviderInterface
	 */
	private $provider;

	/**
	 * @var array
	 * */
	private $locationList = array();


	/**
	 * @param \tx_solr_Site $site
	 */
	public function __construct(\tx_solr_Site $site) {
		$this->site = $site;
		// Get the configuration of EXT:solr
		//$this->siteConfiguration = $site->getSolrConfiguration();
		$this->setAdapter();
		$this->setProvider();
		//$this->setLocationList();
	}

	/**
	 * Sets the configuration of given plugin
	 */
	public function setConfiguration(array $config) {
		$this->siteConfiguration = $config;
	}

	/**
	 * Sets the adapter
	 */
	private function setAdapter() {

		$adapterName = self::DEFAULT_ADAPTER;

		if(isset($this->siteConfiguration['index.']['adapter'])) {
			$adapterName = strtolower($this->siteConfiguration['index.']['adapter']);
			if(!in_array($adapterName, $this->supportedAdapter)) {
				$adapterName = self::DEFAULT_ADAPTER;
			}
		}

		switch ($adapterName) {
			case 'buzzhttpadapter':
				$adapter = new \Geocoder\HttpAdapter\BuzzHttpAdapter();
				break;
			case 'guzzlehttpadapter':
				$adapter = new \Geocoder\HttpAdapter\GuzzleHttpAdapter();
				break;
			case 'sockethttpadapter':
				$adapter = new \Geocoder\HttpAdapter\SocketHttpAdapter();
				break;
			case 'zendhttpadapter':
				$adapter = new \Geocoder\HttpAdapter\ZendHttpAdapter();
				break;
			case 'curlhttpadapter':
			default:
				$adapter  = new \Geocoder\HttpAdapter\CurlHttpAdapter();
				break;
		}

		$this->adapter = $adapter;
	}

	/**
	 *
	 * @return \Geocoder\HttpAdapter\HttpAdapterInterface Returns the Adapter
	 */
	public function getAdapter() {
		return $this->adapter;
	}

	/**
	 * Sets the provider
	 */
	private function setProvider() {
		// Default Provider
		$providerName = self::DEFAULT_PROVIDER;
		$provider = null;


		if(!empty($this->siteConfiguration['index.']['provider.'])) {
			$providerName = strtolower($this->siteConfiguration['index.']['provider.']['name']);
			if(!in_array($providerName, $this->supportedProvider)) {
				$providerName = self::DEFAULT_PROVIDER;
			}
		}

		switch ($providerName) {
			case 'googlemapsprovider';
			default:
				$locale = (isset($this->siteConfiguration['index.']['provider.']['locale'])) ?
					$this->siteConfiguration['index.']['provider.']['locale'] : null;
				$region = (isset($this->siteConfiguration['index.']['provider.']['region'])) ?
					$this->siteConfiguration['index.']['provider.']['region'] : null;
				$useSsl = false;
				if(isset($this->siteConfiguration['index.']['provider.']['useSsl'])) {
					if($this->siteConfiguration['index.']['provider.']['useSsl'] == '1') {
						$useSsl = true;
					}
				}

				$provider = new \Geocoder\Provider\GoogleMapsProvider($this->getAdapter(), $locale, $region, $useSsl);
				break;
		}

		$this->provider = $provider;
	}

	/**
	 *
	 * @return \Geocoder\Provider\ProviderInterface Returns the Provider
	 */
	public function getProvider() {
		return $this->provider;
	}

	/**
	 * Save the defined location configured with Typoscript.
	 * Required values are the uid of a page and the city.
	 * Example:
	 * 	location {
	 * 		1 {
	 * 			uid = 6, 51
	 *			city = Frankfurt
	 *			address = Kaiserstraße 73
	 * 			zip = 60329
	 * 			geolocation = 50.1077219, 8.666562
	 *		}
	 * 	}
	 */
	public function setLocationList() {
		if(!empty($this->siteConfiguration['index.']['location.'])) {
			$locationDefiniton = $this->siteConfiguration['index.']['location.'];
			$locationList = array();
			foreach ($locationDefiniton as $location) {
				$uidList = array();
				$city = "";
				$address = "";
				$zip = "";
				$geolocation = "";
				foreach ($location as $key => $value) {
					switch ($key) {
						case 'uid':
							$uidList = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $value);
							break;
						case 'city':
							$city = trim($value);
							break;
						case 'address':
							$address = trim($value);
							break;
						case 'zip':
							$zip = trim($value);
							break;
						case 'geolocation':
							$geolocation = trim($value);
							break;
					}
				}
				foreach($uidList as $uid) {
					$location = array();
					$location['uid'] = $uid;
					$location['city'] = $city;
					$location['zip'] = $zip;
					$location['address'] = $address;
					$location['geolocation'] = $geolocation;
					$locationList[] = $location;
				}
			}
			$this->locationList = $locationList;
		}

	}

	/**
	 * @return array Array contains the defined location to add to Solrdocument
	 */
	public function getLocationList() {
		return $this->locationList;
	}

}