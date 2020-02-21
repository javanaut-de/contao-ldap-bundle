<?php

use DebugBar\StandardDebugBar;

namespace HeimrichHannot\Ldap;

abstract class LdapPersonGroupModel extends \Model
{

    protected static $arrRequiredAttributes = ['cn', 'uniqueMember']; // TODO dn?
    protected static $strPrefix             = '';
    protected static $strLdapModel          = '';
    protected static $strLocalModel         = '';
    protected static $strLdapGroupModel     = '';
    protected static $strLocalGroupModel    = '';

    public static function findAll(array $arrOptions = [])
    { 
        $objConnection = Ldap::getConnection(strtolower(static::$strPrefix));

        if ($objConnection)
        {

			/*
			 * ldap_search(<link>, <dn>, <filter>, <fields>)
			 *
			 * filter: (string) Ldap Suchklausel (objectClass=*)
			 * fields: (array) Angeforderte Attribute ['dn','cn']
			 */
            $strQuery = ldap_search(
                $objConnection,
                \Config::get('ldap'.static::$strPrefix.'GroupBase'), // 'ldap' . static::$strPrefix . 'Base'
                "(objectClass=*)",
                static::$arrRequiredAttributes
            );
            if (!$strQuery)
            {
            	die('ldap query failed');
                return false;
            }
            $arrResult = ldap_get_entries($objConnection, $strQuery);
            if (!is_array($arrResult))
            {
                return false;
            }
            $arrGroups = [];
            foreach ($arrResult as $strKey => $arrGroup)
            {
            	// cn = arrGroup['cn'][0]
            	// dn = arrGroup['dn']
            
               //\System::log($strKey . ' => ' . json_encode($arrGroup), 'groups','x');
            
                if ($strKey == 'count')
                {
                    continue;
                }
                
				// matching groupOfUniqueNames
                if (array_key_exists('uniquemember',$arrGroup)) {

					//\System::log(json_encode($arrGroup),'LdapPersonGroupModelfindAll()/$arrGroup','debug');
                
					$arrGroups[] = [
						'dn' 	=> $arrGroup['dn'],
						'cn'   => $arrGroup['cn'][0],
                        'persons' => $arrGroup['uniquemember']['count'] > 0 ? $arrGroup['uniquemember'] : []
                    ];
				}
            }

            return $arrGroups;
        }
        else
        {
            return false;
        }
    }

	/*
	 * Die Methode übersetzt arrayweise übergebene
	 * LDAP-GID in Contao-GIDs.
	 *
	 * TODO refactor
	 */
    public static function getLocalLdapGroupIds($arrRemoteLdapGroupIds)
    {
        $arrResult = [];
        foreach ($arrRemoteLdapGroupIds as $currentGid)
        {
            $strLocalGroupModelClass = static::$strLocalGroupModel;

            $objGroup = $strLocalGroupModelClass::findBy('dn', $currentGid);
            if ($objGroup !== null)
            {
                $arrResult[] = $objGroup->id;
            }
        }
        return $arrResult;
    }
}