<?php
	define('ABS_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR.'Cashier'.DIRECTORY_SEPARATOR);
	define('PIGCMS_CORE_PATH','./pigcms/');
	define('PIGCMS_CORE_PATH_FOLDER','./Cashier/pigcms/');

	define('PIGCMS_TPL_PATH','./pigcms_tpl/');
	define('PIGCMS_TPL_PATH_FOLDER','./Cashier/pigcms_tpl/');

	define('PIGCMS_STATIC_PATH','./pigcms_static/');
	define('PIGCMS_STATIC_PATH_FOLDER','./Cashier/pigcms_static/');
	define('ABS_UPLOAD_PATH','/Cashier');/**独立作为站时不要配置此项或者配置成空''*****/
	define('APP_NAME','Merchants');
	define('DEBUG',true);
	define('GZIP',true);
	//define('File_Server_Topdomain', 'pigcms.cn')

	include ABS_PATH.'config'.DIRECTORY_SEPARATOR.'config.inc.php';
	include ABS_PATH.PIGCMS_CORE_PATH.'base.php';
	bpBase::creatApp();
?>