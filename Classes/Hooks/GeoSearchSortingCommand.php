<?php
namespace TYPO3\Solrgeo\Hooks;

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
 *
 * @package solrgeo
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class GeoSearchSortingCommand extends \Tx_Solr_PiResults_SortingCommand {

	protected function getSortingLinks() {
		$helper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\Solrgeo\\Utility\\Helper');
		$settings = $helper->getConfiguration('tx_solrgeo');

		$sortOptions = parent::getSortingLinks();
		foreach($sortOptions as $k => $sortOption) {
			foreach($sortOption as $key => $value) {
				if($key == 'optionName' && $value == 'geosearch') {
					if(!empty($settings['search.']['targetPageId'])) {
						$tmp = $sortOption;
						unset($sortOptions[$k]);
						$query = $this->search->getQuery();
						//$helper->setQuery($query);
						$tmp['link'] = '<a href="http://'.$helper->getDomain().'/
											?id='.$settings['search.']['targetPageId'].'&tx_solrgeo_search[q]='.$query->getKeywordsRaw().'">'.$tmp['label'].'</a>';
						$sortOptions[] = $tmp;
					}
				}
			}
		}

		return $sortOptions;
	}


} 