<?php

define('CORE_PATH', dirname(__FILE__));

spl_autoload_register(function($class_name){
	$locations = array(
		CORE_PATH . '/inc',
	);

	foreach ($locations as &$location){
		if (file_exists("{$location}/{$class_name}.inc.php")){
			include_once("{$location}/{$class_name}.inc.php");
			return;
		}
	}

	die("Unable to find {$class_name}.inc.php <pre>" . print_r(debug_backtrace(), true) . '</pre>');
});

?>
