<?php
class DAO_BoxItem extends C4_ORMHelper {
	const ID = 'id';
	const ITEM = 'item';
	const SOURCE = 'source';
	const ORIGIN = 'origin';
	const UNIT = 'unit';
	const WEIGHED = 'weighed';
	const CASECOST = 'casecost';
	const UNITSPERCASE = 'unitspercase';
	const UNITCOST = 'unitcost';
	const CASESNEEDED = 'casesneeded';
	const CASESROUNDED = 'casesrounded';
	const REMAINDER = 'remainder';
	const GUIDANCE = 'guidance';
	const STARTDATE = 'startdate';
	const ENDDATE = 'enddate';
	const TOTALCOST = 'totalcost';
	const PRODUCTS = 'products';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();

		$sql = "INSERT INTO box_item () VALUES ()";
		$db->Execute($sql);
		$id = $db->LastInsertId();

		self::update($id, $fields);

		return $id;
	}

	static function update($ids, $fields) {
		parent::_update($ids, 'box_item', $fields);
	}

	static function updateWhere($fields, $where) {
		parent::_updateWhere('box_item', $fields, $where);
	}

	/**
	 * @param string $where
	 * @param mixed $sortBy
	 * @param mixed $sortAsc
	 * @param integer $limit
	 * @return Model_BoxItem[]
	 */
	static function getWhere($where=null, $sortBy=null, $sortAsc=true, $limit=null) {
		$db = DevblocksPlatform::getDatabaseService();

		list($where_sql, $sort_sql, $limit_sql) = self::_getWhereSQL($where, $sortBy, $sortAsc, $limit);

		// SQL
		$sql = "SELECT id, item, source, origin, unit, weighed, casecost, unitspercase, unitcost, casesneeded, casesrounded, remainder, guidance, startdate, enddate, totalcost, products ".
			"FROM box_item ".
			$where_sql.
			$sort_sql.
			$limit_sql
		;
		$rs = $db->Execute($sql);

		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_BoxItem	 */
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
	* @param integer $startdate
	* @param integer $enddate
	* @return Model_BoxItem	 */
	static function getByDateRange($startdate, $enddate) {
		$objects = self::getWhere(sprintf("%s >= %d AND %s <= %d",
			self::STARTDATE,
			$startdate,
			self::ENDDATE,
			$enddate
		));
	
		if(count($objects))
			return $objects;
	
		return null;
	}
	

	/**
	 * @param resource $rs
	 * @return Model_BoxItem[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();

		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_BoxItem();
			$object->id = $row['id'];
			$object->item = $row['item'];
			$object->source = $row['source'];
			$object->origin = $row['origin'];
			$object->unit = $row['unit'];
			$object->weighed = $row['weighed'];
			$object->casecost = $row['casecost'];
			$object->unitspercase = $row['unitspercase'];
			$object->unitcost = $row['unitcost'];
			$object->casesneeded = $row['casesneeded'];
			$object->casesrounded = $row['casesrounded'];
			$object->remainder = $row['remainder'];
			$object->guidance = $row['guidance'];
			$object->startdate = $row['startdate'];
			$object->enddate = $row['enddate'];
			$object->totalcost = $row['totalcost'];
			$object->products = json_decode($row['products'], true);
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

		$db->Execute(sprintf("DELETE FROM box_item WHERE id IN (%s)", $ids_list));

		// Fire event
		/*
		$eventMgr = DevblocksPlatform::getEventService();
		$eventMgr->trigger(
		new Model_DevblocksEvent(
		'context.delete',
		array(
		'context' => 'cerberusweb.contexts.',
		'context_ids' => $ids
		)
		)
		);
		*/

		return true;
	}

	public static function getSearchQueryComponents($columns, $params, $sortBy=null, $sortAsc=null) {
		$fields = SearchFields_BoxItem::getFields();

		// Sanitize
		if('*'==substr($sortBy,0,1) || !isset($fields[$sortBy]))
		$sortBy=null;

		list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);

		$select_sql = sprintf("SELECT ".
			"box_item.id as %s, ".
			"box_item.item as %s, ".
			"box_item.source as %s, ".
			"box_item.origin as %s, ".
			"box_item.unit as %s, ".
			"box_item.weighed as %s, ".
			"box_item.casecost as %s, ".
			"box_item.unitspercase as %s, ".
			"box_item.unitcost as %s, ".
			"box_item.casesneeded as %s, ".
			"box_item.casesrounded as %s, ".
			"box_item.remainder as %s, ".
			"box_item.guidance as %s, ".
			"box_item.startdate as %s, ".
			"box_item.enddate as %s, ".
			"box_item.totalcost as %s ".
			"box_item.products as %s ",
			SearchFields_BoxItem::ID,
			SearchFields_BoxItem::ITEM,
			SearchFields_BoxItem::SOURCE,
			SearchFields_BoxItem::ORIGIN,
			SearchFields_BoxItem::UNIT,
			SearchFields_BoxItem::WEIGHED,
			SearchFields_BoxItem::CASECOST,
			SearchFields_BoxItem::UNITSPERCASE,
			SearchFields_BoxItem::UNITCOST,
			SearchFields_BoxItem::CASESNEEDED,
			SearchFields_BoxItem::CASESROUNDED,
			SearchFields_BoxItem::REMAINDER,
			SearchFields_BoxItem::GUIDANCE,
			SearchFields_BoxItem::STARTDATE,
			SearchFields_BoxItem::ENDDATE,
			SearchFields_BoxItem::TOTALCOST,
			SearchFields_BoxItem::PRODUCTS
		);
			
		$join_sql = "FROM box_item ";

		// Custom field joins
		//list($select_sql, $join_sql, $has_multiple_values) = self::_appendSelectJoinSqlForCustomFieldTables(
		//	$tables,
		//	$params,
		//	'box_item.id',
		//	$select_sql,
		//	$join_sql
		//);
		$has_multiple_values = false; // [TODO] Temporary when custom fields disabled

		$where_sql = "".
		(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "WHERE 1 ");
			
		$sort_sql = (!empty($sortBy)) ? sprintf("ORDER BY %s %s ",$sortBy,($sortAsc || is_null($sortAsc))?"ASC":"DESC") : " ";

		return array(
			'primary_table' => 'box_item',
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
			($has_multiple_values ? 'GROUP BY box_item.id ' : '').
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
			$object_id = intval($row[SearchFields_BoxItem::ID]);
			$results[$object_id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
			$count_sql =
			($has_multiple_values ? "SELECT COUNT(DISTINCT box_item.id) " : "SELECT COUNT(box_item.id) ").
			$join_sql.
			$where_sql;
			$total = $db->GetOne($count_sql);
		}

		mysql_free_result($rs);

		return array($results,$total);
	}

};

class SearchFields_BoxItem implements IDevblocksSearchFields {
	const ID = 'b_id';
	const ITEM = 'b_item';
	const SOURCE = 'b_source';
	const ORIGIN = 'b_origin';
	const UNIT = 'b_unit';
	const WEIGHED = 'b_weighed';
	const CASECOST = 'b_casecost';
	const UNITSPERCASE = 'b_unitspercase';
	const UNITCOST = 'b_unitcost';
	const CASESNEEDED = 'b_casesneeded';
	const CASESROUNDED = 'b_casesrounded';
	const REMAINDER = 'b_remainder';
	const GUIDANCE = 'b_guidance';
	const STARTDATE = 'b_startdate';
	const ENDDATE = 'b_enddate';
	const TOTALCOST = 'b_totalcost';
	const PRODUCTS = 'b_products';

	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();

		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'box_item', 'id', $translate->_('box_item.id')),
			self::ITEM => new DevblocksSearchField(self::ITEM, 'box_item', 'item', $translate->_('box_item.item')),
			self::SOURCE => new DevblocksSearchField(self::SOURCE, 'box_item', 'source', $translate->_('box_item.source')),
			self::ORIGIN => new DevblocksSearchField(self::ORIGIN, 'box_item', 'origin', $translate->_('box_item.origin')),
			self::UNIT => new DevblocksSearchField(self::UNIT, 'box_item', 'unit', $translate->_('box_item.unit')),
			self::WEIGHED => new DevblocksSearchField(self::WEIGHED, 'box_item', 'weighed', $translate->_('box_item.weighed')),
			self::CASECOST => new DevblocksSearchField(self::CASECOST, 'box_item', 'casecost', $translate->_('box_item.casecost')),
			self::UNITSPERCASE => new DevblocksSearchField(self::UNITSPERCASE, 'box_item', 'unitspercase', $translate->_('box_item.unitspercase')),
			self::UNITCOST => new DevblocksSearchField(self::UNITCOST, 'box_item', 'unitcost', $translate->_('box_item.unitcost')),
			self::CASESNEEDED => new DevblocksSearchField(self::CASESNEEDED, 'box_item', 'casesneeded', $translate->_('box_item.casesneeded')),
			self::CASESROUNDED => new DevblocksSearchField(self::CASESROUNDED, 'box_item', 'casesrounded', $translate->_('box_item.casesrounded')),
			self::REMAINDER => new DevblocksSearchField(self::REMAINDER, 'box_item', 'remainder', $translate->_('box_item.remainder')),
			self::GUIDANCE => new DevblocksSearchField(self::GUIDANCE, 'box_item', 'guidance', $translate->_('box_item.guidance')),
			self::STARTDATE => new DevblocksSearchField(self::STARTDATE, 'box_item', 'startdate', $translate->_('box_item.startdate')),
			self::ENDDATE => new DevblocksSearchField(self::ENDDATE, 'box_item', 'enddate', $translate->_('box_item.enddate')),
			self::TOTALCOST => new DevblocksSearchField(self::TOTALCOST, 'box_item', 'totalcost', $translate->_('box_item.totalcost')),
			self::PRODUCTS => new DevblocksSearchField(self::PRODUCTS, 'box_item', 'totalcost', $translate->_('box_item.products')),
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

class Model_BoxItem {
	public $id;
	public $item;
	public $source;
	public $origin;
	public $unit;
	public $weighed;
	public $casecost;
	public $unitspercase;
	public $unitcost;
	public $casesneeded;
	public $casesrounded;
	public $remainder;
	public $guidance;
	public $startdate;
	public $enddate;
	public $totalcost;
	public $products;
};

class View_BoxItem extends C4_AbstractView {
	const DEFAULT_ID = 'boxitem';

	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();

		$this->id = self::DEFAULT_ID;
		// [TODO] Name the worklist view
		$this->name = $translate->_('BoxItem');
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_BoxItem::ID;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_BoxItem::ID,
			SearchFields_BoxItem::ITEM,
			SearchFields_BoxItem::SOURCE,
			SearchFields_BoxItem::ORIGIN,
			SearchFields_BoxItem::UNIT,
			SearchFields_BoxItem::WEIGHED,
			SearchFields_BoxItem::CASECOST,
			SearchFields_BoxItem::UNITSPERCASE,
			SearchFields_BoxItem::UNITCOST,
			SearchFields_BoxItem::CASESNEEDED,
			SearchFields_BoxItem::CASESROUNDED,
			SearchFields_BoxItem::REMAINDER,
			SearchFields_BoxItem::GUIDANCE,
			SearchFields_BoxItem::STARTDATE,
			SearchFields_BoxItem::ENDDATE,
			SearchFields_BoxItem::TOTALCOST,
			SearchFields_BoxItem::PRODUCTS,
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
		$objects = DAO_BoxItem::search(
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
		return $this->_doGetDataSample('DAO_BoxItem', $size);
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
			case SearchFields_BoxItem::ID:
			case SearchFields_BoxItem::ITEM:
			case SearchFields_BoxItem::SOURCE:
			case SearchFields_BoxItem::ORIGIN:
			case SearchFields_BoxItem::UNIT:
			case SearchFields_BoxItem::WEIGHED:
			case SearchFields_BoxItem::CASECOST:
			case SearchFields_BoxItem::UNITSPERCASE:
			case SearchFields_BoxItem::UNITCOST:
			case SearchFields_BoxItem::CASESNEEDED:
			case SearchFields_BoxItem::CASESROUNDED:
			case SearchFields_BoxItem::REMAINDER:
			case SearchFields_BoxItem::GUIDANCE:
			case SearchFields_BoxItem::STARTDATE:
			case SearchFields_BoxItem::ENDDATE:
			case SearchFields_BoxItem::TOTALCOST:
			case SearchFields_BoxItem::PRODUCTS:
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
		return SearchFields_BoxItem::getFields();
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		// [TODO] Move fields into the right data type
		switch($field) {
			case SearchFields_BoxItem::ID:
			case SearchFields_BoxItem::ITEM:
			case SearchFields_BoxItem::SOURCE:
			case SearchFields_BoxItem::ORIGIN:
			case SearchFields_BoxItem::UNIT:
			case SearchFields_BoxItem::WEIGHED:
			case SearchFields_BoxItem::CASECOST:
			case SearchFields_BoxItem::UNITSPERCASE:
			case SearchFields_BoxItem::UNITCOST:
			case SearchFields_BoxItem::CASESNEEDED:
			case SearchFields_BoxItem::CASESROUNDED:
			case SearchFields_BoxItem::REMAINDER:
			case SearchFields_BoxItem::GUIDANCE:
			case SearchFields_BoxItem::STARTDATE:
			case SearchFields_BoxItem::ENDDATE:
			case SearchFields_BoxItem::TOTALCOST:
			case SearchFields_BoxItem::PRODUCTS:
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
		@set_time_limit(600); // 10m

		$change_fields = array();
		$custom_fields = array();

		// Make sure we have actions
		if(empty($do))
		return;

		// Make sure we have checked item if we want a checked list
		if(0 == strcasecmp($filter,"checks") && empty($ids))
		return;
			
		if(is_array($do))
			foreach($do as $k => $v) {
				switch($k) {
					// [TODO] Implement actions
					case 'example':
						//$change_fields[DAO_BoxItem::EXAMPLE] = 'some value';
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
				list($objects,$null) = DAO_BoxItem::search(
				array(),
				$this->getParams(),
				100,
				$pg++,
				SearchFields_BoxItem::ID,
				true,
				false
				);
				$ids = array_merge($ids, array_keys($objects));
	
			} while(!empty($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			
			DAO_BoxItem::update($batch_ids, $change_fields);
	
			// Custom Fields
			//self::_doBulkSetCustomFields(ChCustomFieldSource_BoxItem::ID, $custom_fields, $batch_ids);
				
			unset($batch_ids);
		}

		unset($ids);
	}
};

class DAO_BoxItemSource extends C4_ORMHelper {
	const ID = 'id';
	const SOURCE = 'source';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();

		$sql = "INSERT INTO box_item_source () VALUES ()";
		$db->Execute($sql);
		$id = $db->LastInsertId();

		self::update($id, $fields);

		return $id;
	}

	static function update($ids, $fields) {
		parent::_update($ids, 'box_item_source', $fields);
	}

	static function updateWhere($fields, $where) {
		parent::_updateWhere('box_item_source', $fields, $where);
	}

	/**
	 * @param string $where
	 * @param mixed $sortBy
	 * @param mixed $sortAsc
	 * @param integer $limit
	 * @return Model_BoxItemSource[]
	 */
	static function getWhere($where=null, $sortBy=null, $sortAsc=true, $limit=null) {
		$db = DevblocksPlatform::getDatabaseService();

		list($where_sql, $sort_sql, $limit_sql) = self::_getWhereSQL($where, $sortBy, $sortAsc, $limit);

		// SQL
		$sql = "SELECT id, name ".
			"FROM box_item_source ".
			$where_sql.
			$sort_sql.
			$limit_sql
		;
		$rs = $db->Execute($sql);

		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_BoxItemSource	 */
	static function get($id) {
		$objects = self::getWhere(sprintf("%s = %d",
			self::ID,
			$id
		));

		if(isset($objects[$id]))
			return $objects[$id];

		return null;
	}
	
	static function getAll() {
		return self::getWhere();
	}

	/**
	 * @param resource $rs
	 * @return Model_BoxItemSource[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();

		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_BoxItemSource();
			$object->id = $row['id'];
			$object->name = $row['name'];
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

		$db->Execute(sprintf("DELETE FROM box_item_source WHERE id IN (%s)", $ids_list));

		// Fire event
		/*
		$eventMgr = DevblocksPlatform::getEventService();
		$eventMgr->trigger(
		new Model_DevblocksEvent(
		'context.delete',
		array(
		'context' => 'cerberusweb.contexts.',
		'context_ids' => $ids
		)
		)
		);
		*/

		return true;
	}

	public static function getSearchQueryComponents($columns, $params, $sortBy=null, $sortAsc=null) {
		$fields = SearchFields_BoxItemSource::getFields();

		// Sanitize
		if('*'==substr($sortBy,0,1) || !isset($fields[$sortBy]))
		$sortBy=null;

		list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);

		$select_sql = sprintf("SELECT ".
			"box_item_source.id as %s, ".
			"box_item_source.source as %s ",
			SearchFields_BoxItemSource::ID,
			SearchFields_BoxItemSource::NAME
		);
			
		$join_sql = "FROM box_item_source ";

		// Custom field joins
		//list($select_sql, $join_sql, $has_multiple_values) = self::_appendSelectJoinSqlForCustomFieldTables(
		//	$tables,
		//	$params,
		//	'box_item_source.id',
		//	$select_sql,
		//	$join_sql
		//);
		$has_multiple_values = false; // [TODO] Temporary when custom fields disabled

		$where_sql = "".
		(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "WHERE 1 ");
			
		$sort_sql = (!empty($sortBy)) ? sprintf("ORDER BY %s %s ",$sortBy,($sortAsc || is_null($sortAsc))?"ASC":"DESC") : " ";

		return array(
			'primary_table' => 'box_item_source',
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
			($has_multiple_values ? 'GROUP BY box_item_source.id ' : '').
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
			$object_id = intval($row[SearchFields_BoxItemSource::ID]);
			$results[$object_id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
			$count_sql =
			($has_multiple_values ? "SELECT COUNT(DISTINCT box_item_source.id) " : "SELECT COUNT(box_item_source.id) ").
			$join_sql.
			$where_sql;
			$total = $db->GetOne($count_sql);
		}

		mysql_free_result($rs);

		return array($results,$total);
	}

};

class SearchFields_BoxItemSource implements IDevblocksSearchFields {
	const ID = 'b_id';
	const SOURCE = 'b_source';

	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();

		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'box_item_source', 'id', $translate->_('box_item_source.id')),
			self::SOURCE => new DevblocksSearchField(self::SOURCE, 'box_item_source', 'source', $translate->_('box_item_source.source')),
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

class Model_BoxItemSource {
	public $id;
	public $name;
};

class View_BoxItemSource extends C4_AbstractView {
	const DEFAULT_ID = 'boxitemsource';

	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();

		$this->id = self::DEFAULT_ID;
		// [TODO] Name the worklist view
		$this->name = $translate->_('BoxItemSource');
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_BoxItemSource::ID;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_BoxItemSource::ID,
			SearchFields_BoxItemSource::NAME,
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
		$objects = DAO_BoxItemSource::search(
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
		return $this->_doGetDataSample('DAO_BoxItemSource', $size);
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
			case SearchFields_BoxItemSource::ID:
			case SearchFields_BoxItemSource::NAME:
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
		return SearchFields_BoxItemSource::getFields();
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		// [TODO] Move fields into the right data type
		switch($field) {
			case SearchFields_BoxItemSource::ID:
			case SearchFields_BoxItemSource::NAME:
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
		@set_time_limit(600); // 10m

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
					//$change_fields[DAO_BoxItemSource::EXAMPLE] = 'some value';
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
				list($objects,$null) = DAO_BoxItemSource::search(
				array(),
				$this->getParams(),
				100,
				$pg++,
				SearchFields_BoxItemSource::ID,
				true,
				false
				);
				$ids = array_merge($ids, array_keys($objects));
	
			} while(!empty($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			
			DAO_BoxItemSource::update($batch_ids, $change_fields);
	
			// Custom Fields
			//self::_doBulkSetCustomFields(ChCustomFieldSource_BoxItemSource::ID, $custom_fields, $batch_ids);
				
			unset($batch_ids);
		}

		unset($ids);
	}
};