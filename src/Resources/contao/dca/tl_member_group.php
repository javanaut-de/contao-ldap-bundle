<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_member_group'];

/**
 * Fields
 */
$arrDca['fields']['dn'] = [
 'label'     => 'Member Group DN',
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array('maxlength'=>255),
    'sql' => "varchar(255) DEFAULT NULL"
];