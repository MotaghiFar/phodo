<?php

/**
 * PHODO
 *
 * PHODO is a PDO mananger for PHP
 * @author		Mehrdad MotaghiFar <mehrdad@motaghifar.ir>
 * @copyright	Copyright (c) 2014 Mehrdad MotaghiFar
 * @license		http://opensource.org/licenses/mit-license.php The MIT License
 * @version		1.0
 */

class phodo
{

	/**
	 * @var $dbh object	Database Handle
	 * @var $sth object	Statement handle
	 */
	private $dbh;
	private $sth;


	/**
	 * Connect to Database
	 *
	 * @param string $db_type Database type
	 * @param string $db_host Database host
	 * @param string $db_user Database username
	 * @param string $db_pass Database password
	 * @param string $db_name Database name
	 */
	public function __construct($db_type, $db_host, $db_user, $db_pass, $db_name)
	{
		$dsn = $db_type . ':host=' . $db_host . ';dbname=' . $db_name;
		$options = array(
			PDO::ATTR_PERSISTENT => true,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		);
		if ($db_type == 'mysql') {
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8";
		}
		try {
			$this->pdo = new PDO($dsn, $db_user, $db_pass, $options);

		} catch (PDOException $e) {
			exit($e->getMessage());
		}
	}

	/**
	 * query
	 *
	 * @param	string		$sql
	 * @param	array		$params
	 * @return	array|int
	 */
	public function query($sql, $params=array())
	{
		$sql = trim($sql);

		try {
			$this->sth = $this->pdo->prepare($sql);
			$this->bind($params);
			if($this->sth->execute() !== false) {
				if(preg_match("/^(" . implode("|", array("select", "describe", "pragma")) . ") /i", $sql))
					return $this->sth->fetchAll(PDO::FETCH_ASSOC);
				elseif(preg_match("/^(" . implode("|", array("delete", "insert", "update")) . ") /i", $sql))
					return $this->sth->rowCount();
			}
		} catch (PDOException $e) {
			exit($e->getMessage());
		}
	}

	/**
	 * select from database
	 *
	 * @param	string	$table
	 * @param	string	$where
	 * @param	array	$params
	 * @param	string	$filds
	 * @param	int		$limit
	 * @return	array
	 */
	public function select($table, $where='1', $params=array(), $filds='*', $limit='')
	{
		$sql = 'SELECT ' . $filds . ' FROM ' . $table . ' WHERE ' . $where;
		$sql .= (!empty($limit)) ? ' LIMIT ' . $limit : '' ;
		return $this->query($sql, $params);
	}

	/**
	 * update
	 *
	 * @param	string	$table
	 * @param	array	$data
	 * @param	string	$where
	 * @param	array	$params
	 * @return	int
	 */
	public function update($table, $data, $where, $params=array())
	{
		$sql = 'UPDATE ' .$table . ' SET ';

		$i = 1;
		foreach ($data as $key => $value) {
			$sql .= "" . $key . " = :" . $key . "";
			$sql .= ($i++ < count($data)) ? ', ' : '';
			$params[':' . $key] = $value;
		}

		$sql .= ' WHERE ' . $where . ';';

		return $this->query($sql, $params);
	}

	/**
	 * delete
	 *
	 * @param	string	$table
	 * @param	string	$where
	 * @param	array	$params
	 * @param	int		$limit
	 * @return	int
	 */
	public function delete($table, $where, $params=array(), $limit='')
	{
		$sql = "DELETE FROM " . $table . ' WHERE ' . $where;
		$sql .= ($limit != '') ? ' LIMIT ' . $limit : '';

		return $this->query($sql, $params);
	}

	/**
	 * delete
	 *
	 * @param	string	$table
	 * @param	string	$where
	 * @return	int
	 */
	public function insert($table, $data)
	{
		$sql = 'INSERT INTO ' . $table . ' (' . implode(array_keys($data), ', ') . ") VALUES (:" . implode(array_keys($data), ', :') . ");";

		return $this->query($sql, $data);
	}

	/**
	 * bind
	 *
	 * @param array $params
	 */
	private function bind($params = array())
	{
		foreach ($params as $key => $value) {
			$this->sth->bindValue($key, $value);
		}
	}

}
