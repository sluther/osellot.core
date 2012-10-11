<?php
class DAO_Product extends DevblocksORMHelper {
	const ID = 'id';
	const PRICE = 'price';
	const PRICE_SETUP = 'price_setup';
	const RECURRING = 'recurring';
	const TAXABLE = 'taxable';
	const SKU = 'sku';
	const NAME = 'name';
	const DESCRIPTION = 'description';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "INSERT INTO product () VALUES ()";
		$db->Execute($sql);
		$id = $db->LastInsertId();
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'product', $fields);
	}
	
	static function updateWhere($fields, $where) {
		parent::_updateWhere('product', $fields, $where);
	}
	
	/**
	 * @param string $where
	 * @param mixed $sortBy
	 * @param mixed $sortAsc
	 * @param integer $limit
	 * @return Model_Product
	 */
	static function getWhere($where=null, $sortBy=null, $sortAsc=true, $limit=null) {
		$db = DevblocksPlatform::getDatabaseService();
		
		list($where_sql, $sort_sql, $limit_sql) = self::_getWhereSQL($where, $sortBy, $sortAsc, $limit);
		
		// SQL
		$sql = "SELECT id, price, price_setup, recurring, taxable, sku, name, description ".
			"FROM product ".
			$where_sql.
			$sort_sql.
			$limit_sql
		;
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_Product
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
	 * 
	 * @param string $sku
	 * @return Model_Product
	 */
	static function getBySKU($sku) {
		$db = DevblocksPlatform::getDatabaseService();
		$objects = self::getWhere(sprintf("%s = %s",
			self::SKU,
			$db->qstr($sku)
		));
		
		if(count($objects))
			return array_shift($objects);
		
		return null;
	}
	
	/**
	* @return array Model_Product
	*/
	static function getAll() {
		return self::getWhere();
	}
	
	/**
	* @param integer $id
	* @return Model_ProductSetting
	*/
	static function getProductSettings($id) {
		$settings = DAO_ProductSetting::getProductSettings($id);

		return $settings;
	}
	
	/**
	 * @param resource $rs
	 * @return Model_Product[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_Product();
			$object->id = $row['id'];
			$object->price = $row['price'];
			$object->price_setup = $row['price_setup'];
			$object->recurring = $row['recurring'];
			$object->taxable = $row['taxable'];
			$object->sku = $row['sku'];
			$object->name = $row['name'];
			$object->description = $row['description'];

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
		
		$db->Execute(sprintf("DELETE FROM product WHERE id IN (%s)", $ids_list));
		
		return true;
	}
	
	public static function getSearchQueryComponents($columns, $params, $sortBy=null, $sortAsc=null) {
		$fields = View_Product::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);
		
		$select_sql = sprintf("SELECT ".
			"product.id as %s, ".
			"product.price as %s, ".
			"product.price_setup as %s, ".
			"product.recurring as %s, ".
			"product.taxable as %s, ".
			"product.sku as %s, ".
			"product.name as %s, ".
			"product.description as %s ",
			SearchFields_Product::ID,
			SearchFields_Product::PRICE,
			SearchFields_Product::PRICE_SETUP,
			SearchFields_Product::RECURRING,
			SearchFields_Product::TAXABLE,
			SearchFields_Product::SKU,
			SearchFields_Product::NAME,
			SearchFields_Product::DESCRIPTION
		);
			
		$join_sql = "FROM product ";
		
//		if(isset($tables['product_setting'])) {
//			$select_sql .= sprintf(
//				", pc.name AS %s, ".
//				"pc.value AS %s",
//				SearchFields_ProductSetting::NAME,
//				SearchFields_ProductSetting::VALUE
//			);
//			$join_sql .= "LEFT JOIN product_setting pc ON (p.id=pc.product_id) ";
//		}
		$has_multiple_values = false; // [TODO] Temporary when custom fields disabled
				
		$where_sql = "".
			(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "WHERE 1 ");
			
		$sort_sql = (!empty($sortBy)) ? sprintf("ORDER BY %s %s ",$sortBy,($sortAsc || is_null($sortAsc))?"ASC":"DESC") : " ";
	
		return array(
			'primary_table' => 'product',
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
			($has_multiple_values ? 'GROUP BY product.id .' : '').
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
			$object_id = intval($row[SearchFields_Product::ID]);
			$results[$object_id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
			$count_sql = 
				($has_multiple_values ? "SELECT COUNT(DISTINCT product.id') " : "SELECT COUNT(product.id) ").
				$join_sql.
				$where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
	}

};

class SearchFields_Product implements IDevblocksSearchFields {
	const ID = 'p_id';
	const PRICE = 'p_price';
	const PRICE_SETUP = 'p_price_setup';
	const RECURRING = 'p_recurring';
	const TAXABLE = 'p_taxable';
	const SKU = 'p_sku';
	const NAME = 'p_name';
	const DESCRIPTION = 'p_description';

	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'product', 'id', $translate->_('product.id')),
			self::PRICE => new DevblocksSearchField(self::PRICE, 'product', 'price', $translate->_('product.price')),
			self::PRICE_SETUP => new DevblocksSearchField(self::PRICE_SETUP, 'product', 'price_setup', $translate->_('product.price_setup')),
			self::RECURRING => new DevblocksSearchField(self::RECURRING, 'product', 'recurring', $translate->_('product.recurring')),
			self::TAXABLE => new DevblocksSearchField(self::TAXABLE, 'product', 'taxable', $translate->_('product.taxable')),
			self::SKU => new DevblocksSearchField(self::SKU, 'product', 'sku', $translate->_('product.sku')),
			self::NAME => new DevblocksSearchField(self::NAME, 'product', 'name', $translate->_('product.name')),
			self::DESCRIPTION => new DevblocksSearchField(self::DESCRIPTION, 'product', 'description', $translate->_('product.description')),

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
class Model_Product {
	public $id;
	public $price;
	public $price_setup;
	public $recurring;
	public $taxable;
	public $sku;
	public $name;
	public $description;
	
	public function getSettings() {
		return DAO_ProductSetting::getProductSettings($this->id);
	}

	public function getSetting($name, $default = '') {
		return DAO_ProductSetting::getProducSetting($this->id, $name, $default);
	}
};

class View_Product extends C4_AbstractView {
	const DEFAULT_ID = 'product';

	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
	
		$this->id = self::DEFAULT_ID;
		// [TODO] Name the worklist view
		$this->name = $translate->_('Product');
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_Product::ID;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_Product::NAME,
			SearchFields_Product::PRICE,
			SearchFields_Product::PRICE_SETUP,
			SearchFields_Product::RECURRING,
			SearchFields_Product::TAXABLE,
			SearchFields_Product::SKU,
		);
		// [TODO] Filter fields
		$this->addColumnsHidden(array(
			SearchFields_Product::DESCRIPTION,
		));
		
		// [TODO] Filter fields
		$this->addParamsHidden(array(
		));
		
		$this->doResetCriteria();
	}

	function getData() {
		$objects = DAO_Product::search(
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
		return $this->_doGetDataSample('DAO_Product', $size);
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
		$tpl->display('devblocks:osellot.core::billing/tabs/products/view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		// [TODO] Move the fields into the proper data type
		switch($field) {
			case SearchFields_Product::ID:
			case SearchFields_Product::PRICE:
			case SearchFields_Product::PRICE_SETUP:
			case SearchFields_Product::RECURRING:
			case SearchFields_Product::TAXABLE:
			case SearchFields_Product::SKU:
			case SearchFields_Product::NAME:
			case SearchFields_Product::DESCRIPTION:

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
		return SearchFields_Product::getFields();
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		// [TODO] Move fields into the right data type
		switch($field) {
			case SearchFields_Product::ID:
			case SearchFields_Product::PRICE:
			case SearchFields_Product::PRICE_SETUP:
			case SearchFields_Product::RECURRING:
			case SearchFields_Product::TAXABLE:
			case SearchFields_Product::SKU:
			case SearchFields_Product::NAME:
			case SearchFields_Product::DESCRIPTION:

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
					//$change_fields[DAO_Product::EXAMPLE] = 'some value';
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
			list($objects,$null) = DAO_Product::search(
				array(),
				$this->getParams(),
				100,
				$pg++,
				SearchFields_Product::ID,
				true,
				false
			);
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(count($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			
			DAO_Product::update($batch_ids, $change_fields);

			// Custom Fields
			//self::_doBulkSetCustomFields(ChCustomFieldSource_Product::ID, $custom_fields, $batch_ids);
			
			unset($batch_ids);
		}

		unset($ids);
	}			
};

class DAO_ProductSetting extends DevblocksORMHelper {
	const PRODUCT_ID = 'product_id';
	const NAME = 'name';
	const VALUE = 'value';
	
	/**
	 * @param string $where
	 * @param mixed $sortBy
	 * @param mixed $sortAsc
	 * @param integer $limit
	 * @return Model_ProductSetting[]
	 */
	static function getWhere($where=null, $sortBy=null, $sortAsc=true, $limit=null) {
		$db = DevblocksPlatform::getDatabaseService();

		list($where_sql, $sort_sql, $limit_sql) = self::_getWhereSQL($where, $sortBy, $sortAsc, $limit);
		
		// SQL
		$sql = "SELECT product_id, name, value ".
			"FROM product_setting ".
			$where_sql.
			$sort_sql.
			$limit_sql
		;
		$rs = $db->Execute($sql);
		return self::_getObjectsFromResult($rs);
	}
	
	/**
	 * @param integer $product_id
	 * @param string $name
	 * @param string value
	 * @return Model_ProductSetting[]
	 */
	static function setProductSetting($product_id, $name, $value) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("REPLACE INTO product_setting (%s, %s, %s) VALUES (%s, %s, %s)",
			self::PRODUCT_ID,
			self::NAME,
			self::VALUE,
			$product_id,
			$db->qstr($name),
			$db->qstr($value)
		);
		$db->Execute($sql);
				
		return null;
	}

	/**
	* @param integer $product_id
	* @param string $name
	* @param string $default
	* @return Model_ProductSetting[]
	*/
	static function getProductSetting($product_id, $name, $default) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$setting = self::getWhere(sprintf("%s = %d AND %s = %s",
			self::PRODUCT_ID,
			$product_id,
			self::NAME,
			$db->qstr($name)
		));
		
		if(!empty($setting)) {
			return array_shift($setting);
		}
		
		return $default;
	}
	
	/**
	 * @param integer $product_id
	 * @return Model_ProductSetting[]
	 */
	static function getProductSettings($product_id) {
		$objects = self::getWhere(sprintf("%s = %d",
			self::PRODUCT_ID,
			$product_id
		));
		
		if(count($objects))
			return $objects;
		
		return null;
	}
	
	/**
	 * @param resource $rs
	 * @return Model_ProductSetting[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_ProductSetting();
			$object->product_id = $row['product_id'];
			$object->name = $row['name'];
			$object->value = $row['value'];

			$objects[] = $object;
		}
		
		mysql_free_result($rs);
		
		return $objects;
	}

};

class Model_ProductSetting {
	public $product_id;
	public $name;
	public $value;
};