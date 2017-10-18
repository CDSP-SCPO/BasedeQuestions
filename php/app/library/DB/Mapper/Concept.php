<?php

/**
 * @package DB
 * @subpackage Mapper
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Mapper_Concept {
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
			$this->setDbTable('DB_Table_Concept');
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
	 * @param DB_Model_Concept $concept
	 * @return int
	 */
	public function save(DB_Model_Concept $concept)
	{

		$data = array(
			'id' => $concept->get_id(),// ($tlid = $ddifile->get_translation_language_id()) !== '' ? $tlid : NULL,
			'concept_list_id' => ($cid = $concept->get_concept_list_id()) !== '' ? $cid : NULL,
			'title_translation_id' => $concept->get_title_translation_id(),
			'position' => $concept->get_position(),
			'created' => $concept->get_created(),
			'modified' => $concept->get_modified()
		);
		
		if (null === ($id = $concept->get_id()))
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
	 * @return DB_Model_Concept
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
		
        $concepts = array();
        
        for ($i = 0; $i < $l; $i++)
        {
        	$concepts[] = $this->_hydrate($results[$i]);
        }
        
        return $concepts;
        
	}

	/**
	 * @param Zend_Db_Table_Row $row
	 * @return DB_Model_Concept
	 */
	protected function _hydrate($row)
	{
		$concept = new DB_Model_Concept;
		$concept->set_id($row->id);
		$concept->set_concept_list_id($row->concept_list_id);
		$concept->set_title_translation_id($row->title_translation_id);
		$concept->set_position($row->position);
		$concept->set_created($row->created);
		$concept->set_modified($row->modified);
		return $concept;
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
		$query = 'DELETE FROM concepts WHERE id IN (';
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
	 * @param int $translationLanguageId
	 * @return array
	 */
	public function findAllWithDetails($translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
SELECT 
	concepts.id AS id,
	title_translation.translated_text AS title,
	concept_list_titles.translated_text AS concept_list_title,
	concepts.created AS created,
	concepts.modified AS modified
	
FROM
	concepts
	LEFT JOIN
	translation_entries AS title_translation
	ON
	title_translation.translation_id = concepts.title_translation_id
	AND
	title_translation.translation_language_id = $translationLanguageId
	LEFT JOIN
	concept_lists AS concept_list
	ON
	concepts.concept_list_id = concept_list.id
	LEFT JOIN
	translation_entries AS concept_list_titles
	ON
	concept_list_titles.translation_id = concept_list.title_translation_id
	AND
	concept_list_titles.translation_language_id = $translationLanguageId

HEREDOC;
		return $adapter->query($query)->fetchAll();
	}

	/**
	 * @param int $id
	 * @param int $translationLanguageId
	 * @return array
	 */
	public function findTitleTranslation($id, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$id = $adapter->quote($id);
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
SELECT 
	concepts.id AS id,
	title_translation.translated_text AS title,
	concept_list_title_translation.translated_text AS concept_list_title,
	concept_list.id AS concept_list_id,
	concepts.created AS created,
	concepts.modified AS modified
	
FROM
	concepts
INNER JOIN
	translation_entries AS title_translation
ON
	title_translation.translation_id = concepts.title_translation_id
AND
	title_translation.translation_language_id = $translationLanguageId
INNER JOIN
	concept_lists AS concept_list
ON
	concepts.concept_list_id = concept_list.id
INNER JOIN
	translation_entries AS concept_list_title_translation
ON
	concept_list_title_translation.translation_id = concept_list.title_translation_id
AND
	concept_list_title_translation.translation_language_id = $translationLanguageId
WHERE
	concepts.id = $id
HEREDOC;
		
		$result = $adapter->query($query)->fetchAll();
		return isset($result[0]) ? $result[0] : NULL;
	}

	public function findAllForConceptList($conceptListId, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$conceptListId = $adapter->quote($conceptListId);
		$query = <<<HEREDOC
SELECT
	concepts.id AS concept_id,
	title_translation.translated_text AS concept_title,
	concepts.position AS concept_position,
	COUNT(concept_id) AS concept_count

FROM
	concepts
	LEFT JOIN
	translation_entries AS title_translation
	ON
	concepts.title_translation_id = title_translation.translation_id
	AND
	title_translation.translation_language_id = $translationLanguageId
	LEFT JOIN
	variables
	ON
	variables.concept_id = concepts.id

WHERE
	concepts.concept_list_id = $conceptListId
	
GROUP BY
	concepts.id
	
HEREDOC;

		$result = $adapter->query($query)->fetchAll();
		return isset($result[0]) ? $result : NULL;
	}
	
	/**
	 * @param int $conceptId
	 * @param int $translationLanguageId
	 * @return array
	 */
	public function findAllTranslations($conceptId, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$conceptId = $adapter->quote($conceptId);
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
SELECT 
	concepts.id AS id,
	concepts.title_translation_id AS title_translation_id,
	title_translation.translated_text AS title,
	title_translation.id AS title_translation_entry_id,
	translation_language.id AS lang_id,
	translation_language.code AS lang_code,
	translation_language_label_translation.translated_text AS lang_label,
	concepts.created AS created,
	concepts.modified AS modified
	
FROM
	concepts
	INNER JOIN
	translation_entries AS title_translation
	ON
	title_translation.translation_id = concepts.title_translation_id
	INNER JOIN
	translation_languages AS translation_language
	ON
	translation_language.id = title_translation.translation_language_id
	INNER JOIN
	translation_entries AS translation_language_label_translation
	ON
	translation_language_label_translation.translation_id = translation_language.label_translation_id
	AND
	translation_language_label_translation.translation_language_id = $translationLanguageId

WHERE
	concepts.id = $conceptId
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}

	/**
	 * @param array $ids
	 * @param int $translationLanguageId
	 */
	public function findAllTitleAndId($ids, $translationLanguageId)
	{
		
		if (empty($ids))
		{
			return array();
		}
		
		$adapter = $this->getDBTable()->getAdapter();
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$l = count($ids);
		$_ids = array();
		
		for ($i = 0; $i < $l; $i++)
		{
			$_ids[] = $adapter->quote($ids[$i]);
		}
		
		$_ids = implode(', ', $_ids);
		
		$query = <<<HEREDOC
	SELECT 
		concepts.id,
		translation_entries.translated_text AS title
		
	FROM 
		concepts
		
	INNER JOIN
		translation_entries
	ON
		translation_entries.translation_id = concepts.title_translation_id
	AND
		translation_entries.translation_language_id = $translationLanguageId
	AND
		concepts.id IN ($_ids)
	ORDER BY id ASC
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}

}
