<?php

/**
 * @package DB
 * @subpackage Mapper
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Mapper_StudySerie {
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
			$this->setDbTable('DB_Table_StudySerie');
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
	 * @param DB_Model_StudySerie $serie
	 * @return int
	 */
	public function save(DB_Model_StudySerie $serie)
	{

		$data = array(
			'id' => $serie->get_id(),
			'title_translation_id' => $serie->get_title_translation_id(),
			'description_translation_id' => $serie->get_description_translation_id(),
		);
		
		if (null === ($id = $serie->get_id()))
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
	 * @return DB_Model_StudySerie
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
	 * @return DB_Model_StudySerie
	 */
	protected function _hydrate($row)
	{
		$serie = new DB_Model_StudySerie;
		$serie->set_id($row->id);
		$serie->set_title_translation_id($row->title_translation_id);
		$serie->set_description_translation_id($row->description_translation_id);
		$serie->set_created($row->created);
		$serie->set_modified($row->modified);
		return $serie;
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
		$mapper = new DB_Mapper_StudySerie;
		$serie = $mapper->find($id); 
		$tids = array();
		$tids[] = $serie->get_title_translation_id();
		$tids[] = $serie->get_description_translation_id();

		$table = $this->getDbTable();
		$where = $table->getAdapter()->quoteInto('id = ?', $id);
		
		if ($n = $table->delete($where))
		{
			$mapper = new DB_Mapper_Translation;
			$mapper->delete($tids);
		}
		
		return $n;
	}
	
	/**
	 * @param array ids
	 * @return int
	 */
	protected function _deleteByIds($ids)
	{
		$table = $this->getDbTable();
		$query = 'DELETE FROM domains WHERE id IN (';
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
		
        $series = array();
        
        for ($i = 0; $i < $l; $i++)
        {
        	$series[] = $this->_hydrate($results[$i]);
        }
        
        return $series;
	}

	/**
	 * Used to list study series in the admin or to create select options
	 * 
	 * @param int $translationLanguageId
	 * @return array an associative array with id, title, created, modified
	 */
	public function findAllWithDetails($translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
SELECT 
	study_series.id AS id,
	title_translation.translated_text AS title,
	description_translation.translated_text AS description,
	study_series.created AS created,
	study_series.modified AS modified,
	COUNT(ddi_files.id) AS ddi_file_count
	
FROM
	study_series
INNER JOIN
	translation_entries AS title_translation
ON
	title_translation.translation_id = study_series.title_translation_id
INNER JOIN
	translation_entries AS description_translation
ON
	description_translation.translation_id = study_series.description_translation_id
LEFT JOIN
	ddi_files
ON
	study_series.id = ddi_files.study_serie_id
	
WHERE
	title_translation.translation_language_id = $translationLanguageId
AND
	description_translation.translation_language_id = $translationLanguageId

GROUP BY
	study_series.id

ORDER BY
	ddi_file_count DESC, title ASC
		
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
	
	/**
	 * 
	 * @param int $studySerieId
	 * @param int $translationLanguageId
	 * @return array an associative array with id, title, created, modified
	 */
	public function findWithDetails($studySerieId, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$studySerieId = $adapter->quote($studySerieId);
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
SELECT 
	study_series.id AS id,
	title_translation.translated_text AS title,
	description_translation.translated_text AS description,
	study_series.created AS created,
	study_series.modified AS modified
	
FROM
	study_series
INNER JOIN
	translation_entries AS title_translation
ON
	title_translation.translation_id = study_series.title_translation_id
AND
	title_translation.translation_language_id = $translationLanguageId
INNER JOIN
	translation_entries AS description_translation
ON
	description_translation.translation_id = study_series.description_translation_id
AND 
	description_translation.translation_language_id = $translationLanguageId
	
WHERE
	study_series.id = $studySerieId;

HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
	
	/**
	 * @param int $serieId
	 * @param int $translationLanguageId
	 * @return array
	 */
	public function findTitleTranslation($serieId, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$serieId = $adapter->quote($serieId);
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
SELECT 
	study_series.id AS id,
	title_translation.translated_text AS title,
	study_series.created AS created,
	study_series.modified AS modified
	
FROM
	study_series
INNER JOIN
	translation_entries AS title_translation
ON
	title_translation.translation_id = study_series.title_translation_id
INNER JOIN
	translation_entries AS description_translation
ON
	description_translation.translation_id = study_series.description_translation_id
	
WHERE
	title_translation.translation_language_id = $translationLanguageId
AND
	description_translation.translation_language_id = $translationLanguageId
AND 
	study_series.id = $serieId
HEREDOC;
		$result = $adapter->query($query)->fetchAll();
		return isset($result[0]) ? $result[0] : NULL;
	}
	
	/**
	 * @param int $serieId
	 * @return array
	 */
	public function findAllTranslations($serieId, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$serieId = $adapter->quote($serieId);
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
SELECT 
	study_series.id AS id,
	study_series.title_translation_id AS title_translation_id,
	study_series.description_translation_id AS description_translation_id,
	title_translation.translated_text AS title,
	title_translation.id AS title_translation_entry_id,
	description_translation.translated_text AS description,
	description_translation.id AS description_translation_entry_id,
	translation_language.id AS lang_id,
	translation_language.code AS lang_code,
	translation_language_label_translation.translated_text AS lang_label,
	study_series.created AS created,
	study_series.modified AS modified
	
FROM
	study_series
	
	INNER JOIN
	translation_entries AS title_translation
	ON
	title_translation.translation_id = study_series.title_translation_id
	
	INNER JOIN
	translation_entries AS description_translation
	ON
	description_translation.translation_id = study_series.description_translation_id
	
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
	study_series.id = $serieId
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}

	/**
	 * @param int $domainId
	 * @param int $translationLanguageId
	 * @return array
	 */
	public function findTitleTranslationsForDdifile($ddiFileId, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$ddiFileId = $adapter->quote($ddiFileId);
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
SELECT 
	study_series.id AS id,
	title_translation.translated_text AS title,
	study_series.created AS created,
	study_series.modified AS modified
	
FROM
	study_series
	INNER JOIN
		translation_entries AS title_translation
	ON
		title_translation.translation_id = study_series.title_translation_id
	INNER JOIN
		translation_entries AS description_translation
	ON
		description_translation.translation_id = study_series.description_translation_id
	INNER JOIN
		ddi_files
	ON
		ddi_files.ddi_file_id = $ddiFileId
	AND
		ddi_files.study_serie_id = study_series.id
	
WHERE
	title_translation.translation_language_id = $translationLanguageId
AND
	description_translation.translation_language_id = $translationLanguageId

HEREDOC;
		$result = $adapter->query($query)->fetchAll();
		return isset($result) ? $result : array();
	}
	
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
		study_series.id,
		translation_entries.translated_text AS title
		
	FROM 
		study_series
		
	INNER JOIN
		translation_entries
	ON
		translation_entries.translation_id = study_series.title_translation_id
	AND
		translation_entries.translation_language_id = $translationLanguageId
	AND
		study_series.id IN ($_ids)
	ORDER BY id ASC
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
	
}
