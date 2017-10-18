<?php
/**
 * @package Helper
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class BDQ_Helper_ResultsLink extends Zend_View_Helper_Abstract
{

	public function resultsLink()
	{
		$translate = Zend_Registry::get('translateFront');
		$label = $translate->_('li0380000000');
		
		if ($this->view->searchResultsUrl) //set in BDQ_FrontController::init()
		{
			echo '<a class="backToResults action goldAnchor" href="' . $this->view->searchResultsUrl. "\">$label</a>";
		}
		
	}

}