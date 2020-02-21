<?php

//$GLOBALS['TL_CONFIG']['displayErrors'] = true;

/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['user'][\Refulgent\ContaoLDAPSupportBundle\Legacy\Ldap\Ldap::MODULE_LDAP_LOGIN] = 'HeimrichHannot\Ldap\ModuleLdapLogin';

/**
 * Hooks
 */
if (TL_MODE == 'FE' && \Config::get('addLdapForMembers'))
{
    // order is correct
    $GLOBALS['TL_HOOKS']['importUser'][]       = ['Refulgent\ContaoLDAPSupportBundle\Legacy\Ldap\Backend\LdapMember', 'importPersonFromLdap'];
    $GLOBALS['TL_HOOKS']['checkCredentials'][] = ['Refulgent\ContaoLDAPSupportBundle\Legacy\Ldap\Backend\LdapMember', 'authenticateAgainstLdap'];
}

if (TL_MODE == 'BE' && \Config::get('addLdapForUsers'))
{
    // order is correct
    $GLOBALS['TL_HOOKS']['importUser'][]       = ['Refulgent\ContaoLDAPSupportBundle\Legacy\Ldap\Backend\LdapUser', 'importPersonFromLdap'];
    $GLOBALS['TL_HOOKS']['checkCredentials'][] = ['Refulgent\ContaoLDAPSupportBundle\Legacy\Ldap\Backend\LdapUser', 'authenticateAgainstLdap'];
}