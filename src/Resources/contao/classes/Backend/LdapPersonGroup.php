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
	 * options_callback: Invoked when tl_settings
	 * backend form is created.
	 */
    public static function getLdapGroupsAsOptions()
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
			// asoc array dn->cn von ldap gruppen
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

	 /* save_callback: Invoked when tl_settings
	 * in backend was submitted and selection
	 * of ldap groups to be imported
	 * is stored.
	 *
     * @param $varValue LDAP groups selected to import in settings
     *
     * @return decoded dataset of selected groups
     */
	public static function storeSettings($varValue) {

		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'varValue' => $varValue));
			
		// num array of strings with encoded dn
		// of currently selected ldap groups
		$arrSelectedLdapGroups = \StringUtil::deserialize($varValue, true);
		
        if (!empty($arrSelectedLdapGroups)) {

			foreach($arrSelectedLdapGroups as $k => $v) {
				$arrSelectedLdapGroups[$k] = base64_decode(\Input::decodeEntities($v));
			}

			$varValue = serialize($arrSelectedLdapGroups);
		}

		// do nothing when ldap is deactivated
		if (!\Config::get('addLdapFor' . static::$strPrefix . 's')) {
			return $varValue;
		}

		// refresh groups
		static::updateLocalGroups($arrSelectedLdapGroups);

		$ldapPersonClass = 'Refulgent\\ContaoLDAPSupport\\Ldap'.static::$strPrefix;
		$ldapPersonClass::updatePersons();

		\System::getContainer()
			->get('logger')
			->info('Result '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'varValue' => $varValue));

		return $varValue;
	}

	/*
	 * load_callback: Invoked before tl_settings
	 * form in backend is opened. Delivers
	 * selection of ldap groups to be
	 * imported.
	 */
    public static function loadPersonGroups($value, $container) {

		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'value' => $value,
					'container' => $container));

		if($value !== null) {

        	$arrSelectedGroups = \StringUtil::deserialize($value);

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

    /*
     * Adds/updates local member groups
	 * as representation of remote ldap
	 * groups.
     *
	 * Local groups of which a pendant
	 * in LDAP Groups doesn't exist
	 " or are not to be imported anymore
	 * are disabled.
	 * 
	 * TODO Verschachtelte Ifs optimieren
	 *
	 * @param arrSelectedGroups Array of strings containing DNs
	 * of groups to be imported. All groups are imported
	 * when not passed.
	 */
    public static function updateLocalGroups($arrSelectedLdapGroups = null) {

		\System::getContainer()
			->get('logger')
			->info('Invoke '.__CLASS__.'::'.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'arrSelectedGroups' => $arrSelectedLdapGroups));

		$strLdapGroupModel = static::$strLdapGroupModel;
		// array of array(cn,dn,persons[])
		$arrLdapGroups     = $strLdapGroupModel::findAll();

		// skip if no ldap grps present
		if (!is_array($arrLdapGroups) || empty($arrLdapGroups)) {
			return;
		}

		$strLocalGroupModel = static::$strLocalGroupModel;

		foreach ($arrLdapGroups as $ldapGroup) {

			$ldapDN = $ldapGroup['dn'];

			// Col mit 1 Objekt aus lokaler Gruppe
			$collectionGroup = $strLocalGroupModel::findByDn($ldapDN);

			$objGroup = null;

			if($collectionGroup !== null) {
				// Objekt aus lokaler Gruppe
				$objGroup = $collectionGroup->current();
			}

			if (
				$arrSelectedLdapGroups === null ||
				in_array($ldapDN, $arrSelectedLdapGroups)) {
            
				if ($objGroup === null) {
					$objGroup = new $strLocalGroupModel();
					$objGroup->dn = $ldapDN;
				}

				$objGroup->tstamp = time();

				foreach($arrLdapGroups as $group) {
					if($group['dn'] == $ldapDN) {
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
    }
}