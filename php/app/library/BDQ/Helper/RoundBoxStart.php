<?php
/**
 * @package Helper
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class BDQ_Helper_RoundBoxStart extends Zend_View_Helper_Abstract
{

	public function roundBoxStart(array $classes = NULL)
	{
		echo '<div class="rndBox' . ($classes ? ' ' . implode(' ', $classes) : '') .  '"><div class="b1t"><div></div></div><div class="b1c">';
	}

}