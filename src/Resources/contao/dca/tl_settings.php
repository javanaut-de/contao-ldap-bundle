<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_settings'];

/**
 * Palettes
 */
if (extension_loaded('ldap'))
{
    $arrDca['palettes']['__selector__'][] = 'addLdapForMembers';
    $arrDca['palettes']['__selector__'][] = 'addLdapForUsers';
    $arrDca['palettes']['default']        =
        str_replace('{chmod_legend', '{ldap_legend},addLdapForMembers,addLdapForUsers;{chmod_legend', $arrDca['palettes']['default']);
}
else
{
    \Message::addInfo('LDAP PHP Extension not enabled, open your php.ini and uncomment "extension = php_ldap.dll".');
}

/**
 * Subpalettes
 */
// subpalettes are generated dynamically below
$arrDca['subpalettes']['addLdapForMembers'] = '';
$arrDca['subpalettes']['addLdapForUsers']   = '';


/**
 * Fields
 */

$arrFields = [
    'host'                => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['host'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['mandatory' => true, 'maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
    ],
    'port'                => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['port'],
        'exclude'   => true,
        'inputType' => 'text',
        'default'   => 389,
        'eval'      => ['mandatory' => true, 'maxlength' => 5, 'rgxp' => 'digit', 'tl_class' => 'w50'],
        'load_callback' => [['DefaultHandler', 'getDefaultValue']],
    ],
    'binddn'              => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['binddn'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['mandatory' => true, 'maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'long clr'],
    ],
    'password'            => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['password'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['mandatory' => true, 'maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
    ],
    'authMethod'          => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['authMethod'],
        'default'   => 'plain',
        'exclude'   => true,
        'inputType' => 'select',
        'options'   => ['ssl'],
        'eval'      => ['tl_class' => 'w50', 'includeBlankOption' => true],
        'load_callback' => [['DefaultHandler', 'getDefaultValue']],
    ],
    'personBase'          => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['personBase'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['mandatory' => true, 'maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
    ],
    'personFilter'        => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['personFilter'],
        'exclude'   => true,
        'inputType' => 'text',
        'default'   => '(objectClass=*)', //(&(objectClass=person)(objectClass=posixAccount))
        'eval'      => ['decodeEntities' => true, 'tl_class' => 'w50'],
        'load_callback' => [['DefaultHandler', 'getDefaultValue']],
    ],
    'ldapUsernameField'   => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['ldapUsernameField'],
        'exclude'   => true,
        'inputType' => 'text',
        'default'   => 'uid',
        'eval'      => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
    ],
    'ldapGroupMemberField'   => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['ldapGroupMemberField'],
        'exclude'   => true,
        'inputType' => 'text',
        'default'   => 'uniqueMember',
        'eval'      => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
        'load_callback' => [['DefaultHandler', 'getDefaultValue']],
    ],
    'skipLdapUsernames'   => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['skipLdapUsernames'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'long clr'],
    ],
    'personFieldMapping'  => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['personFieldMapping'],
        'inputType' => 'multiColumnEditor',
        'eval'      => [
            'multiColumnEditor' => [
                'minRowCount' => 0,
                'fields'      => [
                    'contaoField' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['contaoField'],
                        'inputType' => 'text',
                        'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
                    ],
                    'ldapField'   => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['ldapField'],
                        'inputType' => 'text',
                        'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
                    ],
                ],
            ],
        ],
        'sql'       => "blob NULL",
    ],
    'defaultPersonValues' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['defaultPersonValues'],
        'inputType' => 'multiColumnEditor',
        'eval'      => [
            'multiColumnEditor' => [
                'minRowCount' => 0,
                'fields'      => [
                    'field'        => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['field'],
                        'inputType' => 'text',
                        'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
                    ],
                    'defaultValue' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['defaultValue'],
                        'inputType' => 'text',
                        'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
                    ],
                ],
            ],
        ],
        'sql'       => "blob NULL",
    ],
    'groupBase'           => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['groupBase'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['mandatory' => true, 'maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
    ],
    'groupFilter'         => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['groupFilter'],
        'exclude'   => true,
        'inputType' => 'text',
        'default'   => '(objectClass=*)', //(&(objectClass=group))
        'eval'      => ['decodeEntities' => true, 'tl_class' => 'w50'],
        'load_callback' => [['DefaultHandler', 'getDefaultValue']],
    ],
    'groupFieldMapping'   => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['groupFieldMapping'],
        'inputType' => 'multiColumnEditor',
        'eval'      => [
            'multiColumnEditor' => [
                'minRowCount' => 0,
                'fields'      => [
                    'contaoField' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['contaoField'],
                        'inputType' => 'text',
                        'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
                    ],
                    'ldapField'   => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['ldapField'],
                        'inputType' => 'text',
                        'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
                    ],
                ],
            ],
        ],
        'sql'       => "blob NULL",
    ],
    'groups'              => [
        'label'            => &$GLOBALS['TL_LANG']['tl_settings']['groups'],
        'exclude'          => true,
        'filter'           => true,
        'inputType'        => 'checkboxWizard',
        'eval'             => ['maxlength' => 360, 'multiple' => true, 'tl_class' => 'long clr'],
        // save_callback and options_callback is set dynamically below
    ]
];

$arrDca['fields'] = array_merge(
    $arrDca['fields'],
    [
        'addLdapForMembers' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_settings']['addLdapForMembers'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true],
        ],
        'addLdapForUsers'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_settings']['addLdapForUsers'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true],
        ],
    ]
);

// dynamically add fields for members and users
foreach ($arrFields as $strField => $arrData) {

    if ($strField == 'groupFieldMapping') {
        continue;
    }

    
    $arrDca['fields']['ldapMember' . ucfirst($strField)] = $arrData;
    $arrDca['subpalettes']['addLdapForMembers'] .= 'ldapMember' . ucfirst($strField) . ',';

    $arrDca['fields']['ldapUser' . ucfirst($strField)] = $arrData;
    $arrDca['subpalettes']['addLdapForUsers'] .= 'ldapUser' . ucfirst($strField) . ',';
}

$arrDca['fields']['ldapMemberGroups']['options_callback'] = ['Refulgent\ContaoLDAPSupport\LdapMemberGroup', 'getLdapGroupsAsOptions'];
$arrDca['fields']['ldapMemberGroups']['save_callback']    = [['Refulgent\ContaoLDAPSupport\LdapMemberGroup', 'storeSettings']];
$arrDca['fields']['ldapMemberGroups']['load_callback']    = [['Refulgent\ContaoLDAPSupport\LdapMemberGroup', 'loadPersonGroups']];

$arrDca['fields']['ldapUserGroups']['options_callback'] = ['Refulgent\ContaoLDAPSupport\LdapUserGroup', 'getLdapGroupsAsOptions'];
$arrDca['fields']['ldapUserGroups']['save_callback']    = [['Refulgent\ContaoLDAPSupport\LdapUserGroup', 'storeSettings']];
$arrDca['fields']['ldapUserGroups']['load_callback']    = [['Refulgent\ContaoLDAPSupport\LdapUserGroup', 'loadPersonGroups']];


/*
 *
 */
class DefaultHandler {

    public static function getDefaultValue($value, DataContainer $container) {

        if(!$value) {
            $value = $GLOBALS['TL_DCA']
            ['tl_settings']['fields']
            [$container->field]['default'];
        }

        return $value;
    }
}




