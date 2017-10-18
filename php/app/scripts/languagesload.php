#! /usr/bin/php
<?php
/**
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 */
require_once 'inc/headers.php';

$translationLanguageMapper = new DB_Mapper_TranslationLanguage;
$translationMapper = new DB_Mapper_Translation;
$translationEntryMapper = new DB_Mapper_TranslationEntry;

$fr = new DB_Model_TranslationLanguage;

$fr->set_code('fr');
$fr->set_code_solr('FR');
$fr->set_enabled_gui(1);
$fr->set_enabled_solr(1);

$frLabelTranslation = new DB_Model_Translation;
$frLabelTranslationId = $translationMapper->save($frLabelTranslation);

$fr->set_label_translation_id($frLabelTranslationId);
$frId = $translationLanguageMapper->save($fr);

$frLabelTranslationEntry_fr = new DB_Model_TranslationEntry;
$frLabelTranslationEntry_fr->set_translation_id($frLabelTranslationId);
$frLabelTranslationEntry_fr->set_translation_language_id($frId);
$frLabelTranslationEntry_fr->set_translated_text('Français');

$translationEntryMapper->save($frLabelTranslationEntry_fr);


$en = new DB_Model_TranslationLanguage;

$en->set_code('en');
$en->set_code_solr('EN');
$en->set_enabled_gui(1);
$en->set_enabled_solr(1);

$enLabelTranslation = new DB_Model_Translation;
$enLabelTranslationId = $translationMapper->save($enLabelTranslation);

$en->set_label_translation_id($enLabelTranslationId);

$enId = $translationLanguageMapper->save($en);

$frLabelTranslationEntry_en = new DB_Model_TranslationEntry;
$frLabelTranslationEntry_en->set_translation_id($frLabelTranslationId);
$frLabelTranslationEntry_en->set_translation_language_id($enId);
$frLabelTranslationEntry_en->set_translated_text('French');
$translationEntryMapper->save($frLabelTranslationEntry_en);

$enLabelTranslationEntry_en = new DB_Model_TranslationEntry;
$enLabelTranslationEntry_en->set_translation_id($enLabelTranslationId);
$enLabelTranslationEntry_en->set_translation_language_id($enId);
$enLabelTranslationEntry_en->set_translated_text('English');

$enLabelTranslationEntry_fr = new DB_Model_TranslationEntry;
$enLabelTranslationEntry_fr->set_translation_id($enLabelTranslationId);
$enLabelTranslationEntry_fr->set_translation_language_id($frId);
$enLabelTranslationEntry_fr->set_translated_text('Anglais');

$translationEntryMapper->save($enLabelTranslationEntry_en);
$translationEntryMapper->save($enLabelTranslationEntry_fr);
