<?php

namespace Refulgent\ContaoLDAPSupport;

class LdapMember extends LdapPerson
{
    protected static $strPrefix          = 'Member';
    protected static $strLocalModel      = '\MemberModel';
    protected static $strLocalGroupModel = '\MemberGroupModel';
    protected static $strLdapModel       = 'Refulgent\ContaoLDAPSupport\LdapMemberModel';
    protected static $strLdapGroupModel  = 'Refulgent\ContaoLDAPSupport\LdapMemberGroupModel';
}

echo LdapMember::class;