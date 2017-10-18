<?php

/**
 * @package DB
 * @subpackage Mapper
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Mapper_FundingAgency {
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
			$this->setDbTable('DB_Table_FundingAgency');
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
	 * @param DB_Model_FundingAgency $agency
	 * @return int
	 */
	public function save(DB_Model_FundingAgency $agency)
	{

		$data = array(
			'id' => $agency->get_id(),
			'ddi_file_id' => $agency->get_ddi_file_id(),
			'title' => $agency->get_title(),
			'abbreviation' => $agency->get_abbreviation(),
		);

		if (null === ($id = $agency->get_id()))
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
	 * @return DB_Model_FundingAgency
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
		
        $agencys = array();
        
        for ($i = 0; $i < $l; $i++)
        {
        	$agencys[] = $this->_hydrate($results[$i]);
        }
        
        return $agencys;
        
	}

	/**
	 * @param Zend_Db_Table_Row $row
	 * @return DB_Model_FundingAgency
	 */
	protected function _hydrate($row)
	{
		$agency = new DB_Model_FundingAgency;
		$agency->set_id($row->id);
		$agency->set_ddi_file_id($row->ddi_file_id);
		$agency->set_title($row->title);
		$agency->set_abbreviation($row->abbreviation);
		
		return $agency;
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
		$query = 'DELETE FROM funding_agencies WHERE id IN (';
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
	 * @param int $ddiFileId
	 * @return array
	 */
	public function findForDdifile($ddiFileId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$ddiFileId = $adapter->quote($ddiFileId);
		$query = <<<HEREDOC
		SELECT
			*
		FROM
			funding_agencies
		WHERE
			ddi_file_id = $ddiFileId
HEREDOC;
		$result = $adapter->query($query)->fetchAll();
		return isset($result) ? $result : null;
	}
}
