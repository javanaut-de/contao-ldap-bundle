<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_user_group'];

/**
 * Fields
 */
$arrDca['fields']['ldapGid'] = [
 'label'     => 'gid',
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array('maxlength'=>255),
    'sql' => "varchar(255) NOT NULL default ''"
];