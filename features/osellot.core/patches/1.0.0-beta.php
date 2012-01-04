<?php
$db = DevblocksPlatform::getDatabaseService();
$tables = $db->metaTables();

// `customer_profile` ========================
if(!isset($tables['customer_profile'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS customer_profile (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
			address_id INT UNSIGNED NOT NULL DEFAULT 0 UNIQUE,
			profile_id INT UNSIGNED NOT NULL DEFAULT 0 UNIQUE,
			PRIMARY KEY (id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);
}

// `gateway_setting` ========================
if(!isset($tables['gateway'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS gateway (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
			extension_id VARCHAR(255) DEFAULT '' NOT NULL,
			name VARCHAR(255) DEFAULT '' NOT NULL,
			enabled TINYINT(1) UNSIGNED DEFAULT 0 NOT NULL,
			test_mode TINYINT(1) UNSIGNED DEFAULT 0 NOT NULL,
			PRIMARY KEY (id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);
}

// `gateway_setting` ========================
if(!isset($tables['gateway_setting'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS gateway_setting (
			gateway_id INT UNSIGNED NOT NULL DEFAULT 0,
			name VARCHAR(255) NOT NULL DEFAULT '',
			value TEXT NOT NULL
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);
}


// `invoice` ========================
if(!isset($tables['invoice'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS invoice (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			account_id INT UNSIGNED NOT NULL,
			amount DECIMAL(25,6) UNSIGNED NOT NULL DEFAULT 0,
			amount_paid DECIMAL(25,6) UNSIGNED NOT NULL DEFAULT 0,
			status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
			number VARCHAR(32) NOT NULL DEFAULT '',
			number INT(11) NOT NULL 0,
			PRIMARY KEY (id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);
}

// `invoice_item` ========================
if(!isset($tables['invoice_item'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS invoice_item (
			invoice_id INT UNSIGNED NOT NULL DEFAULT 0,
			product_id INT UNSIGNED NOT NULL DEFAULT 0,
			quantity INT UNSIGNED NOT NULL DEFAULT 0,		
			amount DECIMAL(25,6) UNSIGNED NOT NULL DEFAULT 0,
			PRIMARY KEY (invoice_id, product_id),
			INDEX invoice_id (invoice_id),
			INDEX product_id (product_id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);
}

// `invoice_attribute` ========================
if(!isset($tables['invoice_attribute'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS invoice_attribute (
			invoice_id INT UNSIGNED NOT NULL DEFAULT 0,
			name VARCHAR(255) NOT NULL DEFAULT '',
			value TEXT NOT NULL
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);
}

// `payment_profile` ========================
if(!isset($tables['payment_profile'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS payment_profile (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
			address_id INT UNSIGNED NOT NULL DEFAULT 0 UNIQUE,
			profile_id INT UNSIGNED NOT NULL DEFAULT 0,
			plugin_id VARCHAR(255) NOT NULL DEFAULT '',
			PRIMARY KEY (id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);
}

// `payment_profile_extra` ========================
if(!isset($tables['payment_profile_extra'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS payment_profile_extra (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
			profile_id INT UNSIGNED NOT NULL DEFAULT 0,
			name VARCHAR(255) NOT NULL DEFAULT '',
			value TEXT NOT NULL,
			PRIMARY KEY (id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);
}

// `product` ========================
if(!isset($tables['product'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS product (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			price DECIMAL(25,6) UNSIGNED NOT NULL DEFAULT 0,
			price_setup DECIMAL(25,6) UNSIGNED NOT NULL DEFAULT 0,
			recurring TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
			taxable TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
			sku VARCHAR(32) NOT NULL DEFAULT '',
			name VARCHAR(255) NOT NULL DEFAULT '',
			description TEXT NOT NULL,
			PRIMARY KEY (id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);
}

// `product_setting` ========================
if(!isset($tables['product_setting'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS product_setting (
			product_id INT UNSIGNED NOT NULL DEFAULT 0,
			name VARCHAR(255) NOT NULL DEFAULT '',
			value TEXT NOT NULL,
			PRIMARY KEY (id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);
}

// `transaction` ========================
if(!isset($tables['transaction'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS transaction (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
			to_address_id INT UNSIGNED NOT NULL DEFAULT 0,
			from_address_id INT UNSIGNED NOT NULL DEFAULT 0,
			amount DECIMAL(25,6) UNSIGNED NOT NULL DEFAULT 0,
			plugin_id VARCHAR(255) NOT NULL DEFAULT '',
			trans_id INT UNSIGNED NOT NULL DEFAULT 0 UNIQUE,
			profile_id INT UNSIGNED NOT NULL DEFAULT 0,
			status TINYINT(2) NOT NULL DEFAULT 0,
			PRIMARY KEY (id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);
}

// `contact_person` ========================
list($columns, $indexes) = $db->metaTable('contact_person');

if(!isset($columns['phone']))
	$db->Execute("ALTER TABLE contact_person ADD COLUMN phone VARCHAR(11) NOT NULL DEFAULT ''");
if(!isset($columns['name']))
	$db->Execute("ALTER TABLE contact_person ADD COLUMN name VARCHAR(255) ");
if(!isset($columns['address_line1']))
	$db->Execute("ALTER TABLE contact_person ADD COLUMN address_line1 VARCHAR(255) NOT NULL DEFAULT ''");
if(!isset($columns['address_line2']))
	$db->Execute("ALTER TABLE contact_person ADD COLUMN address_line2 VARCHAR(255) NOT NULL DEFAULT ''");
if(!isset($columns['address_city']))
	$db->Execute("ALTER TABLE contact_person ADD COLUMN address_city VARCHAR(255) NOT NULL DEFAULT ''");
if(!isset($columns['address_province']))
	$db->Execute("ALTER TABLE contact_person ADD COLUMN address_province VARCHAR(255) NOT NULL DEFAULT ''");
if(!isset($columns['address_postal']))
	$db->Execute("ALTER TABLE contact_person ADD COLUMN address_postal VARCHAR(255) NOT NULL DEFAULT ''");

$gateway_plugins = DevblocksPlatform::getExtensions('cc.gateway.osellot.core');
foreach($gateway_plugins as $gateway) {
	$sql = sprintf("INSERT INTO gateway_setting (extension_id,enabled,test_mode,username,password,extra) " . 
					"VALUES ('%s', '0', '0', '', '', '')", $gateway->id); 
		
	$db->Execute($sql);
}	

return TRUE;