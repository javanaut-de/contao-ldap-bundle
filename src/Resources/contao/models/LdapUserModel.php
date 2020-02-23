<?php

namespace Refulgent\ContaoLDAPSupport;

class LdapUserModel extends LdapPersonModel
{
    protected static $strPrefix          = 'User';
    protected static $strLocalModel      = '\UserModel';
    protected static $strLocalGroupModel = '\UserGroupModel';
    protected static $strLdapModel       = 'Refulgent\ContaoLDAPSupport\LdapUserModel';
    protected static $strLdapGroupModel  = 'Refulgent\ContaoLDAPSupport\LdapUserGroupModel';
}

echo LdapUserModel::class;