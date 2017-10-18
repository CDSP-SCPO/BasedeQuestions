<?php

/**
 * @package DB
 * @subpackage Mapper
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Mapper_Category {
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
			$this->setDbTable('DB_Table_Category');
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
	 * @param DB_Model_Category $category
	 * @return int
	 */
	public function save(DB_Model_Category $category)
	{

		$data = array(
			'id' => $category->get_id(),
			'variable_id' => $category->get_variable_id(),
			'missing' => $category->get_missing(),
			'number' => $category->get_number(),
			'label' => $category->get_label(),
			'stats' => $category->get_stats(),
			'type' => $category->get_type(),
			'value' => $category->get_value(),
		);
		
		if (null === ($id = $category->get_id()))
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
	 * @return DB_Model_Category
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
	 * @return DB_Model_Category
	 */
	protected function _hydrate($row)
	{
		$category = new DB_Model_Category;
		$category->set_id($row->id);
		$category->set_missing($row->missing);
		$category->set_variable_id($row->variable_id);
		$category->set_number($row->number);
		$category->set_label($row->label);
		$category->set_stats($row->stats);
		$category->set_type($row->type);
		$category->set_value($row->value);
		return $category;
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
		$query = 'DELETE FROM categories WHERE id IN (';
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
	 * @param DB_Model_Variable $variable
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
