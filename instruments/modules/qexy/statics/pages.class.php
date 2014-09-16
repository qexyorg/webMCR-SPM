<?php
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

		$lvl = $this->user->lvl();

		$this->configs = $this->init->get_config();

		$bd_names = $this->configs['bd_names'];
		$bd_users = $this->configs['bd_users'];

		$op = $this->db->MRES($op);

		$query = $this->db->MQ("SELECT	`s`.id, `s`.title, `s`.`data`, `s`.`text_html`, `s`.uid_create, `s`.uid_update, `s`.`access`,
										`cr`.`{$bd_users['login']}` AS login_create,
										`up`.`{$bd_users['login']}` AS login_update
								FROM `qx_statics` AS `s`
								LEFT JOIN `{$bd_names['users']}` AS `cr`
									ON `cr`.`{$bd_users['id']}` = `s`.uid_create
								LEFT JOIN `{$bd_names['users']}` AS `up`
									ON `up`.`{$bd_users['id']}` = `s`.uid_create
								WHERE `s`.`uniq`='$op'
									AND `s`.`status`='1'");

		if(!$query || $this->db->MNR($query)<=0){ $this->init->notify("Страница не найдена", "404/", 3); }

		$ar = $this->db->MFAS($query);

		if($lvl < intval($ar['access'])){ $this->init->notify("Доступ запрещен!", "403/", 3); }
		
		// Filter returned vars [Start]
		$id				= intval($ar['id']);
		$title			= $this->db->HSC($ar['title']);
		$text			= $ar['text_html'];
		$data			= json_decode($ar['data'], true);

		$author_id		= intval($ar['uid_create']);
		$updater_id		= intval($ar['uid_update']);
		$author			= $this->db->HSC($ar['login_create']);
		$updater		= $this->db->HSC($ar['login_update']);

		$created		= date("d.m.Y в H:i:s", intval($data['time_create']));
		$updated		= date("d.m.Y в H:i:s", intval($data['time_update']));
		// Filter returned vars [End]

		// If you need destroy unused vars, remove comments
		// unset($lvl, $bd_names, $bd_users, $query, $ar, $data);

		include_once(STC_STYLE.'pages/page-full.html');

		$this->title	= $title;

		return ob_get_clean();
	}

	public function _list(){
		ob_start();

		$op = (isset($_GET['op'])) ? $_GET['op'] : '404';

		$content		= $this->page_full($op); // Set content
		$this->bc		= $this->init->get_bc($this->title, '', false, true, false); // Set breadcrumbs

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
