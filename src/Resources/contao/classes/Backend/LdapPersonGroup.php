<?php

namespace Refulgent\ContaoLDAPSupport;

use Contao\CheckBoxWizard;
use Contao\Form;
use Contao\Widget;

use Contao\CoreBundle\Monolog\ContaoContext;



class LdapPersonGroup
{
    protected static $strPrefix          = '';
    protected static $strLdapModel       = '';
    protected static $strLocalModel      = '';
    protected static $strLdapGroupModel  = '';
    protected static $strLocalGroupModel = '';

	/*
	 * Die Methode wird vom Framework aufgerufen
	 * um das Auswahlfeld für die LDAP-Felder zu
	 * generieren.
	 *
	 * options_callback
	 */
    public static function getLdapPersonGroupsAsOptions()
    {
		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL)));

        $strLdapGroupModel = static::$strLdapGroupModel;
        $arrLdapGroups = $strLdapGroupModel::findAll();

        if (!is_array($arrLdapGroups)) {
            return [];
		}

		$arrGroups = [];
        foreach ($arrLdapGroups as $key => $arrGroup) {
			$encodedDN = \Input::encodeSpecialChars(base64_encode($arrGroup['dn']));
			$arrGroups[$encodedDN] = $arrGroup['cn'];
		}

        asort($arrGroups);

		\System::getContainer()
			->get('logger')
			->info('Result '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'arrGroups' => $arrGroups));

        return $arrGroups;
    }

    /**
     * Add/update local member groups as representation of remote ldap groups
     *
	 * save_callback
	 *
     * @param $varValue
     *
     * @return mixed
     */
    public static function updateLocalGroups($varValue)
    {
		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'varValue' => $varValue));
 
		if (!\Config::get('addLdapFor' . static::$strPrefix . 's')) {
			return $varValue;
		}

		// num array of strings with encoded dn
        $arrSelectedLdapGroups = deserialize($varValue, true);

        if (!empty($arrSelectedLdapGroups)) {

			foreach($arrSelectedLdapGroups as $k => $v) {
				$arrSelectedLdapGroups[$k] = base64_decode(\Input::decodeEntities($v));
			}
		}

		$strLdapGroupModel = static::$strLdapGroupModel;
		// array of array(cn,dn,persons[])
		$arrLdapGroups     = $strLdapGroupModel::findAll();

		// skip if no ldap grps present
		if (!is_array($arrLdapGroups) || empty($arrLdapGroups)) {
			return $varValue;
		}

		$strLocalGroupModel = static::$strLocalGroupModel;

		foreach ($arrLdapGroups as $ldapGroup) {

			$ldapDN = $ldapGroup['dn'];

			// Col mit 1 Objekt aus lokaler Gruppe
			$collectionGroup = $strLocalGroupModel::findByDn($ldapDN);

			// TODO optimieren
			$objGroup = null;

			if($collectionGroup !== null) {
				// Objekt aus lokaler Gruppe
				$objGroup = $collectionGroup->current();
			}

			if (in_array($ldapDN, $arrSelectedLdapGroups)) {
            
				if ($objGroup === null) {
					$objGroup = new $strLocalGroupModel();
					$objGroup->dn = $selectedLdapDN;
				}

				$objGroup->tstamp = time();

				foreach($arrLdapGroups as $group) {
					if($group['dn'] == $selectedLdapDN) {
                    	$objGroup->name   = $GLOBALS['TL_LANG']['MSC']['ldapGroupPrefix'] . $group['cn'];
                    }
				}

				$objGroup->disable = false;

			} else {
				if ($objGroup !== null) {
					$objGroup->disable = true;
				}
			}
			
			if ($objGroup !== null) {
				$objGroup->save();
			}
		}

		//$strClass = 'Refulgent\ContaoLDAPSupport\Ldap' . static::$strPrefix;
		//$strClass::updatePersons($arrSelectedGroups);

		return $varValue;
    }

	/*
	 * load_callback
	 */
    public static function loadPersonGroups($value, $container) {

		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'value' => $value,
					'container' => $container));

		if($value !== null) {

        $arrSelectedGroups = deserialize($value);

			if($arrSelectedGroups !== null && !empty($arrSelectedGroups)) {

				foreach($arrSelectedGroups as $k => $v) {
					$arrSelectedGroups[$k] = \Input::encodeSpecialChars(base64_encode($v));
				}

				$value = serialize($arrSelectedGroups);
			}
		}

		\System::getContainer()
			->get('logger')
			->info('Result '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'value' => $value));

		return $value;
    }

	/**
     * Adds active remote ldap group's local representation
	 * keeping the non ldap contao groups
     *
     * @param       $objPerson
     * @param       $arrSelectedGroups
     */
    public static function importGroups($arrSelectedGroups)
    {
		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'objPerson' => $objPerson,
					'arrSelectedGroups' => $arrSelectedGroups));

        $strLocalGroupClass = static::$strLocalGroupModel;
        $strLdapGroupClass  = static::$strLdapGroupModel;

        $objLocalLdapGroups  = $strLocalGroupClass::findBy(["(dn IS NOT NULL)"], null);

        if ($objLocalLdapGroups !== null)
        {
            $arrLocalLdapPersonGroups = $objLocalLdapGroups->fetchEach('dn');

            $objPerson->groups = serialize(
                array_merge(
                    array_diff($arrGroups, $arrLocalLdapPersonGroups), // non ldap local contao groups
                    $strLdapGroupClass::getLocalLdapGroupIds(array_intersect($arrRemoteLdapGroups, $arrSelectedGroups))
                )
            );
        }
    }
}