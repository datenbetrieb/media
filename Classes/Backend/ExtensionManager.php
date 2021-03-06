<?php
namespace TYPO3\CMS\Media\Backend;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Fabien Udriot <fabien.udriot@ecodev.ch>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ExtensionManager {

	/**
	 * The extension key
	 *
	 * @var string
	 */
	protected $extKey = 'media';

	/**
	 * The Configuration Array
	 *
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $databaseHandler;

	/**
	 * Constructor
	 *
	 * @return \TYPO3\CMS\Media\Backend\ExtensionManager
	 */
	public function __construct() {

		// Load configuration
		$this->settings = \TYPO3\CMS\Media\Utility\Setting::getInstance()->getSettings();

		// Merge with Data that comes from the User
		$postData = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST();
		if (!empty($postData['data'])) {
			$this->settings = array_merge($this->settings, $postData['data']);
		}

		$this->databaseHandler = $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Display a message to the Extension Manager whether the configuration is OK or KO.
	 *
	 * @param array $params
	 * @param object $tsObj t3lib_tsStyleConfig
	 * @return string the HTML message
	 */
	public function renderMessage(&$params, &$tsObj) {
		$out = '';

		// display a message if DAM is detected
		if ($this->isDamDetected()) {
			$out .= '
				<div style="">
					<div class="typo3-message message-warning">
						<div class="message-header">
						I found tracks of DAM.
					</div>
						<div class="message-body">
							Would you like to migrate? There is an extension
							<a href="https://github.com/b13/t3ext-dam_falmigration" target="_blank">dam_falmigration</a>
							that can help importing DAM records into FAL.
						</div>
					</div>
				</div>
			';
		}

		// display a message if PHP EXIF extension is not installed
		if (! $this->hasExif()) {
			$out .= '
				<div style="">
					<div class="typo3-message message-warning">
						<div class="message-header">
						Missing EXIF PHP extension
					</div>
						<div class="message-body">
							The extension is not required but Media will just work better if the extension is installed.
						</div>
					</div>
				</div>
			';
		}


		// display a message if Extension is not fully installed
		if ($this->needsUpdate()) {
			$out .= '
			<div style="">
				<div class="typo3-message message-warning">
					<div class="message-header">'
						. $GLOBALS['LANG']->sL('LLL:EXT:media/Resources/Private/Language/locallang_extension_manager.xlf:updater_header') .
					'</div>
					<div class="message-body">
						' . $GLOBALS['LANG']->sL('LLL:EXT:media/Resources/Private/Language/locallang_extension_manager.xlf:updater_message') . '
					</div>
				</div>
			</div>
			';
		}
		else {
			$out .= '
			<div style="">
				<div class="typo3-message message-ok">
					<div class="message-header">'
						. $GLOBALS['LANG']->sL('LLL:EXT:media/Resources/Private/Language/locallang_extension_manager.xlf:ok_header') .
					'</div>
					<div class="message-body">
						' . $GLOBALS['LANG']->sL('LLL:EXT:media/Resources/Private/Language/locallang_extension_manager.xlf:ok_message') . '
					</div>
				</div>
			</div>
			';
		}

		return $out;
	}

	/**
	 * Check whether configuration is available
	 *
	 * @return boolean
	 */
	protected function hasExif() {
		return function_exists('exif_read_data');

	}

	/**
	 * Check whether configuration is available
	 *
	 * @return boolean
	 */
	protected function needsUpdate() {
		return empty($this->settings);
	}

	/**
	 * Tell whether previous installation of DAM is found
	 *
	 * @return boolean
	 */
	protected function isDamDetected() {
		$tables = $this->databaseHandler->admin_get_tables();
		return empty($tables['tx_dam']) ? FALSE : TRUE;
	}


	/**
	 * Render the storage list Field
	 *
	 * @return string
	 */
	public function renderStorage() {

		/** @var $storageRepository \TYPO3\CMS\Core\Resource\StorageRepository */
		$storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Resource\StorageRepository');
		$records = $storageRepository->findAll();

		$options = '';

		/** @var $record \TYPO3\CMS\Core\Resource\ResourceStorage */
		foreach ($records as $record) {
			$selected = '';

			if ($this->settings['storage'] == $record->getUid()) {
				$selected = 'selected="selected"';
			}
			$options .= '<option value="' . $record->getUid() . '" ' . $selected . '>' . $record->getName() . '</option>';
		}

		$output = <<<EOF
				<div class="typo3-tstemplate-ceditor-row" id="userTS-storage">
					<select type="text" name="tx_extensionmanager_tools_extensionmanagerextensionmanager[config][storage][value]">
						$options
					</select>
				</div>
EOF;
		return $output;
	}
}

?>