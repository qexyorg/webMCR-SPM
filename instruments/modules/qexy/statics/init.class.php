<?php
/**
 * Static pages for WebMCR
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

class statics_init{
	// Set default variables
	public $cfg				= array();
	public $user			= false;
	public $db				= false;
	public $user_lvl		= 0;
	public $user_login		= "Гость";
	public $user_group		= "Гость";
	public $user_group_id	= 0;

	// Set constructor vars
	public function __construct($user, $cfg){

		$this->cfg			= $cfg;
		$this->user			= $user;
		$this->user_lvl		= (empty($this->user)) ? 0 : $this->user->lvl();
		$this->user_login	= (empty($this->user)) ? "Гость" : $this->user->name();

		$this->db			= new statics_db();
		if($this->user_lvl < $this->cfg['lvl_access']){ header('Location: '.BASE_URL.'go/403/'); exit; } // Check access user level
	}

	/**
	 * info_set() - Set info session
	 *
	 * @return none
	 *
	*/
	public function info_set(){
		if(isset($_SESSION['stc_info'])){ define('STC_INFO', $this->info()); }else{ define('STC_INFO', ''); }
	}

	/**
	 * info_unset() - Unset info session
	 *
	 * @return none
	 *
	*/
	public function info_unset(){
		if(isset($_SESSION['stc_info'])){unset($_SESSION['stc_info']); unset($_SESSION['stc_info_t']);}
	}

	/**
	 * notify(@param) - Set notify and redirect
	 *
	 * @param $text - Message
	 * @param $url - /go/statics/YOUR_URL
	 * @param $type - 1: Green; 2: Yellow; 3: Red;
	 *
	 * @return none
	 *
	*/
	public function notify($text, $url='', $type=4){
		$_SESSION['stc_info'] = $text;
		$_SESSION['stc_info_t'] = $type;

		header('Location: '.STC_URL.$url); exit;
		return true;
	}

	/**
	 * info() - Set info params
	 *
	 * @return buffer
	 *
	*/
	private function info(){
		ob_start();

		if(empty($_SESSION['stc_info'])){ return ob_get_clean(); }
		
		switch($_SESSION['stc_info_t']){
			case 1: $type = 'alert-success'; break;
			case 2: $type = 'alert-info'; break;
			case 3: $type = 'alert-error'; break;

			default: $type = ''; break;
		}

		$text = $this->db->HSC($_SESSION['stc_info']);

		include_once(STC_STYLE.'info.html');
		return ob_get_clean();
	}

	/**
	 * csrf_check(@param) - Check csrf hacking
	 *
	 * @param - csrf variable
	 *
	 * @return boolean
	 *
	*/
	public function csrf_check($var='stc_f'){
		if(!isset($_SESSION[$var]) || !isset($_POST[$var])){ return false; }

		if($_SESSION[$var]!=$_POST[$var]){ unset($_SESSION[$var]); return false; }

		unset($_SESSION[$var]);

		return true;
	}

	/**
	 * csrf_set(@param) - Set csrf variable
	 *
	 * @param - csrf variable
	 *
	 * @return String
	 *
	*/
	public function csrf_set($var){
		$_SESSION[$var] = md5(randString(30));
		return $_SESSION[$var];
	}

	/**
	 * filter_array_integer(@param) - Filter array variables to integer
	 *
	 * @param - Array
	 *
	 * @return - Array
	 *
	*/
	public function filter_array_integer($array){
		if(empty($array)){ return false; }
		$new_ar = array();
		foreach($array as $key => $value){
			$new_ar[] = intval($value);
		}

		return $new_ar;
	}

	/**
	 * pagination(@param) - Pagination method
	 *
	 * @param - Num result on the page
	 * @param - Default page (/go/statics/YOUR_PAGE)
	 * @param - SQL String
	 * @param - Out db param
	 *
	 * @return - String buffer
	 *
	*/
	public function pagination($res=10, $page='', $sql='', $db=false){
		ob_start();

		$db = ($db===false) ? $this->db : $db;

		if(isset($_GET['pid'])){$pid = intval($_GET['pid']);}else{$pid = 1;}
		$start	= $pid * $res - $res; if($page===0 || $sql===0){ return $start; }
		$query	= $db->MQ($sql);
		$ar		= $db->MFA($query);
		$max	= intval(ceil($ar[0] / $res));
		if($pid<=0 || $pid>$max){ return ob_get_clean(); }
		if($max>1)
		{
			$FirstPge='<li><a href="'.STC_URL.$page.'1"><<</a></li>';
			if($pid-2>0){$Prev2Pge	='<li><a href="'.STC_URL.$page.($pid-2).'">'.($pid-2).'</a></li>';}else{$Prev2Pge ='';}
			if($pid-1>0){$PrevPge	='<li><a href="'.STC_URL.$page.($pid-1).'">'.($pid-1).'</a></li>';}else{$PrevPge ='';}
			$SelectPge = '<li><a href="'.STC_URL.$page.$pid.'"><b>'.$pid.'</b></a></li>';
			if($pid+1<=$max){$NextPge	='<li><a href="'.STC_URL.$page.($pid+1).'">'.($pid+1).'</a></li>';}else{$NextPge ='';}
			if($pid+2<=$max){$Next2Pge	='<li><a href="'.STC_URL.$page.($pid+2).'">'.($pid+2).'</a></li>';}else{$Next2Pge ='';}
			$LastPge='<li><a href="'.STC_URL.$page.$max.'">>></a></li>';
			include(STC_STYLE."pagination.html");
		}

		return ob_get_clean();
	}

	/**
	 * get_config() - Get config from webmcr
	 *
	 * @return - Array
	 *
	*/
	public function get_config(){
		include(MCR_ROOT.'config.php');

		return array(
			'config' => $config,
			'bd_names' => $bd_names,
			'bd_users' => $bd_users,
			'bd_money' => $bd_money,
			'site_ways' => $site_ways);
	}
	
	public function save($cfg){

		$txt  = '<?php'.PHP_EOL;
		$txt .= '$cfg = '.var_export($cfg, true).';'.PHP_EOL;
		$txt .= '?>';

		$result = file_put_contents(MCR_ROOT."configs/statics.cfg.php", $txt);

		if (is_bool($result) and $result == false){return false;}

		return true;
	}

	public function get_bc($title, $url='', $div=false, $active=false, $link=true){
		$divider	= ($div) ? '<span class="divider">/</span>' : '';
		$class		= ($active) ? 'class="active"' : '';

		$start_link	= ($link) ? '<a href="'.STC_URL.$url.'">' : '';
		$end_link	= ($link) ? '</a>' : '';

		return '<li '.$class.'>'.$start_link.$title.$end_link.' '.$divider.'</li>';
	}

	public function sp($page, $data=false){
		ob_start();

		include(STC_STYLE.$page.'.html');

		return ob_get_clean();
	}

	public function get_global($breadcrumbs, $content){
		ob_start();

		include_once(STC_STYLE.'global.html');

		return ob_get_clean();
	}
}

/**
 * Static pages for WebMCR
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014 Qexy.org
 *
 * @version 1.0
 *
 */
?>