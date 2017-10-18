<?php

/**
 * @package DB
 * @subpackage Mapper
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Mapper_Variable {
	
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
			$this->setDbTable('DB_Table_Variable');
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
	 * @param array|DB_Model_Variable $variable An array of DB_Model_Variable objects or a single one
	 * @return int
	 */
	public function save($variable)
	{
		
		if ($variable instanceOf DB_Model_Variable)
		{
			return $this->_saveOne($variable);
		}
		
		elseif (is_array($variable))
		{
			return $this->_saveMany($variable);
		}
		
		else
		{
			throw new Exception('Unexpected type.');
		}
		
	}
	
	/**
	 * @param DB_Model_Variable $variable
	 * @return int
	 */
	protected function _saveOne(DB_Model_Variable $variable)
	{

		$data = array(
			'id' => $variable->get_id(),
			'ddi_file_id' => $variable->get_ddi_file_id(),
			'concept_id' => $variable->get_concept_id(),
			'variable_group_id' => $variable->get_variable_group_id(),
			'nesstar_id' => $variable->get_nesstar_id(),
			'name' => $variable->get_name(),
			'label' => $variable->get_label(),
			'notes' => $variable->get_notes(),
			'valid' => $variable->get_valid(),
			'invalid' => $variable->get_invalid(),
			'universe' => $variable->get_universe(),
		);
		
		if (null === ($id = $variable->get_id()))
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
	 * @param array $variables An array of DB_Model_Variable objects
	 * @return int
	 */
	protected function _saveMany(array $variables)
	{
		
		$adapter = $this->getDbTable()->getAdapter();
		$sql = 'INSERT INTO variables';
		$sql .= ' (ddi_file_id, concept_id, variable_group_id, nesstar_id, name, label, valid, invalid)'; 
		$sql .= ' VALUES ';
		$l = count($variables);
		
		for ($i = 0; $i < $l; $i++)
		{
			$sql .= '(';			
			$sql .= ($vid = $variables[$i]->get_ddi_file_id()) ? $adapter->quote($vid) : 'NULL' . ', '; 
			$sql .= ($cid = $variables[$i]->get_concept_id()) ? $adapter->quote($cid) : 'NULL' . ', ';
			$sql .= ($gid = $variables[$i]->get_variable_group_id()) ? $adapter->quote($gid) : 'NULL' . ', ';
			$sql .= ($nid = $variables[$i]->get_nesstar_id()) ? $adapter->quote($nid) : 'NULL' . ', ';
			$sql .= $adapter->quote($variables[$i]->get_name()) . ', ';
			$sql .= $adapter->quote($variables[$i]->get_label()) . ', ';
			$sql .= $adapter->quote($variables[$i]->get_valid()) . ', ';
			$sql .= $adapter->quote($variables[$i]->get_invalid());
			$sql .= ')';
			
			if ($i < $l - 1)
			{
				$sql .= ', ';	
			}
				
		}
		
		return $adapter->query($sql);
	}

	/**
	 * @param int|array $id An array of ints or a single one
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
	 * @return DB_Model_Variable
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
	 * @param array ids An array of int
	 * @return array
	 */
	protected function _findByIds($ids)
	{
		$results = $this->getDbTable()->find($ids);

		if (0 == ($l = count($results)))
        {
            return;
        }
		
        $variables = array();
        
        for ($i = 0; $i < $l; $i++)
        {
        	$variables[] = $this->_hydrate($results[$i]);
        }
        
        return $variables;
        
	}

	/**
	 * @param Zend_Db_Table_Row $row
	 * @return DB_Model_Variable
	 */
	protected function _hydrate($row)
	{
		$variable = new DB_Model_Variable;
		$variable->set_id($row->id);
		$variable->set_ddi_file_id($row->ddi_file_id);
		$variable->set_concept_id($row->concept_id);
		$variable->set_variable_group_id($row->variable_group_id);
		$variable->set_nesstar_id($row->nesstar_id);
		$variable->set_name($row->name);
		$variable->set_label($row->label);
		$variable->set_notes($row->notes);
		$variable->set_valid($row->valid);
		$variable->set_invalid($row->invalid);
		$variable->set_universe($row->universe);
		return $variable;
	}
	
	
	/**
	 * @param array|int $id An array of int or a single one
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
	 * @param array ids An array of int
	 * @return int
	 */
	protected function _deleteByIds($ids)
	{
		$table = $this->getDbTable();
		$query = 'DELETE FROM variables WHERE id IN (';
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
	variables.id AS variable_id,
	variables.nesstar_id AS variable_nesstar_id,
	variables.variable_group_id AS variable_group_id,
	variables.name AS variable_name,
	variables.label AS variable_label,
	variables.universe AS variable_universe,
	variables.notes AS variable_notes,
	concepts.id AS concept_id,
	concepts.position AS concept_position,
	questions.id AS question_id,
	questions.litteral AS question_litteral,
	questions.item AS question_item,
	questions.pre_question_text AS question_pre_question_text,
	questions.post_question_text AS question_post_question_text,
	questions.interviewer_instructions AS question_interviewer_instructions

FROM
	variables
LEFT JOIN
	concepts
ON
	concepts.id = variables.concept_id
LEFT JOIN
	questions
ON
	questions.variable_id = variables.id
	
WHERE
	variables.ddi_file_id = $ddiFileId
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
	
	public function getCount()
	{
		$adapter = $this->getDbTable()->getAdapter();
		$query = <<<HEREDOC
SELECT
	COUNT(*) AS count
FROM
	variables
HEREDOC;
		$result = $adapter->query($query)->fetchAll();
		return $result[0];
	}
	
}
