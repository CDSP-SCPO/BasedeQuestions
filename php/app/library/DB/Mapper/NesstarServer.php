<?php

/**
 * @package DB
 * @subpackage Mapper
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Mapper_NesstarServer {
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
			$this->setDbTable('DB_Table_NesstarServer');
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
	 * @param DB_Model_NesstarServer $server
	 * @return int
	 */
	public function save(DB_Model_NesstarServer $server)
	{

		$data = array(
			'id' => $server->get_id(),
			'title' => $server->get_title(),
			'ip' => $server->get_ip(),
			'port' => $server->get_port(),
			'domain_name' => $server->get_domain_name(),
			'responsible' => $server->get_responsible(),
		);
		
		if (null === ($id = $server->get_id()))
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
	 * @return DB_Model_NesstarServer
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

        $categories = array();
        
        for ($i = 0; $i < $l; $i++)
        {
        	$categories[] = $this->_hydrate($results[$i]);
        }

        return $categories;

	}

	/**
	 * @param Zend_Db_Table_Row $row
	 * @return DB_Model_NesstarServer
	 */
	protected function _hydrate($row)
	{
		$server = new DB_Model_NesstarServer;
		$server->set_id($row->id);
		$server->set_title($row->title);
		$server->set_ip($row->ip);
		$server->set_port($row->port);
		$server->set_domain_name($row->domain_name);
		$server->set_responsible($row->responsible);
		$server->set_created($row->created);
		$server->set_modified($row->modified);
		return $server;
	}
	
	
	/**
	 * @param mixed $id
	 * @return bool
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
		$query = 'DELETE FROM nesstar_servers WHERE id IN (';
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
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public function findAll($start = 0, $limit = null)
	{
		$results = $this->getDbTable(null, null, $start, $limit)->fetchAll(
		);

		if (0 == ($l = count($results)))
        {
            return;
        }
		
        $ns = array();
        
        for ($i = 0; $i < $l; $i++)
        {
        	$ns[] = $this->_hydrate($results[$i]);
        }
        
        return $ns;
	}
	
	/**
	 * @param int $ddifileid
	 * @return unknown_type
	 */
	public function findTitleForDdifile($ddifileid)
	{
		$adapter = $table->getAdapter();
		$ddifileid = $adapter->quote($ddifileid);
		$query = <<<HEREDOC
SELECT
	nesstar_servers.title AS title
	
FROM
	nesstar_servers
	
INNER JOIN
	ddi_files
	ON
	ddi_files.nesstar_server_id = nesstar_servers.id

WHERE
	ddi_files.id = $ddifileid

HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
	
	public function findAllWithDetails()
	{
		$query = <<<HEREDOC
SELECT
	nesstar_servers.id AS id,
	nesstar_servers.title AS title,
	COUNT(ddi_files.id) AS ddi_file_count

FROM
	nesstar_servers

LEFT JOIN
	ddi_files
	ON
	ddi_files.nesstar_server_id = nesstar_servers.id

GROUP BY
	nesstar_servers.id
	
ORDER BY
	ddi_file_count DESC,
	title
HEREDOC;
		return $this->getDBTable()->getAdapter()->query($query)->fetchAll();
	}
}
