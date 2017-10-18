<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class QuestionController extends BDQ_Locale_FrontController
{

    public function init()
    {
    	parent::init();
    }

    public function nearbyresultAction()
    {
    	
    	if ( ! $this->_request->isXmlHttpRequest())
    	{
    		throw new Zend_Controller_Action_Exception('', 404);
    	}
    	
		$this->view->layout()->setLayout('empty');
		$this->view->from = $from = $this->_request->getBDQParam('from');
		$this->view->to = $to = $this->_request->getBDQParam('to');
		$this->view->ddiFileId = $ddiFileId = $this->_request->getBDQParam('ddiFileId');
		$select = new Solr_Select(
			"questionPosition:$to AND ddiFileId:$ddiFileId"
		);
		$select->setFl(Solr_BDQ_Search_Search::$fl);
		$response = $select->send();
		$response->createDocuments();
		$this->view->question = $response->documents[0];
		$mapper = new DB_Mapper_Concept;
	    $this->view->conceptTitles = $mapper->findAllTitleAndId(
	    	$response->documents[0]->get_conceptId(),
    		$this->_translationLanguageGuiCurrent->get_id()
    	);
		$this->view->domainList = $this->_getDomainList(array($response->documents[0]->get_domainId()));
    }
    
    public function selectionAction()
    {

    	if ($this->_clientSettings->selectedQuestions)
    	{
			$this->view->documents = $documents = $this->_getSelectedQuestionsSolrDocuments();
			
			if ( ! $documents)
			{
				$settings = BDQ_Settings_Client::getInstance();
				$settings->selectedQuestions = '';
				$settings->setCookie();
			}

			$this->view->studyDetails = $this->_getStudiesDetails($documents);
    	}
    	
    	else
    	{
    		$this->view->documents = array();
    		$this->view->studyDetails = array();
    	}

    }
    
	public function exportselectionAction()
	{
		$this->view->layout()->setLayout('empty');
		$documents = $documents = $this->_getSelectedQuestionsSolrDocuments();
		$studyDetails = $this->_getStudiesDetails($documents);
		
		if ($this->_request->getParam('format') == 'csv')
		{
    		$this->_exportCsv($documents, $studyDetails);
		}
		
		elseif ($this->_request->getBDQParam('format') == 'xlsx')
		{
    		$this->_exportXls($documents, $studyDetails);
		}
		
	}
	
	protected function _exportCsv($documents, $studyDetails)
	{
		$response = $this->getResponse();
		$response->setHeader('Content-Type', 'application/csv-tab-delimited-table', true);
    	$response->setHeader('Content-Disposition', "inline; filename=\"export.csv\"", true);
    	$response->sendHeaders();
		$l = count($documents);
		$fp = fopen("php://output", 'w');
		fputcsv($fp, $this->_getHeaderRow(), ';');
		
		for ($i = 0; $i < $l ; $i++)
		{
			$row = $this->_getRow($documents[$i], binarySearch($documents[$i]->get_ddiFileId(), $studyDetails));
			fputcsv($fp, $row, ';');
		}

		fclose($fp);
	}
	
	protected function _exportXls($documents, $studyDetails)
	{
		require_once APPLICATION_PATH . '/../library/PHPExcel.php';
 		$response = $this->getResponse();
		$response->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
    	$response->setHeader('Content-Disposition', "inline; filename=\"export.xlsx\"", true);
    	$response->sendHeaders();
		$workbook = new PHPExcel;
		$sheet = $workbook->getActiveSheet();
		
		
		$header = $this->_getHeaderRow();
		$l = count($header);
		
		for ($i = 0; $i < $l; $i++)
		{
			$sheet->setCellValueByColumnAndRow($i, 1, utf8_encode($header[$i]));
		}
		
		$l2 = count($documents);
		
		for ($i = 0; $i < $l2 ; $i++)
		{
			
			
			$row = $this->_getRow($documents[$i], binarySearch($documents[$i]->get_ddiFileId(), $studyDetails));
			$c = 'A';
			
			for ($j = 0; $j < $l; $j++)
			{
				$sheet->setCellValueByColumnAndRow($j, $i + 2, utf8_encode($row[$j]));
				$sheet->getStyle("$c" . ($i + 2))->getAlignment()->setWrapText(true);
				$sheet->getColumnDimension("$c" . ($i + 2))->setAutoSize(true);
				$sheet->getStyle("$c" . ($i + 2))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
				$sheet->getStyle("$c" . ($i + 2))->getAlignment()->setShrinkToFit(true);
				$c++;
			}
			
		}

		$c = 'A';

		for ($i = 0; $i < $l; $i++)
		{
			$sheet->getColumnDimension($c)->setWidth(25);
			$c++;
		}
		
		$sheet->getColumnDimension('D')->setWidth(60);
		$sheet->getColumnDimension('F')->setWidth(60);
		$writer = new PHPExcel_Writer_Excel2007($workbook);
		$writer->setOffice2003Compatibility(true);
		$writer->save('php://output');
		
	}
	
	protected function _getHeaderRow()
	{
		$fields = array();
		$fields[] = utf8_decode($this->_translate->_('fr0005000000'));
		$fields[] = utf8_decode($this->_translate->_('fr0005000050'));
		$fields[] = utf8_decode($this->_translate->_('fr0005000100'));
		$fields[] = utf8_decode($this->_translate->_('fr0005000150'));
		$fields[] = utf8_decode($this->_translate->_('fr0005000200'));
		$fields[] = utf8_decode($this->_translate->_('fr0005000250'));
		return $fields;
	}
	
	protected function _getRow($document, $details)
	{
		$lang = $document->get_solrLangCode();
		
		$fields = array();
		$fields[] = utf8_decode($details['title']);
		$fields[] = utf8_decode(str_replace('<br/>', "\n", $details['producer']));
		$fields[] = utf8_decode(str_replace('<br/>', "\n", $details['distributor']));
		
		$meth = "get_q$lang";
		$q = $document->$meth();
		
		if ($document->get_hasMultipleItems())
		{
			$meth = "get_i$lang";
			$q .= "\n" . implode("\n", $document->$meth());
		}
		
		$fields[] = utf8_decode($q);
		
		$meth = "get_m$lang";
		$fields[] = utf8_decode(implode("\n", $document->$meth()));
		
		
		$meth = "get_vl$lang";
		$names = $document->get_variableName();	
		$vls = $document->$meth();
		$l = count($names);
		
		$vars = '';

		for ($i = 0; $i < $l; $i++)
		{
			$vars .= $vls[$i] . "\n";
		}
		
		$fields[] = utf8_decode($vars);
		return $fields;
	}
    
	/**
     * @param array $ids
     * @return array
     */
    protected function _getDomainList(array $ids)
    {
    	
    	if (empty($ids))
    	{
    		return;
    	}
    	
    	$mapper = new DB_Mapper_Domain;
    	return $mapper->findAllTitleAndId(
    		$ids,
    		$this->_translationLanguageGuiCurrent->get_id()
    	);

    }

    protected function _getSelectedQuestionsSolrDocuments()
    {
    	$ids = explode(',', $this->_clientSettings->selectedQuestions);
    	$l = count($ids);
    	$q = '(';

    	for ($i = 0; $i < $l; $i++)
    	{
    		$q .= "id:$ids[$i]";
			
    		if ($i < $l - 1)
    		{
    			$q .= ' OR ';
    		}
    		
    	}
    	
    	$q .= ')';
    	$select = new Solr_Select($q);
	$select->setRows($l);
		$select->setFl(Solr_BDQ_Search_Search::$fl);
		$response = $select->send();
		$response->createDocuments();
		return $response->documents;
    }
    
    protected function _getStudiesDetails($documents)
    {
    	$l = count($documents);
		$ddiFileIds = array();
		
		for ($i = 0; $i < $l; $i++)
		{
			$ddiFileIds[] = $documents[$i]->get_ddiFileId();
		}
		
		$mapper = new DB_Mapper_Ddifile;
		return $mapper->findWithDetailsForQuestionSelection($ddiFileIds);
    }
    
}
