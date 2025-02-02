<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2015 Rene Nitzsche <rene@system25.de>
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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

tx_rnbase::load('Tx_Rnbase_Domain_Model_Data');
tx_rnbase::load('Tx_Rnbase_Domain_Model_DomainInterface');
tx_rnbase::load('Tx_Rnbase_Domain_Model_DynamicTableInterface');
tx_rnbase::load('Tx_Rnbase_Domain_Model_RecordInterface');
tx_rnbase::load('tx_rnbase_util_TCA');

/**
 * Basisklasse für die meisten Model-Klassen.
 * Sie stellt einen Konstruktor bereit,
 * der sowohl mit einer UID als auch mit einem Datensatz aufgerufen werden kann.
 * Die Daten werden in den Instanzvariablen $uid und $record abgelegt.
 * Der Umfang von $record kann aber je nach Aufruf unterschiedlich sein!
 *
 * @package TYPO3
 * @subpackage rn_base
 * @author René Nitzsche
 * @author Michael Wagner
 */
class Tx_Rnbase_Domain_Model_Base
	extends Tx_Rnbase_Domain_Model_Data
	implements Tx_Rnbase_Domain_Model_DomainInterface, Tx_Rnbase_Domain_Model_DynamicTableInterface, Tx_Rnbase_Domain_Model_RecordInterface
{
	/**
	 * @var int $uid
	 */
	private $uid;

	/**
	 *
	 * @var string|0
	 */
	private $tableName = 0;

	/**
	 * Most model-classes will be initialized by a uid or a database record. So
	 * this is a common contructor.
	 * Ensure to overwrite getTableName()!
	 *
	 * @param mixed $rowOrUid
	 * @return NULL
	 */
	function __construct($rowOrUid = NULL) {
		return $this->init($rowOrUid);
	}

	/**
	 * Inits the model instance either with uid or a complete data record.
	 * As the result the instance should be completly loaded.
	 *
	 * @param mixed $rowOrUid
	 * @return NULL
	 */
	function init($rowOrUid = NULL) {
		if (is_array($rowOrUid)) {
			parent::init($rowOrUid);
			$this->uid = $this->getProperty('uid');
		}
		else {
			$rowOrUid = (int) $rowOrUid;
			$this->uid = $rowOrUid;
			if ($rowOrUid === 0) {
				parent::init(array());
			} elseif($this->getTableName()) {
				$this->loadRecord();
			}
		}

		// set the modified state to clean
		$this->resetCleanState();

		return NULL;
	}

	/**
	 * loads the record to the model by its uid.
	 *
	 * @return void
	 */
	protected function loadRecord() {

		$options = array();
		if (
			is_object($GLOBALS['BE_USER'])
			&& $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['loadHiddenObjects']
		) {
			$options['enablefieldsbe'] = 1;
		}

		tx_rnbase::load('Tx_Rnbase_Database_Connection');
		$db = Tx_Rnbase_Database_Connection::getInstance();
		$record = $db->getRecord(
			$this->getTableName(),
			$this->uid,
			$options
		);

		$this->setProperty($record);
	}

	/**
	 * Returns the records uid
	 *
	 * @return int
	 */
	function getUid() {
		$uid = 0;
		$tableName = $this->getTableName();
		if (!empty($tableName)) {
			// Take care for localized records where uid of original record
			// is stored in $record['l18n_parent'] instead of $record['uid']!
			$languageParentField = tx_rnbase_util_TCA::getTransOrigPointerFieldForTable($tableName);
			$sysLanguageUidField = tx_rnbase_util_TCA::getLanguageFieldForTable($tableName);
			if (
				!(
					empty($languageParentField)
					&& empty($sysLanguageUidField)
					&& ($this->isPropertyEmpty($sysLanguageUidField))
					&& ($this->isPropertyEmpty($languageParentField))
				)
			) {
				$uid = (int) $this->getProperty($languageParentField);
			}
		}
		return $uid > 0 ? $uid : (int) $this->uid;
	}

	/**
	 * Returns the label of the record, defined in the tca.
	 *
	 * @return int
	 */
	public function getTcaLabel() {
		$label = '';
		$tableName = $this->getTableName();
		if (!empty($tableName)) {
			$labelField = tx_rnbase_util_TCA::getLabelFieldForTable($tableName);
			if (!$this->isPropertyEmpty($labelField)) {
				$label = (string) $this->getProperty($labelField);
			}
		}
		return $label;
	}
	/**
	 * Returns the Language id of the record.
	 *
	 * @return int
	 */
	public function getSysLanguageUid() {
		$uid = 0;
		$tableName = $this->getTableName();
		if (!empty($tableName)) {
			$sysLanguageUidField = tx_rnbase_util_TCA::getLanguageFieldForTable($tableName);
			if (!$this->isPropertyEmpty($sysLanguageUidField)) {
				$uid = (int) $this->getProperty($sysLanguageUidField);
			}
		}
		return $uid;
	}

	/**
	 * Returns the creation date of the record as DateTime object.
	 *
	 * @param DateTimeZone $timezone
	 * @return DateTime
	 */
	public function getCreationDateTime($timezone = NULL) {
		$datetime = NULL;
		$tableName = $this->getTableName();
		if (!empty($tableName)) {
			$field = tx_rnbase_util_TCA::getCrdateFieldForTable($tableName);
			if (!$this->isPropertyEmpty($field)) {
				$tstamp = (int) $this->getProperty($field);
				tx_rnbase::load('tx_rnbase_util_Dates');
				$datetime = tx_rnbase_util_Dates::getDateTime('@' . $tstamp);
			}
		}

		return $datetime;
	}

	/**
	 * Returns the creation date of the record as DateTime object.
	 *
	 * @param DateTimeZone $timezone
	 * @return DateTime
	 */
	public function getLastModifyDateTime($timezone = NULL) {
		$datetime = NULL;
		$tableName = $this->getTableName();
		if (!empty($tableName)) {
			$field = tx_rnbase_util_TCA::getTstampFieldForTable($tableName);
			if (!$this->isPropertyEmpty($field)) {
				$tstamp = (int) $this->getProperty($field);
				tx_rnbase::load('tx_rnbase_util_Dates');
				$datetime = tx_rnbase_util_Dates::getDateTime('@' . $tstamp);
			}
		}

		return $datetime;
	}

	/**
	 * Reload this records from database
	 *
	 * @return Tx_Rnbase_Domain_Model_Base
	 */
	public function reset() {

		$this->loadRecord();

		// set the modified state to clean
		$this->resetCleanState();

		return $this;
	}
	/**
	 * Liefert den aktuellen Tabellenname
	 *
	 * @return Tabellenname als String
	 */
	public function getTableName() {
		return $this->tableName;
	}

	/**
	 * Setzt den aktuellen Tabellenname
	 *
	 * @param string $tableName
	 * @return Tx_Rnbase_Domain_Model_Base
	 */
	public function setTableName($tableName = 0) {
		$this->tableName = $tableName;
		return $this;
	}

	/**
	 * Check if this record is valid.
	 * If FALSE, the record is maybe deleted in database.
	 *
	 * @return boolean
	 */
	public function isValid() {
		$record = $this->getProperty();
		return !empty($record);
	}

	/**
	 * Check if record is persisted in database. This is if uid is not 0.
	 *
	 * @return boolean
	 */
	public function isPersisted() {
		return $this->getUid() > 0;
	}

	/**
	 * validates the data of a model with the tca definition of a its table.
	 *
	 * @param array $options
	 *     only_record_fields: validates only fields included in the record (default)
	 * @return bolean
	 */
	public function validateProperties($options = NULL) {
		return tx_rnbase_util_TCA::validateModel(
			$this,
			$options === NULL ? array('only_record_fields' => TRUE) : $options
		);
	}

	/**
	 * Ist der Datensatz als gelöscht markiert?
	 * Wenn es keine Spalte oder TCA gibt, is es nie gelöscht!
	 *
	 * @return boolean
	 */
	public function isDeleted() {
		$tableName = $this->getTableName();
		$field = empty($GLOBALS['TCA'][$tableName]['ctrl']['delete'])
			? 'deleted'
			: $GLOBALS['TCA'][$tableName]['ctrl']['delete']
		;
		$value = $this->hasProperty($field) ? (int) $this->getProperty($field) : 0;

		return $value > 0;
	}

	/**
	 * Ist der Datensatz als gelöscht markiert?
	 * Wenn es keine Spalte oder TCA gibt, is es nie gelöscht!
	 *
	 * @return boolean
	 */
	public function isHidden() {
		$tableName = $this->getTableName();
		$field = empty($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['disabled'])
			? 'hidden'
			: $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['disabled']
		;
		$value = $this->hasProperty($field) ? (int) $this->getProperty($field) : 0;

		return $value > 0;
	}

	/**
	 * Returns the record
	 *
	 * @return array
	 */
	function getRecord() {
		return $this->getProperty();
	}

	/**
	 * Liefert bei Tabellen, die im $TCA definiert sind,
	 * die Namen der Tabellenspalten als Array.
	 *
	 * @return array mit Spaltennamen oder 0
	 */
	public function getColumnNames() {
		$columns = $this->getTCAColumns();
		return is_array($columns) ? array_keys($columns) : 0;
	}

	/**
	 * Liefert die TCA-Definition der in der Tabelle definierten Spalten
	 *
	 * @return array
	 */
	public function getTcaColumns() {
		$columns = tx_rnbase_util_TCA::getTcaColumns($this->getTableName());
		return empty($columns) ? 0 : $columns;
	}

}
