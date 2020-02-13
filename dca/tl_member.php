<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_member'];

/**
 * Fields
 */
$arrDca['fields']['ldapUid'] = [
 'label'     => 'uid',
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array('maxlength'=>255),
    'sql' => "varchar(255) NOT NULL default ''",
];