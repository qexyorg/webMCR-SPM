<?php
/**
 * Static pages for WebMCR
 *
 * MySQLi class for using database
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014 Qexy.org
 *
 * @version 1.0
 *
 */

if (!defined('QEXY')){ exit("Hacking Attempt!"); }

class statics_db{

	private $row = false;
	public $num = 0;

	public function __construct(){
		$this->db = getDB();
	}

	/**
	 * Public Method MQ(@param)
	 *
	 * @param string (Syntax SQL)
	 *
	 * @return resource or false(boolean)
	 *
	 */
	public function MQ($query, $num=0){

		$_SESSION['stc_count_mq']++;
		$this->num = $num;

		$this->row[$this->num] = $this->db->query($query);
		
		return $this->row[$this->num];
	}


	/**
	 * Public Method MFA(@param)
	 *
	 * @param resource
	 *
	 * @return array or false(boolean)
	 *
	 */
	public function MFA($query){
		return mysql_fetch_array($this->row[$this->num]->getResult());
	}


	/**
	 * Public Method MFAS(@param)
	 *
	 * @param resource
	 *
	 * @return array or false(boolean)
	 *
	 */
	public function MFAS($query){
		return mysql_fetch_assoc($this->row[$this->num]->getResult());//mysqli_fetch_assoc($query);
	}


	/**
	 * Public Method MNR(@param)
	 *
	 * @param resource
	 *
	 * @return integer or false(boolean)
	 *
	 */
	public function MNR($query=false){
		return $this->row[$this->num]->rowCount();
	}


	/**
	 * Public Method MAR()
	 *
	 * @return integer
	 *
	 */
	public function MAR(){
		return mysql_affected_rows();
	}


	/**
	 * Public Method MRES(@param)
	 *
	 * @param string
	 *
	 * @return string or false(boolean)
	 *
	 */
	public function MRES($query){

		return mysql_real_escape_string($query);
	}


	/**
	 * Public Method HSC(@param)
	 *
	 * @param string
	 *
	 * @return string
	 *
	 */
	public function HSC($query){
		return htmlspecialchars($query);
	}
}

/**
 * Static pages for WebMCR
 *
 * MySQLi class for using database
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014 Qexy.org
 *
 * @version 1.0
 *
 */
?>
