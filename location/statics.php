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
 * @version 1.0.1
 *
 */


// Check webMCR constant
if(!defined('MCR')){ exit("Hacking Attempt!"); }

// Load configuration
require_once(MCR_ROOT.'configs/statics.cfg.php');

// Set default constants
define('QEXY', true);														// Default module costant
define('STC_VERSION', '1.0.1');												// Module version
define('STC_STYLE', STYLE_URL.'Default/modules/qexy/statics/');				// Module style folder
define('STC_STYLE_ADMIN', STC_STYLE.'admin/');								// Module style admin folder
define('STC_URL', BASE_URL.'?mode=statics');								// Base module url
define('STC_ADMIN_URL', STC_URL.'&do=admin');								// Base module admin url
define('STC_CLASS_PATH', MCR_ROOT.'instruments/modules/qexy/statics/');		// Root module class folder
define('MCR_URL_ROOT', 'http://'.$_SERVER['SERVER_NAME']);					// Base full site url

// Loading API
if(!file_exists(MCR_ROOT."instruments/modules/qexy/api/api.class.php")){ exit("API not found! <a href=\"https://github.com/qexyorg/webMCR-API\" target=\"_blank\">Download</a>"); }
require_once(MCR_ROOT."instruments/modules/qexy/api/api.class.php");

// Set default url for module
$api->url = "?mode=statics";

// Set default style path for module
$api->style = STC_STYLE;

// Set module cfg
$api->cfg = $cfg;

// Check access user level
if($api->user->lvl < $cfg['lvl_access']){ header('Location: '.BASE_URL.'?mode=403'); exit; }

// Load css style and javascript
$content_js .= '<link href="'.STC_STYLE.'css/statics.css" rel="stylesheet">';
$content_js .= '<script src="'.STC_STYLE.'js/statics.js"></script>';

// Check for installation
if($cfg['install']==true){ $install = true; }

// Set active menu
$menu->SetItemActive('statics');

// Set default page
$do = isset($_GET['do']) ? $_GET['do'] : '403';

if(isset($install) && $do!=='install'){ $api->notify("Требуется установка", "&do=install", "Внимание!", 4); }


/*
 * Load pugins
 *
 * Format:
 * 1. Include class
 * 2. Create new object for $mad_module (For use core methods, set __construct $api)
 * 3. Set main method for $stc_content
 * 4. Set title(from class or other) for $stc_title
 * 5. Set BreadCrumbs(from class or other) for $stc_bc
 *
 */

switch($do){
	// Load module admin
	case 'admin':
		require_once(STC_CLASS_PATH.'admin.class.php');
		$stc_module		= new statics_admin($api);
		$stc_content	= $stc_module->_list();
		$stc_title		= $stc_module->title;
		$stc_bc			= $stc_module->bc;
	break;

	// Load module pages
	case 'page':
		require_once(STC_CLASS_PATH.'pages.class.php');
		$stc_module		= new statics_pages($api);
		$stc_content	= $stc_module->_list();
		$stc_title		= $stc_module->title;
		$stc_bc			= $stc_module->bc;
	break;

	// Load 404 page (static)
	case '404':
		$stc_content	= $api->sp("404.html");
		$stc_title		= "Страница не найдена";
		$stc_bc			= $api->bc(array("Главная" => BASE_URL, $cfg['title'] => STC_URL, "Страница не найдена" => ""));
	break;

	// Load 404 page (static)
	case '403':
		$stc_content	= $api->sp("404.html");
		$stc_title		= "Доступ запрещен";
		$stc_bc			= $api->bc(array( "Главная" => BASE_URL, $cfg['title'] => STC_URL, "Доступ запрещен" => ""));
	break;

	// Load installation
	case 'install':
		if(!isset($install) && !isset($_SESSION['install_finished'])){ $api->notify("Установка уже произведена", "", "Упс!", 4); }
		require_once(MCR_ROOT."install_statics/install.class.php");
		$stc_module		= new install_statics($api);
		$stc_content	= $stc_module->_list();
		$stc_title		= $stc_module->title;
		$stc_bc			= $stc_module->bc;
	break;

	// Load default menu
	default: $api->notify("Страница не найдена", "&do=404", "404", 3); break;
}

// Set default page title
$page = $cfg['title'].' — '.$stc_title;

$content_data = array(
	"CONTENT" => $stc_content,
	"BC" => $stc_bc,
	"API_INFO" => $api->get_notify(),
);

// Set returned content
$content_main = $api->sp("global.html", $content_data);

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
 * @version 1.0.1
 *
 */
?>
