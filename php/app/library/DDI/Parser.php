<?php

/**
 * @package DDI
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
interface DDI_Parser {

	/**
	 * @return array
	 */
	public function getStudyDescription();

	/**
	 * @return array
	 */
	public function & getVariables();

}