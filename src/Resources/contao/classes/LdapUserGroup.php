<?php

namespace Refulgent\ContaoLDAPSupport;

class LdapUserGroup extends LdapPersonGroup
{
    protected static $strPrefix          = 'User';
    protected static $strLocalModel      = '\UserModel';
    protected static $strLocalGroupModel = '\UserGroupModel';
    protected static $strLdapModel       = 'Refulgent\ContaoLDAPSupport\LdapUserModel';
    protected static $strLdapGroupModel  = 'Refulgent\ContaoLDAPSupport\LdapUserGroupModel';
}