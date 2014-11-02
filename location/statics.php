<?php
/**
 * Static pages for WebMCR
 *
 * General proccess
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014 Qexy.org
 *
 * @version 1.0
 *
 */


// Check webMCR constant
if(!defined('MCR')){ exit("Hacking Attempt!"); }

// Set default constants
define('QEXY', true); // Default module costant
define('STC_VERSION', '1.0'); // Module version
define('STC_STYLE', STYLE_URL.'Default/modules/qexy/statics/'); // Module style folder
define('STC_STYLE_ADMIN', STC_STYLE.'admin/'); // Module style admin folder
define('STC_URL', BASE_URL.'go/statics/'); // Base module url
define('STC_ADMIN_URL', STC_URL.'admin/'); // Base module admin url
define('STC_CLASS_PATH', MCR_ROOT.'instruments/modules/qexy/statics/'); // Root module class folder
define('MCR_URL_ROOT', 'http://'.$_SERVER['SERVER_NAME']); // Base full site url

// Load css style and javascript
$content_js .= '<link href="'.STC_STYLE.'css/statics.css" rel="stylesheet">';
$content_js .= '<script src="'.STC_STYLE.'js/statics.js"></script>';

// Set default count of queries
$_SESSION['stc_count_mq'] = 0;

// Set database driver
if(isset($config['db_driver'])){
	if($config['db_driver']=='mysqli'){
		$driver = 'mysqli';
	}elseif($config['db_driver']=='mysql'){
		$driver = 'mysql';
	}else{
		exit("Sorry, but this database driver not supported");
	}
}else{
	$driver = 'mysql.old';
}

// Load configuration
require_once(MCR_ROOT.'configs/statics.cfg.php');

// Check for installation
if($cfg['install']==true){ $install = true; }

// Load database driver
require_once(STC_CLASS_PATH.'db/'.$driver.'.class.php');

// Initialization core methods
require_once(STC_CLASS_PATH.'init.class.php'); $stc_init = new statics_init($user, $cfg);

// Set notify
$stc_init->info_set();

// Set active menu
$menu->SetItemActive('statics');

// Set default page
$do = isset($_GET['do']) ? $_GET['do'] : '403';

if(isset($install) && $do!=='install'){ $stc_init->notify("Требуется установка", "install/", 4); }


/*
 * Load pugins
 *
 * Format:
 * 1. Include class
 * 2. Create new object for $mad_module (For use core methods, set __construct $stc_init)
 * 3. Set main method for $stc_content
 * 4. Set title(from class or other) for $stc_title
 * 5. Set BreadCrumbs(from class or other) for $stc_bc
 *
 */

switch($do){
	// Load module admin
	case 'admin':
		require_once(STC_CLASS_PATH.'admin.class.php');
		$stc_module		= new statics_admin($stc_init);
		$stc_content	= $stc_module->_list();
		$stc_title		= $stc_module->title;
		$stc_bc			= $stc_module->bc;
	break;

	// Load module pages
	case 'page':
		require_once(STC_CLASS_PATH.'pages.class.php');
		$stc_module		= new statics_pages($stc_init);
		$stc_content	= $stc_module->_list();
		$stc_title		= $stc_module->title;
		$stc_bc			= $stc_module->bc;
	break;

	// Load 404 page (static)
	case '404':
		$stc_content	= $stc_init->sp("404");
		$stc_title		= "Страница не найдена";
		$stc_bc			= $stc_init->get_bc($stc_title, '', false, true, false);
	break;

	// Load 404 page (static)
	case '403':
		$stc_content	= $stc_init->sp("403");
		$stc_title		= "Доступ запрещен";
		$stc_bc			= $stc_init->get_bc($stc_title, '', false, true, false);
	break;

	// Load installation
	case 'install':
		if(!isset($install) && !isset($_SESSION['install_finished'])){ $stc_init->notify("Установка уже произведена", "", 4); }
		require_once(MCR_ROOT."install_statics/install.class.php");
		$stc_module		= new install_statics($stc_init);
		$stc_content	= $stc_module->_list();
		$stc_title		= $stc_module->title;
		$stc_bc			= $stc_module->bc;
	break;

	// Load default menu
	default: $stc_init->notify("Страница не найдена", "404/", 3); break;
}

// Set default page title
$page = $cfg['title'].' >> '.$stc_title;

// Set returned content
$content_main = $stc_init->get_global($stc_bc, $stc_content);

// Unset notify
$stc_init->info_unset();

// Get num queries
//$content_main .= $_SESSION['stc_count_mq'];


/**
 * Static pages for WebMCR
 *
 * General proccess
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014 Qexy.org
 *
 * @version 1.0
 *
 */
?>
