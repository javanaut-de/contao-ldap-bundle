<?php

namespace Refulgent\ContaoLDAPSupport;

use Contao\CoreBundle\Monolog\ContaoContext;

use Contao\Model\Collection;

abstract class LdapPersonGroupModel extends \Model
{

    protected static $arrRequiredAttributes = ['cn', 'uniqueMember']; // TODO dn?
    protected static $strPrefix             = '';
    protected static $strLdapModel          = '';
    protected static $strLocalModel         = '';
    protected static $strLdapGroupModel     = '';
    protected static $strLocalGroupModel    = '';

    /*
     * @return Array with ldap result objects or false when empty
     */
    public static function findAll(array $arrOptions = [])
    { 
		\System::getContainer()
			->get('monolog.logger.contao')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'arrOptions' => $arrOptions));

        $objConnection = Ldap::getConnection(strtolower(static::$strPrefix));

        if ($objConnection) {

            // TODO comment default mechanism
            $strFilterFieldName = 'ldap'.static::$strPrefix.'GroupFilter';

            if(!($strFilter = \Config::get($strFilterFieldName))) {
                $strFilter = '(objectClass=*)';
                
                // TODO DCA not available here when loggin in
                //$GLOBALS['TL_DCA']['tl_settings']
                //    ['fields'][$strFilterFieldName]['default'];
            }

			/*
			 * ldap_search(<link>, <dn>, <filter>, <fields>)
			 *
			 * filter: (string) Ldap Suchklausel (objectClass=*)
			 * fields: (array) Angeforderte Attribute ['dn','cn']
			 */
            try{
                $strQuery = ldap_search(
                    $objConnection,
                    \Config::get('ldap'.static::$strPrefix.'GroupBase'),
                    $strFilter,
                    static::$arrRequiredAttributes
                );
            } catch (\ErrorException $ee) {
                \System::getContainer()
                    ->get('monolog.logger.contao')
                    ->error('Exception occurred on LDAP search '.__CLASS__.'::'.__FUNCTION__,
                        array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
                            'error' => $ee));

                \Message::addError('Exception occurred on LDAP search');
                return false;
            }

            if (!$strQuery) {
                \Message::addError('LDAP query failed');
                return false;
            }

            $arrResult = ldap_get_entries($objConnection, $strQuery);
            
            if (!is_array($arrResult)) {
                return false;
            }

            $arrGroups = [];
            foreach ($arrResult as $key => $arrGroup) {      
                
                if ($key == 'count') {
                    continue;
                }
                
				// matching groupOfUniqueNames
                if (array_key_exists('uniquemember',$arrGroup)) {
                
					$arrGroups[] = [
						'dn' 	=> $arrGroup['dn'],
						'cn'   => $arrGroup['cn'][0],
                        'persons' => $arrGroup['uniquemember']['count'] > 0 ? $arrGroup['uniquemember'] : []
                    ];
				}
            }

			\System::getContainer()
				->get('monolog.logger.contao')
				->info('Result '.__CLASS__.'::'.__FUNCTION__,
					array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
						'arrGroups' => $arrGroups));

            return $arrGroups;
        } else {
            return false;
        }
    }

    public static function findAllImported() {

        $strLocalGroupClass = static::$strLocalGroupModel;
        $collectionLocalGroups = $strLocalGroupClass::findAll();

        $arrImportedLdapGroups = [];
        if($collectionLocalGroups !== null) {
            while ($collectionLocalGroups->next()) {
                if($collectionLocalGroups->dn !== null) {
                    $arrImportedLdapGroups[] = $collectionLocalGroups->current();
                }
            }
        }

        return new Collection($arrImportedLdapGroups, 'tl_user_group');
    }

    /*
	 * Translates array-wise passed DNs into
     * corresponding local group IDs
     * 
     * WARNING: Not valid until storage of submitted data is complete!
	 *
	 * @return Array of string containing DNs of Groups selected for import
	 */
    public static function findSelectedLdapGroups()
    {
        $arrSelectedLdapGroups = \StringUtil::deserialize(
           \Config::get('ldap' . static::$strPrefix . 'Groups'),  // TODO rename sql field: ldapPrefixSelectedGroups
            true);
    
		\System::getContainer()
			->get('monolog.logger.contao')
			->info('Result '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'arrSelectedLdapGroups' => $arrSelectedLdapGroups));
    
        return $arrSelectedLdapGroups;
    }

	/*
	 * Die Methode übersetzt arrayweise übergebene
	 * LDAP-DNs in Contao-GIDs.
	 *
	 * TODO Aktualität ist nicht immer gegeben
	 */
    public static function getLocalLdapGroupIds($arrRemoteLdapGroupDNs)
    {
		\System::getContainer()
			->get('monolog.logger.contao')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'arrRemoteLdapGroupDNs' => $arrRemoteLdapGroupDNs));

        $arrResult = [];
        foreach ($arrRemoteLdapGroupDNs as $currentGroupDN)
        {
            $strLocalGroupModelClass = static::$strLocalGroupModel;

            $objGroup = $strLocalGroupModelClass::findBy('dn', $currentGroupDN);
            if ($objGroup !== null)
            {
                $arrResult[] = $objGroup->id;
            }
        }

		\System::getContainer()
			->get('monolog.logger.contao')
			->info('Result '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'arrResult' => $arrResult));

        return $arrResult;
    }
}