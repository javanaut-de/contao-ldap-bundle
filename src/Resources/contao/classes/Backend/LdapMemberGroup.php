<?php

namespace Refulgent\ContaoLDAPSupport;

class LdapMemberGroup extends LdapPersonGroup
{
    protected static $strPrefix          = 'Member';
    protected static $strLocalModel      = '\MemberModel';
    protected static $strLocalGroupModel = '\MemberGroupModel';
    protected static $strLdapModel       = 'Refulgent\ContaoLDAPSupportBundle\Backend\LdapMemberModel';
    protected static $strLdapGroupModel  = 'Refulgent\ContaoLDAPSupportBundle\Backend\LdapMemberGroupModel';
}