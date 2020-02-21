<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_member'];

/**
 * Fields
 */
$arrDca['fields']['dn'] = [
 'label'     => 'Member DN',
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array('maxlength'=>255),
    'sql' => "varchar(255) DEFAULT NULL",
];