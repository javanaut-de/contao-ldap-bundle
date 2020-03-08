<?php

namespace Refulgent\ContaoLDAPSupport;

use Contao\CoreBundle\Monolog\ContaoContext;

use Contao\Model\Collection;

abstract class LdapPersonModel extends \Model
{
    protected static $arrRequiredAttributes = ['uid'];
    protected static $strPrefix             = '';
    protected static $strLdapModel          = '';
    protected static $strLocalModel         = '';
    protected static $strLdapGroupModel     = '';
    protected static $strLocalGroupModel    = '';

	/*
	 * Die Methode holt alle Personen aus einem
	 * LDAP-Benutzerverzeichnis und gibt diese
	 * als Array zurück.
	 *
	 * @return [count,0:[uid:[0],dn]] with ldap users or false
	 */
    public static function findAll(array $arrOptions = [])
    {
		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'arrOptions' => $arrOptions));

        if ($objConnection = Ldap::getConnection(strtolower(static::$strPrefix)))
        {
            $arrAttributes = static::$arrRequiredAttributes;
            $arrAttributes = static::addAttributes($arrAttributes);

            $strQuery = ldap_search(
                $objConnection,
                \Config::get('ldap' . static::$strPrefix . 'PersonBase'),
                \Config::get('ldap' . static::$strPrefix . 'PersonFilter'),
                $arrAttributes
            );

            if (!$strQuery) {
                return false;
            }

            $arrResult = ldap_get_entries($objConnection, $strQuery);

            if (!is_array($arrResult)) {
                return false;
            }

			\System::getContainer()
				->get('logger')
				->info('Result '.__CLASS__.'::'.__FUNCTION__,
					array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL), 
						'arrResult' => $arrResult));

            return $arrResult;
        }
        else
        {
            return false;
        }
    }

    /*public static function findByDn($strUserDN)
    {
		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'strUserDN' => $strUserDN));

        if ($objConnection = Ldap::getConnection(strtolower(static::$strPrefix)))
        {

            $strFilter = '(&(dn=' . $strUserDN . ')' . \Config::get(
                    'ldap' . static::$strPrefix . 'PersonFilter'
                ) . ')'; 

            $arrAttributes = static::$arrRequiredAttributes;
            $arrAttributes = static::addAttributes($arrAttributes);
   
            // search by username
            $strQuery = ldap_search($objConnection, \Config::get('ldap' . static::$strPrefix . 'PersonBase'), $strFilter, $arrAttributes);

            if (!$strQuery) {
            	die('findByDn: query failed');
                return null;
            }

            $arrResult = ldap_get_entries($objConnection, $strQuery);

            if (!is_array($arrResult) || empty($arrResult)) {
                return null;
            }

			\System::getContainer()
				->get('logger')
				->info('Result[0] '.__CLASS__.'::'.__FUNCTION__,
					array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
						'arrResult' => $arrResult));

            return $arrResult[0];
        } else {
            return null;
        }
    }*/

    /*public static function findAllImported() {

        $strLocalClass = static::$strLocalModel;
        $collectionLocalPersons = $strLocalClass::findAll();

        $arrImportedLdapPersons = [];
        if($collectionLocalPersons !== null) {
            while ($collectionLocalPersons->next()) {
                if($collectionLocalPersons->dn !== null) {
                    $arrImportedLdapPersons[] = $collectionLocalPersons->current();
                }
            }
        }

        return new Collection($arrImportedLdapPersons, 'tl_user');
    }*/

    public static function findByUsername($strUsername)
    {
		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'strUsername' => $strUsername));

        if ($objConnection = Ldap::getConnection(strtolower(static::$strPrefix)))
        {
            $strFilter = '(&(' . \Config::get('ldap' . static::$strPrefix . 'LdapUsernameField') . '=' . $strUsername . ')' . \Config::get(
                    'ldap' . static::$strPrefix . 'PersonFilter'
                ) . ')'; 

            $arrAttributes = static::$arrRequiredAttributes;
            $arrAttributes = static::addAttributes($arrAttributes);

            // search by username
            $strQuery = ldap_search($objConnection, \Config::get('ldap' . static::$strPrefix . 'PersonBase'), $strFilter, $arrAttributes);



            if (!$strQuery) {
            	die('findByUsername: query failed');
                return null;
            }

            $arrResult = ldap_get_entries($objConnection, $strQuery);

            if (!is_array($arrResult) || empty($arrResult)) {
                return null;
            }

			\System::getContainer()
				->get('logger')
				->info('Result[0] '.__CLASS__.'::'.__FUNCTION__,
					array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
						'arrResult' => $arrResult));

            return $arrResult[0];
        } else {
            return null;
        }
    }

    private static function addAttributes($arrAttributes)
    {
		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'arrAttributes' => $arrAttributes));

        foreach (\StringUtil::deserialize(\Config::get('ldap' . static::$strPrefix . 'PersonFieldMapping'), true) as $arrMapping)
        {
            if (strpos($arrMapping['ldapField'], '%') !== false)
            {
                preg_match_all('@%[^%]*%@i', $arrMapping['ldapField'], $arrMatches);

                foreach ($arrMatches[0] as $strTag) {
                    $arrAttributes[] = rtrim(ltrim($strTag, '%'), '%');
                }
            } else {
                $arrAttributes[] = $arrMapping['ldapField'];
            }
        }

		\System::getContainer()
			->get('logger')
			->info('Result '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'arrAttributes' => $arrAttributes));

        return $arrAttributes;
    }

    /*
     * @param strUserDN String with DN of user whose assigned groups shall be found
     * @return Num array of strings containing DNs of assigned groups
     */
    public static function findAssignedGroups($strUserDN)
    {
		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
                    'strUserDN' => $strUserDN));

        $strLdapGroupModelClass = static::$strLdapGroupModel;

        $arrRemoteLdapGroups = $strLdapGroupModelClass::findAll();

        $arrGroups = [];

        if (!is_array($arrRemoteLdapGroups)) {
            return $arrGroups;
        }

        foreach ($arrRemoteLdapGroups as $key => $arrGroup) {

            if (
                $key === 'count' || 
                array_search($strUserDN, $arrGroup['persons']) === false) {
                continue;
            }

            $arrGroups[] = $arrGroup['dn'];
        }

		\System::getContainer()
			->get('logger')
			->info('Result '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'arrGroups' => $arrGroups));

        return $arrGroups;
    }
}