<?php
class DAO_Product extends C4_ORMHelper {
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
	* @return Model_ProductAttribute
	*/
	static function getProductAttributes($id) {
		$attributes = DAO_ProductAttribute::getProductAttributes($id);

		return $attributes;
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
	
	public static function random() {
		return self::_getRandom('product');
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
		
//		if(isset($tables['product_attribute'])) {
//			$select_sql .= sprintf(
//				", pc.name AS %s, ".
//				"pc.value AS %s",
//				SearchFields_ProductAttribute::NAME,
//				SearchFields_ProductAttribute::VALUE
//			);
//			$join_sql .= "LEFT JOIN product_attribute pc ON (p.id=pc.product_id) ";
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
	
	public function getAttribute($name, $default = null) {
		return DAO_ProductAttribute::getProductAttribute($this->id, $name, $default);
	}
	
// 	public function getAttributeGroup($prefix) {
// 		return DAO_InvoiceAttribute::getInvoiceAttributeGroup($this->id, $prefix);
// 	}
	
	public function getAttributes() {
		$attributes = DAO_ProductAttribute::getProductAttributes($this->id);
		$product_attributes = array();
		foreach($attributes as $attribute) {
			$product_attributes[$attribute->name] = $attribute->value;
		}
		return $product_attributes;
	}
	
	public function setAttribute($name, $value) {
		if(null == DAO_ProductAttribute::getProductAttribute($this->id, $name, null)) {
			DAO_ProductAttribute::addProductAttribute($this->id, $name, $value);
		} else {
			DAO_ProductAttribute::setProductAttribute($this->id, $name, $value);
		}
	}
};

class View_Product extends C4_AbstractView implements IAbstractView_Subtotals, IAbstractView_QuickSearch {
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
	
	function getSubtotalFields() {
		$all_fields = $this->getParamsAvailable();
		
		$fields = array();

		if(is_array($all_fields))
		foreach($all_fields as $field_key => $field_model) {
			$pass = false;
			
			switch($field_key) {
				// DAO
				case SearchFields_Ticket::ORG_NAME:
				case SearchFields_Ticket::TICKET_FIRST_WROTE:
				case SearchFields_Ticket::TICKET_LAST_WROTE:
				case SearchFields_Ticket::TICKET_SPAM_TRAINING:
				case SearchFields_Ticket::TICKET_SUBJECT:
				case SearchFields_Ticket::TICKET_GROUP_ID:
				case SearchFields_Ticket::TICKET_OWNER_ID:
					$pass = true;
					break;

				// Virtuals
				case SearchFields_Ticket::VIRTUAL_STATUS:
					$pass = true;
					break;
					
				case SearchFields_Ticket::VIRTUAL_CONTEXT_LINK:
				case SearchFields_Ticket::VIRTUAL_WATCHERS:
					$pass = true;
					break;
					
				// Valid custom fields
				default:
					if('cf_' == substr($field_key,0,3))
						$pass = $this->_canSubtotalCustomField($field_key);
					break;
			}
			
			if($pass)
				$fields[$field_key] = $field_model;
		}
		
		return $fields;
	}
	
	function getSubtotalCounts($column) {
		$counts = array();
		$fields = $this->getFields();

		if(!isset($fields[$column]))
			return array();
		
		switch($column) {
			case SearchFields_Ticket::ORG_NAME:
			case SearchFields_Ticket::TICKET_FIRST_WROTE:
			case SearchFields_Ticket::TICKET_LAST_WROTE:
			case SearchFields_Ticket::TICKET_SUBJECT:
				$counts = $this->_getSubtotalCountForStringColumn('DAO_Ticket', $column);
				break;
				
			case SearchFields_Ticket::TICKET_SPAM_TRAINING:
				$label_map = array(
					'' => 'Not trained',
					'S' => 'Spam',
					'N' => 'Not spam',
				);
				$counts = $this->_getSubtotalCountForStringColumn('DAO_Ticket', $column, $label_map);
				break;
				
			case SearchFields_Ticket::TICKET_OWNER_ID:
				$label_map = array();
				$workers = DAO_Worker::getAll();
				foreach($workers as $k => $v)
					$label_map[$k] = $v->getName();
				$counts = $this->_getSubtotalCountForStringColumn('DAO_Ticket', $column, $label_map, 'in', 'worker_id[]');
				break;
				
			case SearchFields_Ticket::TICKET_GROUP_ID:
				$counts = $this->_getSubtotalCountForBuckets();
				break;
				
			case SearchFields_Ticket::VIRTUAL_STATUS:
				$counts = $this->_getSubtotalCountForStatus();
				break;
				
			case SearchFields_Ticket::VIRTUAL_CONTEXT_LINK:
				$counts = $this->_getSubtotalCountForContextLinkColumn('DAO_Ticket', CerberusContexts::CONTEXT_TICKET, $column);
				break;
				
			case SearchFields_Ticket::VIRTUAL_WATCHERS:
				$counts = $this->_getSubtotalCountForWatcherColumn('DAO_Ticket', $column);
				break;
				
			default:
				// Custom fields
				if('cf_' == substr($column,0,3)) {
					$counts = $this->_getSubtotalCountForCustomColumn('DAO_Ticket', $column, 't.id');
				}
				
				break;
		}
		
		return $counts;
	}
	
	function isQuickSearchField($token) {
		switch($token) {
			case SearchFields_Ticket::TICKET_GROUP_ID:
			case SearchFields_Ticket::VIRTUAL_STATUS:
				return true;
			break;
		}
		
		return false;
	}
	
	function quickSearch($token, $query, &$oper, &$value) {
		switch($token) {
			case SearchFields_Ticket::VIRTUAL_STATUS:
				$statuses = array();
				$oper = DevblocksSearchCriteria::OPER_IN;
				
				if(preg_match('#([\!\=]+)(.*)#', $query, $matches)) {
					$oper_hint = trim($matches[1]);
					$query = trim($matches[2]);
					
					switch($oper_hint) {
						case '!':
						case '!=':
							$oper = DevblocksSearchCriteria::OPER_NIN;
							break;
					}
				}
				
				$inputs = DevblocksPlatform::parseCsvString($query);
				
				if(is_array($inputs))
				foreach($inputs as $v) {
					switch(strtolower(substr($v,0,1))) {
						case 'o':
							$statuses['open'] = true;
							break;
						case 'w':
							$statuses['waiting'] = true;
							break;
						case 'c':
							$statuses['closed'] = true;
							break;
						case 'd':
							$statuses['deleted'] = true;
							break;
					}
				}
				
				if(empty($statuses)) {
					$value = null;
					
				} else {
					$value = array_keys($statuses);
				}
				
				return true;
				break;
				
			case SearchFields_Ticket::TICKET_GROUP_ID:
				$search_ids = array();
				$oper = DevblocksSearchCriteria::OPER_IN;
				
				if(preg_match('#([\!\=]+)(.*)#', $query, $matches)) {
					$oper_hint = trim($matches[1]);
					$query = trim($matches[2]);
					
					switch($oper_hint) {
						case '!':
						case '!=':
							$oper = DevblocksSearchCriteria::OPER_NIN;
							break;
					}
				}
				
				$groups = DAO_Group::getAll();
				$inputs = DevblocksPlatform::parseCsvString($query);

				if(is_array($inputs))
				foreach($inputs as $input) {
					foreach($groups as $group_id => $group) {
						if(0 == strcasecmp($input, substr($group->name,0,strlen($input))))
							$search_ids[$group_id] = true;
					}
				}
				
				if(!empty($search_ids)) {
					$value = array_keys($search_ids);
				} else {
					$value = null;
				}
				
				return true;
				break;
				
		}
		
		return false;
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
		$tpl->display('devblocks:osellot.core::products/view.tpl');
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
				$tpl->display('devblocks:cerberusweb.core::internal/views/criteria/__string.tpl');
				break;
			case 'placeholder_number':
				$tpl->display('devblocks:cerberusweb.core::internal/views/criteria/__number.tpl');
				break;
			case 'placeholder_bool':
				$tpl->display('devblocks:cerberusweb.core::internal/views/criteria/__bool.tpl');
				break;
			case 'placeholder_date':
				$tpl->display('devblocks:cerberusweb.core::internal/views/criteria/__date.tpl');
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

class Context_Product extends Extension_DevblocksContext implements IDevblocksContextProfile, IDevblocksContextPeek {
	function authorize($context_id, Model_Worker $worker) {
		return TRUE;
	}
	
	function getRandom() {
		return DAO_Product::random();
	}

	function profileGetUrl($context_id) {
		if(empty($context_id))
			return '';
		
		$url_writer = DevblocksPlatform::getUrlService();
		$url = $url_writer->writeNoProxy(sprintf("c=profiles&type=product&id=%d", $context_id, true));
		return $url;
	}
	
	function getMeta($context_id) {
		$product = DAO_Product::get($context_id);
		
		$url = $this->profileGetUrl($context_id);
		$friendly = DevblocksPlatform::strToPermalink($product->name);
		
		if(!empty($friendly))
			$url .= '-' . $friendly;
		
		return array(
			'id' => $product->id,
			'name' => $product->name,
			'permalink' => $url,
		);
	}
	
	function getContext($product, &$token_labels, &$token_values, $prefix=null) {
		if(is_null($prefix))
			$prefix = 'Product:';
		
		$translate = DevblocksPlatform::getTranslationService();
		$fields = DAO_CustomField::getByContext(CerberusContexts::CONTEXT_KB_ARTICLE);
		
		// Polymorph
		if(is_numeric($product)) {
			$product = DAO_Product::get($product);
		} elseif($product instanceof Model_Product) {
			// It's what we want already.
		} else {
			$product = null;
		}
		/* @var $product Model_Product */
		
		// Token labels
		$token_labels = array(
			'content' => $prefix.$translate->_('kb_article.content'),
			'id' => $prefix.$translate->_('common.id'),
			'title' => $prefix.$translate->_('kb_article.title'),
			'updated|date' => $prefix.$translate->_('kb_article.updated'),
			'views' => $prefix.$translate->_('kb_article.views'),
			'record_url' => $prefix.$translate->_('common.url.record'),
		);
		
		if(is_array($fields))
		foreach($fields as $cf_id => $field) {
			$token_labels['custom_'.$cf_id] = $prefix.$field->name;
		}

		// Token values
		$token_values = array();
		
		$token_values['_context'] = CerberusContexts::CONTEXT_KB_ARTICLE;
		
		// Token values
		if(null != $product) {
			$token_values['_label'] = $product->name;
			$token_values['content'] = $product->getContent();
			$token_values['id'] = $product->id;
			$token_values['title'] = $product->title;
			$token_values['updated'] = $product->updated;
			$token_values['views'] = $product->views;
			
			// URL
			$url_writer = DevblocksPlatform::getUrlService();
			$token_values['record_url'] = $url_writer->writeNoProxy(sprintf("c=profiles&type=product&id=%d-%s",$product->id, DevblocksPlatform::strToPermalink($product->title)), true);
		}
		
		return TRUE;
	}

	function lazyLoadContextValues($token, $dictionary) {
		if(!isset($dictionary['id']))
			return;
		
		$context = CerberusContexts::CONTEXT_KB_ARTICLE;
		$context_id = $dictionary['id'];
		
		@$is_loaded = $dictionary['_loaded'];
		$values = array();
		
		if(!$is_loaded) {
			$labels = array();
			CerberusContexts::getContext($context, $context_id, $labels, $values);
		}
		
		switch($token) {
			case 'watchers':
				$watchers = array(
					$token => CerberusContexts::getWatchers($context, $context_id, true),
				);
				$values = array_merge($values, $watchers);
				break;
				
			default:
				if(substr($token,0,7) == 'custom_') {
					$fields = $this->_lazyLoadCustomFields($context, $context_id);
					$values = array_merge($values, $fields);
				}
				break;
		}
		
		return $values;
	}
	
	function getChooserView($view_id=null) {
		$active_worker = CerberusApplication::getActiveWorker();

		if(empty($view_id))
			$view_id = 'chooser_'.str_replace('.','_',$this->id).time().mt_rand(0,9999);
		
		// View
		$defaults = new C4_AbstractViewModel();
		$defaults->id = $view_id;
		$defaults->is_ephemeral = true;
		$defaults->class_name = $this->getViewClass();
		$view = C4_AbstractViewLoader::getView($view_id, $defaults);
//		$view->name = 'Headlines';
//		$view->view_columns = array(
//			SearchFields_CallEntry::IS_OUTGOING,
//			SearchFields_CallEntry::PHONE,
//			SearchFields_CallEntry::UPDATED_DATE,
//		);
		$view->addParams(array(
			//SearchFields_Product::IS_CLOSED => new DevblocksSearchCriteria(SearchFields_Product::IS_CLOSED,'=',0),
		), true);
		$view->renderSortBy = SearchFields_Product::NAME;
		$view->renderSortAsc = false;
		$view->renderLimit = 10;
		$view->renderFilters = false;
		$view->renderTemplate = 'contextlinks_chooser';
		
		C4_AbstractViewLoader::setView($view_id, $view);
		return $view;
	}
	
	function getView($context=null, $context_id=null, $options=array()) {
		$view_id = str_replace('.','_',$this->id);
		
		$defaults = new C4_AbstractViewModel();
		$defaults->id = $view_id;
		$defaults->class_name = $this->getViewClass();
		$view = C4_AbstractViewLoader::getView($view_id, $defaults);
		
		$params_req = array();
		
		if(!empty($context) && !empty($context_id)) {
			$params_req = array(
				new DevblocksSearchCriteria(SearchFields_Product::CONTEXT_LINK,'=',$context),
				new DevblocksSearchCriteria(SearchFields_Product::CONTEXT_LINK_ID,'=',$context_id),
			);
		}
		
		$view->addParamsRequired($params_req, true);
		
		$view->renderTemplate = 'context';
		C4_AbstractViewLoader::setView($view_id, $view);
		return $view;
	}
	
	function renderPeekPopup($context_id=0, $view_id='') {
		$tpl = DevblocksPlatform::getTemplateService();
		
		$product_attributes = array(
			'included_sd' => array('label' => 'Included SD Minutes', 'type' => 'int', 'value' => '0'),
			'included_hd' => array('label' => 'Included HD Minutes', 'type' => 'int', 'value' => '0'),
			'rate_sd_minutes' => array('label' => 'SD Overage Rate', 'type' => 'float', 'value' => '0'),
			'rate_hd_minutes' => array('label' => 'HD Overage Rate', 'type' => 'float', 'value' => '0'),
			'rate_clip_megabytes' => array('label' => 'Clip Overage Rate (Megabytes)', 'type' => 'float', 'value' => '0'),
			'rate_file_transfer' => array('label' => 'File Transfer', 'type' => 'float', 'value' => '0'),
			'pooled' => array('label' => 'Pooled Plan', 'type' => 'bool', 'value' => '0'),
			'billed_as_data' => array('label' => 'Billed As Data', 'type' => 'bool', 'value' => '0')
		);
		
		if(!empty($context_id)) {
			if(null !== ($product = DAO_Product::get($context_id))) {
				$tpl->assign('product', $product);
				
				foreach($product_attributes as $key => $attribute) {
					$attribute['value'] = $product->getAttribute($key, isset($attribute['value']) ? $attribute['value'] : null);
					$product_attributes[$key] = $attribute;
				}
			}
		}
		
		$tpl->assign('product_attributes', $product_attributes);
		
		if(!empty($view_id))
			$tpl->assign('view_id', $view_id);
		
		$tpl->display('devblocks:osellot.core::products/ajax/peek.tpl');
	}
};

class DAO_ProductAttribute extends C4_ORMHelper {
	const PRODUCT_ID = 'product_id';
	const NAME = 'name';
	const VALUE = 'value';
	
	/**
	 * @param string $where
	 * @param mixed $sortBy
	 * @param mixed $sortAsc
	 * @param integer $limit
	 * @return Model_ProductAttribute[]
	 */
	static function getWhere($where=null, $sortBy=null, $sortAsc=true, $limit=null) {
		$db = DevblocksPlatform::getDatabaseService();

		list($where_sql, $sort_sql, $limit_sql) = self::_getWhereSQL($where, $sortBy, $sortAsc, $limit);
		
		// SQL
		$sql = "SELECT product_id, name, value ".
			"FROM product_attribute ".
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
	* @return Model_ProductAttribute[]
	*/
	static function addProductAttribute($product_id, $name, $value) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("INSERT INTO product_attribute (%s, %s, %s) VALUES (%s, %s, %s)",
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
	 * @param string value
	 * @return Model_ProductAttribute[]
	 */
	static function setProductAttribute($product_id, $name, $value) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("UPDATE product_attribute SET %s = %s WHERE %s = %d AND %s = %s",
			self::VALUE,
			$db->qstr($value),
			self::PRODUCT_ID,
			$product_id,
			self::NAME,
			$db->qstr($name)
		);
		$db->Execute($sql);
				
		return null;
	}
	/**
	* @param integer $product_id
	* @param string $name
	* @param string $default
	* @return Model_ProductAttribute[]
	*/
	static function getProductAttribute($product_id, $name, $default) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$attribute = self::getWhere(sprintf("%s = %d AND %s = %s",
			self::PRODUCT_ID,
			$product_id,
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
	 * @param integer $product_id
	 * @return Model_ProductAttribute[]
	 */
	static function getProductAttributes($product_id) {
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
	 * @return Model_ProductAttribute[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_ProductAttribute();
			$object->product_id = $row['product_id'];
			$object->name = $row['name'];
			$object->value = $row['value'];

			$objects[] = $object;
		}
		
		mysql_free_result($rs);
		
		return $objects;
	}

};

class Model_ProductAttribute {
	public $product_id;
	public $name;
	public $value;
};