<?php
/**
 * @package Helper
 */

/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
class BDQ_Helper_WordCloud extends Zend_View_Helper_Abstract
{
	/**
	 * @var Zend_Translate
	 */
	protected $_translate;
	
	/**
	 * @return BDQ_Helper_WordCloud
	 */
	public function __construct()
	{
		$this->_translate = Zend_Registry::get('translateFront');
	}
	
	/**
	 * Displays a word cloud.
	 * 
	 * @param array $words Two dimensions array. Elements must have two keys : freq and word.
	 * @param string $urlMask Used with str_replace to add the word param in the place of wordParam.
	 * @return void
	 */
	public function wordCloud(array & $words, $urlMask, $tag = 'h2')
	{
		echo "<$tag>", $this->_translate->_('li0375000000'), "</$tag>",
			'<ul id="wordCloud">';
		$l = count($words);

		for ($i = 0; $i < $l; $i++):	
			$word = $words[$i];
			$val = $word['freq'] / 10;

			if ($val > 2.2)
			{
				$size = "2.2em";
			}

			else if ($val > 1.5)
			{
				$size = $val . "em";
			}

			else
			{
				$size = "1.2em";
			}

			echo "<li><a style=\"font-size:$size;\" href=\"" . str_replace('wordParam', rawurlencode($word['word']) . SEARCH_QUESTION, $urlMask) . "\">$word[word]</a></li>";
		endfor;

		echo '</ul>';
	}

}