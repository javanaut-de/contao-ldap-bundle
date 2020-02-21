<?php

namespace Refulgent\ContaoLDAPSupportBundle\Legacy\Ldap\Backend;

class LdapUser extends LdapPerson
{
    protected static $strPrefix          = 'User';
    protected static $strLocalModel      = '\UserModel';
    protected static $strLocalGroupModel = '\UserGroupModel';
    protected static $strLdapModel       = 'Refulgent\ContaoLDAPSupportBundle\Legacy\Ldap\LdapUserModel';
    protected static $strLdapGroupModel  = 'Refulgent\ContaoLDAPSupportBundle\Legacy\Ldap\LdapUserGroupModel';
}