<?php

/**
 * @package DB
 * @subpackage Mapper
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Mapper_ConceptList {
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
			$this->setDbTable('DB_Table_ConceptList');
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
	 * @param DB_Model_ConceptList $cl
	 * @return int
	 */
	public function save(DB_Model_ConceptList $cl)
	{

		$data = array(
			'id' => $cl->get_id(),
			'title_translation_id' => $cl->get_title_translation_id(),
			'description_translation_id' => $cl->get_description_translation_id(),
		);
		
		if (null === ($id = $cl->get_id()))
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
	 * @return DB_Model_ConceptList
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
		
        $cls = array();
        
        for ($i = 0; $i < $l; $i++)
        {
        	$cls[] = $this->_hydrate($results[$i]);
        }
        
        return $cls;
        
	}

	/**
	 * @param Zend_Db_Table_Row $row
	 * @return DB_Model_ConceptList
	 */
	protected function _hydrate($row)
	{
		$cl = new DB_Model_ConceptList;
		$cl->set_id($row->id);
		$cl->set_title_translation_id($row->title_translation_id);
		$cl->set_description_translation_id($row->title_translation_id);
		$cl->set_created($row->created);
		$cl->set_modified($row->modified);
		return $cl;
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
		$query = 'DELETE FROM concept_lists WHERE id IN (';
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
	concept_lists.id AS id,
	title_translation.translated_text AS title,
	ddi_files.id AS ddi_file_id,
	COUNT(DISTINCT concepts.id) AS concept_count,
	COUNT(DISTINCT ddi_files.id) AS ddi_file_count,
	COUNT(DISTINCT domains.id) AS domain_count,
	concept_lists.created AS created,
	concept_lists.modified AS modified
	
FROM
	concept_lists
	
	LEFT JOIN
	translation_entries AS title_translation
	ON
	title_translation.translation_id = concept_lists.title_translation_id
	AND
	title_translation.translation_language_id = $translationLanguageId
	
	LEFT JOIN
	concepts
	ON
	concepts.concept_list_id = concept_lists.id

	LEFT JOIN
	ddi_files
	ON
	ddi_files.concept_list_id = concept_lists.id

	LEFT JOIN
	domains
	ON
	domains.concept_list_id = concept_lists.id


GROUP BY
	id
	
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}

	/**
	 * @param int $clId
	 * @param int $translationLanguageId
	 * @return array
	 */
	public function findTitleTranslation($clId, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$clId = $adapter->quote($clId);
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
SELECT 
	concept_lists.id AS id,
	title_translation.translated_text AS title,
	concept_lists.created AS created,
	concept_lists.modified AS modified
	
FROM
	concept_lists
	
	LEFT JOIN
	translation_entries AS title_translation
	ON
	title_translation.translation_id = concept_lists.title_translation_id
	AND
	title_translation.translation_language_id = $translationLanguageId
	
	LEFT JOIN
	translation_entries AS description_translation
	ON
	description_translation.translation_id = concept_lists.description_translation_id
	AND
	description_translation.translation_language_id = $translationLanguageId
	
WHERE
	concept_lists.id = $clId
HEREDOC;
		$result = $adapter->query($query)->fetchAll();
		return isset($result[0]) ? $result[0] : NULL;
	}

	/**
	 * @param int $conceptListId
	 * @param int $translationLanguageId
	 * @return array
	 */
	public function findAllTranslations($conceptListId, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$conceptListId = $adapter->quote($conceptListId);
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
SELECT
	concept_lists.id AS id,
	concept_lists.title_translation_id AS title_translation_id,
	concept_lists.description_translation_id AS description_translation_id,
	title_translation.translated_text AS title,
	title_translation.id AS title_translation_entry_id,
	description_translation.translated_text AS description,
	description_translation.id AS description_translation_entry_id,
	translation_language.id AS lang_id,
	translation_language.code AS lang_code,
	translation_language_label_translation.translated_text AS lang_label,
	concept_lists.created AS created,
	concept_lists.modified AS modified
	
FROM
	concept_lists
	
	INNER JOIN
	translation_entries AS title_translation
	ON
	title_translation.translation_id = concept_lists.title_translation_id
		
	INNER JOIN
	translation_entries AS description_translation
	ON
	description_translation.translation_id = concept_lists.description_translation_id
		
	INNER JOIN
	translation_languages AS translation_language
	ON
	translation_language.id = title_translation.translation_language_id
	AND
	translation_language.id = description_translation.translation_language_id
	
	INNER JOIN
	translation_entries AS translation_language_label_translation
	ON
	translation_language.label_translation_id = translation_language_label_translation.translation_id
	AND
	translation_language_label_translation.translation_language_id = $translationLanguageId
	
WHERE
	concept_lists.id = $conceptListId
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}

	/**
	 * @param int $conceptId
	 * @param int $translationLanguageId
	 * @return array
	 */
	public function findTitleTranslationForConcept($conceptId, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$conceptId = $adapter->quote($conceptId);
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
SELECT
	translation_entries.translated_text AS title
FROM 
	translation_entries
INNER JOIN
	concept_lists
	ON
	concept_lists.title_translation_id = translation_entries.translation_id
INNER JOIN
	concepts
	ON
	concepts.concept_list_id = concept_lists.id
WHERE
	concepts.id = $conceptId
	AND
	translation_entries.translation_language_id = $translationLanguageId
HEREDOC;
		return  ($result = $adapter->query($query)->fetchAll()) !== array() ? $result[0] : NULL;
	}
	
	/**
	 * @param int $conceptListId
	 * @param int $translationLanguageId
	 * @return array
	 */
	public function findWithDetails($conceptListId, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$conceptListId = $adapter->quote($conceptListId);
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
SELECT 
	title_translation.translated_text AS title,
	description_translation.translated_text AS description
	
FROM
	concept_lists
	
	LEFT JOIN
	translation_entries AS title_translation
	ON
	title_translation.translation_id = concept_lists.title_translation_id
	AND
	title_translation.translation_language_id = $translationLanguageId
		
	LEFT JOIN
	translation_entries AS description_translation
	ON
	description_translation.translation_id = concept_lists.description_translation_id
	AND
	description_translation.translation_language_id = $translationLanguageId
		
	LEFT JOIN
	translation_languages AS translation_language
	ON
	translation_language.id = title_translation.translation_language_id
	
WHERE
	concept_lists.id = $conceptListId
HEREDOC;
		return  ($result = $adapter->query($query)->fetchAll()) !== array() ? $result[0] : NULL;
	}
	
}
