<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class StudyserieController extends BDQ_Locale_FrontController
{

    public function init()
    {
    	parent::init();
    }

    public function viewAction()
    {
    	$id = $this->_request->getBDQParam('id');
    	$mapper = new DB_Mapper_StudySerie;

    	if ( ! ($this->view->serie = $mapper->findWithDetails($id, $this->_translationLanguageGuiCurrent->get_id())))
    	{
    		throw new Zend_Controller_Action_Exception($this->_translate->_('fr0040000000'), 404);	
    	}

    	$tls = Zend_Registry::get('translationLanguagesSolr');
    	$mapper = new DB_Mapper_StudyDescription;
    	$studies = array();

    	foreach ($tls as $tl)
    	{
    		$titles = $mapper->findStudyTitlesForStudySerie($id, $tl->get_id(), $this->_translationLanguageGuiCurrent->get_id());

    		if (count($titles) > 0)
    		{
    			$studies[$tl->get_code_solr()] = $titles;
    			$wc = new Solr_BDQ_Search_WordCloud(
    				'solrLangCode:' . $tl->get_code_solr() . ' studySerieId:' . $id,
    				$tl->get_code_solr()
    			);
    			$wc->tfLowerBound = 0.43;
    			$cloudWords[$tl->get_code_solr()] = $wc->getWords();
    		}
    		
    	}
    	
    	$concepts = NULL;
    	$conceptLists = NULL;
    	
    	while (list($lang, $_studies) = each($studies))
    	{
    		$l = count($_studies);
    		
    		if ($l > 0)
    		{

    			if ($clId = $_studies[0]['concept_list_id'])
    			{
	    			$sameCl = true;
	    		
		    		for ($i = 0; $i < $l; $i++)
		    		{
	
		    			if ( ! ($_studies[$i]['concept_list_id'] == $clId))
		    			{
		    				$sameCl =false;
		    				break;
		    			}
		    			
		    		}
		    		
		    		if ($sameCl)
		    		{
		    			$search = new Solr_BDQ_Search_ConceptFacet(
		    				"studySerieId:$id solrLangCode:$lang",
		    				$this->_translationLanguageGuiCurrent->get_id()
		    			);
			    		$concepts[$lang] = $search->getConceptFacets();
			    		$conceptListMapper = new DB_Mapper_ConceptList;
			    		$conceptLists[$lang] = $conceptListMapper->findTitleTranslation(
			    			$clId,
			    			$this->_translationLanguageGuiCurrent->get_id()
			    		);
		    		}
	    		
    			}
    		
    		}
    	}
    	
    	$this->view->concepts = $concepts;
    	$this->view->conceptList = $conceptLists;
    	reset($studies);
    	$this->view->id = $id;
    	$this->view->studiesGroupedByLanguages = $studies;
    	$this->view->cloudWords = $cloudWords;
    }

}