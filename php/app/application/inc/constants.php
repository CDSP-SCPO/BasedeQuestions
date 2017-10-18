<?php
/**
 * Some constants impacting the application behaviour.
 * See /application/configs/client-settings.ini for the default user settings. 
 * 
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */


define('LESSC_PATH', '/var/lib/gems/1.8/bin/lessc');
define('YUI_COMPRESSOR_PATH', '/opt/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar');

{
	 //Folder where XML DDI files are stored
	define('DDI_FILES', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'ddi' . DIRECTORY_SEPARATOR);
	//Folder where deleted XML DDI files are stored
	define('DELETED_DDI_FILES', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'ddi' . DIRECTORY_SEPARATOR . 'deleted' . DIRECTORY_SEPARATOR);
	//Folder where questionnaire PDF are stored
	define('QUESTIONNAIRE_FILES', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'questionnaire' . DIRECTORY_SEPARATOR);
	//Folder where deleted questionnaire PDF are stored
	define('DELETED_QUESTIONNAIRE_FILES', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'questionnaire' . DIRECTORY_SEPARATOR . 'deleted' . DIRECTORY_SEPARATOR);
}

//Folder where stop word files are stored
{
	define('STOPWORDS_FILES', realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public'  . DIRECTORY_SEPARATOR . 'stopwords') . DIRECTORY_SEPARATOR);
	define('STOPWORDS_BASEURL', '/stopwords');
}

define('EXCEPTIONS_LOG', realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' .  DIRECTORY_SEPARATOR . 'logs') . DIRECTORY_SEPARATOR . 'exceptions.log');

define('AUTOCOMPLETE_URL', '/autocomplete.php');

//The value from a solr field referencing an auto-incremented integer row from the data base that maps to the NULL value
define('SOLR_NULL', '0');

{
	//The NULL value for a URL param
	define('URL_PARAM_NULL', rawurlencode('-'));
	//The separator between items in most multivalued URL param
	define('URL_PARAM_SEPARATOR', '++');
	//The separator between query filters in a multivalued URL param
	define('QUERY_FILTER_SEPARATOR', '#');
}

{
	//The default solr document language if none is specified
	define('DEFAULT_SOLR_DOCUMENT_LANGUAGE', 'FR');
	//The default GUI language if none is specified
	define('DEFAULT_GUI_LANGUAGE', 'fr');
}

//The perl compatible regular expression word characters including accented ones that aren't matched with \w
define('PCRE_WORD_CHARACTERS', 'a-zA-ZàáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ0-9');

{
	//The printf mask to build a nesstar link to a study
	define('NESSTAR_STUDY_URL_MASK', '%s/webview/?v=2&study=%s%%2Fobj%%2FfStudy%%2F%s&mode=documentation&submode=ddi&node=0&top=yes');
	//The printf mask to build a nesstar link to a variable
	define('NESSTAR_VARIABLE_URL_MASK', '%s/webview/?v=2&study=%s/obj/fStudy/%s&mode=documentation&submode=variable&variable=%s/obj/fVariable/%s_%s&top=yes');
}

{
	define('ADVANCED_SEARCH_AND_OPERATOR', 0);
	define('ADVANCED_SEARCH_OR_OPERATOR', 1);
	define('ADVANCED_SEARCH_NOT_OPERATOR', 2);
	define('ADVANCED_SEARCH_ANALYSIS_ALL_TERMS_REQUIRED_TRUE', 1);
	define('ADVANCED_SEARCH_ANALYSIS_ALL_TERMS_REQUIRED_FALSE', 2);
	define('ADVANCED_SEARCH_ANALYSIS_PHRASE_TRUE', 3);
	define('ADVANCED_SEARCH_ANALYSIS_STARTS_WITH', 6);
	define('ADVANCED_SEARCH_ANALYSIS_LEVENSHTEIN', 7);
	define('ADVANCED_SEARCH_ANALYSIS_DISTANCE', 8);
}

{
	//A search that target the question field
	define('SEARCH_QUESTION', 1);
	//A search that target the modalities field
	define('SEARCH_MODALITIES', 2);
	//A search that target the variable label
	define('SEARCH_VARIABLE', 4); 
} // Example of combo : SEARCH_QUESTION & SEARCH_MODALITIES

//The number of item to open when displaying a question
define('MAX_ITEMS_OPENED', 1);

//The number of modalities to open when displaying a variable or a question
define('MAX_MODALITIES_OPENED', 5);

define('MAX_MATCHES_DISPLAY', 5);

//The number of variables and question to show on the question selection page
define('MAX_QUESTION_SELECTION_DISPLAY', 5);

//The number of facets to show by category in the dynamic facets menu
define('MAX_FACETS_DISPLAY', 5);

//The number of modalities to show on a variable page
define('MAX_MODALITIES_VARIABLE_PAGE', 50);

//The number of study to show on the study list page
define('MAX_STUDY_DISPLAY', 10);

//The number of crafted query for spellcheck suggestions to throw at the solr server
define('MAX_SPELLCHECK_QUERIES', 128);

//Set this to true to have all users from an unallowed IP addresse redirected to the maintenance page
{
	define('MAINTENANCE_MODE', false);
	//The allowed IP address during maintenance mode
	define('MAINTENANCE_ALLOW_IP', '193.54.67.93');
}

{
	//The minimum % below which a word doesn't appear in the study page word cloud
	define('TF_LOWER_BOUND', '0.3');
	define('TF_UPPER_BOUND', '100');
}

//A separator used in group concat between items
define('GC_MULTIPLE_VALUE_SEPARATOR','!<(^.^)>!');
