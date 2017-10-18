<?php

/**
 * @package DB
 * @subpackage Mapper
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Mapper_User {
	/**
	 * @var Zend_Db_Table_Abstract
	 */
	protected $_dbTable;

	/**
	 * @return Zend_Db_Table_Abstract
	 */
	public function getDbTable()
	{

		if (null === $this->_dbTable)
		{
			$this->setDbTable('DB_Table_User');
		}

		return $this->_dbTable;
	}

	/**
	 * @param mixed $dbTable
	 * @return void
	 */
	public function setDbTable($dbTable)
	{
		
		if (is_string($dbTable))
		{
			$dbTable = new $dbTable();
			
        }

		if ( ! $dbTable instanceof Zend_Db_Table_Abstract) 
        {
			throw new Exception('Invalid table data gateway provided');
			}

			$this->_dbTable = $dbTable;
			return $this;
	}

	/**
	 * @param DB_Model_User $user
	 * @return int
	 */
	public function save(DB_Model_User $user)
	{
		
		$data = array(
			'id' => $user->get_id(),
			'user_name' => $user->get_user_name(),
			'password' => md5($user->get_password() . DB_Model_User::$salt),
			'real_name' => $user->get_real_name(),
		);

		if (null === ($id = $user->get_id()))
		{
			unset($data['id']);
			return $this->getDbTable()->insert($data);
		}

		else
		{
			return $this->getDbTable()->update($data, array('id = ?' => $id));
		}

	}

	/**
	 * @param mixed $id
	 * @return mixed
	 */
	public function find($id)
	{

		if (is_numeric($id))
		{
			return $this->_findById($id);
		}

		if (is_array($id))
		{
			return $this->_findByIds($id);
		}

	}

	/**
	 * @param int id
	 * @return DB_Model_User
	 */
	protected function _findById($id)
	{
		$result = $this->getDbTable()->find($id);

		if (0 == count($result))
        {
            return;
        }

        return $this->_hydrate($result->current());
        
	}

	/**
	 * @param array ids
	 * @return array
	 */
	protected function _findByIds($ids)
	{
		$results = $this->getDbTable()->find($ids);

		if (0 == ($l = count($results)))
        {
            return;
        }
		
        $users = array();
        
        for ($i = 0; $i < $l; $i++)
        {
        	$users[] = $this->_hydrate($results[$i]);
        }
        
        return $users;
        
	}

	/**
	 * @param Zend_Db_Table_Row $row
	 * @return DB_Model_User
	 */
	protected function _hydrate($row)
	{
		$user = new DB_Model_User;
		$user->set_id($row->id);
		$user->set_user_name($row->user_name);
		$user->set_password($row->password);
		$user->set_real_name($row->real_name);
		return $user;
	}
	
	
	/**
	 * @param mixed $id
	 * @return int
	 */
	public function delete($id)
	{

		if (is_numeric($id))
		{
			return $this->_deleteById($id);
		}

		if (is_array($id))
		{
			return $this->_deleteByIds($id);
		}
	}
	
	/**
	 * @param int id
	 * @return int
	 */
	protected function _deleteById($id)
	{
		$table = $this->getDbTable();
		$where = $table->getAdapter()->quoteInto('id = ?', $id);
		return $table->delete($where);
	}
	
	/**
	 * @param array ids
	 * @return int
	 */
	protected function _deleteByIds($ids)
	{
		$table = $this->getDbTable();
		$query = 'DELETE FROM users WHERE id IN (';
		$l = count($ids);
		
		for ($i = 0; $i < $l; $i++)
		{
			$query .= $ids[$i];

			if ($i < $l -1)
			{
				$query .= ', ';
			}

		}
		
		$query .= ')';

		return $table->getAdapter()->query($query)->rowCount();
	}
	
	/**
	 * @param string $login
	 * @return int
	 */
	public function loginExists($login)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$adapter->quote($login);
		$query = <<<HEREDOC
		SELECT 
		id
			FROM users
		WHERE
			user_name = "$login";
HEREDOC;
		return $adapter->query($query)->rowCount() > 0;
	}
	
	/**
	 * @return array
	 */
	public function findAll()
	{
		$query = "SELECT * FROM users";
		return $this->getDbTable()->getAdapter()->query($query)->fetchAll();
		
	}
	
	public function deleteByLogin($login)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$adapter->quote($login);
		$query = "DELETE FROM users WHERE user_name =\"$login\"";
		return $adapter->query($query)->rowCount();
	}
	
	public function findByLogin($login)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$adapter->quote($login);
		$query = "SELECT * FROM users WHERE user_name =\"$login\"";
		$res = $this->getDbTable()->getAdapter()->query($query)->fetchAll();
		
		if (count($res) == 0)
		{
			return NULL;
		}
		
		else
		{
			$user = new DB_Model_User;
			$user->set_id($res[0]['id']);
			$user->set_real_name($res[0]['real_name']);
			$user->set_user_name($res[0]['user_name']);
			return $user;
		}
	}

}