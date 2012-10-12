<?php
class DAO_Transaction extends C4_ORMHelper {
	const ID = 'id';
	const TO_ADDRESS_ID = 'to_address_id';
	const FROM_ADDRESS_ID = 'from_address_id';
	const AMOUNT = 'amount';
	const PLUGIN_ID = 'plugin_id';
	const TRANS_ID = 'trans_id';
	const PROFILE_ID = 'profile_id';
	const STATUS = 'status';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "INSERT INTO transaction () VALUES ()";
		$db->Execute($sql);
		$id = $db->LastInsertId();
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'transaction', $fields);
	}
	
	static function updateWhere($fields, $where) {
		parent::_updateWhere('transaction', $fields, $where);
	}
	
	/**
	 * @param string $where
	 * @param mixed $sortBy
	 * @param mixed $sortAsc
	 * @param integer $limit
	 * @return Model_$class_name
	 */
	static function getWhere($where=null, $sortBy=null, $sortAsc=true, $limit=null) {
		$db = DevblocksPlatform::getDatabaseService();

		list($where_sql, $sort_sql, $limit_sql) = self::_getWhereSQL($where, $sortBy, $sortAsc, $limit);
		
		// SQL
		$sql = "SELECT id, to_address_id, from_address_id, amount, plugin_id, trans_id, profile_id, status ".
			"FROM transaction ".
			$where_sql.
			$sort_sql.
			$limit_sql
		;
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_$class_name
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
	 * @param resource $rs
	 * @return Model_Transaction[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_Transaction();
			$object->id = $row['id'];
			$object->to_address_id = $row['to_address_id'];
			$object->from_address_id = $row['from_address_id'];
			$object->amount = $row['amount'];
			$object->plugin_id = $row['plugin_id'];
			$object->trans_id = $row['trans_id'];
			$object->profile_id = $row['profile_id'];
			$object->status = $row['status'];

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
		
		$db->Execute(sprintf("DELETE FROM transaction WHERE id IN (%s)", $ids_list));
		
		return true;
	}
	
	public static function getSearchQueryComponents($columns, $params, $sortBy=null, $sortAsc=null) {
		$fields = _Transaction::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);
		
		$select_sql = sprintf("SELECT ".
			"transaction.id as %s, ".
			"transaction.to_address_id as %s, ".
			"transaction.from_address_id as %s, ".
			"transaction.amount as %s, ".
			"transaction.plugin_id as %s, ".
			"transaction.trans_id as %s, ".
			"transaction.profile_id as %s, ".
			"transaction.status as %s ",
				SearchFields_Transaction::ID,
				SearchFields_Transaction::TO_ADDRESS_ID,
				SearchFields_Transaction::FROM_ADDRESS_ID,
				SearchFields_Transaction::AMOUNT,
				SearchFields_Transaction::PLUGIN_ID,
				SearchFields_Transaction::TRANS_ID,
				SearchFields_Transaction::PROFILE_ID,
				SearchFields_Transaction::STATUS

			);
			
		$join_sql = "FROM transaction";
		
		// Custom field joins
		//list($select_sql, $join_sql, $has_multiple_values) = self::_appendSelectJoinSqlForCustomFieldTables(
		//	$tables,
		//	$params,
		//	'transaction.id',
		//	$select_sql,
		//	$join_sql
		//);
		$has_multiple_values = false; // [TODO] Temporary when custom fields disabled
				
		$where_sql = "".
			(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "WHERE 1 ");
			
		$sort_sql = (!empty($sortBy)) ? sprintf("ORDER BY %s %s ",$sortBy,($sortAsc || is_null($sortAsc))?"ASC":"DESC") : " ";
	
		return array(
			'primary_table' => 'transaction',
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
			($has_multiple_values ? 'GROUP BY transaction.id .' : '').
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
			$object_id = intval($row[SearchFields_Transaction::ID]);
			$results[$object_id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
			$count_sql = 
				($has_multiple_values ? "SELECT COUNT(DISTINCT transaction.id') " : "SELECT COUNT(transaction.id) ").
				$join_sql.
				$where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
	}

};

class SearchFields_Transaction implements IDevblocksSearchFields {
	const ID = 't_id';
	const TO_ADDRESS_ID = 't_to_address_id';
	const FROM_ADDRESS_ID = 't_from_address_id';
	const AMOUNT = 't_amount';
	const PLUGIN_ID = 't_plugin_id';
	const TRANS_ID = 't_trans_id';
	const PROFILE_ID = 't_profile_id';
	const STATUS = 't_status';

	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'transaction', 'id', $translate->_('dao.transaction.id')),
			self::TO_ADDRESS_ID => new DevblocksSearchField(self::TO_ADDRESS_ID, 'transaction', 'to_address_id', $translate->_('dao.transaction.to_address_id')),
			self::FROM_ADDRESS_ID => new DevblocksSearchField(self::FROM_ADDRESS_ID, 'transaction', 'from_address_id', $translate->_('dao.transaction.from_address_id')),
			self::AMOUNT => new DevblocksSearchField(self::AMOUNT, 'transaction', 'amount', $translate->_('dao.transaction.amount')),
			self::PLUGIN_ID => new DevblocksSearchField(self::PLUGIN_ID, 'transaction', 'plugin_id', $translate->_('dao.transaction.plugin_id')),
			self::TRANS_ID => new DevblocksSearchField(self::TRANS_ID, 'transaction', 'trans_id', $translate->_('dao.transaction.trans_id')),
			self::PROFILE_ID => new DevblocksSearchField(self::PROFILE_ID, 'transaction', 'profile_id', $translate->_('dao.transaction.profile_id')),
			self::STATUS => new DevblocksSearchField(self::STATUS, 'transaction', 'status', $translate->_('dao.transaction.status')),

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
class Model_Transaction {
	public $id;
	public $to_address_id;
	public $from_address_id;
	public $amount;
	public $plugin_id;
	public $trans_id;
	public $profile_id;
	public $status;

};

class View_Transaction extends C4_AbstractView {
	const DEFAULT_ID = 'transaction';

	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
	
		$this->id = self::DEFAULT_ID;
		// [TODO] Name the worklist view
		$this->name = $translate->_('Transaction');
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_Transaction::ID;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_Transaction::ID,
			SearchFields_Transaction::TO_ADDRESS_ID,
			SearchFields_Transaction::FROM_ADDRESS_ID,
			SearchFields_Transaction::AMOUNT,
			SearchFields_Transaction::PLUGIN_ID,
			SearchFields_Transaction::TRANS_ID,
			SearchFields_Transaction::PROFILE_ID,
			SearchFields_Transaction::STATUS,

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
		$objects = DAO_Transaction::search(
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
		return $this->_doGetDataSample('DAO_Transaction', $size);
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
		$tpl->display('devblocks:example.plugin::path/to/view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		// [TODO] Move the fields into the proper data type
		switch($field) {
			case SearchFields_Transaction::ID:
			case SearchFields_Transaction::TO_ADDRESS_ID:
			case SearchFields_Transaction::FROM_ADDRESS_ID:
			case SearchFields_Transaction::AMOUNT:
			case SearchFields_Transaction::PLUGIN_ID:
			case SearchFields_Transaction::TRANS_ID:
			case SearchFields_Transaction::PROFILE_ID:
			case SearchFields_Transaction::STATUS:

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
		return SearchFields_Transaction::getFields();
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		// [TODO] Move fields into the right data type
		switch($field) {
			case SearchFields_Transaction::ID:
			case SearchFields_Transaction::TO_ADDRESS_ID:
			case SearchFields_Transaction::FROM_ADDRESS_ID:
			case SearchFields_Transaction::AMOUNT:
			case SearchFields_Transaction::PLUGIN_ID:
			case SearchFields_Transaction::TRANS_ID:
			case SearchFields_Transaction::PROFILE_ID:
			case SearchFields_Transaction::STATUS:

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
					//$change_fields[DAO_Transaction::EXAMPLE] = 'some value';
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
			list($objects,$null) = DAO_Transaction::search(
				array(),
				$this->getParams(),
				100,
				$pg++,
				SearchFields_Transaction::ID,
				true,
				false
			);
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(count($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			
			DAO_Transaction::update($batch_ids, $change_fields);

			// Custom Fields
			//self::_doBulkSetCustomFields(ChCustomFieldSource_Transaction::ID, $custom_fields, $batch_ids);
			
			unset($batch_ids);
		}

		unset($ids);
	}			
};
