<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_member_group'];

/**
 * Fields
 */
$arrDca['fields']['ldapGid'] = [
 'label'     => 'gid',
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array('maxlength'=>255),
    'sql' => "varchar(255) NOT NULL DEFAULT ''"
];