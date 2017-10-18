<?php

/**
 * @package DB
 * @subpackage Mapper
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Mapper_Question {
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
			$this->setDbTable('DB_Table_Question');
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
	 * @param DB_Model_Question $question
	 * @return int
	 */
	public function save(DB_Model_Question $question)
	{

		$data = array(
			'id' => $question->get_id(),
			'variable_id' => $question->get_variable_id(),
			'solr_id' => $question->get_solr_id(),
			'litteral' => $question->get_litteral(),
			'item' => $question->get_item(),
			'pre_question_text' => $question->get_pre_question_text(),
			'post_question_text' => $question->get_post_question_text(),
			'interviewer_instructions' => $question->get_interviewer_instructions(),
		);
		
		if (null === ($id = $question->get_id()))
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
	 * @return DB_Model_Question
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
	 * @return DB_Model_Question
	 */
	protected function _hydrate($row)
	{
		$question = new DB_Model_Question;
		$question->set_id($row->id);
		$question->set_variable_id($row->variable_id);
		$question->set_solr_id($row->solr_id);
		$question->set_litteral($row->litteral);
		$question->set_item($row->item);
		$question->set_pre_question_text($row->pre_question_text);
		$question->set_post_question_text($row->post_question_text);
		$question->set_interviewer_instructions($row->interviewer_instructions);
		return $question;
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
		$query = 'DELETE FROM questions WHERE id IN (';
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

		return $this->getTable()->getAdapter()->query($query)->rowCount();
	}

	/**
	 * @param int $variableId
	 * @return array
	 */
	public function findForVariable($variableId)
	{
		$table = $this->getDbTable();
		$select = $table->select();
		$select->where('variable_id = ?', $variableId);
		return $table->fetchAll($select);
	}	
}