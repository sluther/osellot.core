<?php
class DAO_Gateway extends C4_ORMHelper {
	const ID = 'id';
	const EXTENSION_ID = 'extension_id';
	const NAME = 'name';
	const ENABLED = 'enabled';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "INSERT INTO gateway () VALUES ()";
		$db->Execute($sql);
		$id = $db->LastInsertId();
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'gateway', $fields);
	}
	
	static function updateWhere($fields, $where) {
		parent::_updateWhere('gateway', $fields, $where);
	}
	
	/**
	 * @param string $where
	 * @param mixed $sortBy
	 * @param mixed $sortAsc
	 * @param integer $limit
	 * @return Model_Gateway[]
	 */
	static function getWhere($where=null, $sortBy=null, $sortAsc=true, $limit=null) {
		$db = DevblocksPlatform::getDatabaseService();

		list($where_sql, $sort_sql, $limit_sql) = self::_getWhereSQL($where, $sortBy, $sortAsc, $limit);
		
		// SQL
		$sql = "SELECT id, extension_id, name, enabled ".
			"FROM gateway ".
			$where_sql.
			$sort_sql.
			$limit_sql
		;
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_Gateway[]
	 */
	static function get($id) {
		$objects = self::getWhere(sprintf("%s = %d",
			self::ID,
			$id
		));
		
		if(isset($objects[$id]))
			return $objects[$id];
		
		return null;
	}
	
	/**
	* @return Model_Gateway[]
	*/
	static function getAll() {
		$db = DevblocksPlatform::getDatabaseService();
		$objects = self::getWhere();
	
		if(count($objects))
			return $objects;
	
		return null;
	}
	
	/**
	* @return Model_Gateway[]
	*/
	static function getEnabled() {
		$db = DevblocksPlatform::getDatabaseService();
			$objects = self::getWhere(sprintf("%s = %s",
			self::ENABLED,
			1
		));
		
		if(count($objects))
			return $objects;
	
		return null;
	}
	
	/**
	 * @param resource $rs
	 * @return Model_Gateway[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_Gateway();
			$object->id = $row['id'];
			$object->extension_id = $row['extension_id'];
			$object->name = $row['name'];
			$object->enabled = $row['enabled'];

			$objects[$object->id] = $object;
		}
		
		mysql_free_result($rs);
		
		return $objects;
	}
	
	static function delete($ids) {
		if(!is_array($ids)) $ids = array($ids);
		$db = DevblocksPlatform::getDatabaseService();
		
		if(empty($ids))
			return;
		
		$ids_list = implode(',', $ids);
		
		$db->Execute(sprintf("DELETE FROM gateway WHERE id IN (%s)", $ids_list));
		
		return true;
	}
	
	public static function getSearchQueryComponents($columns, $params, $sortBy=null, $sortAsc=null) {
		$fields = SearchFields_Gateway::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);
		
		$select_sql = sprintf("SELECT ".
			"gateway.id as %s, ".
			"gateway.extension_id as %s, ".
			"gateway.name as %s, ".
			"gateway.enabled as %s ",
				SearchFields_Gateway::ID,
				SearchFields_Gateway::EXTENSION_ID,
				SearchFields_Gateway::NAME,
				SearchFields_Gateway::ENABLED

			);
			
		$join_sql = "FROM gateway ";
		
		// Custom field joins
		//list($select_sql, $join_sql, $has_multiple_values) = self::_appendSelectJoinSqlForCustomFieldTables(
		//	$tables,
		//	$params,
		//	'gateway.id',
		//	$select_sql,
		//	$join_sql
		//);
		$has_multiple_values = false; // [TODO] Temporary when custom fields disabled
				
		$where_sql = "".
			(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "WHERE 1 ");
			
		$sort_sql = (!empty($sortBy)) ? sprintf("ORDER BY %s %s ",$sortBy,($sortAsc || is_null($sortAsc))?"ASC":"DESC") : " ";
	
		return array(
			'primary_table' => 'gateway',
			'select' => $select_sql,
			'join' => $join_sql,
			'where' => $where_sql,
			'has_multiple_values' => $has_multiple_values,
			'sort' => $sort_sql,
		);
	}
	
    /**
     * Enter description here...
     *
     * @param array $columns
     * @param DevblocksSearchCriteria[] $params
     * @param integer $limit
     * @param integer $page
     * @param string $sortBy
     * @param boolean $sortAsc
     * @param boolean $withCounts
     * @return array
     */
    static function search($columns, $params, $limit=10, $page=0, $sortBy=null, $sortAsc=null, $withCounts=true) {
		$db = DevblocksPlatform::getDatabaseService();
		
		// Build search queries
		$query_parts = self::getSearchQueryComponents($columns,$params,$sortBy,$sortAsc);

		$select_sql = $query_parts['select'];
		$join_sql = $query_parts['join'];
		$where_sql = $query_parts['where'];
		$has_multiple_values = $query_parts['has_multiple_values'];
		$sort_sql = $query_parts['sort'];
		
		$sql = 
			$select_sql.
			$join_sql.
			$where_sql.
			($has_multiple_values ? 'GROUP BY gateway.id .' : '').
			$sort_sql;
		if($limit > 0) {
    		$rs = $db->SelectLimit($sql,$limit,$page*$limit) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		} else {
		    $rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
            $total = mysql_num_rows($rs);
		}
		
		$results = array();
		$total = -1;
		
		while($row = mysql_fetch_assoc($rs)) {
			$result = array();
			foreach($row as $f => $v) {
				$result[$f] = $v;
			}
			$object_id = intval($row[SearchFields_Gateway::ID]);
			$results[$object_id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
			$count_sql = 
				($has_multiple_values ? "SELECT COUNT(DISTINCT gateway.id') " : "SELECT COUNT(gateway.id) ").
				$join_sql.
				$where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
	}

};

class SearchFields_Gateway implements IDevblocksSearchFields {
	const ID = 'g_id';
	const EXTENSION_ID = 'g_extension_id';
	const NAME = 'g_name';
	const ENABLED = 'g_enabled';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'gateway', 'id', $translate->_('gateway.id')),
			self::EXTENSION_ID => new DevblocksSearchField(self::EXTENSION_ID, 'gateway', 'extension_id', $translate->_('gateway.extension_id')),
			self::NAME => new DevblocksSearchField(self::NAME, 'gateway', 'name', $translate->_('gateway.name')),
			self::ENABLED => new DevblocksSearchField(self::ENABLED, 'gateway', 'enabled', $translate->_('gateway.enabled')),
		);
		
		// Custom Fields
		//$fields = DAO_CustomField::getByContext(CerberusContexts::XXX);

		//if(is_array($fields))
		//foreach($fields as $field_id => $field) {
		//	$key = 'cf_'.$field_id;
		//	$columns[$key] = new DevblocksSearchField($key,$key,'field_value',$field->name);
		//}
		
		// Sort by label (translation-conscious)
		uasort($columns, create_function('$a, $b', "return strcasecmp(\$a->db_label,\$b->db_label);\n"));

		return $columns;		
	}
};

class Model_Gateway {
	public $id;
	public $extension_id;
	public $name;
	public $enabled;
};

class View_Gateway extends C4_AbstractView {
	const DEFAULT_ID = 'gateway';

	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
	
		$this->id = self::DEFAULT_ID;
		// [TODO] Name the worklist view
		$this->name = $translate->_('Gateway');
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_Gateway::ID;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_Gateway::ID,
			SearchFields_Gateway::EXTENSION_ID,
			SearchFields_Gateway::NAME,
			SearchFields_Gateway::ENABLED,

		);
		// [TODO] Filter fields
		$this->addColumnsHidden(array(
		));
		
		// [TODO] Filter fields
		$this->addParamsHidden(array(
		));
		
		$this->doResetCriteria();
	}

	function getData() {
		$objects = DAO_Gateway::search(
			$this->view_columns,
			$this->getParams(),
			$this->renderLimit,
			$this->renderPage,
			$this->renderSortBy,
			$this->renderSortAsc,
			$this->renderTotal
		);
		return $objects;
	}
	
	function getDataSample($size) {
		return $this->_doGetDataSample('DAO_Gateway', $size);
	}

	function render() {
		$this->_sanitize();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);
		$tpl->assign('view', $this);

		// Custom fields
		//$custom_fields = DAO_CustomField::getByContext(CerberusContexts::XXX);
		//$tpl->assign('custom_fields', $custom_fields);

		// [TODO] Set your template path
		$tpl->display('devblocks:osellot.core::configuration/section/gateway/view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		// [TODO] Move the fields into the proper data type
		switch($field) {
			case SearchFields_Gateway::ID:
			case SearchFields_Gateway::EXTENSION_ID:
			case SearchFields_Gateway::NAME:
			case SearchFields_Gateway::ENABLED:

			case 'placeholder_string':
				$tpl->display('devblocks:osellot.core::internal/views/criteria/__string.tpl');
				break;
			case 'placeholder_number':
				$tpl->display('devblocks:osellot.core::internal/views/criteria/__number.tpl');
				break;
			case 'placeholder_bool':
				$tpl->display('devblocks:osellot.core::internal/views/criteria/__bool.tpl');
				break;
			case 'placeholder_date':
				$tpl->display('devblocks:osellot.core::internal/views/criteria/__date.tpl');
				break;
			/*
			default:
				// Custom Fields
				if('cf_' == substr($field,0,3)) {
					$this->_renderCriteriaCustomField($tpl, substr($field,3));
				} else {
					echo ' ';
				}
				break;
			*/
		}
	}

	function renderCriteriaParam($param) {
		$field = $param->field;
		$values = !is_array($param->value) ? array($param->value) : $param->value;

		switch($field) {
			default:
				parent::renderCriteriaParam($param);
				break;
		}
	}

	function getFields() {
		return SearchFields_Gateway::getFields();
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		// [TODO] Move fields into the right data type
		switch($field) {
			case SearchFields_Gateway::ID:
			case SearchFields_Gateway::EXTENSION_ID:
			case SearchFields_Gateway::NAME:
			case SearchFields_Gateway::ENABLED:

			case 'placeholder_string':
				// force wildcards if none used on a LIKE
				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
				&& false === (strpos($value,'*'))) {
					$value = $value.'*';
				}
				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
				break;
			case 'placeholder_number':
				$criteria = new DevblocksSearchCriteria($field,$oper,$value);
				break;
				
			case 'placeholder_date':
				@$from = DevblocksPlatform::importGPC($_REQUEST['from'],'string','');
				@$to = DevblocksPlatform::importGPC($_REQUEST['to'],'string','');

				if(empty($from)) $from = 0;
				if(empty($to)) $to = 'today';

				$criteria = new DevblocksSearchCriteria($field,$oper,array($from,$to));
				break;
				
			case 'placeholder_bool':
				@$bool = DevblocksPlatform::importGPC($_REQUEST['bool'],'integer',1);
				$criteria = new DevblocksSearchCriteria($field,$oper,$bool);
				break;
				
			/*
			default:
				// Custom Fields
				if(substr($field,0,3)=='cf_') {
					$criteria = $this->_doSetCriteriaCustomField($field, substr($field,3));
				}
				break;
			*/
		}

		if(!empty($criteria)) {
			$this->addParam($criteria, $field);
			$this->renderPage = 0;
		}
	}
		
	function doBulkUpdate($filter, $do, $ids=array()) {
		@set_time_limit(0);
	  
		$change_fields = array();
		$custom_fields = array();

		// Make sure we have actions
		if(empty($do))
			return;

		// Make sure we have checked items if we want a checked list
		if(0 == strcasecmp($filter,"checks") && empty($ids))
			return;
			
		if(is_array($do))
		foreach($do as $k => $v) {
			switch($k) {
				// [TODO] Implement actions
				case 'example':
					//$change_fields[DAO_Gateway::EXAMPLE] = 'some value';
					break;
				/*
				default:
					// Custom fields
					if(substr($k,0,3)=="cf_") {
						$custom_fields[substr($k,3)] = $v;
					}
					break;
				*/
			}
		}

		$pg = 0;

		if(empty($ids))
		do {
			list($objects,$null) = DAO_Gateway::search(
				array(),
				$this->getParams(),
				100,
				$pg++,
				SearchFields_Gateway::ID,
				true,
				false
			);
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(count($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			
			DAO_Gateway::update($batch_ids, $change_fields);

			// Custom Fields
			//self::_doBulkSetCustomFields(ChCustomFieldSource_Gateway::ID, $custom_fields, $batch_ids);
			
			unset($batch_ids);
		}

		unset($ids);
	}			
};

class DAO_GatewaySetting extends C4_ORMHelper {
	const PRODUCT_ID = 'gateway_id';
	const NAME = 'name';
	const VALUE = 'value';
	
	/**
	 * @param string $where
	 * @param mixed $sortBy
	 * @param mixed $sortAsc
	 * @param integer $limit
	 * @return Model_GatewaySetting[]
	 */
	static function getWhere($where=null, $sortBy=null, $sortAsc=true, $limit=null) {
		$db = DevblocksPlatform::getDatabaseService();

		list($where_sql, $sort_sql, $limit_sql) = self::_getWhereSQL($where, $sortBy, $sortAsc, $limit);
		
		// SQL
		$sql = "SELECT gateway_id, name, value ".
			"FROM gateway_setting ".
			$where_sql.
			$sort_sql.
			$limit_sql
		;
		$rs = $db->Execute($sql);
		return self::_getObjectsFromResult($rs);
	}
	
	/**
	 * @param integer $gateway_id
	 * @param string $name
	 * @param string value
	 * @return Model_GatewaySetting[]
	 */
	static function setGatewaySetting($gateway_id, $name, $value) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("REPLACE INTO gateway_setting (%s, %s, %s) VALUES (%s, %s, %s)",
			self::PRODUCT_ID,
			self::NAME,
			self::VALUE,
			$gateway_id,
			$db->qstr($name),
			$db->qstr($value)
		);
		$db->Execute($sql);
				
		return null;
	}

	/**
	* @param integer $gateway_id
	* @param string $name
	* @param string default
	* @return Model_GatewaySetting[]
	*/
	static function getGatewaySetting($gateway_id, $name, $default) {
	
		if(null == $setting = self::getWhere(sprintf("%s = %d AND %s = %s",
			self::PRODUCT_ID,
			$gateway_id,
			self::NAME,
			$db->qstr($name)
		))) {
			return $default;
		} else {
			return $setting;
		}
		
		return null;
	}
	
	/**
	 * @param integer $gateway_id
	 * @return Model_GatewaySetting[]
	 */
	static function getGatewaySettings($gateway_id) {
		$objects = self::getWhere(sprintf("%s = %d",
			self::PRODUCT_ID,
			$gateway_id
		));
		
		if(count($objects))
			return $objects;
		
		return null;
	}
	
	/**
	 * @param resource $rs
	 * @return Model_GatewaySetting[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_GatewaySetting();
			$object->gateway_id = $row['gateway_id'];
			$object->name = $row['name'];
			$object->value = $row['value'];

			$objects[] = $object;
		}
		
		mysql_free_result($rs);
		
		return $objects;
	}

};

class Model_GatewaySetting {
	public $gateway_id;
	public $name;
	public $value;
};