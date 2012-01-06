<?php
class DAO_Invoice extends DevblocksORMHelper {
	const ID = 'id';
	const ACCOUNT_ID = 'account_id';
	const AMOUNT = 'amount';
	const AMOUNT_PAID = 'amount_paid';
	const STATUS = 'status';
	const NUMBER = 'number';
	const CREATED_DATE = 'created_date';
	const PAID_DATE = 'paid_date';

	static function addItem($invoice_id, $product_id, &$invoice_total, $quantity=1) {
		DAO_InvoiceItem::addInvoiceItem($invoice_id, $product_id, $invoice_total, $quantity);
	}
	
	static function setAttribute($invoice_id, $name, $value) {
		if(!is_null($value)) {
			if(null == DAO_InvoiceAttribute::getInvoiceAttribute($invoice_id, $name, null)) {
				DAO_InvoiceAttribute::addInvoiceAttribute($invoice_id, $name, $value);
			} else {
				DAO_InvoiceAttribute::setInvoiceAttribute($invoice_id, $name, $value);
			}
		}
	}
	
	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "INSERT INTO invoice () VALUES ()";
		$db->Execute($sql);
		$id = $db->LastInsertId();
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'invoice', $fields);
	}
	
	static function updateWhere($fields, $where) {
		parent::_updateWhere('invoice', $fields, $where);
	}
	
	/**
	 * @param string $where
	 * @param mixed $sortBy
	 * @param mixed $sortAsc
	 * @param integer $limit
	 * @return Model_Invoice[]
	 */
	static function getWhere($where=null, $sortBy=null, $sortAsc=true, $limit=null) {
		$db = DevblocksPlatform::getDatabaseService();

		list($where_sql, $sort_sql, $limit_sql) = self::_getWhereSQL($where, $sortBy, $sortAsc, $limit);
		
		// SQL
		$sql = "SELECT id, account_id, amount, amount_paid, status, number, created_date, paid_date ".
			"FROM invoice ".
			$where_sql.
			$sort_sql.
			$limit_sql
		;
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_Invoice[]
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
	* @param integer $account_id
	* @return Model_Invoice[]
	*/
	static function getAllByAccount($account_id) {
		$objects = self::getWhere(sprintf("%s = %d",
			self::ACCOUNT_ID,
			$account_id
		));
		
		if(count($objects))
			return $objects;
	
		return null;
	}
	
	/**
	* @param integer $account_id
	* @param integer $start
	* @param integer $end
	* @return Model_Invoice[]
	*/
	static function getByAccountAndDateRange($account_id, $start, $end) {
		$objects = DAO_Invoice::getWhere(sprintf("%s = %d AND %s >= %d AND %s <= %d",
			DAO_Invoice::ACCOUNT_ID,
			$account_id,
			DAO_Invoice::CREATED_DATE,
			$start,
			DAO_Invoice::CREATED_DATE,
			$end
		));
		
		if(count($objects))
		return $objects;
		
		return null;
	}
	
	
	/**
	* @param integer $account_id
	* @param array $statuses
	* @return Model_Invoice[]
	*/
	static function getByAccountAndStatus($account_id, $statuses) {
		$objects = self::getWhere(sprintf("%s = %d AND %s IN (%s)",
			self::ACCOUNT_ID,
			$account_id,
			self::STATUS,
			implode(',', $statuses)
		));
	
		if(count($objects))
			return $objects;
	
		return null;
	}
	
	/**
	* @param integer $start
	* @param integer $end
	* @param array $statuses
	* @return Model_Invoice[]
	*/
	static function getByDateRangeAndStatus($start, $end, $statuses) {
		$objects = DAO_Invoice::getWhere(sprintf("%s >= %d AND %s <= %d AND %s IN (%s)",
			DAO_Invoice::CREATED_DATE,
			$start,
			DAO_Invoice::CREATED_DATE,
			$end,
			DAO_Invoice::STATUS,
			implode(',', $statuses)
		));
		
		if(count($objects))
			return $objects;
		
		return null;
	}
	
	/**
	* @param integer $account_id
	* @return Model_Invoice[]
	*/
	static function getPaidByAccount($account_id) {
		return self::getByAccountAndStatus($account_id, array('1', '2'));
	}
	
	/**
	* @param integer $account_id
	* @return Model_Invoice[]
	*/
	static function getUnpaidByAccount($account_id) {
		return self::getByAccountAndStatus($account_id, array('0', '1'));
	}
	
	/**
	 * @param resource $rs
	 * @return Model_Invoice[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_Invoice();
			$object->id = $row['id'];
			$object->account_id = $row['account_id'];
			$object->amount = $row['amount'];
			$object->amount_paid = $row['amount_paid'];
			$object->status = $row['status'];
			$object->number = $row['number'];
			$object->created_date = $row['created_date'];
			$object->paid_date = $row['paid_date'];

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
		
		$db->Execute(sprintf("DELETE FROM invoice WHERE id IN (%s)", $ids_list));
		
		return true;
	}
	
	public static function getSearchQueryComponents($columns, $params, $sortBy=null, $sortAsc=null) {
		$fields = _Invoice::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);
		
		$select_sql = sprintf("SELECT ".
			"i.id as %s, ".
			"i.account_id as %s, ".
			"i.amount as %s, ".
			"i.amount_paid as %s, ".
			"i.status as %s, ".
			"i.number as %s ".
			"i.created_date as %s ".
			"i.paid_date as %s ",
			SearchFields_Invoice::ID,
			SearchFields_Invoice::ACCOUNT_ID,
			SearchFields_Invoice::AMOUNT,
			SearchFields_Invoice::AMOUNT_PAID,
			SearchFields_Invoice::STATUS,
			SearchFields_Invoice::NUMBER,
			SearchFields_Invoice::CREATED_DATE,
			SearchFields_Invoice::PAID_DATE

			);
			
		$join_sql = "FROM invoice i";
// 			"LEFT JOIN invoice_item ii ON (i.id = ii.invoice_id)";
		
		// Custom field joins
		//list($select_sql, $join_sql, $has_multiple_values) = self::_appendSelectJoinSqlForCustomFieldTables(
		//	$tables,
		//	$params,
		//	'invoice.id',
		//	$select_sql,
		//	$join_sql
		//);
		$has_multiple_values = false; // [TODO] Temporary when custom fields disabled
				
		$where_sql = "".
			(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "WHERE 1 ");
			
		$sort_sql = (!empty($sortBy)) ? sprintf("ORDER BY %s %s ",$sortBy,($sortAsc || is_null($sortAsc))?"ASC":"DESC") : " ";
	
		return array(
			'primary_table' => 'invoice',
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
			($has_multiple_values ? 'GROUP BY invoice.id .' : '').
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
			$object_id = intval($row[SearchFields_Invoice::ID]);
			$results[$object_id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
			$count_sql = 
				($has_multiple_values ? "SELECT COUNT(DISTINCT invoice.id') " : "SELECT COUNT(invoice.id) ").
				$join_sql.
				$where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
	}

};

class SearchFields_Invoice implements IDevblocksSearchFields {
	const ID = 'i_id';
	const ACCOUNT_ID = 'i_account_id';
	const AMOUNT = 'i_amount';
	const AMOUNT_PAID = 'i_amount_paid';
	const STATUS = 'i_status';
	const NUMBER = 'i_number';
	const CREATED_DATE = 'i_created_date';
	const PAID_DATE = 'i_paid_date';

	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'invoice', 'id', $translate->_('invoice.id')),
			self::ACCOUNT_ID => new DevblocksSearchField(self::ACCOUNT_ID, 'invoice', 'account_id', $translate->_('invoice.account_id')),
			self::AMOUNT => new DevblocksSearchField(self::AMOUNT, 'invoice', 'amount', $translate->_('invoice.amount')),
			self::AMOUNT_PAID => new DevblocksSearchField(self::AMOUNT_PAID, 'invoice', 'amount_paid', $translate->_('invoice.amount_paid')),
			self::STATUS => new DevblocksSearchField(self::STATUS, 'invoice', 'status', $translate->_('invoice.status')),
			self::NUMBER => new DevblocksSearchField(self::NUMBER, 'invoice', 'number', $translate->_('invoice.number')),
			self::CREATED_DATE => new DevblocksSearchField(self::CREATED_DATE, 'invoice', 'created_date', $translate->_('invoice.created_date')),
			self::PAID_DATE => new DevblocksSearchField(self::PAID_DATE, 'invoice', 'paid_date', $translate->_('invoice.paid_date')),

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
class Model_Invoice {
	public $id;
	public $account_id;
	public $amount;
	public $amount_paid;
	public $status;
	public $number;
	public $created_date;
	public $paid_date;
	
	public function addItem($product_id, &$invoice_total, $quantity) {
		DAO_InvoiceItem::addInvoiceItem($this->id, $product_id, $invoice_total, $quantity);
	}
	
	public function getItems() {
		return DAO_InvoiceItem::getInvoiceItems($this->id);
	}
	
	public function deleteItem($item_id) {
		DAO_InvoiceItem::deleteInvoiceItem($this->id, $item_id);
	}
	
	public function deleteItems() {
		DAO_InvoiceItem::deleteInvoiceItems($this->id);
	}
	
	public function getAttribute($name, $default = '') {
		return DAO_InvoiceAttribute::getInvoiceAttribute($this->id, $name, $default);
	}
	
	public function getAttributeGroup($prefix) {
		return DAO_InvoiceAttribute::getInvoiceAttributeGroup($this->id, $prefix);
	}
	
	public function getAttributes() {
		return DAO_InvoiceAttribute::getInvoiceAttributes($this->id);
	}
	
	
	public function setAttribute($name, $value) {
		if(null == DAO_InvoiceAttribute::getInvoiceAttribute($this->id, $name, null)) {
			DAO_InvoiceAttribute::addInvoiceAttribute($this->id, $name, $value);
		} else {
			DAO_InvoiceAttribute::setInvoiceAttribute($this->id, $name, $value);
		}
	}
	
	public function deleteAttribute($name) {
		DAO_InvoiceAttribute::deleteInvoiceAttribute($this->id, $name);
	}
	
	public function deleteAttributeGroup($prefix) {
		DAO_InvoiceAttribute::deleteInvoiceAttributeGroup($this->id, $prefix);
	}
	
	public function deleteAttributes() {
		DAO_InvoiceAttribute::deleteInvoiceAttributes($this->id);
	}
};

class View_Invoice extends C4_AbstractView {
	const DEFAULT_ID = 'invoice';

	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
	
		$this->id = self::DEFAULT_ID;
		// [TODO] Name the worklist view
		$this->name = $translate->_('Invoice');
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_Invoice::ID;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_Invoice::ID,
			SearchFields_Invoice::ACCOUNT_ID,
			SearchFields_Invoice::AMOUNT,
			SearchFields_Invoice::AMOUNT_PAID,
			SearchFields_Invoice::STATUS,
			SearchFields_Invoice::NUMBER,
			SearchFields_Invoice::CREATED_DATE,
			SearchFields_Invoice::PAID_DATE,
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
		$objects = DAO_Invoice::search(
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
		return $this->_doGetDataSample('DAO_Invoice', $size);
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
			case SearchFields_Invoice::ID:
			case SearchFields_Invoice::ACCOUNT_ID:
			case SearchFields_Invoice::AMOUNT:
			case SearchFields_Invoice::AMOUNT_PAID:
			case SearchFields_Invoice::STATUS:
			case SearchFields_Invoice::NUMBER:
			case SearchFields_Invoice::CREATED_DATE:
			case SearchFields_Invoice::PAID_DATE:

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
		return SearchFields_Invoice::getFields();
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		// [TODO] Move fields into the right data type
		switch($field) {
			case SearchFields_Invoice::ID:
			case SearchFields_Invoice::ACCOUNT_ID:
			case SearchFields_Invoice::AMOUNT:
			case SearchFields_Invoice::AMOUNT_PAID:
			case SearchFields_Invoice::STATUS:
			case SearchFields_Invoice::NUMBER:
			case SearchFields_Invoice::CREATED_DATE:
			case SearchFields_Invoice::PAID_DATE:

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
					//$change_fields[DAO_Invoice::EXAMPLE] = 'some value';
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
			list($objects,$null) = DAO_Invoice::search(
				array(),
				$this->getParams(),
				100,
				$pg++,
				SearchFields_Invoice::ID,
				true,
				false
			);
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(count($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			
			DAO_Invoice::update($batch_ids, $change_fields);

			// Custom Fields
			//self::_doBulkSetCustomFields(ChCustomFieldSource_Invoice::ID, $custom_fields, $batch_ids);
			
			unset($batch_ids);
		}

		unset($ids);
	}			
};

class DAO_InvoiceItem extends DevblocksORMHelper {
	const INVOICE_ID = 'invoice_id';
	const PRODUCT_ID = 'product_id';
	const QUANTITY = 'quantity';
	const AMOUNT = 'amount';

	static function addInvoiceItem($invoice_id, $product_id, &$invoice_total, $quantity) {
		$db = DevblocksPlatform::getDatabaseService();
		
		// update 
		$item_price = DAO_Product::get($product_id)->price;
		
		$sql = sprintf("REPLACE INTO invoice_item (%s, %s, %s, %s) VALUES (%s, %s, %s, %s)",
			self::INVOICE_ID,
			self::PRODUCT_ID,
			self::QUANTITY,
			self::AMOUNT,
			$invoice_id,
			$product_id,
			$quantity,
			$item_price
		);
		
		$db->Execute($sql);
		
		$invoice_total += $quantity * $item_price;
		
		return null;
	}
	
	/**
	* @param integer $invoice_id
	* @param integer $product_id
	*/
	
	static function deleteInvoiceItem($invoice_id, $product_id) {
		$db = DevblocksPlatform::getDatabaseService();
	
		$sql = sprintf("DELETE FROM invoice_item WHERE %s = %d AND %s = %d",
			self::INVOICE_ID,
			$invoice_id,
			self::PRODUCT_ID,
			$product_id
		);
		$rs = $db->Execute($sql);
	
		return true;
	}
	
	/**
	* @param integer $invoice_id
	*/
	
	static function deleteInvoiceItems($invoice_id) {
		$db = DevblocksPlatform::getDatabaseService();
	
		$sql = sprintf("DELETE FROM invoice_item WHERE %s = %d",
			self::INVOICE_ID,
			$invoice_id
		);
		$rs = $db->Execute($sql);
	
		return true;
	}
	
	/**
	 * @param string $where
	 * @param mixed $sortBy
	 * @param mixed $sortAsc
	 * @param integer $limit
	 * @return Model_InvoiceItem[]
	 */
	static function getWhere($where=null, $sortBy=null, $sortAsc=true, $limit=null) {
		$db = DevblocksPlatform::getDatabaseService();

		list($where_sql, $sort_sql, $limit_sql) = self::_getWhereSQL($where, $sortBy, $sortAsc, $limit);
		
		// SQL
		$sql = "SELECT invoice_id, product_id, quantity, amount ".
			"FROM invoice_item ".
			$where_sql.
			$sort_sql.
			$limit_sql
		;
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}
	
	/**
	* @param integer $invoice_id
	* @return Model_InvoiceItem[]
	*/
	static function getInvoiceItems($invoice_id) {
		$objects = self::getWhere(sprintf("%s = %d",
			self::INVOICE_ID,
			$invoice_id
		));
		
		if(count($objects))
			return $objects;
	
		return null;
	}
	
	/**
	 * @param resource $rs
	 * @return Model_InvoiceItem[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_InvoiceItem();
			$object->invoice_id = $row['invoice_id'];
			$object->product_id = $row['product_id'];
			$object->quantity = $row['quantity'];
			$object->amount = $row['amount'];
			
			$objects[] = $object;
		}
		
		mysql_free_result($rs);
		
		return $objects;
	}
};

class Model_InvoiceItem {
	public $invoice_id;
	public $product_id;
	public $quantity;
	public $amount;
};

class DAO_InvoiceAttribute extends DevblocksORMHelper {
	const INVOICE_ID = 'invoice_id';
	const NAME = 'name';
	const VALUE = 'value';
	
	/**
	 * @param string $where
	 * @param mixed $sortBy
	 * @param mixed $sortAsc
	 * @param integer $limit
	 * @return Model_InvoiceAttribute[]
	 */
	static function getWhere($where=null, $sortBy=null, $sortAsc=true, $limit=null) {
		$db = DevblocksPlatform::getDatabaseService();

		list($where_sql, $sort_sql, $limit_sql) = self::_getWhereSQL($where, $sortBy, $sortAsc, $limit);
		
		// SQL
		$sql = "SELECT invoice_id, name, value ".
			"FROM invoice_attribute ".
			$where_sql.
			$sort_sql.
			$limit_sql
		;
		$rs = $db->Execute($sql);
		return self::_getObjectsFromResult($rs);
	}
	
	/**
	* @param integer $invoice_id
	* @param string $name
	* @param string value
	* @return Model_InvoiceAttribute[]
	*/
	static function addInvoiceAttribute($invoice_id, $name, $value) {
		$db = DevblocksPlatform::getDatabaseService();
	
		$sql = sprintf("INSERT INTO invoice_attribute (%s, %s, %s) VALUES (%s, %s, %s)",
			self::INVOICE_ID,
			self::NAME,
			self::VALUE,
			$invoice_id,
			$db->qstr($name),
			$db->qstr($value)
		);
		$db->Execute($sql);
	
		return null;
	}
	
	/**
	 * @param integer $invoice_id
	 * @param string $name
	 * @param string value
	 * @return Model_InvoiceAttribute[]
	 */
	static function setInvoiceAttribute($invoice_id, $name, $value) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("UPDATE invoice_attribute SET %s = %s WHERE %s = %d AND %s = %s",
			self::VALUE,
			$db->qstr($value),
			self::INVOICE_ID,
			$invoice_id,
			self::NAME,
			$db->qstr($name)
		);
		$db->Execute($sql);
				
		return null;
	}
	
	/**
	* @param integer $invoice_id
	* @param string $name
	* @param string $default
	* @return string
	*/
	static function getInvoiceAttribute($invoice_id, $name, $default) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$attribute = self::getWhere(sprintf("%s = %d AND %s = %s",
			self::INVOICE_ID,
			$invoice_id,
			self::NAME,
			$db->qstr($name)
		));
		
		if(!empty($attribute)) {
			$attribute = array_shift($attribute);
			return $attribute->value;
		}
	
		return $default;
	}
	
	/**
	* @param integer $invoice_id
	* @param string $prefix
	* @return string
	*/
	static function getInvoiceAttributeGroup($invoice_id, $prefix) {
		$db = DevblocksPlatform::getDatabaseService();
	
		$attributes = self::getWhere(sprintf("%s = %d AND %s LIKE %s",
			self::INVOICE_ID,
			$invoice_id,
			self::NAME,
			$db->qstr($prefix.'%')
		));
	
		if(!empty($attributes)) {
			return $attributes;
		}
	
		return null;
	}
	
	/**
	 * @param integer $invoice_id
	 * @return Model_InvoiceAttribute[]
	 */
	static function getInvoiceAttributes($invoice_id) {
		$objects = self::getWhere(sprintf("%s = %d",
			self::INVOICE_ID,
			$invoice_id
		));
		
		if(count($objects))
			return $objects;
		
		return null;
	}
	
	/**
	* @param integer $invoice_id
	* @param string $name
	*/
	
	static function deleteInvoiceAttribute($invoice_id, $name) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("DELETE FROM invoice_attribute WHERE %s = %d AND %s = %s",
			self::INVOICE_ID,
			$invoice_id,
			self::NAME,
			$db->qstr($name)
		);
		$rs = $db->Execute($sql);
		
		return true;
	}
	
	/**
	* @param integer $invoice_id
	* @param string $prefix
	*/
	
	static function deleteInvoiceAttributeGroup($invoice_id, $prefix) {
		$db = DevblocksPlatform::getDatabaseService();
	
		$sql = sprintf("DELETE FROM invoice_attribute WHERE %s = %d AND %s LIKE %s",
			self::INVOICE_ID,
			$invoice_id,
			self::NAME,
			$db->qstr($prefix.'%')
		);
		print $sql;
		$rs = $db->Execute($sql);
	
		return true;
	}
	
	/**
	* @param integer $invoice_id
	* @param string $name
	*/
	
	static function deleteInvoiceAttributes($invoice_id) {
		$db = DevblocksPlatform::getDatabaseService();
	
		$sql = sprintf("DELETE FROM invoice_attribute WHERE %s = %d",
			self::INVOICE_ID,
			$invoice_id
		);
		$rs = $db->Execute($sql);
	
		return true;
	}
	
	/**
	 * @param resource $rs
	 * @return Model_InvoiceAttribute[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_InvoiceAttribute();
			$object->invoice_id = $row['invoice_id'];
			$object->name = $row['name'];
			$object->value = $row['value'];

			$objects[] = $object;
		}
		
		mysql_free_result($rs);
		
		return $objects;
	}

};

class Model_InvoiceAttribute {
	public $invoice_id;
	public $name;
	public $value;
};