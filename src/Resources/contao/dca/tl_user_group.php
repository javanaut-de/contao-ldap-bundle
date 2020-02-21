<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_user_group'];

/**
 * Fields
 */
$arrDca['fields']['dn'] = [
 'label'     => 'User Group DN',
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array('maxlength'=>255),
    'sql' => "varchar(255) DEFAULT NULL"
];