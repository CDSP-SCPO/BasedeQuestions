<?php

/**
 * @package DB
 * @subpackage Mapper
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Mapper_Questionnaire {
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
			$this->setDbTable('DB_Table_Questionnaire');
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
	 * @param DB_Model_Questionnaire $questionnaire
	 * @return int
	 */
	public function save(DB_Model_Questionnaire $questionnaire)
	{

		$data = array(
			'id' => $questionnaire->get_id(),
			'ddi_file_id' => ($did = $questionnaire->get_ddi_file_id()) !== '' ? $did : NULL,
			'title' => $questionnaire->get_title(),
			'file_name' => $questionnaire->get_file_name(),
			'created' => $questionnaire->get_created(),
			'modified' => $questionnaire->get_modified()
		);
		
		if (null === ($id = $questionnaire->get_id()))
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
	 * @return DB_Model_Questionnaire
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
		
        $questionnaires = array();
        
        for ($i = 0; $i < $l; $i++)
        {
        	$questionnaires[] = $this->_hydrate($results[$i]);
        }
        
        return $questionnaires;
        
	}

	/**
	 * @param Zend_Db_Table_Row $row
	 * @return DB_Model_Questionnaire
	 */
	protected function _hydrate($row)
	{
		$questionnaire = new DB_Model_Questionnaire;
		$questionnaire->set_id($row->id);
		$questionnaire->set_ddi_file_id($row->ddi_file_id);
		$questionnaire->set_title($row->title);
		$questionnaire->set_file_name($row->file_name);
		$questionnaire->set_created($row->created);
		$questionnaire->set_modified($row->modified);
		return $questionnaire;
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
		$query = 'DELETE FROM questionnaires WHERE id IN (';
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
	
	public function findForDdifile($ddiFileId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$ddiFileId = $adapter->quote($ddiFileId);
		$query = <<<HEREDOC
SELECT
	questionnaires.id AS questionnaire_id,
	questionnaires.file_name AS questionnaire_file_name,
	questionnaires.title AS questionnaire_title
	
FROM
	questionnaires

WHERE
	questionnaires.ddi_file_id = $ddiFileId

HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
	
	public function deleteForDddile($ddiFileId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$ddiFileId = $adapter->quote($ddiFileId);
		$query = <<<HEREDOC
DELETE
	
FROM
	questionnaires

WHERE
	questionnaires.ddi_file_id = $ddiFileId

HEREDOC;
		return $adapter->query($query);
	}

}
