#! /usr/bin/php
<?php
require_once 'inc/headers.php';

$translationMapper = new DB_Mapper_Translation;
$conceptListMapper = new DB_Mapper_ConceptList;
$conceptMapper = new DB_Mapper_Concept;
$translationEntryMapper = new DB_Mapper_TranslationEntry;


$cevipofCl = new DB_Model_ConceptList;

$titleTranslation = new DB_Model_Translation;
$descriptionTranslation = new DB_Model_Translation;

$titleTranslationId = $translationMapper->save($titleTranslation);
$descriptionTranslationId = $translationMapper->save($descriptionTranslation);

$cevipofCl->set_title_translation_id($titleTranslationId);
$cevipofCl->set_description_translation_id($descriptionTranslationId);

$clId = $conceptListMapper->save($cevipofCl);

$titleTranslationEntryFR = new DB_Model_TranslationEntry;
$titleTranslationEntryFR->set_translated_text('Liste de concept du CEVIPOF');
$titleTranslationEntryFR->set_translation_id($titleTranslationId);
$titleTranslationEntryFR->set_translation_language_id(1);

$descriptionTranslationEntryFR = new DB_Model_TranslationEntry;
$descriptionTranslationEntryFR->set_translated_text('');
$descriptionTranslationEntryFR->set_translation_id($descriptionTranslationId);
$descriptionTranslationEntryFR->set_translation_language_id(1);

$titleTranslationEntryEN = new DB_Model_TranslationEntry;
$titleTranslationEntryEN->set_translated_text('CEVIPOF\'s concept list');
$titleTranslationEntryEN->set_translation_id($titleTranslationId);
$titleTranslationEntryEN->set_translation_language_id(2);

$descriptionTranslationEntryEN = new DB_Model_TranslationEntry;
$descriptionTranslationEntryEN->set_translated_text('');
$descriptionTranslationEntryEN->set_translation_id($descriptionTranslationId);
$descriptionTranslationEntryEN->set_translation_language_id(1);

$translationEntryMapper->save($titleTranslationEntryFR);
$translationEntryMapper->save($titleTranslationEntryEN);
$translationEntryMapper->save($descriptionTranslationEntryFR);
$translationEntryMapper->save($descriptionTranslationEntryEN);

$concepts = array(
'Vote et décision électorale',
'Implication et participation politique',
'Aliénation politique et efficacité',
'Confiance et rapport aux normes',
'Positionnement politique',
'Enjeux',
'Acteurs politiques',
'Conjoncture économique et sociale',
'Conjoncture politique et institutions',
'Libéralisme et conservatisme culturel',
'Valeurs économiques et inégalités',
'Valeurs démocratiques et tolérance politique',
'Environnement/Energie/Développement durable/Sciences et techniques',
'Immigration/Xénophobie/Antisémitisme/Intégration',
'Europe/Construction et intégration européenne/Attitudes internationales',
'Médias',
'Connaissance et information',
'Socialisation et sociabilité',
'Identité/Identification territoriale et communautaire',
'Religion et religiosité',
'Renseignement signalétiques',
'Codes "administratifs et techniques"',
'Variables contextuelles'
);

$i = 0;

foreach($concepts as $concept)
{
	$titleTranslation = new DB_Model_Translation;

	$titleTranslationId = $translationMapper->save($titleTranslation);
	
	$titleTranslationEntryFR = new DB_Model_TranslationEntry;
	$titleTranslationEntryFR->set_translated_text($concept);
	$titleTranslationEntryFR->set_translation_id($titleTranslationId);
	$titleTranslationEntryFR->set_translation_language_id(1);
	$translationEntryMapper->save($titleTranslationEntryFR);
	
	$titleTranslationEntryEN = new DB_Model_TranslationEntry;
	$titleTranslationEntryEN->set_translated_text("$concept - EN");
	$titleTranslationEntryEN->set_translation_id($titleTranslationId);
	$titleTranslationEntryEN->set_translation_language_id(2);
	$translationEntryMapper->save($titleTranslationEntryEN);
	
	$concept = new DB_Model_Concept;
	$concept->set_position($i);
	$concept->set_title_translation_id($titleTranslationId);
	$concept->set_concept_list_id($clId);
	
	$conceptMapper->save($concept);
	$i++;
}