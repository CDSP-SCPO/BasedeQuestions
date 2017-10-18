<?php

/**
 * @author Xavier Schepler
 * @copyright Reseau Quetelet
 */
class IndexController extends BDQ_Locale_FrontController
{

    public function init()
    {
    	parent::init();
    }

    public function indexAction()
    {
    	$this->view->layout()->setLayout('empty');
    }

    public function helpAction() {}

    public function maintenanceAction() {}

    public function aboutAction() {}

    public function homeAction()
    {
    	$ddiFileMapper = new DB_Mapper_Ddifile;
    	$this->view->ddiFileCounts = $ddiFileMapper->getCountsByLanguage(
    		$this->_translationLanguageGuiCurrent->get_id()
    	);
    	$variableMapper = new DB_Mapper_Variable;
    	$this->view->variableCount = $variableMapper->getCount();
    }

}

