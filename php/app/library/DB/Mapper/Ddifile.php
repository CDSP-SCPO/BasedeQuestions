<?php

/**
 * @package DB
 * @subpackage Mapper
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Mapper_Ddifile {
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
			$this->setDbTable('DB_Table_Ddifile');
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
	 * @param DB_Model_Ddifile $ddifile
	 * @return int
	 */
	public function save(DB_Model_Ddifile $ddifile)
	{

		$data = array(
			'id' => $ddifile->get_id(),
			'translation_language_id' => ($tlid = $ddifile->get_translation_language_id()) !== '' ? $tlid : NULL,
			'nesstar_server_id' => ($nsid = $ddifile->get_nesstar_server_id()) !== '' ? $nsid : NULL,
			'concept_list_id' => ($clid = $ddifile->get_concept_list_id()) !== '' ? $clid : NULL,
			'study_serie_id' => ($ssid = $ddifile->get_study_serie_id()) !== '' ? $ssid : NULL,
			'file_name' => $ddifile->get_file_name(),
			'questionnaire_url' => $ddifile->get_questionnaire_url(),
			'alternate_description_url' => $ddifile->get_alternate_description_url(),
			'multiple_item_parsed' => $ddifile->get_multiple_item_parsed(),
		);
		
		if ( ! is_numeric($id = $ddifile->get_id()))
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
	 * @return DB_Model_Ddifile
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
		
        $ddifiles = array();
        
        for ($i = 0; $i < $l; $i++)
        {
        	$ddifiles[] = $this->_hydrate($results[$i]);
        }
        
        return $ddifiles;
        
	}

	/**
	 * @param Zend_Db_Table_Row $row
	 * @return DB_Model_Ddifile
	 */
	protected function _hydrate($row)
	{
		$ddifile = new DB_Model_Ddifile;
		$ddifile->set_id($row->id);
		$ddifile->set_translation_language_id($row->translation_language_id);
		$ddifile->set_questionnaire_url($row->questionnaire_url);
		$ddifile->set_alternate_description_url($row->alternate_description_url);
		$ddifile->set_nesstar_server_id($row->nesstar_server_id);
		$ddifile->set_concept_list_id($row->concept_list_id);
		$ddifile->set_study_serie_id($row->study_serie_id);
		$ddifile->set_file_name($row->file_name);
		$ddifile->set_multiple_item_parsed($row->multiple_item_parsed);
		$ddifile->set_created($row->created);
		$ddifile->set_modified($row->modified);
		return $ddifile;
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
		$query = 'DELETE FROM ddi_files WHERE id IN (';
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
	
	public function findWithStudyTitle($id)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$id = $adapter->quote($id);
		$query = <<<HEREDOC
	SELECT
		study_descriptions.title AS title,
		ddi_files.id AS id
		
	FROM
		study_descriptions
	INNER JOIN
		ddi_files
		ON
		ddi_files.id = study_descriptions.ddi_file_id
		
	WHERE
		ddi_files.id = $id
		
	ORDER BY
		title
HEREDOC;
		$result = $adapter->query($query)->fetchAll();
		return isset($result) ? $result[0] : null;

	}
	
	public function findWithDetails($id, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$id = $adapter->quote($id);
		$query = <<<HEREDOC
	SELECT
		study_descriptions.title AS study_title,
		study_descriptions.year AS study_year,
		nesstar_servers.title AS nesstar_server_title,
		translation_entries.translated_text AS study_serie_title,
		ddi_files.id AS id,
		ddi_files.file_name AS file_name,
		ddi_files.questionnaire_url AS questionnaire_url,
		ddi_files.multiple_item_parsed AS multiple_item_parsed,
		ddi_files.created AS created,
		ddi_files.modified AS modified

	FROM
		study_descriptions
	INNER JOIN
		ddi_files
		ON
		ddi_files.id = study_descriptions.ddi_file_id
	LEFT JOIN
		nesstar_servers
		ON
		nesstar_servers.id = ddi_files.nesstar_server_id
	LEFT JOIN
		study_series
		ON
		study_series.id = ddi_files.study_serie_id
	LEFT JOIN
		translations
		ON
		translations.id = study_series.title_translation_id
	LEFT JOIN
		translation_entries
		ON
		translation_entries.translation_id = translations.id
		AND
		translation_entries.translation_language_id = $translationLanguageId
		
	WHERE
		ddi_files.id = $id

HEREDOC;
		
		$result = $adapter->query($query)->fetchAll();
		return isset($result) ? $result[0] : null;
	}
	
	public function findAllWithDetails($translationLanguageId, $start = 0, $limit = 0, $ddiFileLanguageId = NULL)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$vs = GC_MULTIPLE_VALUE_SEPARATOR;
		$query = <<<HEREDOC
	SELECT
		ddi_files.id AS id,
		translation_languages.code_solr AS study_code_solr,
		ddi_files.nesstar_server_id AS study_nesstar_server_id,
		study_descriptions.nesstar_id AS study_nesstar_id,
		study_descriptions.title AS study_title,
		study_descriptions.year AS study_year,
		translation_language_label.translated_text AS tl_label,
		nesstar_servers.title AS ns_title,
		translation_entries.translated_text AS study_serie_title,
		GROUP_CONCAT(domain_title_translation_entry.translated_text SEPARATOR '$vs') AS study_domain,
		GROUP_CONCAT(domains.id SEPARATOR '$vs') AS study_domain_id,
		study_series.id AS study_study_serie_id,
		GROUP_CONCAT(distributors.abbreviation SEPARATOR '$vs') AS study_distributor_abbreviation,
		GROUP_CONCAT(distributors.title SEPARATOR '$vs') AS study_distributor_title,
		concept_list_title_translation_entry.translated_text AS study_concept_list,
		concept_list.id AS study_concept_list_id
		

	FROM
		study_descriptions
		
		INNER JOIN
		ddi_files
		ON
		ddi_files.id = study_descriptions.ddi_file_id
		
		INNER JOIN
		translation_languages
		ON
		ddi_files.translation_language_id = translation_languages.id
		
		INNER JOIN
		translation_entries AS translation_language_label
		ON
		translation_language_label.translation_id = translation_languages.label_translation_id
		AND
		translation_language_label.translation_language_id = $translationLanguageId
		
		LEFT JOIN
		nesstar_servers
		ON
		ddi_files.nesstar_server_id = nesstar_servers.id
		
		LEFT JOIN
		study_series
		ON
		study_series.id = ddi_files.study_serie_id
		
		LEFT JOIN
		translations
		ON
		translations.id = study_series.title_translation_id
		
		LEFT JOIN
		translation_entries
		ON
		translation_entries.translation_id = translations.id
		AND
		translation_entries.translation_language_id = $translationLanguageId
		
		LEFT JOIN
		domains_ddi_files
		ON
		domains_ddi_files.ddi_file_id = ddi_files.id
		
		LEFT JOIN
		domains
		ON
		domains.id = domains_ddi_files.domain_id
		
		LEFT JOIN
		distributors
		ON
		distributors.ddi_file_id = ddi_files.id
		
		LEFT JOIN
		translations AS domain_title_translation
		ON
		domain_title_translation.id = domains.title_translation_id
		
		LEFT JOIN
		translation_entries AS domain_title_translation_entry
		ON
		domain_title_translation_entry.translation_id = domain_title_translation.id
		AND
		domain_title_translation_entry.translation_language_id = $translationLanguageId
		
		LEFT JOIN
		concept_lists AS concept_list
		ON
		ddi_files.concept_list_id = concept_list.id

		LEFT JOIN
		translations AS concept_list_title_translation
		ON
		concept_list_title_translation.id = concept_list.title_translation_id
		
		LEFT JOIN
		translation_entries AS concept_list_title_translation_entry
		ON
		concept_list_title_translation_entry.translation_id = concept_list_title_translation.id
		AND
		concept_list_title_translation_entry.translation_language_id = $translationLanguageId
		

HEREDOC;

		if ($ddiFileLanguageId)
		{
			$query .= <<<HEREDOC
		WHERE
			ddi_files.translation_language_id = $ddiFileLanguageId	
			
HEREDOC;
		}
		
		$query .= <<<HEREDOC
	GROUP BY
		ddi_files.id

	ORDER BY
		study_domain,
		study_serie_title,
		study_title
	
HEREDOC;

		if ($limit)
		{
			
			$query .= <<<HEREDOC
	LIMIT
		$start, $limit
			
HEREDOC;
		}

		return $adapter->query($query)->fetchAll();
	}
	
	public function findWithDetailsForQuestionSelection(array $ids)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$l = count($ids);
		
		if ($l == 0)
		{
			return array();
		}
		
		for ($i = 0; $i < $l; $i ++)
		{
			$ids[$i] = $adapter->quote($ids[$i]);
		}
		
		$ids = implode(',', $ids);
		$query = <<<HEREDOC
	SELECT
		study_descriptions.ddi_file_id AS id,
		study_descriptions.title,
		study_descriptions.abstract,
		study_descriptions.universe,
		study_descriptions.sample_procedure,
		study_descriptions.analysis_unit,
		study_descriptions.geographic_coverage,
		study_descriptions.nation,
		study_descriptions.case_quantity,
		study_descriptions.collect_mode,
		GROUP_CONCAT(DISTINCT CONCAT(producers.abbreviation, ' ', producers.title) SEPARATOR '<br/>') AS producer,
		GROUP_CONCAT(DISTINCT collect_dates.date SEPARATOR ' ') AS collect_date,
		GROUP_CONCAT(DISTINCT CONCAT(distributors.abbreviation, ' ', distributors.title) SEPARATOR '<br/>') AS distributor,
		GROUP_CONCAT(DISTINCT CONCAT(funding_agencies.abbreviation, ' ', funding_agencies.title) SEPARATOR '<br/>') AS funding_agency
		
	FROM
		study_descriptions
		LEFT JOIN
		producers
		ON
		producers.ddi_file_id = study_descriptions.ddi_file_id
		LEFT JOIN
		distributors
		ON
		distributors.ddi_file_id = study_descriptions.ddi_file_id
		LEFT JOIN
		funding_agencies
		ON
		funding_agencies.ddi_file_id = study_descriptions.ddi_file_id
		LEFT JOIN
		collect_dates
		ON
		collect_dates.ddi_file_id = study_descriptions.ddi_file_id

	WHERE
		study_descriptions.ddi_file_id IN ($ids)

	GROUP BY
		study_descriptions.ddi_file_id
		
	ORDER BY
		study_descriptions.ddi_file_id
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
	
	public function findAllWithDetailsForSynchronization()
	{
		$adapter = $this->getDbTable()->getAdapter();
		$query = <<<HEREDOC
	SELECT
		ddi_files.id AS id,
		ddi_files.nesstar_server_id AS study_nesstar_server_id,
		ddi_files.file_name AS ddi_file_name,
		study_descriptions.nesstar_id AS study_nesstar_id,
		study_descriptions.title AS study_title,
		nesstar_servers.title AS ns_title,
		nesstar_servers.ip_and_port AS ns_ip_and_port,
		nesstar_servers.responsible as ns_responsible

	FROM
		study_descriptions
		
		INNER JOIN
		ddi_files
		ON
		ddi_files.id = study_descriptions.ddi_file_id
		
		LEFT JOIN
		nesstar_servers
		ON
		ddi_files.nesstar_server_id = nesstar_servers.id

HEREDOC;
		
		return $adapter->query($query)->fetchAll();
	}
	
	public function findAllWithDetailsForSolrIndexBuild($producerId = null, $serieId = null)
	{
		$adapter = $this->getDbTable()->getAdapter();
		
		$where = '';
		
		if ($producerId)
		{
			$where = <<<HEREDOC

	WHERE		
		domains_ddi_files.domain_id	= $producerId
HEREDOC;
		}
		
		elseif ($serieId)
		{
			$where = <<<HEREDOC

	WHERE
		ddi_files.study_serie_id = $serieId
HEREDOC;
		}
		
		$query = <<<HEREDOC
	SELECT
		ddi_files.id AS study_ddi_file_id,
		ddi_files.nesstar_server_id AS study_nesstar_server_id,
		ddi_files.study_serie_id as study_serie_id,
		ddi_files.concept_list_id AS study_concept_list_id,
		ddi_files.translation_language_id AS study_language_id,
		ddi_files.questionnaire_url AS study_questionnaire_url,
		study_descriptions.nesstar_id AS study_nesstar_id,
		study_descriptions.title AS study_title,
		study_descriptions.id AS study_description_id,
		YEAR(study_descriptions.year) AS study_description_year,
		GROUP_CONCAT(domains_ddi_files.domain_id SEPARATOR '!<(^.^)>!') AS study_domain_ids,
		translation_languages.code_solr AS study_solr_language_code,
		GROUP_CONCAT(questionnaires.title SEPARATOR '!<(^.^)>!') AS study_questionnaire_titles,
		GROUP_CONCAT(questionnaires.id SEPARATOR '!<(^.^)>!') AS study_questionnaire_ids

	FROM
		study_descriptions

		INNER JOIN
		ddi_files
		ON
		ddi_files.id = study_descriptions.ddi_file_id
		
		INNER JOIN
		translation_languages
		ON
		ddi_files.translation_language_id = translation_languages.id

		LEFT JOIN
		domains_ddi_files
		ON
		domains_ddi_files.ddi_file_id = ddi_files.id
		
		LEFT JOIN
		questionnaires
		ON
		questionnaires.ddi_file_id = ddi_files.id	
	$where

	GROUP BY
		ddi_files.id

HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
	
	public function getVariableWithQuestionCount($ddiFileId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$ddiFileId = $adapter->quote($ddiFileId);
		$query = <<<HEREDOC
	SELECT
		
	(
		SELECT
			COUNT(variables.id) 
	
		FROM
			variables
		
		LEFT JOIN
			questions
			
		ON
			questions.variable_id = variables.id
	
		WHERE
			variables.ddi_file_id = $ddiFileId
		
		AND
			questions.litteral IS NOT NULL
	
		AND
			questions.litteral <> ''
	)
		
	-
	
	(
		SELECT
			COUNT(questions.id) AS c2
	
		FROM
			variables
	
		LEFT JOIN
			questions
			
		ON
			questions.variable_id = variables.id
	
		WHERE
			variables.ddi_file_id = $ddiFileId
			
		AND
			variables.variable_group_id IS NOT NULL
	)
	
	+
	
	(
		SELECT
			COUNT(DISTINCT variable_group_id) AS c3
	
		FROM
			variables

		WHERE
			variables.ddi_file_id = $ddiFileId
			
		AND
			variables.variable_group_id IS NOT NULL
	)
	
	AS
	
	study_question_count

HEREDOC;
		$result = $adapter->query($query)->fetchAll();
		return $result[0]['study_question_count'];
	}

	/**
	 * @return int
	 */
	public function getStudyCount()
	{
		$adapter = $this->getDbTable()->getAdapter();
		$query = <<<HEREDOC
	SELECT
		COUNT(*) AS nb

	FROM
		ddi_files
HEREDOC;
		$result = $adapter->query($query)->fetchAll();
		return $result[0]['nb'];
	}
	
	/**
	 * @return array
	 */
	public function getCountsByLanguage($translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
	SELECT
		COUNT(ddi_files.id) AS count,
		language_label_translation.translated_text AS language_label
	FROM
		ddi_files
	LEFT JOIN
		translation_languages
	ON
		ddi_files.translation_language_id = translation_languages.id
	LEFT JOIN
		translation_entries AS language_label_translation
	ON
		translation_languages.label_translation_id = language_label_translation.translation_id
	AND
		language_label_translation.translation_language_id = $translationLanguageId
		
	GROUP BY
		ddi_files.translation_language_id
		
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
}