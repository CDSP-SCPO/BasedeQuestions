<?php
/**
 * @package Helper
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 * @package Helper
 */
class BDQ_Helper_FacetGripStart extends Zend_View_Helper_Abstract
{

	public function facetGripStart(array $classes = NULL)
	{
		echo '<div class="facetHeader' . ($classes ? ' ' . implode(' ', $classes) : '') .  '"><div class="b1t"><div></div></div><div class="b1v">';
	}

}