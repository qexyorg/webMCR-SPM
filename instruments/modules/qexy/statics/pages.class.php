<?php
/**
 * Static pages for WebMCR
 *
 * Pages class (plugin)
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

class statics_pages{
	// Set default variables
	private $cfg			= array();
	private $user			= false;
	private $db				= false;
	private $init			= false;
	private $configs		= array();
	public	$in_header		= '';
	public	$title			= '';

	// Set constructor vars
	public function __construct($init){

		$this->cfg			= $init->cfg;
		$this->user			= $init->user;
		$this->db			= $init->db;
		$this->init			= $init;

	}

	// Get full page
	private function page_full($op){
		ob_start();

		$lvl = $this->user->lvl;

		$this->configs = $this->init->getMcrConfig();

		$bd_names = $this->configs['bd_names'];
		$bd_users = $this->configs['bd_users'];

		$op = $this->db->safesql($op);

		$query = $this->db->query("SELECT	`s`.id, `s`.title, `s`.`data`, `s`.`text_html`, `s`.uid_create, `s`.uid_update, `s`.`access`,
										`cr`.`{$bd_users['login']}` AS login_create,
										`up`.`{$bd_users['login']}` AS login_update
									FROM `qx_statics` AS `s`
									LEFT JOIN `{$bd_names['users']}` AS `cr`
										ON `cr`.`{$bd_users['id']}` = `s`.uid_create
									LEFT JOIN `{$bd_names['users']}` AS `up`
										ON `up`.`{$bd_users['id']}` = `s`.uid_create
									WHERE `s`.`uniq`='$op'
										AND `s`.`status`='1'");

		if(!$query || $this->db->num_rows($query)<=0){ $this->init->notify("Страница не найдена", "&do=404", "404", 3); }

		$ar = $this->db->get_row($query);

		if($lvl < intval($ar['access'])){ $this->init->notify("Доступ запрещен!", "&do=403", "403", 3); }

		$data = json_decode($ar['data'], true);

		$content = array(
			"ID" => intval($ar['id']),
			"TITLE" => $this->db->HSC($ar['title']),
			"TEXT" => $ar['text_html'],
			"DATA" => $data,

			"AUTHOR_ID" => intval($ar['uid_create']),
			"UPDATER_ID" => intval($ar['uid_update']),
			"AUTHOR" => $this->db->HSC($ar['login_create']),
			"UPDATER" => $this->db->HSC($ar['login_update']),
			"CREATED" => date("d.m.Y в H:i:s", intval($data['time_create'])),
			"UPDATED" => date("d.m.Y в H:i:s", intval($data['time_update'])),
		);

		echo $this->init->sp("pages/page-full.html", $content);

		$this->title	= $content['TITLE'];

		return ob_get_clean();
	}

	public function _list(){
		ob_start();

		$op = (isset($_GET['op'])) ? $_GET['op'] : '404';

		echo $this->page_full($op);

		$array = array(
			"Главная" => BASE_URL,
			$this->init->cfg['title'] => STC_URL,
			$this->title => ""
		);
		
		$this->bc		= $this->init->bc($array); // Set breadcrumbs

		return ob_get_clean();
	}
}

/**
 * Static pages for WebMCR
 *
 * Pages class (plugin)
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014 Qexy.org
 *
 * @version 1.0.1
 *
 */
?>
