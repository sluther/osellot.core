<?php
class CallsPlugin extends DevblocksPlugin {
};

class CallsTranslations extends DevblocksTranslationsExtension {
	function __construct($manifest) {
		parent::__construct($manifest);	
	}
	
	function getTmxFile() {
		return dirname(dirname(__FILE__)) . '/strings.xml';
	}
};
