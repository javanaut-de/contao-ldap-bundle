<?php

//$GLOBALS['TL_CONFIG']['displayErrors'] = true;

/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['user']['ldapLogin'] = 'Refulgent\ContaoLDAPSupport\ModuleLdapLogin';

/**
 * Hooks
 */
if (TL_MODE == 'FE' && \Config::get('addLdapForMembers'))
{
    // order is correct
    $GLOBALS['TL_HOOKS']['importUser'][]       = ['Refulgent\ContaoLDAPSupport\LdapMember', 'importPersonFromLdap'];
    $GLOBALS['TL_HOOKS']['checkCredentials'][] = ['Refulgent\ContaoLDAPSupport\LdapMember', 'authenticateAgainstLdap'];
}

if (TL_MODE == 'BE' && \Config::get('addLdapForUsers'))
{
    // order is correct
    $GLOBALS['TL_HOOKS']['importUser'][]       = ['Refulgent\ContaoLDAPSupport\LdapUser', 'importPersonFromLdap'];
    $GLOBALS['TL_HOOKS']['checkCredentials'][] = ['Refulgent\ContaoLDAPSupport\LdapUser', 'authenticateAgainstLdap'];
}