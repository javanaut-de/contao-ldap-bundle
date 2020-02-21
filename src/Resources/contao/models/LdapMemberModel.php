<?php

namespace Refulgent\ContaoLDAPSupportBundle;

class LdapMemberModel extends LdapPersonModel
{
    protected static $strPrefix          = 'Member';
    protected static $strLocalModel      = '\MemberModel';
    protected static $strLocalGroupModel = '\MemberGroupModel';
    protected static $strLdapModel       = 'Refulgent\ContaoLDAPSupportBundle\LdapMemberModel';
    protected static $strLdapGroupModel  = 'Refulgent\ContaoLDAPSupportBundle\LdapMemberGroupModel';
}