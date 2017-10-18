<?php

/**
 * @package DB
 * @subpackage Mapper
 */

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class DB_Mapper_TranslationLanguage {

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
			$this->setDbTable('DB_Table_TranslationLanguage');
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
	 * @param DB_Model_TranslationLanguage $tl
	 * @return int
	 */
	public function save(DB_Model_TranslationLanguage $tl)
	{

		$data = array(
			'id' => $tl->get_id(),
			'code' => $tl->get_code(),
			'code_solr' => $tl->get_code_solr(),
			'label_translation_id' => $tl->get_label_translation_id(),
			'enabled_gui' => $tl->get_enabled_gui(),
			'enabled_solr' => $tl->get_enabled_solr(),
		);
		
		if (null === ($id = $tl->get_id()))
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
	 * @return DB_Model_TranslationLanguage
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
		
        $tls = array();
        
        for ($i = 0; $i < $l; $i++)
        {
        	$tls[] = $this->_hydrate($results[$i]);
        }
        
        return $tls;
        
	}

	/**
	 * @param Zend_Db_Table_Row $row
	 * @return DB_Model_TranslationLanguage
	 */
	protected function _hydrate($row)
	{
		$tl = new DB_Model_TranslationLanguage;
		$tl->set_id($row->id);
		$tl->set_code($row->code);
		$tl->set_code_solr($row->code_solr);
		$tl->set_label_translation_id($row->label_translation_id);
		$tl->set_enabled_gui($row->enabled_gui);
		$tl->set_enabled_solr($row->enabled_solr);
		return $tl;
	}

	/**
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public function findAll($start = 0, $limit = null, $where = null, $order = null)
	{
		
		$results = $this->getDbTable($where, $order, $start, $limit)->fetchAll();

		if (0 == ($l = count($results)))
        {
            return;
        }
		
        $tl = array();
        
        for ($i = 0; $i < $l; $i++)
        {
        	$tl[] = $this->_hydrate($results[$i]);
        }
        
        return $tl;
	}

	public function findAllLinkedToAtLeastOneDdifile($translationId)
	{
		$adapter = $this->getDBTable()->getAdapter();
		$translationId = $adapter->quote($translationId);
		$query = <<<HEREDOC
		SELECT 
			DISTINCT 
				translation_languages.id AS translation_language_id, 
				code_solr, 
				code, 
				translation_entries.translated_text AS label
		FROM
			translation_languages
			INNER JOIN
			ddi_files
			ON
			translation_languages.id = ddi_files.translation_language_id
			INNER JOIN
			translation_entries
			ON
			translation_languages.label_translation_id = translation_entries.translation_id
			AND
			translation_entries.translation_language_id = $translationId
HEREDOC;
		return $adapter->query($query)->fetchAll();
	}
	
	public function findAllSameTranslationLabelLanguage()
	{
		$query = <<<HEREDOC
		SELECT 
			translation_languages.id AS id,
			translation_entries.translated_text AS label,
			translation_languages.code AS code
		FROM
			translation_languages
			INNER JOIN
			translation_entries
			ON
			translation_languages.label_translation_id = translation_entries.translation_id
			AND
			translation_languages.id = translation_entries.translation_language_id
HEREDOC;
		return $this->getDBTable()->getAdapter()->query($query)->fetchAll();
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
		$query = 'DELETE FROM translation_languages WHERE id IN (';
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
	
}

