<?php
/**
 * Static pages for WebMCR
 *
 * Admin class (plugin)
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014 Qexy.org
 *
 * @version 1.0.1
 *
 */

// Check Qexy constant
if (!defined('QEXY')){ exit("Hacking Attempt!"); }

class statics_admin{
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

		if($this->user->lvl < $this->cfg['lvl_admin']){ $this->init->notify("Доступ запрещен!", "&do=403", "403", 3); }
	}

	private function pages_array(){
		ob_start();

		$start		= $this->init->pagination($this->cfg['rop_pages'], 0, 0); // Set start pagination
		$end		= $this->cfg['rop_pages']; // Set end pagination

		$query = $this->db->query("SELECT id, `uniq`, title
								FROM `qx_statics`
								ORDER BY id DESC
								LIMIT $start,$end");

		if(!$query || $this->db->num_rows($query)<=0){ echo $this->init->sp("admin/page-none.html"); return ob_get_clean(); } // Check returned result

		while($ar = $this->db->get_row($query)){

			$data = array(
				"ID" => intval($ar['id']),
				"TITLE" => $this->db->HSC($ar['title']),
				"UNIQ" => $this->db->HSC($ar['uniq']),
			);

			echo $this->init->sp("admin/page-id.html", $data);
		}

		return ob_get_clean();
	}

	private function _main(){
		ob_start();

		$sql			= "SELECT COUNT(*) FROM `qx_statics`"; // Set SQL query for pagination function
		$page			= "&do=admin&pid="; // Set url for pagination function
		$pagination		= $this->init->pagination($this->cfg['rop_pages'], $page, $sql); // Set pagination

		$pages			= $this->pages_array(); // Set content to variable

		$stc_f_security = 'stc_delete'; // Set default name for csrf security variable

		$data = array(
			"PAGINATION" => $pagination,
			"CONTENT" => $pages,
			"STC_F_SECURITY" => $stc_f_security,
			"STC_F_SET" => $this->init->csrf_set($stc_f_security)
		);

		echo $this->init->sp('admin/page-list.html', $data);

		return ob_get_clean();
	}

	/**
	 * filter_int(@param) - Filter for variables $var > 0
	 *
	 * @param - int,string,boolean
	 *
	 * @return integer (natural)
	 *
	*/
	private function filter_int($var){
		$var = (intval($var)<1) ? 1 : intval($var);
		return $var;
	}

	/**
	 * filter_text(@param) - Filter for config files
	 *
	 * @param - int,string,boolean
	 *
	 * @return string without chars - < > " '
	 *
	*/
	private function filter_text($var){

		$var = preg_replace("/[\<\>\"\']+/", "", $var);

		return $var;
	}

	private function settings(){
		ob_start();

		// CSRF Security name
		$stc_f_security = 'stc_settings';

		// Check for post method and CSRF hacking
		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit']) || !$this->init->csrf_check($stc_f_security)){ $this->init->notify("Hacking Attempt!", "&do=403", "403", 3); }

			// Filter saving vars [Start]
			$this->cfg['title']			= $this->filter_text($_POST['title']);
			$this->cfg['rop_pages']		= $this->filter_int($_POST['rop_pages']);
			$this->cfg['lvl_access']	= intval($_POST['lvl_access']);
			$this->cfg['lvl_admin']		= intval($_POST['lvl_admin']);
			// Filter saving vars [End]

			if(!$this->init->savecfg($this->cfg, "configs/statics.cfg.php")){ $this->init->notify("Ошибка сохранения настроек", "&do=admin&op=settings", "Ошибка!", 3); } // Check save config

			$this->init->notify("Настройки успешно сохранены", "&do=admin&op=settings", "Успех!", 1);
		}

		$content = array(
			"TITLE" => $this->db->HSC($this->cfg['title']),
			"ROP_PAGES" => intval($this->cfg['rop_pages']),
			"LVL_ACCESS" => intval($this->cfg['lvl_access']),
			"LVL_ADMIN" => intval($this->cfg['lvl_admin']),
			"STC_F_SET" => $this->init->csrf_set($stc_f_security),
			"STC_F_SECURITY" => $stc_f_security
		);

		echo $this->init->sp("admin/settings.html", $content);

		return ob_get_clean();
	}

	private function page_add(){
		ob_start();

		// CSRF Security name
		$stc_f_security = 'stc_add';

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit']) || !$this->init->csrf_check($stc_f_security)){ $this->init->notify("Hacking Attempt!", "&do=403", "403", 3); }

			$title		= $this->db->safesql($_POST['title']);
			$status		= (intval($_POST['status'])===1) ? 1 : 0;
			$access		= intval($_POST['access']);
			$uniq		= $this->db->safesql($_POST['uniq']);

			if(empty($title)){ $this->init->notify("Не заполнено поле \"Название\"", "&do=admin&op=new", "Ошибка!", 3); }
			if(empty($uniq) || !preg_match("/^\w+$/i", $uniq)){ $this->init->notify("Неверно заполнено поле \"Идентификатор\"", "&do=admin&op=new", "Ошибка!", 3); }

			$text_bb	= $this->db->safesql($this->db->HSC($_POST['text_bb']));
			$text_html	= $this->init->bb_decode($_POST['text_bb']);
			$text_html	= $this->db->safesql($text_html);
			$uid		= $this->user->id;
			$ip			= $this->init->getIP();

			// Set data
			$data = array(
					"time_create" => time(),
					"time_update" => time(),
					"ip_create" => $ip,
					"ip_update" => $ip
				);

			$data = $this->db->safesql(json_encode($data)); // Pack data to json

			$insert = $this->db->query("INSERT INTO `qx_statics`
											(title, `uniq`, text_bb, text_html, `status`, `access`, uid_create, uid_update, `data`)
										VALUES
											('$title', '$uniq', '$text_bb', '$text_html', '$status', '$access', '$uid', '$uid', '$data')");

			if(!$insert){ $this->init->notify("Ошибка добавления статической страницы.", "&do=admin&op=new", "Ошибка!", 3); }

			$this->init->notify("Статическая страница успешно добавлена", "&do=admin", "Успех!", 1);
		}


		$content = array(
			"TITLE" => "",
			"UNIQ" => "",
			"ACCESS" => "",
			"STATUS" => "",
			"TEXT_BB" => "",
			"SUBMIT" => "Добавить",
			"STC_F_SET" => $this->init->csrf_set($stc_f_security),
			"STC_F_SECURITY" => $stc_f_security,
			"BB_PANEL" => $this->init->bb_panel("", 'text_bb')
		);

		echo $this->init->sp("admin/page-change.html", $content);

		return ob_get_clean();
	}

	private function page_edit(){
		ob_start();

		if(empty($_GET['act'])){ $this->init->notify("Страница не найдена!", "&do=404", "404", 3); }

		$id = intval($_GET['act']); // Get page id

		$query = $this->db->query("SELECT title, `uniq`, `text_bb`, `status`, `access`, `data`
								FROM `qx_statics`
								WHERE id='$id'");
		if(!$query || $this->db->num_rows($query)<=0){ $this->init->notify("Страница не найдена!", "&do=404", "404", 3); }

		$ar = $this->db->get_row($query);

		$data = json_decode($ar['data'], true); // Unpack json data

		// CSRF Security name
		$stc_f_security = 'stc_edit';

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit']) || !$this->init->csrf_check($stc_f_security)){ $this->init->notify("Hacking Attempt!", "&do=403", "403", 3); }

			$title		= $this->db->safesql($_POST['title']);
			$status		= (intval($_POST['status'])===1) ? 1 : 0;
			$access		= intval($_POST['access']);
			$uniq		= $this->db->safesql($_POST['uniq']);

			if(empty($title)){ $this->init->notify("Не заполнено поле \"Название\"", "&do=admin&op=new", "Ошибка!". 3); }
			if(empty($uniq) || !preg_match("/^\w+$/i", $uniq)){ $this->init->notify("Неверно заполнено поле \"Идентификатор\"", "&do=admin&op=new", "Ошибка!", 3); }

			$text_bb	= $this->db->safesql($this->db->HSC($_POST['text_bb']));
			$text_html	= $this->init->bb_decode($_POST['text_bb']);
			$text_html	= $this->db->safesql($text_html);
			$uid		= $this->user->id;
			$ip			= $this->init->getIP();

			$data = array(
					"time_create" => intval($data['time_create']),
					"time_update" => time(),
					"ip_create" => $data['ip_create'],
					"ip_update" => $ip
				);

			$data = $this->db->safesql(json_encode($data));

			$update = $this->db->query("UPDATE `qx_statics`
									SET title='$title', `uniq`='$uniq', text_bb='$text_bb', text_html='$text_html',
										`status`='$status', `access`='$access', uid_update='$uid', `data`='$data'
									WHERE id='$id'");
			if(!$update){ $this->init->notify("Ошибка обновления страницы.", "&do=admin&op=edit&act=$id", 3); }

			$this->init->notify("Страница успешно изменена", "&do=admin&op=edit&act=$id", "Успех!", 1);
		}

		$status		= (intval($ar['status'])===0) ? 'selected' : '';
		$text_bb	= $this->db->HSC($ar['text_bb']);

		$content = array(
			"TITLE" => $this->db->HSC($ar['title']),
			"UNIQ" => $this->db->HSC($ar['uniq']),
			"ACCESS" => intval($ar['access']),
			"STATUS" => $status,
			"TEXT_BB" => $text_bb,
			"SUBMIT" => "Сохранить",
			"STC_F_SET" => $this->init->csrf_set($stc_f_security),
			"STC_F_SECURITY" => $stc_f_security,
			"BB_PANEL" => $this->init->bb_panel($text_bb, 'text_bb')
		);

		echo $this->init->sp("admin/page-change.html", $content);

		return ob_get_clean();
	}

	private function page_delete(){

		$stc_f_security = 'stc_delete';

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->init->notify("Hacking Attempt!", "&do=403", "403", 3); }
		
		if(!isset($_POST['delete']) || !$this->init->csrf_check($stc_f_security)){ $this->init->notify("Hacking Attempt!", "&do=403", "403", 3); }

		$id = intval($_POST['delete']);

		$delete = $this->db->query("DELETE FROM `qx_statics` WHERE id='$id'");

		if(!$delete){ $this->init->notify("Ошибка удаления страницы.", "&do=admin", "Ошибка!", 3); }

		$count = $this->db->get_affected_rows();

		if($count<=0){ $this->init->notify("Вы ничего не удалили.", "&do=admin", "Пусто", 2); }

		$this->init->notify("Выбранные страницы успешно удалены ($count)", "&do=admin", "Успех!", 1);

		exit;
	}

	public function _list(){
		ob_start();

		$op = (isset($_GET['op'])) ? $_GET['op'] : 'main';

		/**
		 * Select needed page
		 */

		switch($op){
			case "new":
				$this->title	= "Панель управления — Добавление страницы"; // Set page title (In tag <title></title>)
				$content		= $this->page_add(); // Set content
				$array = array(
					"Главная" => BASE_URL,
					$this->init->cfg['title'] => STC_URL,
					"Панель управления" => STC_ADMIN_URL,
					"Добавление страницы" => ""
				);
				$this->bc		= $this->init->bc($array);
			break;

			case "edit":
				$this->title	= "Панель управления — Редактирование страницы";
				$content		= $this->page_edit();
				$array = array(
					"Главная" => BASE_URL,
					$this->init->cfg['title'] => STC_URL,
					"Панель управления" => STC_ADMIN_URL,
					"Редактирование страницы" => ""
				);
				$this->bc		= $this->init->bc($array);
			break;

			case "delete":
				$this->title	= "Панель управления — Удаление страницы";
				$content		= $this->page_delete();
				$array = array(
					"Главная" => BASE_URL,
					$this->init->cfg['title'] => STC_URL,
					"Панель управления" => STC_ADMIN_URL,
					"Удаление страницы" => ""
				);
				$this->bc		= $this->init->bc($array);
			break;

			case "settings":
				$this->title	= "Панель управления — Настройки";
				$content		= $this->settings();
				$array = array(
					"Главная" => BASE_URL,
					$this->init->cfg['title'] => STC_URL,
					"Панель управления" => STC_ADMIN_URL,
					"Настройки" => ""
				);
				$this->bc		= $this->init->bc($array);
			break;

			default:
				$this->title	= "Панель управления — Главная";
				$content		= $this->_main();
				$array = array(
					"Главная" => BASE_URL,
					$this->init->cfg['title'] => STC_URL,
					"Панель управления" => STC_ADMIN_URL
				);
				$this->bc		= $this->init->bc($array);
			break;
		}

		echo $content;

		return ob_get_clean();
	}
}

/**
 * Static pages for WebMCR
 *
 * Admin class (plugin)
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014 Qexy.org
 *
 * @version 1.0.1
 *
 */
?>
