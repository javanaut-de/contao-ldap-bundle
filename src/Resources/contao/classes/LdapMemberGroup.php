<?php

namespace Refulgent\ContaoLDAPSupport;

class LdapMemberGroup extends LdapPersonGroup
{
    protected static $strPrefix          = 'Member';
    protected static $strLocalModel      = '\MemberModel';
    protected static $strLocalGroupModel = '\MemberGroupModel';
    protected static $strLdapModel       = 'Refulgent\ContaoLDAPSupport\Backend\LdapMemberModel';
    protected static $strLdapGroupModel  = 'Refulgent\ContaoLDAPSupport\Backend\LdapMemberGroupModel';
}

echo LdapMemberGroup::class;