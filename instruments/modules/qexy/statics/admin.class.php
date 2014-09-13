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
 * @version 1.0
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

		if($this->init->user_lvl < $this->cfg['lvl_admin']){ $this->init->notify("Доступ запрещен!", "403/", 3); }
	}

	private function pages_array(){
		ob_start();

		$start		= $this->init->pagination($this->cfg['rop_pages'], 0, 0); // Set start pagination
		$end		= $this->cfg['rop_pages']; // Set end pagination

		$query = $this->db->MQ("SELECT id, `uniq`, title
								FROM `qx_statics`
								ORDER BY id DESC
								LIMIT $start,$end");

		if(!$query || $this->db->MNR($query)<=0){ include_once(STC_STYLE_ADMIN.'page-none.html'); return ob_get_clean(); } // Check returned result

		while($ar = $this->db->MFAS($query)){
			// Filter vars [Start]
			$id		= intval($ar['id']);
			$title	= $this->db->HSC($ar['title']);
			$uniq	= $this->db->HSC($ar['uniq']);
			// Filter vars [End]

			include(STC_STYLE_ADMIN.'page-id.html');
		}

		return ob_get_clean();
	}

	private function _main(){
		ob_start();

		$sql			= "SELECT COUNT(*) FROM `qx_statics`"; // Set SQL query for pagination function
		$page			= "admin/page-"; // Set url for pagination function
		$pagination		= $this->init->pagination($this->cfg['rop_pages'], $page, $sql); // Set pagination

		$pages			= $this->pages_array(); // Set content to variable

		$stc_f_security = 'stc_delete'; // Set default name for csrf security variable
		$stc_f_set		= $this->init->csrf_set($stc_f_security); // Set csrf security variable

		include_once(STC_STYLE_ADMIN.'page-list.html');

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
			if(!isset($_POST['submit']) || !$this->init->csrf_check($stc_f_security)){ $this->init->notify("Hacking Attempt!", "403/", 3); }

			// Filter saving vars [Start]
			$this->cfg['title']			= $this->filter_text($_POST['title']);
			$this->cfg['rop_pages']		= $this->filter_int($_POST['rop_pages']);
			$this->cfg['lvl_access']	= intval($_POST['lvl_access']);
			$this->cfg['lvl_admin']		= intval($_POST['lvl_admin']);
			// Filter saving vars [End]

			if(!$this->init->save($this->cfg)){ $this->init->notify("Ошибка сохранения настроек", "admin/settings/", 3); } // Check save config

			$this->init->notify("Настройки успешно сохранены", "admin/settings/", 1);
		}

		// Filter returned vars [Start]
		$title			= $this->db->HSC($this->cfg['title']);
		$rop_pages		= intval($this->cfg['rop_pages']);
		$lvl_access		= intval($this->cfg['lvl_access']);
		$lvl_admin		= intval($this->cfg['lvl_admin']);
		// Filter returned vars [End]

		// CSRF Security set
		$stc_f_set		= $this->init->csrf_set($stc_f_security);

		include_once(STC_STYLE_ADMIN.'settings.html');

		return ob_get_clean();
	}

	/**
	 * BBquote(@param) - Recursive function for bb codes
	 *
	 * @param - String
	 *
	 * @return callback function
	 *
	*/
	private function BBquote($text)
	{
		$reg = '#\[quote]((?:[^[]|\[(?!/?quote])|(?R))+)\[/quote]#isu';
		if (is_array($text)){$text = '<blockquote>'.$text[1].'</blockquote>';}
		return preg_replace_callback($reg, 'self::BBquote', $text);
	}

	/**
	 * bb_decode(@param) - Change BB-code to HTML
	 *
	 * @param - String
	 *
	 * @return String
	 *
	*/
	private function bb_decode($text)
	{
		$text = nl2br($text);

		$patern = array(
			'/\[b\](.*)\[\/b\]/Usi',
			'/\[i\](.*)\[\/i\]/Usi',
			'/\[s\](.*)\[\/s\]/Usi',
			'/\[u\](.*)\[\/u\]/Usi',
			'/\[left\](.*)\[\/left\]/Usi',
			'/\[center\](.*)\[\/center\]/Usi',
			'/\[right\](.*)\[\/right\]/Usi',
			'/\[code\](.*)\[\/code\]/Usi',
		);

		$replace = array(
			'<b>$1</b>',
			'<i>$1</i>',
			'<s>$1</s>',
			'<u>$1</u>',
			'<p align="left">$1</p>',
			'<p align="center">$1</p>',
			'<p align="right">$1</p>',
			'<code>$1</code>',
		);

		$text = preg_replace($patern, $replace, $text);
		$text = preg_replace("/\[url=(?:&#039;|&quot;|\'|\")((((ht|f)tps?|mailto):(?:\/\/)?)(?:[^<\s\'\"]+))(?:&#039;|&quot;|\'|\")\](.*)\[\/url\]/Usi", "<a href=\"$1\">$5</a>", $text);
		$text = preg_replace("/\[img\](((ht|f)tps?:(?:\/\/)?)(?:[^<\s\'\"]+))\[\/img\]/Usi", "<img src=\"$1\">", $text);
		$text = preg_replace("/\[color=(?:&#039;|&quot;|\'|\")(\#[a-z0-9]{6})(?:&#039;|&quot;|\'|\")\](.*)\[\/color\]/Usi", "<font color=\"$1\">$2</font>", $text);
		$text = preg_replace("/\[size=(?:&#039;|&quot;|\'|\")([1-6]{1})(?:&#039;|&quot;|\'|\")\](.*)\[\/size\]/Usi", "<font size=\"$1\">$2</font>", $text);

		$text = $this->BBquote($text);

		return $text;
	}

	private function page_add(){
		ob_start();

		// CSRF Security name
		$stc_f_security = 'stc_add';

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit']) || !$this->init->csrf_check($stc_f_security)){ $this->init->notify("Hacking Attempt!", "403/", 3); }

			$title		= $this->db->MRES($_POST['title']);
			$status		= (intval($_POST['status'])===1) ? 1 : 0;
			$access		= intval($_POST['access']);
			$uniq		= $this->db->MRES($_POST['uniq']);

			if(empty($title)){ $this->init->notify("Ошибка! Не заполнено поле \"Название\"", "admin/new/", 3); }
			if(empty($uniq) || !preg_match("/^\w+$/i", $uniq)){ $this->init->notify("Ошибка! Неверно заполнено поле \"Идентификатор\"", "admin/new/", 3); }

			$text_bb	= $this->db->MRES($this->db->HSC($_POST['text_bb']));
			$text_html	= $this->bb_decode($_POST['text_bb']);
			$text_html	= $this->db->MRES($text_html);
			$uid		= $this->user->id();
			$ip			= GetRealIp();

			// Set data
			$data = array(
					"time_create" => time(),
					"time_update" => time(),
					"ip_create" => $ip,
					"ip_update" => $ip
				);

			$data = $this->db->MRES(json_encode($data)); // Pack data to json

			$insert = $this->db->MQ("INSERT INTO `qx_statics`
											(title, `uniq`, text_bb, text_html, `status`, `access`, uid_create, uid_update, `data`)
										VALUES
											('$title', '$uniq', '$text_bb', '$text_html', '$status', '$access', '$uid', '$uid', '$data')");

			if(!$insert){ $this->init->notify("Ошибка добавления статической страницы.", "admin/new/", 3); }

			$this->init->notify("Статическая страница успешно добавлена", "admin/", 1);
		}

		$title = $uniq = $access = $status = $text_bb = ""; // Set default variables
		$submit = "Добавить"; // Set button name

		// CSRF Security set
		$stc_f_set		= $this->init->csrf_set($stc_f_security);

		include_once(STC_STYLE_ADMIN.'page-change.html');

		return ob_get_clean();
	}

	private function page_edit(){
		ob_start();

		if(empty($_GET['act'])){ $this->init->notify("Страница не найдена!", "admin/", 3); }

		$id = intval($_GET['act']); // Get page id

		$query = $this->db->MQ("SELECT title, `uniq`, `text_bb`, `status`, `access`, `data`
								FROM `qx_statics`
								WHERE id='$id'");
		if(!$query || $this->db->MNR($query)<=0){ $this->init->notify("Страница не найдена!", "admin/", 3); }

		$ar = $this->db->MFAS($query);

		$data = json_decode($ar['data'], true); // Unpack json data

		// CSRF Security name
		$stc_f_security = 'stc_edit';

		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!isset($_POST['submit']) || !$this->init->csrf_check($stc_f_security)){ $this->init->notify("Hacking Attempt!", "403/", 3); }

			$title		= $this->db->MRES($_POST['title']);
			$status		= (intval($_POST['status'])===1) ? 1 : 0;
			$access		= intval($_POST['access']);
			$uniq		= $this->db->MRES($_POST['uniq']);

			if(empty($title)){ $this->init->notify("Ошибка! Не заполнено поле \"Название\"", "admin/new/", 3); }
			if(empty($uniq) || !preg_match("/^\w+$/i", $uniq)){ $this->init->notify("Ошибка! Неверно заполнено поле \"Идентификатор\"", "admin/new/", 3); }

			$text_bb	= $this->db->MRES($this->db->HSC($_POST['text_bb']));
			$text_html	= $this->bb_decode($_POST['text_bb']);
			$text_html	= $this->db->MRES($text_html);
			$uid		= $this->user->id();
			$ip			= GetRealIp();

			$data = array(
					"time_create" => intval($data['time_create']),
					"time_update" => time(),
					"ip_create" => $data['ip_create'],
					"ip_update" => $ip
				);

			$data = $this->db->MRES(json_encode($data));

			$update = $this->db->MQ("UPDATE `qx_statics`
									SET title='$title', `uniq`='$uniq', text_bb='$text_bb', text_html='$text_html',
										`status`='$status', `access`='$access', uid_update='$uid', `data`='$data'
									WHERE id='$id'");
			if(!$update){ $this->init->notify("Ошибка обновления страницы.", "admin/edit/$id/", 3); }

			$this->init->notify("Страница успешно изменена", "admin/edit/$id/", 1);
		}

		$title		= $this->db->HSC($ar['title']);
		$uniq		= $this->db->HSC($ar['uniq']);
		$access		= intval($ar['access']);
		$status		= (intval($ar['status'])===0) ? 'selected' : '';
		$text_bb	= $this->db->HSC($ar['text_bb']);
		$submit		= "Сохранить";

		// CSRF Security set
		$stc_f_set		= $this->init->csrf_set($stc_f_security);

		include_once(STC_STYLE_ADMIN.'page-change.html');

		return ob_get_clean();
	}

	private function page_delete(){

		$stc_f_security = 'stc_delete';

		if($_SERVER['REQUEST_METHOD']!='POST'){ $this->init->notify("Hacking Attempt!", "403/", 3); }
		
		if(!isset($_POST['delete']) || !$this->init->csrf_check($stc_f_security)){ $this->init->notify("Hacking Attempt!", "403/", 3); }

		$id = intval($_POST['delete']);

		$delete = $this->db->MQ("DELETE FROM `qx_statics` WHERE id='$id'");

		if(!$delete){ $this->init->notify("Ошибка удаления страницы.", "admin/", 3); }

		$count = $this->db->MAR();

		if($count<=0){ $this->init->notify("Вы ничего не удалили.", "admin/", 2); }

		$this->init->notify("Выбранные страницы успешно удалены ($count)", "admin/", 1);

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
				$this->title	= "Панель управления >> Добавление страницы"; // Set page title (In tag <title></title>)
				$content		= $this->page_add(); // Set content
				$this->bc		= $this->init->get_bc('Панель управления', 'admin/', true).$this->init->get_bc("Добавление страницы", '', false, true, false); // Set breadcrumbs
			break;

			case "edit":
				$this->title	= "Панель управления >> Редактирование страницы";
				$content		= $this->page_edit();
				$this->bc		= $this->init->get_bc('Панель управления', 'admin/', true).$this->init->get_bc("Редактирование страницы", '', false, true, false);
			break;

			case "delete":
				$this->title	= "Панель управления >> Удаление страницы";
				$content		= $this->page_delete();
				$this->bc		= $this->init->get_bc('Панель управления', 'admin/', true).$this->init->get_bc("Удаление страницы", '', false, true, false);
			break;

			case "settings":
				$this->title	= "Панель управления >> Настройки";
				$content		= $this->settings();
				$this->bc		= $this->init->get_bc('Панель управления', 'admin/', true).$this->init->get_bc("Настройки", '', false, true, false);
			break;

			default:
				$this->title	= "Панель управления >> Главная";
				$content		= $this->_main();
				$this->bc		= $this->init->get_bc("Панель управления", "", false, true, false); /*$this->init->get_bc('Новости', 'news/', true).*/
			break;
		}

		echo $content;

		return ob_get_clean();
	}
}

/**
 * Control moder panel (CMP) for webmcr
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014-2014 Qexy.org
 *
 * @version 1.0
 *
 */
?>
