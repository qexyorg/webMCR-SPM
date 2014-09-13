<?php
/**
 * Static pages for WebMCR
 *
 * Installation class
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014 Qexy.org
 *
 * @version 1.0
 *
 */

// Check Qexy constant
if (!defined('QEXY')){ exit("Hacking Attempt!"); }

$content_js .= '<link href="'.BASE_URL.'install_statics/styles/css/install.css" rel="stylesheet">';

class install_statics{
	// Set default variables
	private $cfg			= array();
	private $user			= false;
	private $db				= false;
	private $init			= false;
	private $configs		= array();
	public	$in_header		= '';
	public	$title			= '';

	// Set counstructor values
	public function __construct($init){

		$this->cfg			= $init->cfg;
		$this->user			= $init->user;
		$this->db			= $init->db;
		$this->init			= $init;

		if($this->init->user_lvl < $this->cfg['lvl_admin']){ header('Location: '.BASE_URL); exit; }
	}

	private function check_table(){
		$query = $this->db->MQ("SELECT COUNT(*) FROM `qx_statics`");
		if(!$query){ return false; }

		return true;
	}

	private function step_1(){
		ob_start();

		$write_menu = $write_cfg = $write_configs = $check_install = '';

		if(!is_writable(MCR_ROOT.'instruments/menu_items.php')){
			$write_menu = '<div class="alert alert-error"><b>Внимание!</b> Выставите права 777 на файл <b>instruments/menu_items.php</b></div>';
		}

		if(!is_writable(MCR_ROOT.'configs')){
			$write_configs = '<div class="alert alert-error"><b>Внимание!</b> Выставите права 777 на папку <b>configs</b></div>';
		}

		if(!is_writable(MCR_ROOT.'configs/statics.cfg.php')){
			$write_cfg = '<div class="alert alert-error"><b>Внимание!</b> Выставите права 777 на файл <b>configs/statics.cfg.php</b></div>';
		}

		if($this->check_table()){
			$check_install = '<div class="alert"><b>Внимание!</b> Вы уже ранее устанавливали данный модуль. Данная установка будет произведена поверх старого модуля. Если хотите полностью переустановить предыдущий модуль, выберите пункт "Переустановить".</div>';
		}

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit'])){ $this->init->notify("Hacking Attempt!", "install/", 3); }

			if(!empty($write_menu) || !empty($write_cfg) || !empty($write_configs)){ $this->init->notify("Требуется выставить необходимые права на запись", "install/", 3); }

			if(isset($_POST['reinstall']) && $_POST['reinstall']=='true'){
				$drop = $this->db->MQ("DROP TABLE IF EXISTS `qx_statics`");
				if(!$drop){ $this->init->notify("Ошибка переустановки #1", "install/", 3); }
			}

			$sql = "CREATE TABLE IF NOT EXISTS `qx_statics` (
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
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

			$query = $this->db->MQ($sql);

			if(!$query){ $this->init->notify("Ошибка переустановки #2", "install/", 3); }

			$_SESSION['install_step'] = "2";

			$this->init->notify("", "install/step_2/", 2);
		}

		include_once(MCR_ROOT.'install_statics/styles/step-1.html');

		return ob_get_clean();
	}

	private function saveMenu($menu) {
	
		$txt  = "<?php if (!defined('MCR')) exit;".PHP_EOL;
		$txt .= '$menu_items = '.var_export($menu, true).';'.PHP_EOL;

		$result = file_put_contents(MCR_ROOT."instruments/menu_items.php", $txt);

		return (is_bool($result) and $result == false)? false : true;	
	}

	private function step_2(){
		ob_start();

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit'])){ $this->init->notify("Hacking Attempt!", "install/", 3); }

			require(MCR_ROOT."instruments/menu_items.php");

			$menu_items[1]['statics'] = array (
			  'name' => 'Статические страницы',
			  'url' => 'go/statics/admin/',
			  'parent_id' => 'admin',
			  'lvl' => 15,
			  'permission' => -1,
			  'active' => false,
			  'inner_html' => '',
			);

			if(!$this->saveMenu($menu_items)){ $this->init->notify("Ошибка переустановки #3", "install/", 3); }

			$_SESSION['install_step'] = "3";

			$this->init->notify("", "install/step_3/", 2);
		}

		include_once(MCR_ROOT.'install_statics/styles/step-2.html');

		return ob_get_clean();
	}

	private function step_3(){
		ob_start();

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit'])){ $this->init->notify("Hacking Attempt!", "install/", 3); }

			$this->cfg['install'] = false;

			if(!$this->init->save($this->cfg)){ $this->init->notify("Ошибка переустановки #4", "install/", 3); }

			$_SESSION['install_step'] = "finish";

			$this->init->notify("", "install/finish/", 2);
		}

		include_once(MCR_ROOT.'install_statics/styles/step-3.html');

		return ob_get_clean();
	}

	private function finish(){
		ob_start();
	
		$_SESSION['install_finished'] = true;

		unset($_SESSION['install_step']);

		include_once(MCR_ROOT.'install_statics/styles/finish.html');

		return ob_get_clean();
	}

	public function _list(){
		ob_start();

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'main';

		/**
		 * Select needed page
		 */

		$step = (!isset($_SESSION['install_step'])) ? "1" : $_SESSION['install_step'];

		switch($step){
			case "2":
				$this->title	= "Установка >> Шаг 2"; // Set page title (In tag <title></title>)
				$content		= $this->step_2(); // Set content
				$this->bc		= $this->init->get_bc("Установка - Шаг 2", "", false, true, false); // Set breadcrumbs
			break;

			case "3":
				$this->title	= "Установка >> Шаг 3"; // Set page title (In tag <title></title>)
				$content		= $this->step_3(); // Set content
				$this->bc		= $this->init->get_bc("Установка - Шаг 3", "", false, true, false); // Set breadcrumbs
			break;

			case "finish":
				$this->title	= "Установка >> Конец установки"; // Set page title (In tag <title></title>)
				$content		= $this->finish(); // Set content
				$this->bc		= $this->init->get_bc("Установка - Конец установки", "", false, true, false); // Set breadcrumbs
			break;

			default:
				$this->title	= "Установка >> Шаг 1";
				$content		= $this->step_1();
				$this->bc		= $this->init->get_bc("Установка - Шаг 1", "", false, true, false); /*$this->init->get_bc('Новости', 'news/', true).*/
			break;
		}

		echo $content;

		return ob_get_clean();
	}
}

/**
 * Static pages for WebMCR
 *
 * Installation class
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014 Qexy.org
 *
 * @version 1.0
 *
 */
?>