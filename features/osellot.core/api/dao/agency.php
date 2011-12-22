<?php
class DAO_Agency extends C4_ORMHelper {
	const ID = 'id';
	const NAME = 'name';
	const EMAIL_ID = 'email_id';
	const AUTH_SALT = 'auth_salt';
	const AUTH_PASSWORD = 'auth_password';
	const ADDRESS_LINE1 = 'address_line1';
	const ADDRESS_LINE2 = 'address_line2';
	const ADDRESS_CITY = 'address_city';
	const ADDRESS_PROVINCE = 'address_province';
	const ADDRESS_POSTAL = 'address_postal';
	const CREATED = 'created';
	const LAST_LOGIN = 'last_login';
	const POSITION = 'position';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "INSERT INTO agency () VALUES ()";
		$db->Execute($sql);
		$id = $db->LastInsertId();
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'agency', $fields);
	}
	
	static function updateWhere($fields, $where) {
		parent::_updateWhere('agency', $fields, $where);
	}
	
	/**
	 * @param string $where
	 * @param mixed $sortBy
	 * @param mixed $sortAsc
	 * @param integer $limit
	 * @return Model_Agency[]
	 */
	static function getWhere($where=null, $sortBy=null, $sortAsc=true, $limit=null) {
		$db = DevblocksPlatform::getDatabaseService();

		list($where_sql, $sort_sql, $limit_sql) = self::_getWhereSQL($where, $sortBy, $sortAsc, $limit);
		
		// SQL
		$sql = "SELECT id, name, email_id, auth_salt, auth_password, address_line1, address_line2, address_city, address_province, address_postal, created, last_login, position ".
			"FROM agency ".
			$where_sql.
			$sort_sql.
			$limit_sql
		;
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_Agency	 */
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
	* @param integer $email_id
	* @return Model_Agency	 */
	static function getAgencyByEmailId($email_id) {
		$objects = self::getWhere(sprintf("%s = %d",
			self::EMAIL_ID,
			$email_id
		));
	
		if(count($objects))
			return array_shift($objects);
	
		return null;
	}
	
	/**
	 * @param resource $rs
	 * @return Model_Agency[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_Agency();
			$object->id = $row['id'];
			$object->name = $row['name'];
			$object->email_id = $row['email_id'];
			$object->auth_salt = $row['auth_salt'];
			$object->auth_password = $row['auth_password'];
			$object->address_line1 = $row['address_line1'];
			$object->address_line2 = $row['address_line2'];
			$object->address_city = $row['address_city'];
			$object->address_province = $row['address_province'];
			$object->address_postal = $row['address_postal'];
			$object->created = $row['created'];
			$object->last_login = $row['last_login'];
			$object->position = $row['position'];
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
		
		$db->Execute(sprintf("DELETE FROM agency WHERE id IN (%s)", $ids_list));
		
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
		$fields = SearchFields_Agency::getFields();
		
		// Sanitize
		if('*'==substr($sortBy,0,1) || !isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);
		
		$select_sql = sprintf("SELECT ".
			"a.id as %s, ".
			"a.name as %s, ".
			"a.email_id as %s, ".
			"ad.email as %s, ".
			"a.auth_salt as %s, ".
			"a.auth_password as %s, ".
			"a.address_line1 as %s, ".
			"a.address_line2 as %s, ".
			"a.address_city as %s, ".
			"a.address_province as %s, ".
			"a.address_postal as %s, ".
			"a.created as %s, ".
			"a.last_login as %s, ".
			"a.position as %s ",
			SearchFields_Agency::ID,
			SearchFields_Agency::NAME,
			SearchFields_Agency::EMAIL_ID,
			SearchFields_Agency::EMAIL_ADDRESS,
			SearchFields_Agency::AUTH_SALT,
			SearchFields_Agency::AUTH_PASSWORD,
			SearchFields_Agency::ADDRESS_LINE1,
			SearchFields_Agency::ADDRESS_LINE2,
			SearchFields_Agency::ADDRESS_CITY,
			SearchFields_Agency::ADDRESS_PROVINCE,
			SearchFields_Agency::ADDRESS_POSTAL,
			SearchFields_Agency::CREATED,
			SearchFields_Agency::LAST_LOGIN,
			SearchFields_Agency::POSITION
		);
			
		$join_sql = 
			"FROM agency a ".
			"INNER JOIN address ad ON (ad.id = a.email_id) ".
		
		// Custom field joins
		//list($select_sql, $join_sql, $has_multiple_values) = self::_appendSelectJoinSqlForCustomFieldTables(
		//	$tables,
		//	$params,
		//	'agency.id',
		//	$select_sql,
		//	$join_sql
		//);
		$has_multiple_values = false; // [TODO] Temporary when custom fields disabled
				
		$where_sql = "".
			(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "WHERE 1 ");
			
		$sort_sql = (!empty($sortBy)) ? sprintf("ORDER BY %s %s ",$sortBy,($sortAsc || is_null($sortAsc))?"ASC":"DESC") : " ";
	
		return array(
			'primary_table' => 'agency',
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
			($has_multiple_values ? 'GROUP BY agency.id ' : '').
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
			$object_id = intval($row[SearchFields_Agency::ID]);
			$results[$object_id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
			$count_sql = 
				($has_multiple_values ? "SELECT COUNT(DISTINCT agency.id) " : "SELECT COUNT(agency.id) ").
				$join_sql.
				$where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
	}

};

class SearchFields_Agency implements IDevblocksSearchFields {
	const ID = 'a_id';
	const NAME = 'a_name';
	const EMAIL_ID = 'a_email_id';
	const AUTH_SALT = 'a_auth_salt';
	const AUTH_PASSWORD = 'a_auth_password';
	const ADDRESS_LINE1 = 'a_address_line1';
	const ADDRESS_LINE2 = 'a_address_line2';
	const ADDRESS_CITY = 'a_address_city';
	const ADDRESS_PROVINCE = 'a_address_province';
	const ADDRESS_POSTAL = 'a_address_postal';
	const CREATED = 'a_created';
	const LAST_LOGIN = 'a_last_login';
	const POSITION = 'a_position';
	
	const EMAIL_ADDRESS = 'a_email';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'agency', 'id', $translate->_('agency.id')),
			self::NAME => new DevblocksSearchField(self::NAME, 'agency', 'name', $translate->_('agency.name')),
			self::EMAIL_ADDRESS => new DevblocksSearchField(self::EMAIL_ADDRESS, 'a', 'email', $translate->_('agency.email_id')),
			self::AUTH_SALT => new DevblocksSearchField(self::AUTH_SALT, 'agency', 'auth_salt', $translate->_('agency.auth_salt')),
			self::AUTH_PASSWORD => new DevblocksSearchField(self::AUTH_PASSWORD, 'agency', 'auth_password', $translate->_('agency.auth_password')),
			self::ADDRESS_LINE1 => new DevblocksSearchField(self::ADDRESS_LINE1, 'agency', 'address_line1', $translate->_('agency.address_line1')),
			self::ADDRESS_LINE2 => new DevblocksSearchField(self::ADDRESS_LINE2, 'agency', 'address_line2', $translate->_('agency.address_line2')),
			self::ADDRESS_CITY => new DevblocksSearchField(self::ADDRESS_CITY, 'agency', 'address_city', $translate->_('agency.address_city')),
			self::ADDRESS_PROVINCE => new DevblocksSearchField(self::ADDRESS_PROVINCE, 'agency', 'address_province', $translate->_('agency.address_province')),
			self::ADDRESS_POSTAL => new DevblocksSearchField(self::ADDRESS_POSTAL, 'agency', 'address_postal', $translate->_('agency.address_postal')),
			self::CREATED => new DevblocksSearchField(self::CREATED, 'agency', 'created', $translate->_('agency.created')),
			self::LAST_LOGIN => new DevblocksSearchField(self::LAST_LOGIN, 'agency', 'last_login', $translate->_('agency.last_login')),
			self::POSITION => new DevblocksSearchField(self::POSITION, 'agency', 'position', $translate->_('agency.position')),
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

class Model_Agency {
	public $id;
	public $name;
	public $email_id;
	public $auth_salt;
	public $auth_password;
	public $address_line1;
	public $address_line2;
	public $address_city;
	public $address_province;
	public $address_postal;
	public $created;
	public $last_login;
	public $position;
	
	public function getPrimaryAddress() {
		return DAO_Address::get($this->email_id);
	}
};

class View_Agency extends C4_AbstractView {
	const DEFAULT_ID = 'agency';

	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
	
		$this->id = self::DEFAULT_ID;
		// [TODO] Name the worklist view
		$this->name = $translate->_('Agency');
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_ContactPerson::ID;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_ContactPerson::NAME,
			SearchFields_ContactPerson::ADDRESS_EMAIL,
			SearchFields_ContactPerson::ADDRESS_LINE1,
			SearchFields_ContactPerson::ADDRESS_LINE2,
			SearchFields_ContactPerson::ADDRESS_CITY,
			SearchFields_ContactPerson::ADDRESS_PROVINCE,
			SearchFields_ContactPerson::ADDRESS_POSTAL,
			SearchFields_ContactPerson::CREATED,
			SearchFields_ContactPerson::LAST_LOGIN,
			SearchFields_ContactPerson::POSITION,
		);
		// [TODO] Filter fields
		$this->addColumnsHidden(array(
			SearchFields_ContactPerson::EMAIL_ID,
			SearchFields_ContactPerson::AUTH_SALT,
			SearchFields_ContactPerson::AUTH_PASSWORD,
			SearchFields_ContactPerson::IS_AGENCY
		));
		
		// [TODO] Filter fields
		$this->addParamsHidden(array(
			SearchFields_ContactPerson::IS_AGENCY
		), true);
		
		$this->doResetCriteria();
	}

	function getData() {
		$objects = DAO_ContactPerson::search(
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
		return $this->_doGetDataSample('DAO_ContactPerson', $size);
	}

	function render() {
		$this->_sanitize();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);
		$tpl->assign('view', $this);

		// Custom fields
		//$custom_fields = DAO_CustomField::getByContext(CerberusContexts::XXX);
		//$tpl->assign('custom_fields', $custom_fields);

		$tpl->display('devblocks:osellot.core::billing/tabs/agencies/view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		// [TODO] Move the fields into the proper data type
		switch($field) {
			case SearchFields_ContactPerson::ID:
			case SearchFields_ContactPerson::NAME:
			case SearchFields_ContactPerson::EMAIL_ID:
			case SearchFields_ContactPerson::AUTH_SALT:
			case SearchFields_ContactPerson::AUTH_PASSWORD:
			case SearchFields_ContactPerson::ADDRESS_LINE1:
			case SearchFields_ContactPerson::ADDRESS_LINE2:
			case SearchFields_ContactPerson::ADDRESS_CITY:
			case SearchFields_ContactPerson::ADDRESS_PROVINCE:
			case SearchFields_ContactPerson::ADDRESS_POSTAL:
			case SearchFields_ContactPerson::CREATED:
			case SearchFields_ContactPerson::LAST_LOGIN:
			case SearchFields_ContactPerson::POSITION:
			case 'placeholder_string':
				$tpl->display('devblocks:cerberusweb.core::internal/views/criteria/__string.tpl');
				break;
			case 'placeholder_number':
				$tpl->display('devblocks:cerberusweb.core::internal/views/criteria/__number.tpl');
				break;
			case 'placeholder_bool':
			case SearchFields_ContactPerson::IS_AGENCY:
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
		return SearchFields_ContactPerson::getFields();
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		// [TODO] Move fields into the right data type
		switch($field) {
			case SearchFields_ContactPerson::ID:
			case SearchFields_ContactPerson::NAME:
			case SearchFields_ContactPerson::EMAIL_ID:
			case SearchFields_ContactPerson::AUTH_SALT:
			case SearchFields_ContactPerson::AUTH_PASSWORD:
			case SearchFields_ContactPerson::ADDRESS_LINE1:
			case SearchFields_ContactPerson::ADDRESS_LINE2:
			case SearchFields_ContactPerson::ADDRESS_CITY:
			case SearchFields_ContactPerson::ADDRESS_PROVINCE:
			case SearchFields_ContactPerson::ADDRESS_POSTAL:
			case SearchFields_ContactPerson::POSITION:
			case 'placeholder_string':
				// force wildcards if none used on a LIKE
				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
				&& false === (strpos($value,'*'))) {
					$value = $value.'*';
				}
				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
				break;
			case SearchFields_ContactPerson::POSITION:
			case 'placeholder_number':
				$criteria = new DevblocksSearchCriteria($field,$oper,$value);
				break;
			case SearchFields_ContactPerson::CREATED:
			case SearchFields_ContactPerson::LAST_LOGIN:
				@$from = DevblocksPlatform::importGPC($_REQUEST['from'],'string','');
				@$to = DevblocksPlatform::importGPC($_REQUEST['to'],'string','');

				if(empty($from)) $from = 0;
				if(empty($to)) $to = 'today';

				$criteria = new DevblocksSearchCriteria($field,$oper,array($from,$to));
				break;
			case SearchFields_ContactPerson::IS_AGENCY:
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
					//$change_fields[DAO_ContactPerson::EXAMPLE] = 'some value';
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
			list($objects,$null) = DAO_ContactPerson::search(
				array(),
				$this->getParams(),
				100,
				$pg++,
				SearchFields_ContactPerson::ID,
				true,
				false
			);
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(count($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			
			DAO_ContactPerson::update($batch_ids, $change_fields);

			// Custom Fields
			//self::_doBulkSetCustomFields(ChCustomFieldSource_ContactPerson::ID, $custom_fields, $batch_ids);
			
			unset($batch_ids);
		}

		unset($ids);
	}			
};