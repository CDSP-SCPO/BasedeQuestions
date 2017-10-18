<?php

/**
 * @package DB
 * @subpackage Mapper
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Mapper_StudyDescription {
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
			$this->setDbTable('DB_Table_StudyDescription');
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
	 * @param DB_Model_StudyDescription $study
	 * @return int
	 */
	public function save(DB_Model_StudyDescription $study)
	{

		$data = array(
			'id' => $study->get_id(),
			'ddi_file_id' => ($did = $study->get_ddi_file_id()) !== '' ? $did : NULL,
			'nesstar_id' => $study->get_nesstar_id(),
			'title' => $study->get_title(),
			'year' => $study->get_year(),
			'sample_procedure' => $study->get_sample_procedure(),
			'universe' => $study->get_universe(),
			'analysis_unit' => $study->get_analysis_unit(),
			'geographic_coverage' => $study->get_geographic_coverage(),
			'abstract' => $study->get_abstract(),
			'keywords' => $study->get_keywords(),
			'collect_mode' => $study->get_collect_mode(),
			'nation' => $study->get_nation(),
			'case_quantity' => $study->get_case_quantity()
		);

		if (null === ($id = $study->get_id()))
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
	 * @return DB_Model_StudyDescription
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
		
        $studies = array();
        
        for ($i = 0; $i < $l; $i++)
        {
        	$studies[] = $this->_hydrate($results[$i]);
        }
        
        return $studies;
        
	}

	/**
	 * @param Zend_Db_Table_Row $row
	 * @return DB_Model_StudyDescription
	 */
	protected function _hydrate($row)
	{
		$study = new DB_Model_StudyDescription;
		$study->set_id($row->id);
		$study->set_ddi_file_id($row->ddi_file_id);
		$study->set_title($row->title);
		$study->set_year($row->year);
		$study->set_sample_procedure($row->sample_procedure);
		$study->set_universe($row->universe);
		$study->set_analysis_unit($row->analysis_unit);
		$study->set_geographic_coverage($row->geographic_coverage);
		$study->set_abstract($row->abstract);
		$study->set_keywords($row->keywords);
		$study->set_collect_mode($row->collect_mode);
		$study->set_nesstar_id($row->nesstar_id);
		$study->set_nation($row->nation);
		$study->set_case_quantity($row->case_quantity);
		
		return $study;
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
		$query = 'DELETE FROM study_descriptions WHERE id IN (';
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
		
        $studies = array();
        
        for ($i = 0; $i < $l; $i++)
        {
        	$studies[] = $this->_hydrate($results[$i]);
        }
        
        return $studies;
	}

	public function findAllTitleAndId(array $ids)
	{
		
		if (empty($ids))
		{
			return array();
		}
		
		$adapter = $this->getDBTable()->getAdapter();
		$l = count($ids);
		$_ids = array();
		
		for ($i = 0; $i < $l; $i++)
		{
			$_ids[] = $adapter->quote($ids[$i]);
		}
		
		$_ids = implode(', ', $_ids);
		$query = <<<HEREDOC
	SELECT 
		study_descriptions.id,
		study_descriptions.title

	FROM
		study_descriptions
	WHERE
		study_descriptions.id IN ($_ids)

	ORDER BY 
		id ASC
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
	
	public function findAllWithDetails(array $ids)
	{
		
		if (empty($ids))
		{
			return array();
		}
		
		$adapter = $this->getDBTable()->getAdapter();
		$l = count($ids);
		$_ids = array();
		
		for ($i = 0; $i < $l; $i++)
		{
			$_ids[] = $adapter->quote($ids[$i]);
		}
		
		$_ids = implode(', ', $_ids);
		$query = <<<HEREDOC
	SELECT 
		study_descriptions.id AS id,
		study_descriptions.title AS study_title,
		producers.title AS producer_title,
		producers.abbreviation AS producer_abbreviation,
		distributors.title AS distributor_title,
		distributors.abbreviation AS distributor_abbreviation

	FROM
		study_descriptions

	INNER JOIN
		producers
	ON
		study_descriptions.ddi_file_id = producers.ddi_file_id

	INNER JOIN
		distributors
	ON
		study_descriptions.ddi_file_id = distributors.ddi_file_id

	WHERE
		study_descriptions.id IN ($_ids)
		
	ORDER BY
		study_descriptions.id

HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
	
	/**
	 * @param int $domainId
	 * @param int $languageId
	 * @return array
	 */
	public function findStudyTitlesForDomain($domainId, $languageId, $translationLanguageId = NULL)
	{
		$adapter = $this->getDBTable()->getAdapter();
		$domainId = $adapter->quote($domainId);
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
	SELECT 
		study_descriptions.title,
		ddi_files.id,
		ddi_files.nesstar_server_id,
		study_descriptions.nesstar_id,
		translation_entries.translated_text AS language,
		study_series.id AS study_serie_id,
		study_serie_title.translated_text AS study_serie_title
		
	FROM 
		study_descriptions
		
		INNER JOIN
			ddi_files
		ON
			ddi_files.id = study_descriptions.ddi_file_id
		AND
			ddi_files.translation_language_id = $languageId
					
		INNER JOIN
			domains_ddi_files
		ON
			domains_ddi_files.ddi_file_id = ddi_files.id
		AND
			domains_ddi_files.domain_id = $domainId
			
		LEFT JOIN
			study_series
		ON
			ddi_files.study_serie_id = study_series.id
			
		LEFT JOIN
			translation_entries AS study_serie_title
		ON
			study_serie_title.translation_id = study_series.title_translation_id
		AND study_serie_title.translation_language_id = $translationLanguageId
				
		INNER JOIN
			translation_languages 
		ON
			translation_languages.id = ddi_files.translation_language_id
			
		INNER JOIN
			translation_entries
		ON
			translation_entries.translation_id = translation_languages.label_translation_id
		AND translation_entries.translation_language_id = $translationLanguageId
		

	ORDER BY
		ddi_files.translation_language_id,
		study_descriptions.title
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
	
	/**
	 * @param int $domainId
	 * @param int $translationLanguageId
	 * @return array
	 */
	public function findStudyTitlesForStudySerie($serieId, $languageId, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$serieId = $adapter->quote($serieId);
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$languageId = $adapter->quote($languageId);
		$gcSeparator = GC_MULTIPLE_VALUE_SEPARATOR;
		$query = <<<HEREDOC
	SELECT 
		study_descriptions.title,
		ddi_files.id,
		study_descriptions.nesstar_id,
		translation_entries.translated_text AS language,
		ddi_files.nesstar_server_id,
		ddi_files.concept_list_id,
		GROUP_CONCAT(domains.id SEPARATOR '$gcSeparator') AS domain_id,
		GROUP_CONCAT(domain_title.translated_text SEPARATOR '$gcSeparator') AS domain_title
		
	FROM 
		study_descriptions
		
		INNER JOIN
			ddi_files
		ON
			ddi_files.id = study_descriptions.ddi_file_id
		AND
			ddi_files.translation_language_id = $languageId
			
		INNER JOIN
			study_series
		ON
			study_series.id = ddi_files.study_serie_id
		AND
			study_series.id = $serieId

		INNER JOIN
			translation_languages 
		ON
			translation_languages.id = ddi_files.translation_language_id
			
		INNER JOIN
			translation_entries
		ON
			translation_entries.translation_id = translation_languages.label_translation_id
		AND
			translation_entries.translation_language_id = $translationLanguageId
		
		LEFT JOIN
			domains_ddi_files
		ON
			ddi_files.id = domains_ddi_files.ddi_file_id
		
		LEFT JOIN
			domains
		ON
			domains_ddi_files.domain_id = domains.id
			
		LEFT JOIN
			translation_entries AS domain_title
		ON
			domain_title.translation_id = domains.title_translation_id
		AND
			domain_title.translation_language_id = $translationLanguageId

	GROUP BY
		ddi_files.id
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
	
	public function findStudyTitlesForNesstarserver($nesstarServerId, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$nesstarServerId = $adapter->quote($nesstarServerId);
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
	SELECT 
		study_descriptions.title
		
	FROM 
		study_descriptions
		
	INNER JOIN
		ddi_files
		ON
			ddi_files.id = study_descriptions.ddi_file_id
		AND
			ddi_files.translation_language_id = $translationLanguageId
	
	WHERE
		ddi_files.nesstar_server_id = $nesstarServerId

	ORDER BY
		study_descriptions.title
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}

	/**
	 * @param int $ddifileid
	 * @return array
	 */
	public function findForDdifile($ddifileid)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$ddifileid = $adapter->quote($ddifileid);
		$query = <<<HEREDOC
		SELECT
			*
		FROM
			study_descriptions
		WHERE
			ddi_file_id = $ddifileid
HEREDOC;
		$result = $adapter->query($query)->fetchAll();
		return isset($result) ? $result[0] : null;
	}
	
	/**
	 * @param int $ddifileid
	 * @param string $year
	 * @return int
	 */
	public function updateYear($ddifileid, $year)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$ddifileid = $adapter->quote($ddifileid);
		$year = $adapter->quote($year);
		$query = <<<HEREDOC
	UPDATE
		study_descriptions
	SET
		year = $year
	WHERE
		ddi_file_id = $ddifileid		
HEREDOC;
		return $adapter->query($query)->rowCount();
	}
	
}
