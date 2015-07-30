<?php
/**
 * Static pages module for WebMCR
 *
 * Install class
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2015 Qexy.org
 *
 * @version 1.2.0
 *
 */

// Check Qexy constant
if (!defined('QEXY')){ exit("Hacking Attempt!"); }

$content_js .= '<link href="'.BASE_URL.'install_statics/styles/css/install.css" rel="stylesheet">';

class module{
	// Set default variables
	private $cfg			= array();
	private $user			= false;
	private $db				= false;
	private $api			= false;
	private $configs		= array();
	public	$in_header		= '';
	public	$title			= '';

	// Set counstructor values
	public function __construct($api){

		$this->cfg			= $api->cfg;
		$this->user			= $api->user;
		$this->db			= $api->db;
		$this->api			= $api;
		
		if($this->user->lvl < $this->cfg['lvl_admin']){ $this->api->url = ''; $this->api->notify(); }
	}

	private function step_1(){

		if(!$this->cfg['install']){ $this->api->notify("Установка уже произведена", "", "Ошибка!", 3); }
		if(isset($_SESSION['step_2'])){ $this->api->notify("", "&do=install&op=2", "", 3); }

		$write_menu = $write_cfg = $write_configs = '';

		if(!is_writable(MCR_ROOT.'instruments/menu_items.php')){
			$write_menu = '<div class="alert alert-error"><b>Внимание!</b> Выставите права 777 на файл <b>instruments/menu_items.php</b></div>';
		}

		if(!is_writable(MCR_ROOT.'configs')){
			$write_configs = '<div class="alert alert-error"><b>Внимание!</b> Выставите права 777 на папку <b>configs</b></div>';
		}

		if(!is_writable(MCR_ROOT.'configs/statics.cfg.php')){
			$write_cfg = '<div class="alert alert-error"><b>Внимание!</b> Выставите права 777 на файл <b>configs/statics.cfg.php</b></div>';
		}

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit'])){ $this->api->notify("Hacking Attempt!", "&do=install", "403", 3); }

			if(!empty($write_menu) || !empty($write_cfg) || !empty($write_configs)){ $this->api->notify("Требуется выставить необходимые права на запись", "&do=install", "Ошибка!", 3); }

			$this->cfg['title']			= $this->db->HSC(strip_tags(@$_POST['title']));
			$this->cfg['rop_pages']		= (intval(@$_POST['rop_pages'])<=0) ? 1 : intval(@$_POST['rop_pages']);
			$this->cfg['lvl_access']	= intval(@$_POST['lvl_access']);
			$this->cfg['lvl_admin']		= intval(@$_POST['lvl_admin']);

			// Check save config
			if(!$this->api->savecfg($this->cfg, "configs/statics.cfg.php")){ $this->api->notify("Ошибка сохранения настроек", "&do=install", "Ошибка!", 3); }

			$create = $this->db->query("CREATE TABLE IF NOT EXISTS `qx_statics` (
					  `id` int(10) NOT NULL AUTO_INCREMENT,
					  `title` varchar(32) NOT NULL,
					  `uniq` varchar(32) CHARACTER SET latin1 NOT NULL,
					  `text_bb` text NOT NULL,
					  `text_html` text NOT NULL,
					  `status` tinyint(1) NOT NULL DEFAULT '1',
					  `access` int(4) NOT NULL,
					  `uid_create` int(10) NOT NULL,
					  `uid_update` int(10) NOT NULL,
					  `data` text CHARACTER SET latin1 NOT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `uniq` (`uniq`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

			if(!$create){ $this->api->notify("Ошибка установки", "&do=install", "Ошибка!", 3); }

			$_SESSION['step_2'] = true;

			$this->api->notify("Шаг 2", "&do=install&op=2", "Продолжение установки", 2);
		}

		$content = array(
			"WRITE_MENU" => $write_menu,
			"WRITE_CFG" => $write_cfg,
			"WRITE_CONFIGS" => $write_configs,
		);

		return $this->api->sp(MCR_ROOT.'install_statics/styles/step-1.html', $content, true);
	}

	private function saveMenu($menu) {
	
		$txt	= "<?php if (!defined('MCR')) exit;".PHP_EOL;
		$txt .= '$menu_items = '.var_export($menu, true).';'.PHP_EOL;

		$result = file_put_contents(MCR_ROOT."instruments/menu_items.php", $txt);

		return (is_bool($result) and $result == false)? false : true;	
	}

	private function step_2(){

		if(!isset($_SESSION['step_2'])){ $this->api->notify("", "&do=install", "", 3); }
		if(isset($_SESSION['step_3'])){ $this->api->notify("", "&do=install&op=3", "", 3); }

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit'])){ $this->api->notify("Hacking Attempt!", "&do=install&op=2", "403", 3); }

			require(MCR_ROOT."instruments/menu_items.php");

			if(intval(@$_POST['menu'])==1 && !isset($menu_items[0]['statics'])){
				$menu_items[1]['statics'] = array (
				  'name' => 'Статические страницы',
				  'url' => '?mode=statics&do=admin',
				  'parent_id' => 'admin',
				  'lvl' => 15,
				  'permission' => -1,
				  'active' => false,
				  'inner_html' => '',
				);
			}

			if(!$this->saveMenu($menu_items)){ $this->api->notify("Ошибка установки", "&do=install&op=2", "Ошибка!", 3); }

			$_SESSION['step_3'] = true;

			$this->api->notify("", "&do=install&op=3", "", 2);
		}

		return $this->api->sp(MCR_ROOT.'install_statics/styles/step-2.html', array(), true);
	}

	private function step_3(){

		if(!isset($_SESSION['step_3'])){ $this->api->notify("", "&do=install&op=2", "", 3); }
		if(isset($_SESSION['step_finish'])){ $this->api->notify("", "&do=install&op=finish", "", 3); }

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit'])){ $this->api->notify("Hacking Attempt!", "&do=install", "403", 3); }

			$this->cfg['install'] = false;

			if(!$this->api->savecfg($this->cfg, "configs/statics.cfg.php")){ $this->api->notify("Ошибка установки", "&do=install", "Ошибка!", 3); }

			$_SESSION['step_finish'] = true;

			$this->api->notify("", "&do=install&op=finish", "", 2);
		}

		return $this->api->sp(MCR_ROOT.'install_statics/styles/step-3.html', array(), true);
	}

	private function finish(){

		if(!isset($_SESSION['step_finish'])){ $this->api->notify("", "&do=install&op=3", "", 3); }

		$content = $this->api->sp(MCR_ROOT.'install_statics/styles/finish.html', array(), true);

		unset($_SESSION['step_finish'], $_SESSION['step_3'], $_SESSION['step_2']);

		return $content;
	}

	public function _list(){

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'main';

		switch($op){
			case "2":
				$this->title	= "Установка — Шаг 2"; // Set page title (In tag <title></title>)
				$array = array(
					"Главная" => BASE_URL,
					$this->cfg['title'] => MOD_URL,
					"Установка" => MOD_URL."&do=install",
					"Шаг 2" => ""
				);
				$this->bc		= $this->api->bc($array);

				return $this->step_2(); // Set content
			break;

			case "3":
				$this->title	= "Установка — Шаг 3"; // Set page title (In tag <title></title>)
				$array = array(
					"Главная" => BASE_URL,
					$this->cfg['title'] => MOD_URL,
					"Установка" => MOD_URL."&do=install",
					"Шаг 3" => ""
				);
				$this->bc		= $this->api->bc($array);

				return $this->step_3(); // Set content
			break;

			case "finish":
				$this->title	= "Установка — Конец установки"; // Set page title (In tag <title></title>)
				$array = array(
					"Главная" => BASE_URL,
					$this->cfg['title'] => MOD_URL,
					"Установка" => MOD_URL."&do=install",
					"Конец установки" => ""
				);
				$this->bc		= $this->api->bc($array);

				return $this->finish(); // Set content
			break;

			default:
				$array = array(
					"Главная" => BASE_URL,
					$this->cfg['title'] => MOD_URL,
					"Установка" => MOD_URL."&do=install",
					"Шаг 1" => ""
				);
				$this->bc		= $this->api->bc($array);

				$this->title	= "Установка — Шаг 1";
				return $this->step_1();
			break;
		}

		return '';
	}
}

/**
 * Static pages module for WebMCR
 *
 * Install class
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2015 Qexy.org
 *
 * @version 1.2.0
 *
 */
?>
