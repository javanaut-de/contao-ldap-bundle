<?php

namespace Refulgent\ContaoLDAPSupport;

use Refulgent\ContaoLDAPSupport\Ldap;

use Contao\CoreBundle\Monolog\ContaoContext;

class LdapPerson
{
    protected static $strPrefix          = '';
    protected static $strLdapModel       = '';
    protected static $strLocalModel      = '';
    protected static $strLdapGroupModel  = '';
    protected static $strLocalGroupModel = '';

    /**
     * importUser hook: Invoked when unknown
	 * user tried to login.
     */
    public function importPersonFromLdap($strUsername, $strPassword, $strTable)
    {
		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'strUsername' => $strUsername,
					'strPassword' => $strPassword,
					 'strTable' => $strTable));

        if (static::authenticateLdapPerson($strUsername, $strPassword))
        {
            $strLdapModelClass = static::$strLdapModel;
            static::createOrUpdatePerson(null, $strLdapModelClass::findByUsername($strUsername), $strUsername);

            return true;
        } else {
            return false;
        }
    }

    /**
     * checkCredentials hook: Invoked when user
	 * tried to login with invalid password.
	 *
	 * -> ldap password != contao password
	 *
	 * @return true triggers valid access in contao
     */
    public function authenticateAgainstLdap($strUsername, $strPassword, $objPerson)
    {
		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'strUsername' => $strUsername,
					'strPassword' => $strPassword,
					 'objPerson' => $objPerson));

        if (static::authenticateLdapPerson($strUsername, $strPassword))
        {
            // update since groups and/or mapped fields could have changed remotely
            $strLdapModelClass = static::$strLdapModel;
            static::createOrUpdatePerson($objPerson, $strLdapModelClass::findByUsername($strUsername), $strUsername);

            return true;
        }
        else
        {
            return false;
        }
    }

	// TODO use or remove
	public static function updatePeople($varValue) {

			if($varValue == false) {
                static::disableLdapPersons('User');
                static::disableLdapPersons('Member');
                static::disableLdapGroups('User');
                static::disableLdapGroups('Member');
            }
            
		return $varValue;
	}

	public static function disableLdapPersons($strPrefix) {

        $usernameFieldname = \Config::get('ldap' . $strPrefix . 'LdapUsernameField');
        $arrSkipUsernames = trimsplit(',', \Config::get('ldap' . $strPrefix . 'SkipLdapUsernames'));
        $strModelClass = '\\'.$strPrefix.'Model';
        $strLdapModelClass = 'Refulgent\\ContaoLDAPSupport\\Ldap'.$strPrefix.'Model';

		$arrLdapPersons = $strLdapModelClass::findAll(); 

        if(!empty($arrLdapPersons)) {
            foreach ($arrLdapPersons as $key => $ldapPerson)
            {
                if ($key == 'count' ||
                        $ldapPerson[$usernameFieldname] === null ||
                        $ldapPerson[$usernameFieldname]['count'] < 1) {
                    continue;
                }
                
                $strUsername = $ldapPerson[$usernameFieldname][0];

                $objPerson = $strModelClass::findBy('username', $strUsername);
                $objPerson->disable = true;
                $objPerson->save();
            }
        }
	}

	public static function disableLdapGroups($strPrefix) {
    
        // TODO Statische Klasse mit Klassennamen?
        $strLdapGroupClass  = 'Refulgent\\ContaoLDAPSupport\\Ldap'.$strPrefix.'GroupModel';
        $strGroupModelClass = '\\'.$strPrefix.'GroupModel';

        $arrLdapGroups  = $strLdapGroupClass::findAll();

        if(!empty($arrLdapGroups)) {
            foreach ($arrLdapGroups as $key => $ldapGroup) {

                $objGroup = $strGroupModelClass::findByDn($ldapGroup['dn'])->current();

                $objGroup->disable = true;
                $objGroup->save();
            }
        }
	}

	/*
	 * Checks if passed username exists and
	 * if username / password combination
	 * currently is valid in LDAP directory.
	 *
	 * @return true -> valid
	 */
    public static function authenticateLdapPerson($strUsername, $strPassword)
    {
		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'strUsername' => $strUsername,
					'strPassword' => $strPassword));

        $strLdapModelClass = static::$strLdapModel;
		// asoc array (uid, dn) with user data
        $arrPerson         = $strLdapModelClass::findByUsername($strUsername);
   
		// TODO simplify Ifs
        if(!$arrPerson) {
            die('Person not found.');
        }
        
        if ($arrPerson)
        {
        	/*
			 * ldap_bind(<link>,<dn/rdn(uid)>,<password>)
			 */
            if (!ldap_bind(Ldap::getConnection(strtolower(static::$strPrefix)), $arrPerson['dn'], $strPassword))
            {
                return false;
            }

            return true;
        }
        else
        {
            return false;
        }
    }

	/*
	 * @param arrSelectedGroups array of strings with dn of currently selected ldap groups
	 */
    public static function updatePersons($arrSelectedGroups)
    {
		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'arrSelectedGroups' => $arrSelectedGroups));

        $strLdapModelClass = static::$strLdapModel;
        $arrLdapPersons    = $strLdapModelClass::findAll();

        if (!is_array($arrLdapPersons)) {
            return;
        }

        $arrFoundDNs = [];

        $arrSkipUsernames = trimsplit(',', \Config::get('ldap' . static::$strPrefix . 'SkipLdapUsernames'));

        $strLocalModelClass = static::$strLocalModel;

        foreach ($arrLdapPersons as $strKey => $arrPerson)
        {
			// skip if entry is count or has no uid field
            if ($strKey == 'count'
                || $arrPerson[\Config::get(
                    'ldap' . static::$strPrefix . 'LdapUsernameField'
                )]['count'] < 1){
                continue;
            }

			$strDN = $arrPerson['dn'];
            
			// should be maximum 1 -> else a better filter has to be set
            $strUsername = $arrPerson[\Config::get('ldap' . static::$strPrefix . 'LdapUsernameField')][0];

            if (in_array($strUsername, $arrSkipUsernames)) {
                continue;
            }

            if (Ldap::usernameIsEmail() && !\Validator::isEmail($strUsername)) {
                continue;
            }

            // mark remotely missing persons as disabled
            $arrFoundDNs[] = $strDN;

            $objPerson = $strLocalModelClass::findBy('dn', $strDN);

            $objPerson = static::createOrUpdatePerson($objPerson, $arrPerson, $strUsername, $arrSelectedGroups);

            $objPerson->save();
        }

        // mark remotely missing persons as disabled
        if (($objPersons = $strLocalModelClass::findAll()) !== null)
        {
            while ($objPersons->next())
            {
                if ($objPersons->dn && !in_array($objPersons->dn, $arrFoundDNs))
                {
                    $objPersons->disable = true;
                    $objPersons->save();
                }
                else
                {
                    $objPersons->disable = false;
                    $objPersons->save();
                }
            }
        }
    }

	/*
	 * @param arrSelectedGroups array of strings with dn of currently selected ldap groups
	 */
    public static function createOrUpdatePerson($objPerson, $arrPerson, $strUsername, $arrSelectedGroups = null)
    {
		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'objPerson' => $objPerson,
					'arrPerson' => $arrPerson,
					'strUsername' => $strUsername,
					'arrSelectedGroups' => $arrSelectedGroups));

        $arrSelectedGroups  = $arrSelectedGroups ?: deserialize(\Config::get('ldap' . static::$strPrefix . 'Groups'), true);
        $strLocalModelClass = static::$strLocalModel;

        // create the person initially
        if ($objPerson === null)
        {
            $arrSkipUsernames = trimsplit(',', \Config::get('ldap' . static::$strPrefix . 'SkipLdapUsernames'));
            $strUN = $arrPerson[\Config::get('ldap' . static::$strPrefix . 'LdapUsernameField')][0];

            if (!is_array($arrPerson) || in_array($strUN, $arrSkipUsernames)) {
                return false;
            }

            $objPerson = new $strLocalModelClass();

            $objPerson->tstamp   = $objPerson->dateAdded = time();
            $objPerson->login    = true;
			$objPerson->dn       = $arrPerson['dn'];
            $objPerson->username = $strUsername;

            // store randomized password, so contao will always trigger the checkCredentials hook
            $objPerson->password = md5(time() . $strUsername);

            if (TL_MODE == 'BE')
            {
                $objPerson->showHelp = true;
                $objPerson->useRTE = true;
                $objPerson->useCE = true;
                $objPerson->thumbnails = true;
                $objPerson->backendTheme = 'flexible';
            }

            //static::addGroups($objPerson, $arrSelectedGroups);
            static::applyFieldMapping($objPerson, $arrPerson);
            static::applyDefaultValues($objPerson);

            if (isset($GLOBALS['TL_HOOKS']['ldapAddPerson']) && is_array($GLOBALS['TL_HOOKS']['ldapAddPerson']))
            {
                foreach ($GLOBALS['TL_HOOKS']['ldapAddPerson'] as $callback)
                {
                    $callback[0]->{$callback[1]}($objPerson, $arrSelectedGroups);
                }
            }
        } else {

            //static::addGroups($objPerson, $arrSelectedGroups);
            static::applyFieldMapping($objPerson, $arrPerson);
            static::applyDefaultValues($objPerson);

            // store randomized password, so contao will always trigger the checkCredentials hook
            $objPerson->password = md5(time() . $strUsername);

            if (isset($GLOBALS['TL_HOOKS']['ldapUpdatePerson']) && is_array($GLOBALS['TL_HOOKS']['ldapUpdatePerson']))
            {
                foreach ($GLOBALS['TL_HOOKS']['ldapUpdatePerson'] as $callback)
                {
                    $callback[0]->{$callback[1]}($objPerson, $arrSelectedGroups);
                }
            }
        }

        $objPerson->save();

        return $objPerson;
    }

    public static function applyFieldMapping($objPerson, $arrRemoteLdapPerson)
    {
		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'objPerson' => $objPerson,
					'arrRemoteLdapPerson' => $arrRemoteLdapPerson));

        // if a certain domain is specified in the person filter, this should be the reference if the person has multiple email entries
        preg_match(
            '#@(?P<domain>[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5})#is',
            \Config::get('ldap' . static::$strPrefix . 'PersonFilter'),
            $arrMatches
        );
        $strDomain = $arrMatches['domain'];

        foreach (deserialize(\Config::get('ldap' . static::$strPrefix . 'PersonFieldMapping'), true) as $arrMapping)
        {
            // special case email -> only one-to-one mapping possible
            if ($arrMapping['contaoField'] == 'email' && $strDomain)
            {
                if ($arrRemoteLdapPerson[$arrMapping['ldapField']]['count'] < 1) {
                    continue;
                }

                $arrMailFilter = preg_grep('#(.*)' . $strDomain . '#i', $arrRemoteLdapPerson[$arrMapping['ldapField']]);

                if (is_array($arrMailFilter) && !empty($arrMailFilter) && \Validator::isEmail($arrMailFilter[0]))
                {
                    // take first mail, that fits domain regxp
                    $objPerson->email = $arrMailFilter[0];
                }
            }
            else
            {
                $objPerson->{$arrMapping['contaoField']} = static::getLdapField($arrRemoteLdapPerson, $arrMapping['ldapField']);
            }
        }
    }

    private static function getLdapField($arrRemoteLdapPerson, $strLdapField)
    {
		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'arrRemoteLdapPerson' => $arrRemoteLdapPerson,
					'strLdapField' => $strLdapField));

        if (strpos($strLdapField, '%') !== false)
        {
            return preg_replace_callback(
                '@%[^%]*%@i',
                function ($arrPattern) use ($arrRemoteLdapPerson, $strLdapField)
                {
                    $strPattern = $arrPattern[0];
                    $strTag = rtrim(ltrim($strPattern, '%'), '%');

                    if ($arrRemoteLdapPerson[$strTag]['count'] > 0)
                    {
                        return $arrRemoteLdapPerson[$strTag][0];
                    }

                    return $strPattern;
                },
                $strLdapField
            );
        }
        else
        {
            if ($arrRemoteLdapPerson[$strLdapField]['count'] > 0)
            {
                return $arrRemoteLdapPerson[$strLdapField][0];
            }
        }

        return $strLdapField;
    }

    public static function applyDefaultValues($objPerson)
    {
		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'objPerson' => $objPerson));

        foreach (deserialize(\Config::get('ldap' . static::$strPrefix . 'DefaultPersonValues'), true) as $arrMapping)
        {
            $objPerson->{$arrMapping['field']} = $arrMapping['defaultValue'];
        }
    }
}