<?php

/**
 * @package DB
 * @subpackage Mapper
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Mapper_Domain {
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
			$this->setDbTable('DB_Table_Domain');
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
	 * @param DB_Model_Domain $domain
	 * @return int
	 */
	public function save(DB_Model_Domain $domain)
	{

		$data = array(
			'id' => $domain->get_id(),
			'concept_list_id' => $domain->get_concept_list_id(),
			'title_translation_id' => $domain->get_title_translation_id(),
			'description_translation_id' => $domain->get_description_translation_id(),
		);
		
		if (null === ($id = $domain->get_id()))
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
	 * @return DB_Model_Domain
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
	 * @return DB_Model_Domain
	 */
	protected function _hydrate($row)
	{
		$domain = new DB_Model_Domain;
		$domain->set_id($row->id);
		$domain->set_concept_list_id($row->concept_list_id);
		$domain->set_title_translation_id($row->title_translation_id);
		$domain->set_description_translation_id($row->description_translation_id);
		$domain->set_created($row->created);
		$domain->set_modified($row->modified);
		return $domain;
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
		$mapper = new DB_Mapper_Domain;
		$domain = $mapper->find($id); 
		$tids = array();
		$tids[] = $domain->get_title_translation_id();
		$tids[] = $domain->get_description_translation_id();

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
	 * Used to list domains in the admin or to create select options
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
	domains.id AS id,
	title_translation.translated_text AS title,
	domains.created AS created,
	domains.modified AS modified,
	count(domains_ddi_files.ddi_file_id) AS ddi_file_count
	
FROM
	domains
	INNER JOIN
	translation_entries AS title_translation
	ON
	title_translation.translation_id = domains.title_translation_id
	INNER JOIN
	translation_entries AS description_translation
	ON
	description_translation.translation_id = domains.description_translation_id
	LEFT JOIN
	domains_ddi_files
	ON
	domains.id = domains_ddi_files.domain_id
	
WHERE
	title_translation.translation_language_id = $translationLanguageId
AND
	description_translation.translation_language_id = $translationLanguageId

GROUP BY
	domains.id

ORDER BY
	ddi_file_count DESC, title ASC
		
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
	
	/**
	 * Used to view a domain in the front
	 * 
	 * @param int $translationLanguageId
	 * @return array an associative array with id, title, description, created, modified
	 */
	public function findWithDetails($domainId, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$domainId = $adapter->quote($domainId);
		$query = <<<HEREDOC
SELECT DISTINCT
	domains.id AS id,
	title_translation.translated_text AS title,
	description_translation.translated_text AS description,
	domains.created AS created,
	domains.modified AS modified
	
FROM
	domains
	INNER JOIN
	translation_entries AS title_translation
	ON
	title_translation.translation_id = domains.title_translation_id
	INNER JOIN
	translation_entries AS description_translation
	ON
	description_translation.translation_id = domains.description_translation_id
	INNER JOIN
	domains_ddi_files
	ON
	domains.id = domains_ddi_files.domain_id
	
WHERE
	title_translation.translation_language_id = $translationLanguageId
AND
	description_translation.translation_language_id = $translationLanguageId
AND
	domains.id = $domainId
		
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
	
	/**
	 * @param int $translationLanguageId
	 * @param int $solrLangCode
	 * @return array an associative array
	 */
	public function findAllLinkedToAtLeastOneDdifile($translationLanguageId, $searchLang = NULL)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
SELECT 
	domains.id AS id,
	title_translation.translated_text AS title,
	translation_languages.code_solr AS code_solr
	
FROM
	domains
	INNER JOIN
	translation_entries AS title_translation
	ON
	title_translation.translation_id = domains.title_translation_id
	AND
	title_translation.translation_language_id = $translationLanguageId
	INNER JOIN
	domains_ddi_files
	ON
	domains.id = domains_ddi_files.domain_id
	INNER JOIN
	ddi_files
	ON
	domains_ddi_files.ddi_file_id = ddi_files.id
	INNER JOIN
	translation_languages
	ON
	translation_languages.id = ddi_files.translation_language_id

HEREDOC;

		if (isset($searchLang))
		{

			$query .= <<<HEREDOC
	WHERE
		translation_languages.code_solr = $searchLang
HEREDOC;

		}

		$query .= <<<HEREDOC
GROUP BY
	domains.id

ORDER BY
	translation_languages.code_solr ASC
		
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
	
	/**
	 * @return string
	 */
	static function getDomainByLangs($currentGUITranslationLanguageId, $searchLang = NULL)
    {
    	$mapper = new DB_Mapper_Domain;
    	return $mapper->findAllLinkedToAtLeastOneDdifile($currentGUITranslationLanguageId);
    }
	
	/**
	 * @param int $domainId
	 * @param int $translationLanguageId
	 * @return array
	 */
	public function findTitleTranslation($domainId, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$domainId = $adapter->quote($domainId);
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
SELECT 
	domains.id AS id,
	title_translation.translated_text AS title,
	domains.created AS created,
	domains.modified AS modified
	
FROM
	domains
INNER JOIN
	translation_entries AS title_translation
ON
	title_translation.translation_id = domains.title_translation_id
INNER JOIN
	translation_entries AS description_translation
ON
	description_translation.translation_id = domains.description_translation_id
	
WHERE
	title_translation.translation_language_id = $translationLanguageId
AND
	description_translation.translation_language_id = $translationLanguageId
AND 
	domains.id = $domainId
HEREDOC;
		$table = $this->getDbTable();
		$result = $adapter->query($query)->fetchAll();
		return isset($result[0]) ? $result[0] : NULL;
	}
	
	/**
	 * @param int $domainId
	 * @param int $translationLanguageId
	 * @return array
	 */
	public function findAllTranslations($domainId, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$domainId = $adapter->quote($domainId);
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
SELECT 
	domains.id AS id,
	domains.concept_list_id AS concept_list_id,
	domains.title_translation_id AS title_translation_id,
	domains.description_translation_id AS description_translation_id,
	title_translation.translated_text AS title,
	title_translation.id AS title_translation_entry_id,
	description_translation.translated_text AS description,
	description_translation.id AS description_translation_entry_id,
	translation_language.id AS lang_id,
	translation_language.code AS lang_code,
	translation_language_label_translation.translated_text AS lang_label,
	domains.created AS created,
	domains.modified AS modified
	
FROM
	domains
	INNER JOIN
	translation_entries AS title_translation
	ON
	title_translation.translation_id = domains.title_translation_id
	INNER JOIN
	translation_entries AS description_translation
	ON
	description_translation.translation_id = domains.description_translation_id
	INNER JOIN
	translation_languages AS translation_language
	ON
	translation_language.id = title_translation.translation_language_id
	AND
	translation_language.id = description_translation.translation_language_id
	INNER JOIN
	translation_entries AS translation_language_label_translation
	ON
	translation_language_label_translation.translation_id = translation_language.label_translation_id
	AND
	translation_language_label_translation.translation_language_id = $translationLanguageId

WHERE
	domains.id = $domainId
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}

	/**
	 * @param int $domainId
	 * @return array
	 */
	public function findTitleTranslationsForDdifile($ddiFileId, $translationLanguageId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$ddiFileId = $adapter->quote($ddiFileId);
		$translationLanguageId = $adapter->quote($translationLanguageId);
		$query = <<<HEREDOC
SELECT 
	domains.id AS id,
	title_translation.translated_text AS title,
	domains.created AS created,
	domains.modified AS modified
	
FROM
	domains
	INNER JOIN
		translation_entries AS title_translation
	ON
		title_translation.translation_id = domains.title_translation_id
	INNER JOIN
		translation_entries AS description_translation
	ON
		description_translation.translation_id = domains.description_translation_id
	INNER JOIN
		domains_ddi_files
	ON
		domains_ddi_files.ddi_file_id = $ddiFileId
	AND
		domains_ddi_files.domain_id = domains.id
	
	
WHERE
	title_translation.translation_language_id = $translationLanguageId
AND
	description_translation.translation_language_id = $translationLanguageId

HEREDOC;
		$result = $adapter->query($query)->fetchAll();
		return isset($result) ? $result : array();
	}
	
	/**
	 * @param int $ddiFileId
	 * @return array
	 */
	public function findDomainIdsForDdifile($ddiFileId)
	{
		$adapter = $this->getDbTable()->getAdapter();
		$ddiFileId = $adapter->quote($ddiFileId);
		$query = <<<HEREDOC
SELECT
	domain_id

FROM
	domains_ddi_files
	
WHERE
	ddi_file_id = $ddiFileId


HEREDOC;
		$result = $adapter->query($query)->fetchAll();
		return isset($result) ? $result : array();
	}
	
	/**
	 * @param array $domainsIds
	 * @param array $translationLanguageId
	 * @return array
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
		domains.id,
		translation_entries.translated_text AS title
		
	FROM 
		domains
		
	INNER JOIN
		translation_entries
	ON
		translation_entries.translation_id = domains.title_translation_id
	AND
		translation_entries.translation_language_id = $translationLanguageId
	AND
		domains.id IN ($_ids)
	ORDER BY id ASC
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
	
}
