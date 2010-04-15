<?php
class ChForumsPlugin {
	const ID = 'cerberusweb.forums';
	const SETTING_POSTER_WORKERS = 'forums.forum_workers';
};

// Workspace Sources

class ChWorkspaceSource_ForumThread extends Extension_WorkspaceSource {
	const ID = 'forums.workspace.source.forum_thread';
};

class ChForumsConfigTab extends Extension_ConfigTab {
	const ID = 'forums.config.tab';
	
	function showTab() {
		$settings = DevblocksPlatform::getPluginSettingsService();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->assign('path', $tpl_path);

		@$sources = DAO_ForumsSource::getWhere();
		$tpl->assign('sources', $sources);

		if(null != ($poster_workers_str = $settings->get('cerberusweb.core',ChForumsPlugin::SETTING_POSTER_WORKERS, null))) {
			$tpl->assign('poster_workers_str', $poster_workers_str);
		}
		
		$tpl->display('file:' . $tpl_path . 'config/index.tpl');
	}
	
	function saveTab() {
		$settings = DevblocksPlatform::getPluginSettingsService();
		@$plugin_id = DevblocksPlatform::importGPC($_REQUEST['plugin_id'],'string');

		// Edit|Delete
		@$ids = DevblocksPlatform::importGPC($_REQUEST['ids'],'array',array());
		@$names = DevblocksPlatform::importGPC($_REQUEST['names'],'array',array());
		@$urls = DevblocksPlatform::importGPC($_REQUEST['urls'],'array',array());
		@$keys = DevblocksPlatform::importGPC($_REQUEST['keys'],'array',array());
		@$deletes = DevblocksPlatform::importGPC($_REQUEST['deletes'],'array',array());
				
		@$poster_workers = DevblocksPlatform::importGPC($_REQUEST['poster_workers'],'string','');
		
		// Add
		@$name = DevblocksPlatform::importGPC($_REQUEST['name'],'string','');
		@$url = DevblocksPlatform::importGPC($_REQUEST['url'],'string','');
		@$secret_key = DevblocksPlatform::importGPC($_REQUEST['secret_key'],'string','');

		// Deletes
		if(is_array($deletes) && !empty($deletes)) {
			DAO_ForumsSource::delete($deletes);
		}

		if(!empty($poster_workers)) {
			$settings->set('cerberusweb.core',ChForumsPlugin::SETTING_POSTER_WORKERS, strtolower($poster_workers));
		}
		
		// Edits
		if(is_array($ids) && !empty($ids)) {
			foreach($ids as $idx => $source_id) {
				$source_name = $names[$idx];
				$source_url = $urls[$idx];
				$source_key = $keys[$idx];
				
				$fields = array(
					DAO_ForumsSource::NAME => $source_name,
					DAO_ForumsSource::URL => $source_url,
					DAO_ForumsSource::SECRET_KEY => $source_key,
				);
				DAO_ForumsSource::update($source_id, $fields);
			}
		}
		
		// Add
		if(!empty($name) && !empty($url)) {
			$fields = array(
				DAO_ForumsSource::NAME => $name,
				DAO_ForumsSource::URL => $url,
				DAO_ForumsSource::SECRET_KEY => $secret_key,
			);
			$source_id = DAO_ForumsSource::create($fields);
		}
		
		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('config','forums')));
		exit;
	}
};

if (class_exists('CerberusCronPageExtension')):
class ChForumsCron extends CerberusCronPageExtension {
	function run() {
		// Get the controller and run the import action
		if(null != ($extension = DevblocksPlatform::getExtension(ChForumsController::EXTENSION_ID, true))) {
			if(method_exists($extension, 'import'))
				$extension->import();
		}
	}

	function configure($instance) {
//		$tpl = DevblocksPlatform::getTemplateService();
//		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
//		$tpl->assign('path', $tpl_path);
//
//		$tpl->display($tpl_path . 'cron/config.tpl');
	}
};
endif;

if (class_exists('Extension_ActivityTab')):
class ChForumsActivityTab extends Extension_ActivityTab {
	const VIEW_ACTIVITY_FORUMS = 'activity_forums';
	
	function __construct($manifest) {
		parent::__construct($manifest);
	}
	
	function showTab() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->assign('path', $tpl_path);
		
		if(null == ($view = C4_AbstractViewLoader::getView(self::VIEW_ACTIVITY_FORUMS))) {
			$view = new C4_ForumsThreadView();
			$view->id = self::VIEW_ACTIVITY_FORUMS;
			$view->renderSortBy = SearchFields_ForumsThread::LAST_UPDATED;
			$view->renderSortAsc = 0;
			
			C4_AbstractViewLoader::setView($view->id, $view);
		}

		$tpl->assign('response_uri', 'activity/forums');
		
		$tpl->assign('view', $view);
		$tpl->assign('view_fields', C4_ForumsThreadView::getFields());
		$tpl->assign('view_searchable_fields', C4_ForumsThreadView::getSearchFields());
		
		$tpl->display($tpl_path . 'activity_tab/index.tpl');		
	}
}
endif;

class ChForumsController extends DevblocksControllerExtension {
	const EXTENSION_ID = 'forums.controller';
	private $tpl_path = null;
	
	function __construct($manifest) {
		parent::__construct($manifest);

		$this->tpl_path = dirname(dirname(__FILE__)).'/templates';
	}
		
	function isVisible() {
		// check login
		$session = DevblocksPlatform::getSessionService();
		$visit = $session->getVisit();
		
		if(empty($visit)) {
			return false;
		} else {
			return true;
		}
	}
	
	function handleRequest(DevblocksHttpRequest $request) {
		$worker = CerberusApplication::getActiveWorker();
		if(empty($worker)) return;
		
		$stack = $request->path;
		array_shift($stack); // internal
		
	    @$action = array_shift($stack) . 'Action';

	    switch($action) {
	        case NULL:
	            // [TODO] Index/page render
	            break;
	            
	        default:
			    // Default action, call arg as a method suffixed with Action
				if(method_exists($this,$action)) {
					call_user_func(array(&$this, $action));
				}
	            break;
	    }
	}
	
	function viewForumsExploreAction() {
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string');
		
		$active_worker = CerberusApplication::getActiveWorker();
		$url_writer = DevblocksPlatform::getUrlService();
		
		// Generate hash
		$hash = md5($view_id.$active_worker->id.time()); 
		
		// Loop through view and get IDs
		$view = C4_AbstractViewLoader::getView($view_id);

		// Page start
		@$explore_from = DevblocksPlatform::importGPC($_REQUEST['explore_from'],'integer',0);
		if(empty($explore_from)) {
			$orig_pos = 1+($view->renderPage * $view->renderLimit);
		} else {
			$orig_pos = 1;
		}
		
		$view->renderPage = 0;
		$view->renderLimit = 25;
		$pos = 0;
		
		do {
			$models = array();
			list($results, $total) = $view->getData();

			// Summary row
			if(0==$view->renderPage) {
				$model = new Model_ExplorerSet();
				$model->hash = $hash;
				$model->pos = $pos++;
				$model->params = array(
					'title' => $view->name,
					'created' => time(),
					'worker_id' => $active_worker->id,
					'total' => $total,
					'return_url' => $url_writer->write('c=activity&tab=forums', true),
					'toolbar_extension_id' => 'forums.explorer.toolbar.forums',
				);
				$models[] = $model; 
				
				$view->renderTotal = false; // speed up subsequent pages
			}
			
			if(is_array($results))
			foreach($results as $forum_id => $row) {
				if($forum_id==$explore_from)
					$orig_pos = $pos;
				
				$model = new Model_ExplorerSet();
				$model->hash = $hash;
				$model->pos = $pos++;
				$model->params = array(
					'id' => $row[SearchFields_ForumsThread::ID],
					'worker_id' => $row[SearchFields_ForumsThread::WORKER_ID],
					'is_closed' => $row[SearchFields_ForumsThread::IS_CLOSED],
					'url' => $row[SearchFields_ForumsThread::LINK],
				);
				$models[] = $model; 
			}
			
			DAO_ExplorerSet::createFromModels($models);
			
			$view->renderPage++;
			
		} while(!empty($results));
		
		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('explore',$hash,$orig_pos)));
	}	
	
	function ajaxExploreCloseAction() {
		@$hash = DevblocksPlatform::importGPC($_REQUEST['hash'], 'string', '');
		@$item_id = DevblocksPlatform::importGPC($_REQUEST['item_id'], 'integer', 0);
		@$id = DevblocksPlatform::importGPC($_REQUEST['id'], 'integer', 0);
		
		DAO_ForumsThread::update($id, array(
			DAO_ForumsThread::IS_CLOSED => 1
		));

		$items = DAO_ExplorerSet::get($hash, $item_id);
		if(isset($items[$item_id])) {
			$item = $items[$item_id];
			$item->params['is_closed'] = 1;
			DAO_ExplorerSet::set($hash, $item->params, $item->pos);
		}
		
		exit;
	}
	
	function ajaxExploreReopenAction() {
		@$hash = DevblocksPlatform::importGPC($_REQUEST['hash'], 'string', '');
		@$item_id = DevblocksPlatform::importGPC($_REQUEST['item_id'], 'integer', 0);
		@$id = DevblocksPlatform::importGPC($_REQUEST['id'], 'integer', 0);
		
		DAO_ForumsThread::update($id, array(
			DAO_ForumsThread::IS_CLOSED => 0
		));
		
		$items = DAO_ExplorerSet::get($hash, $item_id);
		if(isset($items[$item_id])) {
			$item = $items[$item_id];
			$item->params['is_closed'] = 0;
			DAO_ExplorerSet::set($hash, $item->params, $item->pos);
		}
		
		exit;
	}
	
	function ajaxExploreAssignAction() {
		@$hash = DevblocksPlatform::importGPC($_REQUEST['hash'], 'string', '');
		@$item_id = DevblocksPlatform::importGPC($_REQUEST['item_id'], 'integer', 0);
		@$id = DevblocksPlatform::importGPC($_REQUEST['id'], 'integer', 0);
		@$worker_id = DevblocksPlatform::importGPC($_REQUEST['worker_id'], 'integer', 0);

		DAO_ForumsThread::update($id, array(
			DAO_ForumsThread::WORKER_ID => $worker_id
		));
		
		$items = DAO_ExplorerSet::get($hash, $item_id);
		if(isset($items[$item_id])) {
			$item = $items[$item_id];
			$item->params['worker_id'] = $worker_id;
			DAO_ExplorerSet::set($hash, $item->params, $item->pos);
		}
		
		exit;
	}
	
	function viewCloseThreadsAction() {
		@$row_ids = DevblocksPlatform::importGPC($_POST['row_id'],'array',array());
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string','');
		
		$fields = array(
			DAO_ForumsThread::IS_CLOSED => 1
		);
		
		DAO_ForumsThread::update($row_ids, $fields);
		
		if(!empty($view_id) && null != ($view = C4_AbstractViewLoader::getView($view_id))) {
			$view->render();
		}
	}
	
	function viewAssignThreadsAction() {
		@$row_ids = DevblocksPlatform::importGPC($_POST['row_id'],'array',array());
		@$worker_id = DevblocksPlatform::importGPC($_REQUEST['assign_worker_id'],'integer',0);
		@$view_id = DevblocksPlatform::importGPC($_REQUEST['view_id'],'string','');

		$active_worker = CerberusApplication::getActiveWorker();

		if(is_array($row_ids) && !empty($row_ids)) { 
			// Do assignments
			$fields = array(
				DAO_ForumsThread::WORKER_ID => $worker_id
			);
			DAO_ForumsThread::update($row_ids, $fields);
	
			// Only send notifications if not assigning to self (or unassigning)
			if(!empty($worker_id) && $active_worker->id != $worker_id) {
				$url_writer = DevblocksPlatform::getUrlService();
				
				// Load threads for notifications
				$forum_threads = DAO_ForumsThread::getWhere(sprintf("%s IN (%s)",
					DAO_ForumsThread::ID,
					implode(',', $row_ids)
				));
		
				// Send notifications about assigned forum threads
				if(is_array($forum_threads) && !empty($forum_threads))
				foreach($forum_threads as $forum_thread) {
					/* @var $forum_thread Model_ForumsThread */
					$fields = array(
						DAO_WorkerEvent::CREATED_DATE => time(),
						DAO_WorkerEvent::WORKER_ID => $worker_id,
						DAO_WorkerEvent::URL => $url_writer->write('c=forums&a=explorer',true) . '?start=' . $forum_thread->id,
						DAO_WorkerEvent::TITLE => 'New Forum Assignment', // [TODO] Translate
						DAO_WorkerEvent::CONTENT => sprintf("%s assigned: %s", $active_worker->getName(), $forum_thread->title), // [TODO] Translate
						DAO_WorkerEvent::IS_READ => 0,
					);
					DAO_WorkerEvent::create($fields);
				}
			}
		}

		if(!empty($view_id) && null != ($view = C4_AbstractViewLoader::getView($view_id))) {
			$view->render();
		}
	}

	function import() {
		$sources = DAO_ForumsSource::getWhere();
		$settings = DevblocksPlatform::getPluginSettingsService();
		
		// Track posters that are also workers
		$poster_workers = array();
		if(null != ($poster_workers_str = $settings->get('cerberusweb.core',ChForumsPlugin::SETTING_POSTER_WORKERS, null))) {
			$poster_workers = DevblocksPlatform::parseCrlfString($poster_workers_str);
		}
		
		foreach($sources as $source) { /* @var $source Model_ForumsSource */
			$source_url = sprintf("%s?lastpostid=%d&limit=100",
				$source->url,
				$source->last_postid
			);
			
			$xml_in = file_get_contents($source_url);
			$xml = new SimpleXMLElement($xml_in);

			$last_postid = 0;
			
			foreach($xml->thread as $thread) {
				@$thread_id = (string) $thread->id;
				@$thread_title = (string) $thread->title;
				@$thread_last_updated = (string) $thread->last_updated;
				@$thread_last_postid = (string) $thread->last_postid;
				@$thread_last_poster = (string) $thread->last_poster;
				@$thread_link = (string) $thread->link;
				
				if(null == ($th = DAO_ForumsThread::getBySourceThreadId($source->id, $thread_id))) {
					$fields = array(
						DAO_ForumsThread::FORUM_ID => $source->id,
						DAO_ForumsThread::THREAD_ID => $thread_id,
						DAO_ForumsThread::TITLE => $thread_title,
						DAO_ForumsThread::LAST_UPDATED => intval($thread_last_updated),
						DAO_ForumsThread::LAST_POSTER => $thread_last_poster,
						DAO_ForumsThread::LINK => $thread_link,
					);
					DAO_ForumsThread::create($fields);
					
				} else {
					// If the last post was a worker, leave the thread at the current closed state
					$closed = (false===array_search(strtolower($thread_last_poster),$poster_workers)) ? 0 : $th->is_closed;
					
					$fields = array(
						DAO_ForumsThread::LAST_UPDATED => intval($thread_last_updated),
						DAO_ForumsThread::LAST_POSTER => $thread_last_poster,
						DAO_ForumsThread::LINK => $thread_link,
						DAO_ForumsThread::IS_CLOSED => $closed,
					);
					DAO_ForumsThread::update($th->id, $fields);
				}
				
				$last_postid = $thread_last_postid;
				
			} // foreach($xml->thread)

			// Save our progress to the database
			if(!empty($last_postid)) {
				DAO_ForumsSource::update($source->id,array(
					DAO_ForumsSource::LAST_POSTID => $last_postid
				));
			}
		
		} // foreach($sources)		
	}
	
	function importAction() {
		$this->import();
		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('activity','forums')));
	}
	
};

class DAO_ForumsThread extends DevblocksORMHelper {
	const ID = 'id';
	const FORUM_ID = 'forum_id';
	const THREAD_ID = 'thread_id';
	const TITLE = 'title';
	const LAST_UPDATED = 'last_updated';
	const LAST_POSTER = 'last_poster';
	const LINK = 'link';
	const WORKER_ID = 'worker_id';
	const IS_CLOSED = 'is_closed';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('forums_thread_seq');
		
		$sql = sprintf("INSERT INTO forums_thread (id) ".
			"VALUES (%d)",
			$id
		);
		$db->Execute($sql);
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'forums_thread', $fields);
	}
	
	/**
	 * @param string $where
	 * @return Model_ForumsThread[]
	 */
	static function getWhere($where=null) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT id, forum_id, thread_id, last_updated, last_poster, title, link, worker_id, is_closed ".
			"FROM forums_thread ".
			(!empty($where) ? sprintf("WHERE %s ",$where) : "").
			"ORDER BY last_updated DESC";
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_ForumsThread
	 */
	static function getById($id) {
		$objects = self::getWhere(sprintf("%s = %d",
			self::ID,
			$id
		));
		
		if(isset($objects[$id]))
			return $objects[$id];
			
		return NULL;
	}
	
	/**
	 * @param integer $thread_id
	 * @return Model_ForumsThread
	 */
	static function getBySourceThreadId($source_id,$thread_id) {
		$objects = self::getWhere(sprintf("%s = %d AND %s = %d",
			self::FORUM_ID,
			$source_id,
			self::THREAD_ID,
			$thread_id
		));
		
		if(is_array($objects) && !empty($objects))
			return array_shift($objects);
			
		return NULL;
	}
	
	/**
	 * @param integer $id
	 * @return Model_ForumsThread	 */
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
	 * @return Model_ForumsThread[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_ForumsThread();
			$object->id = $row['id'];
			$object->forum_id = $row['forum_id'];
			$object->thread_id = $row['thread_id'];
			$object->title = $row['title'];
			$object->last_updated = $row['last_updated'];
			$object->last_poster = $row['last_poster'];
			$object->link = $row['link'];
			$object->worker_id = $row['worker_id'];
			$object->is_closed = $row['is_closed'];
			$objects[$object->id] = $object;
		}
		
		mysql_free_result($rs);
		
		return $objects;
	}
	
	static function getUnassignedTotals() {
		$db = DevblocksPlatform::getDatabaseService();
		
		$rs = $db->Execute("SELECT count(id) as hits, forum_id ".
			"FROM forums_thread ".
			"WHERE worker_id = 0 ".
			"AND is_closed = 0 ".
			"GROUP BY forum_id ".
			"HAVING count(id) > 0 "
		);
		
		$totals = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$totals[$row['forum_id']] = intval($row['hits']);
		}
		
		mysql_free_result($rs);
		
		return $totals;
	}
	
	static function getAssignedWorkerTotals() {
		$db = DevblocksPlatform::getDatabaseService();
		
		$rs = $db->Execute("SELECT count(id) as hits, worker_id ".
			"FROM forums_thread ".
			"WHERE worker_id > 0 ".
			"AND is_closed = 0 ".
			"GROUP BY worker_id ".
			"HAVING count(id) > 0 "
		);
		
		$totals = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$totals[$row['worker_id']] = intval($row['hits']);
		}
		
		mysql_free_result($rs);
		
		return $totals;
	}
	
	static function delete($ids) {
		if(!is_array($ids)) $ids = array($ids);
		$db = DevblocksPlatform::getDatabaseService();
		
		$ids_list = implode(',', $ids);
		
		$db->Execute(sprintf("DELETE QUICK FROM forums_thread WHERE id IN (%s)", $ids_list));
		
		return true;
	}
	
    /**
     * Enter description here...
     *
     * @param DevblocksSearchCriteria[] $params
     * @param integer $limit
     * @param integer $page
     * @param string $sortBy
     * @param boolean $sortAsc
     * @param boolean $withCounts
     * @return array
     */
    static function search($params, $limit=10, $page=0, $sortBy=null, $sortAsc=null, $withCounts=true) {
		$db = DevblocksPlatform::getDatabaseService();
		$fields = SearchFields_ForumsThread::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, array(),$fields,$sortBy);
		$start = ($page * $limit); // [JAS]: 1-based [TODO] clean up + document
		
		$select_sql = sprintf("SELECT ".
			"t.id as %s, ".
			"t.thread_id as %s, ".
			"t.forum_id as %s, ".
			"t.title as %s, ".
			"t.last_updated as %s, ".
			"t.last_poster as %s, ".
			"t.link as %s, ".
			"t.is_closed as %s, ".
			"t.worker_id as %s ",
			    SearchFields_ForumsThread::ID,
			    SearchFields_ForumsThread::THREAD_ID,
			    SearchFields_ForumsThread::FORUM_ID,
			    SearchFields_ForumsThread::TITLE,
			    SearchFields_ForumsThread::LAST_UPDATED,
			    SearchFields_ForumsThread::LAST_POSTER,
			    SearchFields_ForumsThread::LINK,
			    SearchFields_ForumsThread::IS_CLOSED,
			    SearchFields_ForumsThread::WORKER_ID
			 );
		
		$join_sql = 
			"FROM forums_thread t "
		;
			// [JAS]: Dynamic table joins
//			(isset($tables['o']) ? "LEFT JOIN contact_org o ON (o.id=a.contact_org_id)" : " ").
//			(isset($tables['mc']) ? "INNER JOIN message_content mc ON (mc.message_id=m.id)" : " ").

		$where_sql = "".
			(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "");
			
		$sql = $select_sql . $join_sql . $where_sql .  
			(!empty($sortBy) ? sprintf("ORDER BY %s %s",$sortBy,($sortAsc || is_null($sortAsc))?"ASC":"DESC") : "");
		
		$rs = $db->SelectLimit($sql,$limit,$start) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); 
		
		$results = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$result = array();
			foreach($row as $f => $v) {
				$result[$f] = $v;
			}
			$id = intval($row[SearchFields_ForumsThread::ID]);
			$results[$id] = $result;
		}

		// [JAS]: Count all
		$total = -1;
		if($withCounts) {
			$count_sql = "SELECT count(*) " . $join_sql . $where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
    }
};

class SearchFields_ForumsThread implements IDevblocksSearchFields {
	// Address
	const ID = 't_id';
	const THREAD_ID = 't_thread_id';
	const FORUM_ID = 't_forum_id';
	const TITLE = 't_title';
	const LAST_UPDATED = 't_last_updated';
	const LAST_POSTER = 't_last_poster';
	const LINK = 't_link';
	const IS_CLOSED = 't_is_closed';
	const WORKER_ID = 't_worker_id';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 't', 'id', $translate->_('forumsthread.id')),
			self::THREAD_ID => new DevblocksSearchField(self::THREAD_ID, 't', 'thread_id', $translate->_('forumsthread.thread_id')),
			self::FORUM_ID => new DevblocksSearchField(self::FORUM_ID, 't', 'forum_id', $translate->_('forumsthread.forum_id')),
			self::TITLE => new DevblocksSearchField(self::TITLE, 't', 'title', $translate->_('forumsthread.title')),
			self::LAST_UPDATED => new DevblocksSearchField(self::LAST_UPDATED, 't', 'last_updated', $translate->_('forumsthread.last_updated')),
			self::LAST_POSTER => new DevblocksSearchField(self::LAST_POSTER, 't', 'last_poster', $translate->_('forumsthread.last_poster')),
			self::LINK => new DevblocksSearchField(self::LINK, 't', 'link', $translate->_('forumsthread.link')),
			self::IS_CLOSED => new DevblocksSearchField(self::IS_CLOSED, 't', 'is_closed', $translate->_('forumsthread.is_closed')),
			self::WORKER_ID => new DevblocksSearchField(self::WORKER_ID, 't', 'worker_id', $translate->_('forumsthread.worker_id')),
		);
		
		// Sort by label (translation-conscious)
		uasort($columns, create_function('$a, $b', "return strcasecmp(\$a->db_label,\$b->db_label);\n"));

		return $columns;		
	}
};

class Model_ForumsThread {
	public $id;
	public $forum_id;
	public $thread_id;
	public $last_updated;
	public $title;
	public $worker_id;
	public $is_closed;
};

class DAO_ForumsSource extends DevblocksORMHelper {
	const ID = 'id';
	const NAME = 'name';
	const URL = 'url';
	const SECRET_KEY = 'secret_key';
	const LAST_POSTID = 'last_postid';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('generic_seq');
		
		$sql = sprintf("INSERT INTO forums_source (id) ".
			"VALUES (%d)",
			$id
		);
		$db->Execute($sql);
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'forums_source', $fields);
	}
	
	/**
	 * @param string $where
	 * @return Model_ForumsSource[]
	 */
	static function getWhere($where=null) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT id, name, url, secret_key, last_postid ".
			"FROM forums_source ".
			(!empty($where) ? sprintf("WHERE %s ",$where) : "").
			"ORDER BY id asc";
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_ForumsSource	 */
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
	 * @return Model_ForumsSource[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_ForumsSource();
			$object->id = $row['id'];
			$object->name = $row['name'];
			$object->url = $row['url'];
			$object->secret_key = $row['secret_key'];
			$object->last_postid = intval($row['last_postid']);
			$objects[$object->id] = $object;
		}
		
		mysql_free_result($rs);
		
		return $objects;
	}
	
	static function delete($ids) {
		if(!is_array($ids)) $ids = array($ids);
		$db = DevblocksPlatform::getDatabaseService();
		
		$ids_list = implode(',', $ids);
		
		$db->Execute(sprintf("DELETE QUICK FROM forums_source WHERE id IN (%s)", $ids_list));
		
		return true;
	}

};

class Model_ForumsSource {
	public $id;
	public $name;
	public $url;
	public $secret_key;
	public $last_postid;
};

class C4_ForumsThreadView extends C4_AbstractView {
	const DEFAULT_ID = 'forums_overview';

	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$this->id = self::DEFAULT_ID;
		$this->name = $translate->_('forums.ui.forums');
		$this->renderLimit = 10;
		$this->renderSortBy = 't_last_updated';
		$this->renderSortAsc = false;

		$this->view_columns = array(
			SearchFields_ForumsThread::FORUM_ID,
			SearchFields_ForumsThread::LAST_UPDATED,
			SearchFields_ForumsThread::LAST_POSTER,
			SearchFields_ForumsThread::WORKER_ID,
//			SearchFields_ForumsThread::LINK,
		);
		
		$this->params = array(
			SearchFields_ForumsThread::IS_CLOSED => new DevblocksSearchCriteria(SearchFields_ForumsThread::IS_CLOSED,'=',0),
		);
	}

	function getData() {
		$objects = DAO_ForumsThread::search(
			$this->params,
			$this->renderLimit,
			$this->renderPage,
			$this->renderSortBy,
			$this->renderSortAsc,
			$this->renderTotal
		);
		return $objects;
	}

	function render() {
		$this->_sanitize();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);
		$tpl->assign('view', $this);

		$sources = DAO_ForumsSource::getWhere();
		$tpl->assign('sources', $sources);
		
		$workers = DAO_Worker::getAll();
		$tpl->assign('workers', $workers);

		$tpl->assign('view_fields', $this->getColumns());
		$path = dirname(dirname(__FILE__)); 
		$tpl->display('file:' . $path . '/templates/forums/forums_view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->assign('id', $this->id);

		switch($field) {
			case SearchFields_ForumsThread::FORUM_ID:
				$forums = DAO_ForumsSource::getWhere();
				$tpl->assign('forums', $forums);
				
				$tpl->display('file:' . $tpl_path . 'forums/criteria/forum.tpl');
				break;
				
			case SearchFields_ForumsThread::TITLE:
			case SearchFields_ForumsThread::LINK:
			case SearchFields_ForumsThread::LAST_POSTER:
				$tpl->display('file:' . APP_PATH . '/features/cerberusweb.core/templates/internal/views/criteria/__string.tpl');
				break;
				
			case SearchFields_ForumsThread::LAST_UPDATED:
				$tpl->display('file:' . APP_PATH . '/features/cerberusweb.core/templates/internal/views/criteria/__date.tpl');
				break;
				
			case SearchFields_ForumsThread::IS_CLOSED:
				$tpl->display('file:' . APP_PATH . '/features/cerberusweb.core/templates/internal/views/criteria/__bool.tpl');
				break;
				
			case SearchFields_ForumsThread::WORKER_ID:
				$workers = DAO_Worker::getAll();
				$tpl->assign('workers', $workers);
				
				$tpl->display('file:' . APP_PATH . '/features/cerberusweb.core/templates/internal/views/criteria/__worker.tpl');
				break;
				
			default:
				echo '';
				break;
		}
	}

	function renderCriteriaParam($param) {
		$field = $param->field;
		$values = !is_array($param->value) ? array($param->value) : $param->value;

		switch($field) {
			case SearchFields_ForumsThread::FORUM_ID:
				$forums = DAO_ForumsSource::getWhere();
				$strings = array();

				foreach($values as $val) {
					if(!isset($forums[$val]))
						continue;
					else
						$strings[] = $forums[$val]->name;
				}
				echo implode(", ", $strings);
				break;
			
			case SearchFields_ForumsThread::WORKER_ID:
				$workers = DAO_Worker::getAll();
				$strings = array();

				foreach($values as $val) {
					if(empty($val))
						$strings[] = "Nobody";
					elseif(!isset($workers[$val]))
						continue;
					else
						$strings[] = $workers[$val]->getName();
				}
				echo implode(", ", $strings);
				break;
			
			default:
				parent::renderCriteriaParam($param);
				break;
		}
	}

	static function getFields() {
		return SearchFields_ForumsThread::getFields();
	}

	static function getSearchFields() {
		$fields = self::getFields();
		unset($fields[SearchFields_ForumsThread::ID]);
		unset($fields[SearchFields_ForumsThread::THREAD_ID]);
		return $fields;
	}

	static function getColumns() {
		$fields = self::getFields();
		unset($fields[SearchFields_ForumsThread::ID]);
		unset($fields[SearchFields_ForumsThread::THREAD_ID]);
		return $fields;
	}

	function doResetCriteria() {
		parent::doResetCriteria();
		
		$this->params = array(
			SearchFields_ForumsThread::IS_CLOSED => new DevblocksSearchCriteria(SearchFields_ForumsThread::IS_CLOSED,'=',0),
		);
	}
	
	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		switch($field) {
			case SearchFields_ForumsThread::FORUM_ID:
				@$forum_ids = DevblocksPlatform::importGPC($_REQUEST['forum_id'],'array',array());
				$criteria = new DevblocksSearchCriteria($field,$oper,$forum_ids);
				break;
				
			case SearchFields_ForumsThread::TITLE:
			case SearchFields_ForumsThread::LINK:
			case SearchFields_ForumsThread::LAST_POSTER:
				// force wildcards if none used on a LIKE
				if(($oper == DevblocksSearchCriteria::OPER_LIKE || $oper == DevblocksSearchCriteria::OPER_NOT_LIKE)
				&& false === (strpos($value,'*'))) {
					$value = '*'.$value.'*';
				}
				$criteria = new DevblocksSearchCriteria($field, $oper, $value);
				break;
				
			case SearchFields_ForumsThread::LAST_UPDATED:
				@$from = DevblocksPlatform::importGPC($_REQUEST['from'],'string','');
				@$to = DevblocksPlatform::importGPC($_REQUEST['to'],'string','');

				if(empty($from)) $from = 0;
				if(empty($to)) $to = 'today';

				$criteria = new DevblocksSearchCriteria($field,$oper,array($from,$to));
				break;
				
			case SearchFields_ForumsThread::IS_CLOSED:
				@$bool = DevblocksPlatform::importGPC($_REQUEST['bool'],'integer',1);
				$criteria = new DevblocksSearchCriteria($field,$oper,$bool);
				break;
				
			case SearchFields_ForumsThread::WORKER_ID:
				@$worker_id = DevblocksPlatform::importGPC($_REQUEST['worker_id'],'array',array());
				$criteria = new DevblocksSearchCriteria($field,$oper,$worker_id);
				break;
		}

		if(!empty($criteria)) {
			$this->params[$field] = $criteria;
			$this->renderPage = 0;
		}
	}

};

class ChExplorerToolbarForums extends Extension_ExplorerToolbar {
	function __construct($manifest) {
		$this->DevblocksExtension($manifest);
	}
	
	function render(Model_ExplorerSet $item) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		
		$tpl->assign('item', $item);
		
		// Workers
		$tpl->assign('workers', DAO_Worker::getAllActive());
		
		$tpl->display('file:'.$tpl_path.'forums/explore/explorer_toolbar.tpl');
	}
};