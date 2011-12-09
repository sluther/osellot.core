<?php
$db = DevblocksPlatform::getDatabaseService();
$tables = $db->metaTables();

if(!isset($tables['box_item'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS box_item (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			item VARCHAR(255) DEFAULT '' NOT NULL,
			source INT UNSIGNED DEFAULT 0 NOT NULL,
			origin enum('local','notlocal') NOT NULL DEFAULT 'local',
			unit VARCHAR(255) DEFAULT '' NOT NULL,
			weighed INT UNSIGNED DEFAULT 0 NOT NULL,
			casecost INT UNSIGNED DEFAULT 0 NOT NULL,
			unitspercase INT UNSIGNED DEFAULT 0 NOT NULL,
			unitcost INT UNSIGNED DEFAULT 0 NOT NULL,
			casesneeded INT UNSIGNED DEFAULT 0 NOT NULL,
			casesrounded INT UNSIGNED DEFAULT 0 NOT NULL,
			remainder INT UNSIGNED DEFAULT 0 NOT NULL,
			guidance enum('ok','loose','tight') NOT NULL DEFAULT 'ok',
			startdate INT UNSIGNED DEFAULT 0 NOT NULL,
			enddate INT UNSIGNED DEFAULT 0 NOT NULL,
			totalcost INT UNSIGNED DEFAULT 0 NOT NULL,
			products VARCHAR(255) DEFAULT '' NOT NULL,
			PRIMARY KEY (id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);
}

if(!isset($tables['box_item_source'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS box_item_source (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL DEFAULT '',
			PRIMARY KEY (id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);
}