<?php

namespace Refulgent\ContaoLDAPSupportBundle;

class LdapUserModel extends LdapPersonModel
{
    protected static $strPrefix          = 'User';
    protected static $strLocalModel      = '\UserModel';
    protected static $strLocalGroupModel = '\UserGroupModel';
    protected static $strLdapModel       = 'Refulgent\ContaoLDAPSupportBundle\LdapUserModel';
    protected static $strLdapGroupModel  = 'Refulgent\ContaoLDAPSupportBundle\LdapUserGroupModel';
}