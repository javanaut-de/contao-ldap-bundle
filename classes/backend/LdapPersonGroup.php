<?php

namespace HeimrichHannot\Ldap\Backend;

use Contao\CheckBoxWizard;
use Contao\Form;
use Contao\Widget;

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
        $arrGroups = [];

		// TODO Check remove
        /*if (!is_array($arrGroups))
        {
            return [];
        }*/

        $strLdapGroupModel = static::$strLdapGroupModel;
        $arrLdapGroups    = $strLdapGroupModel::findAll();

        if (!is_array($arrLdapGroups))
        {
            return [];
        }

        foreach ($arrLdapGroups as $strId => $arrGroup)
        {
            //$encodedDN = \Input::encodeSpecialChars(base64_encode(trim($arrGroup['dn'])));
            
            $arrGroups[$arrGroup['dn']] = trim($arrGroup['cn']);
        }

        asort($arrGroups);

        return $arrGroups;
    }

    /**
     * Add local member groups as representation of remote ldap groups
     *
	 * save_callback
	 *
     * @param $varValue
     *
     * @return mixed
     */
    public static function updatePersonGroups($varValue)
    {
        if (!\Config::get('addLdapFor' . static::$strPrefix . 's'))
        {
            return $varValue;
        }

        $arrSelectedGroups = deserialize($varValue, true);

		//\System::log(json_encode($arrSelectedGroups),'$arrSelectedGroups','updatePersonGroups()');

        if (!empty($arrSelectedGroups))
        {
            $strLdapGroupModel = static::$strLdapGroupModel;
            $arrGroups         = $strLdapGroupModel::findAll();

			//\System::log(json_encode($arrGroups),'arrGroups','updatePersonGroups()');

            if (!is_array($arrGroups) || empty($arrGroups))
            {
                return $varValue;
            }

            $strLocalGroupModel = static::$strLocalGroupModel;
            foreach ($arrSelectedGroups as $selectedDN)
            {

				//\System::log($selectedCN,'selectedCN','updatePersonGroups()');

				// TODO hier wird über cn statt über dn gematched

                if (in_array($selectedDN, array_keys($arrGroups)))
                {
                    //$decodedDN = base64_decode(str_replace('&#61;', '=', substr($selectedDN,0,strlen($selectedDN)-1)));
                    
                    if (($objGroup = $strLocalGroupModel::findByLdapGid($selectedDN)) === null)
                    {
                        $objGroup          = new $strLocalGroupModel();
                        $objGroup->ldapGid = $selectedDN;
                    }

                    $objGroup->tstamp = time();

					$groupLabel = '';
					foreach($arrGroups as $group) {
						if($group['dn'] == $selectedDN) {
                    		$objGroup->name   = $GLOBALS['TL_LANG']['MSC']['ldapGroupPrefix'] . $group['cn'];
                    	}
					}
                    
                    //\System::log(json_encode($arrGroups), 'LdapPersonGroup::updatePersonGroup', 'debug');

                    $objGroup->save();
                }
            }

            $strClass = 'HeimrichHannot\Ldap\Backend\Ldap' . static::$strPrefix;

            $strClass::updatePersons($arrSelectedGroups);
        }

        return $varValue;
    }

    public static function loadPersonGroups($value, $container) {

        //die('yo!');

        return $value;
    }
}