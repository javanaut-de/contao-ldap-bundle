<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_user'];

/**
 * Fields
 */
$arrDca['fields']['dn'] = [
    'label'     => 'User DN',
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array('maxlength'=>255),
    'sql' => "varchar(255) DEFAULT NULL",
];