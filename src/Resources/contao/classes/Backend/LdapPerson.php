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
			->get('monolog.logger.contao')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'strUsername' => $strUsername,
					'strPassword' => $strPassword,
					 'strTable' => $strTable));

        if ($arrLdapPerson = static::authenticateLdapPerson($strUsername, $strPassword)) {

            // refresh groups
            $ldapGroupModelClass = 'Refulgent\\ContaoLDAPSupport\\Ldap'.static::$strPrefix.'GroupModel';
            $arrSelectedLdapGroups = $ldapGroupModelClass::findSelectedLdapGroups();
            $ldapGroupClass = 'Refulgent\\ContaoLDAPSupport\\Ldap'.static::$strPrefix.'Group';
            $ldapGroupClass::updateGroups($arrSelectedLdapGroups);

            static::updatePerson(
                $objLocalPerson = static::createPerson($arrLdapPerson),
                $arrLdapPerson,
                $arrSelectedLdapGroups);

            static::updateAssignedGroups($objLocalPerson, $arrSelectedLdapGroups);

            $objLocalPerson->save();
        }
   
        return $arrLdapPerson ? true : false;
    }

    /**
     * checkCredentials hook: Invoked when user
	 * tried to login with invalid password.
	 *
	 * -> ldap password != contao password
	 *
	 * @return LDAP-
     */
    public function authenticateAgainstLdap($strUsername, $strPassword, $objLocalPerson)
    {
		\System::getContainer()
			->get('monolog.logger.contao')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'strUsername' => $strUsername,
					'strPassword' => $strPassword,
					 'objPerson' => $objLocalPerson));

        // authenticateLdapPerson() will return null if authentication failed
        if ($arrLdapPerson = static::authenticateLdapPerson($strUsername, $strPassword)) {
            
            // update groups and/or mapped fields since they could have changed remotely
           $ldapGroupModelClass = 'Refulgent\\ContaoLDAPSupport\\Ldap'.static::$strPrefix.'GroupModel';
           $arrSelectedLdapGroups = $ldapGroupModelClass::findSelectedLdapGroups();
           $ldapGroupClass = 'Refulgent\\ContaoLDAPSupport\\Ldap'.static::$strPrefix.'Group';
           $ldapGroupClass::updateGroups($arrSelectedLdapGroups);

            static::updatePerson($objLocalPerson, $arrLdapPerson, $arrSelectedLdapGroups);

            static::updateAssignedGroups($objLocalPerson, $arrSelectedLdapGroups);

            $objLocalPerson->save();
        }

        return $arrLdapPerson ? true : false;
    }

	/*
	 * Checks if passed username exists and
	 * if username / password combination
	 * currently is valid in LDAP directory.
	 *
	 * @return LDAP-Result when valid, else null
	 */
    public static function authenticateLdapPerson($strUsername, $strPassword)
    {
		\System::getContainer()
			->get('monolog.logger.contao')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'strUsername' => $strUsername,
					'strPassword' => $strPassword));

        $strLdapModelClass = static::$strLdapModel;
		// asoc array (uid, dn) with user data
        $arrLdapPerson = $strLdapModelClass::findByUsername($strUsername);
      
        /*
		 * ldap_bind(<link>,<dn/rdn(uid)>,<password>)
		 *
		 * TODO ldap_close notwendig? Nutzen?
		 */
		try {
			ldap_bind(
                Ldap::getConnection(strtolower(static::$strPrefix)),
                $arrLdapPerson['dn'],
                $strPassword);

		} catch (\ErrorException $ee) {

            \System::getContainer()
                ->get('monolog.logger.contao')
                ->error(
                    'Exception '.__CLASS__.'::'.__FUNCTION__,
                    array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL)));
            
			return null;
		}
           
		return $arrLdapPerson;
    }

	/*
	 * TODO descriptions
	 */
    public static function updatePersons($arrSelectedLdapGroups) {

		\System::getContainer()
			->get('monolog.logger.contao')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'arrSelectedLdapGroups' => $arrSelectedLdapGroups));

        $arrUpdatedLdapDNs = [];

        $arrSkipUsernames = \StringUtil::trimsplit(',',
            \Config::get('ldap' . static::$strPrefix . 'SkipLdapUsernames'));
            
        $strLocalModelClass = static::$strLocalModel;

        $strLdapModelClass = static::$strLdapModel;
        if($arrLdapPersons = $strLdapModelClass::findAll()) { // current remote ldap persons

            // iterates all ldap persons
            foreach ($arrLdapPersons as $strKey => $arrLdapPerson)
            {
			    // skip if entry is count or has no uid field
                if ($strKey == 'count'
                    || $arrLdapPerson[\Config::get(
                        'ldap' . static::$strPrefix . 'LdapUsernameField'
                    )]['count'] < 1){
                    continue;
                }

			    $strDN = $arrLdapPerson['dn'];
                
			    // should be maximum 1 -> else a better filter has to be set
                $strUsername =
                    $arrLdapPerson[
                        \Config::get('ldap' . static::$strPrefix . 'LdapUsernameField')]
                            [0];

                if (in_array($strUsername, $arrSkipUsernames)) {
                    continue;
                }

                if (Ldap::usernameIsEmail() && !\Validator::isEmail($strUsername)) {
                    continue;
                }

                $collectionLocalPerson = $strLocalModelClass::findBy('dn', $strDN);

                if($collectionLocalPerson === null) {
                    $objLocalPerson = static::createPerson($arrLdapPerson) ;
                } else {
                    $objLocalPerson = $collectionLocalPerson->current();
                }

                static::updatePerson(
                    $objLocalPerson,
                    $arrLdapPerson,
                    $arrSelectedLdapGroups);

                $objLocalPerson->save();
            
                $arrUpdatedLdapDNs[] = $strDN;
            }
        }

        // mark remotely missing persons as disabled
        if (($collectionPersons = $strLocalModelClass::findAll()) !== null)
        {
            while ($collectionPersons->next()) {

                if(($strDN = $collectionPersons->dn) === null) {
                    continue;
                };

                if ($strDN !== null && !in_array($strDN, $arrUpdatedLdapDNs)) {
                    $collectionPersons->disable = true;
                    $collectionPersons->save();
                } else {
                    $collectionPersons->disable = false;
                    $collectionPersons->save();
                }
            }
        }
    }

    /*
     * TODO description
     */
    public static function createPerson($arrLdapPerson) {

        $arrSkipUsernames = \StringUtil::trimsplit(',', 
            \Config::get('ldap' . static::$strPrefix . 'SkipLdapUsernames'));

        $strUsername = $arrLdapPerson[\Config::get('ldap' . static::$strPrefix . 'LdapUsernameField')][0];

        if (!is_array($arrLdapPerson) || in_array($strUsername , $arrSkipUsernames)) {
            return null;
        }

        $strLocalModelClass = static::$strLocalModel;
        $objLocalPerson = new $strLocalModelClass();

        $objLocalPerson->tstamp   = $objLocalPerson->dateAdded = time();
        $objLocalPerson->login    = true;
        $objLocalPerson->dn       = $arrLdapPerson['dn'];
        $objLocalPerson->username = $strUsername;

        if (TL_MODE == 'BE') {

            $objLocalPerson->showHelp = true;
            $objLocalPerson->useRTE = true;
            $objLocalPerson->useCE = true;
            $objLocalPerson->thumbnails = true;
            $objLocalPerson->backendTheme = 'flexible';
        }

        return $objLocalPerson;
    }

	/*
	 * Updates a local person
     * 
     * // TODO Params aktualisieren
	 * @param objPerson Contao dataset of person
	 * @param arrPerson LDAP dataset of person 0:[count,0,dn,uid:[count,0]]
	 * @param strUsername // TODO useless?
	 * @param arrSelectedGroups array of strings with dn of currently selected ldap groups
	 */
    public static function updatePerson($objLocalPerson, $arrLdapPerson, $arrSelectedLdapGroups)
    {        
		\System::getContainer()
			->get('monolog.logger.contao')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'objLocalPerson' => $objLocalPerson,
                    'arrLdapPerson' => $arrLdapPerson));
                    
        if($objLocalPerson === null) {
            return;
        }

		static::applyFieldMapping($objLocalPerson, $arrLdapPerson);
		static::applyDefaultValues($objLocalPerson);

		/*
		 * store randomized password, so contao
		 * will always trigger the checkCredentials hook
		 *
		 * TODO fix serious security issue
         * TODO block local admin accounts here
         * to prevent accidential locking out
         * if something unforseen happens
		 */
        $objLocalPerson->password = md5(time() . $objLocalPerson->username);
        
        static::updateAssignedGroups($objLocalPerson, $arrSelectedLdapGroups);
    }

    public static function applyFieldMapping($objPerson, $arrRemoteLdapPerson)
    {
		\System::getContainer()
			->get('monolog.logger.contao')
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

        foreach (\StringUtil::deserialize(\Config::get('ldap' . static::$strPrefix . 'PersonFieldMapping'), true) as $arrMapping)
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
			->get('monolog.logger.contao')
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
        } else {
            if ($arrRemoteLdapPerson[$strLdapField]['count'] > 0) {
                return $arrRemoteLdapPerson[$strLdapField][0];
            }
        }

        return $strLdapField;
    }

    public static function applyDefaultValues($objPerson)
    {
		\System::getContainer()
			->get('monolog.logger.contao')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'objPerson' => $objPerson));

        foreach (\StringUtil::deserialize(\Config::get('ldap' . static::$strPrefix . 'DefaultPersonValues'), true) as $arrMapping)
        {
            $objPerson->{$arrMapping['field']} = $arrMapping['defaultValue'];
        }
    }

	/*
     * Updates groups assigned to local person
	 * that were imported from ldap directory.
	 * Only Groups that exist in passed array
	 * will be added, others are removed.
     *
     * @param       $objPerson
     */
    public static function updateAssignedGroups($objLocalPerson, $arrSelectedLdapGroups) {

		\System::getContainer()
			->get('monolog.logger.contao')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'objPerson' => $objLocalPerson,
					'arrSelectedGroups' => $arrSelectedLdapGroups));

        // Find local group IDs (not imported)
        $strLocalGroupModelClass = static::$strLocalGroupModel;
		$collectionLocalGroups = $strLocalGroupModelClass::findAll();

        $arrLocalGroupIds = [];
        if($collectionLocalGroups !== null) {
		    while ($collectionLocalGroups->next()) {
                if($collectionLocalGroups->dn === null) {
                    $arrLocalGroupIds[] = $collectionLocalGroups->id;
                }
            }
        }

//dump(['arrLocalGroupIds',$arrLocalGroupIds]);

		// Assigned
        $arrAssignedGroupIds = \StringUtil::deserialize($objLocalPerson->groups) ?: [];

		// Remote Assigned Ldap
		$strLdapClass = static::$strLdapModel;
		$arrRemoteAssignedLdapGroups = 
				$strLdapClass::findAssignedGroups(
                    $objLocalPerson->dn);

        // Local Assigned
        $arrLocalAssignedGroupIds =
            array_intersect(
                $arrLocalGroupIds,
                $arrAssignedGroupIds);

        $strLdapGroupModelClass = static::$strLdapGroupModel;
        $arrSelectedAssignedGroupIds =
            $strLdapGroupModelClass::getLocalLdapGroupIds(
                array_intersect(
                    $arrRemoteAssignedLdapGroups,
                    $arrSelectedLdapGroups));

        $arrUpdatedGroupIds =
            array_merge(
                $arrLocalAssignedGroupIds,
                $arrSelectedAssignedGroupIds);

        $objLocalPerson->groups = serialize($arrUpdatedGroupIds);
    }
}