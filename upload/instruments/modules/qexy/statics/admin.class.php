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
 * @version 1.2.0
 *
 */

// Check Qexy constant
if (!defined('QEXY')){ exit("Hacking Attempt!"); }

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

		if($this->user->lvl < $this->cfg['lvl_admin']){ $this->api->notify("Доступ запрещен!", "&do=403", "403", 3); }
	}

	private function pages_array(){

		$start		= $this->api->pagination($this->cfg['rop_pages'], 0, 0); // Set start pagination
		$end		= $this->cfg['rop_pages']; // Set end pagination

		$query = $this->db->query("SELECT id, `uniq`, title
								FROM `qx_statics`
								ORDER BY id DESC
								LIMIT $start,$end");

		if(!$query || $this->db->num_rows($query)<=0){ return $this->api->sp("admin/page-none.html"); } // Check returned result

		ob_start();

		while($ar = $this->db->get_row($query)){

			$data = array(
				"ID" => intval($ar['id']),
				"TITLE" => $this->db->HSC($ar['title']),
				"UNIQ" => $this->db->HSC($ar['uniq']),
			);

			echo $this->api->sp("admin/page-id.html", $data);
		}

		return ob_get_clean();
	}

	private function _main(){

		$sql			= "SELECT COUNT(*) FROM `qx_statics`"; // Set SQL query for pagination function
		$page			= "&do=admin&pid="; // Set url for pagination function
		$pagination		= $this->api->pagination($this->cfg['rop_pages'], $page, $sql); // Set pagination

		$pages			= $this->pages_array(); // Set content to variable

		$stc_f_security = 'stc_delete'; // Set default name for csrf security variable

		$data = array(
			"PAGINATION" => $pagination,
			"CONTENT" => $pages,
			"STC_F_SECURITY" => $stc_f_security,
			"STC_F_SET" => $this->api->csrf_set($stc_f_security)
		);

		return $this->api->sp('admin/page-list.html', $data);
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
		// CSRF Security name
		$stc_f_security = 'stc_settings';

		// Check for post method and CSRF hacking
		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit']) || !$this->api->csrf_check($stc_f_security)){ $this->api->notify("Hacking Attempt!", "&do=403", "403", 3); }

			// Filter saving vars [Start]
			$this->cfg['title']			= $this->filter_text(@$_POST['title']);
			$this->cfg['rop_pages']		= $this->filter_int(@$_POST['rop_pages']);
			$this->cfg['lvl_access']	= intval(@$_POST['lvl_access']);
			$this->cfg['lvl_admin']		= intval(@$_POST['lvl_admin']);
			// Filter saving vars [End]

			if(!$this->api->savecfg($this->cfg, "configs/statics.cfg.php")){ $this->api->notify("Ошибка сохранения настроек", "&do=admin&op=settings", "Ошибка!", 3); } // Check save config

			$this->api->notify("Настройки успешно сохранены", "&do=admin&op=settings", "Успех!", 1);
		}

		$content = array(
			"TITLE" => $this->db->HSC($this->cfg['title']),
			"ROP_PAGES" => intval($this->cfg['rop_pages']),
			"LVL_ACCESS" => intval($this->cfg['lvl_access']),
			"LVL_ADMIN" => intval($this->cfg['lvl_admin']),
			"STC_F_SET" => $this->api->csrf_set($stc_f_security),
			"STC_F_SECURITY" => $stc_f_security
		);

		return $this->api->sp("admin/settings.html", $content);
	}

	private function page_add(){

		// CSRF Security name
		$stc_f_security = 'stc_add';

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit']) || !$this->api->csrf_check($stc_f_security)){ $this->api->notify("Hacking Attempt!", "&do=403", "403", 3); }

			$title		= $this->db->safesql(@$_POST['title']);
			$status		= (intval(@$_POST['status'])===1) ? 1 : 0;
			$access		= intval(@$_POST['access']);
			$uniq		= $this->db->safesql(@$_POST['uniq']);

			if(empty($title)){ $this->api->notify("Не заполнено поле \"Название\"", "&do=admin&op=new", "Ошибка!", 3); }
			if(empty($uniq) || !preg_match("/^\w+$/i", $uniq)){ $this->api->notify("Неверно заполнено поле \"Идентификатор\"", "&do=admin&op=new", "Ошибка!", 3); }

			$text_bb	= $this->db->safesql($this->db->HSC(@$_POST['text_bb']));
			$text_html	= $this->api->bb_decode(@$_POST['text_bb']);
			$text_html	= $this->db->safesql($text_html);
			$uid		= $this->user->id;
			$ip			= $this->api->getIP();

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

			if(!$insert){ $this->api->notify("Ошибка добавления статической страницы.", "&do=admin&op=new", "Ошибка!", 3); }

			$this->api->notify("Статическая страница успешно добавлена", "&do=admin", "Успех!", 1);
		}

		$content = array(
			"TITLE" => "",
			"UNIQ" => "",
			"ACCESS" => "",
			"STATUS" => "",
			"TEXT_BB" => "",
			"SUBMIT" => "Добавить",
			"STC_F_SET" => $this->api->csrf_set($stc_f_security),
			"STC_F_SECURITY" => $stc_f_security
		);

		return $this->api->sp("admin/page-change.html", $content);
	}

	private function page_edit(){

		if(empty($_GET['act'])){ $this->api->notify("Страница не найдена!", "&do=404", "404", 3); }

		$id = intval($_GET['act']); // Get page id

		$query = $this->db->query("SELECT title, `uniq`, `text_bb`, `status`, `access`, `data`
									FROM `qx_statics`
									WHERE id='$id'");
		if(!$query || $this->db->num_rows($query)<=0){ $this->api->notify("Страница не найдена!", "&do=404", "404", 3); }

		$ar = $this->db->get_row($query);

		$data = json_decode($ar['data'], true); // Unpack json data

		// CSRF Security name
		$stc_f_security = 'stc_edit';

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit']) || !$this->api->csrf_check($stc_f_security)){ $this->api->notify("Hacking Attempt!", "&do=403", "403", 3); }

			$title		= $this->db->safesql(@$_POST['title']);
			$status		= (intval(@$_POST['status'])===1) ? 1 : 0;
			$access		= intval(@$_POST['access']);
			$uniq		= $this->db->safesql(@$_POST['uniq']);

			if(empty($title)){ $this->api->notify("Не заполнено поле \"Название\"", "&do=admin&op=new", "Ошибка!". 3); }
			if(empty($uniq) || !preg_match("/^\w+$/i", $uniq)){ $this->api->notify("Неверно заполнено поле \"Идентификатор\"", "&do=admin&op=new", "Ошибка!", 3); }

			$text_bb	= $this->db->safesql($this->db->HSC(@$_POST['text_bb']));
			$text_html	= $this->api->bb_decode(@$_POST['text_bb']);
			$text_html	= $this->db->safesql($text_html);
			$uid		= $this->user->id;
			$ip			= $this->api->getIP();

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
			if(!$update){ $this->api->notify("Ошибка обновления страницы.", "&do=admin&op=edit&act=$id", 3); }

			$this->api->notify("Страница успешно изменена", "&do=admin&op=edit&act=$id", "Успех!", 1);
		}

		$content = array(
			"TITLE" => $this->db->HSC($ar['title']),
			"UNIQ" => $this->db->HSC($ar['uniq']),
			"ACCESS" => intval($ar['access']),
			"STATUS" => (intval($ar['status'])===0) ? 'selected' : '',
			"TEXT_BB" => $this->db->HSC($ar['text_bb']),
			"SUBMIT" => "Сохранить",
			"STC_F_SET" => $this->api->csrf_set($stc_f_security),
			"STC_F_SECURITY" => $stc_f_security
		);

		return $this->api->sp("admin/page-change.html", $content);
	}

	private function page_delete(){

		$stc_f_security = 'stc_delete';

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->api->notify("Hacking Attempt!", "&do=403", "403", 3); }
		
		if(!isset($_POST['delete']) || !$this->api->csrf_check($stc_f_security)){ $this->api->notify("Hacking Attempt!", "&do=403", "403", 3); }

		$id = intval(@$_POST['delete']);

		$delete = $this->db->query("DELETE FROM `qx_statics` WHERE id='$id'");

		if(!$delete){ $this->api->notify("Ошибка удаления страницы.", "&do=admin", "Ошибка!", 3); }

		$count = $this->db->get_affected_rows();

		if($count<=0){ $this->api->notify("Вы ничего не удалили.", "&do=admin", "Пусто", 2); }

		$this->api->notify("Выбранные страницы успешно удалены ($count)", "&do=admin", "Успех!", 1);
	}

	public function _list(){

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
					$this->api->cfg['title'] => MOD_URL,
					"Панель управления" => MOD_ADMIN_URL,
					"Добавление страницы" => ""
				);
				$this->bc		= $this->api->bc($array);
			break;

			case "edit":
				$this->title	= "Панель управления — Редактирование страницы";
				$content		= $this->page_edit();
				$array = array(
					"Главная" => BASE_URL,
					$this->api->cfg['title'] => MOD_URL,
					"Панель управления" => MOD_ADMIN_URL,
					"Редактирование страницы" => ""
				);
				$this->bc		= $this->api->bc($array);
			break;

			case "delete":
				$this->title	= "Панель управления — Удаление страницы";
				$content		= $this->page_delete();
				$array = array(
					"Главная" => BASE_URL,
					$this->api->cfg['title'] => MOD_URL,
					"Панель управления" => MOD_ADMIN_URL,
					"Удаление страницы" => ""
				);
				$this->bc		= $this->api->bc($array);
			break;

			case "settings":
				$this->title	= "Панель управления — Настройки";
				$content		= $this->settings();
				$array = array(
					"Главная" => BASE_URL,
					$this->api->cfg['title'] => MOD_URL,
					"Панель управления" => MOD_ADMIN_URL,
					"Настройки" => ""
				);
				$this->bc		= $this->api->bc($array);
			break;

			default:
				$this->title	= "Панель управления — Главная";
				$content		= $this->_main();
				$array = array(
					"Главная" => BASE_URL,
					$this->api->cfg['title'] => MOD_URL,
					"Панель управления" => MOD_ADMIN_URL
				);
				$this->bc		= $this->api->bc($array);
			break;
		}

		return $content;
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
 * @version 1.2.0
 *
 */
?>
