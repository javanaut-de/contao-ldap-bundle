<?php

//$GLOBALS['TL_CONFIG']['displayErrors'] = true;

/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['user']['ldapLogin'] = 'Refulgent\ContaoLDAPSupportBundle\ModuleLdapLogin'; // ref Ldap.php

/**
 * Hooks
 */
if (TL_MODE == 'FE' && \Config::get('addLdapForMembers'))
{
    // order is correct
    $GLOBALS['TL_HOOKS']['importUser'][]       = ['Refulgent\ContaoLDAPSupportBundle\Backend\LdapMember', 'importPersonFromLdap'];
    $GLOBALS['TL_HOOKS']['checkCredentials'][] = ['Refulgent\ContaoLDAPSupportBundle\Backend\LdapMember', 'authenticateAgainstLdap'];
}

if (TL_MODE == 'BE' && \Config::get('addLdapForUsers'))
{
    // order is correct
    $GLOBALS['TL_HOOKS']['importUser'][]       = ['Refulgent\ContaoLDAPSupportBundle\Backend\LdapUser', 'importPersonFromLdap'];
    $GLOBALS['TL_HOOKS']['checkCredentials'][] = ['Refulgent\ContaoLDAPSupportBundle\Backend\LdapUser', 'authenticateAgainstLdap'];
}