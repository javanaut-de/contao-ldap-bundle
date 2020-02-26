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
			->info('Invoke '.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL)));

        $arrGroups = [];

		// TODO Check remove
        /*if (!is_array($arrGroups))
        {
            return [];
        }*/

        $strLdapGroupModel = static::$strLdapGroupModel;
        $arrLdapGroups = $strLdapGroupModel::findAll();

        if (!is_array($arrLdapGroups)) {
            return [];
        }

        foreach ($arrLdapGroups as $strId => $arrGroup) {
			$encodedDN = \Input::encodeSpecialChars(base64_encode($arrGroup['dn']));
			$arrGroups[$encodedDN] = $arrGroup['cn'];
		}

        asort($arrGroups);

		\System::getContainer()
			->get('logger')
			->info('Result '.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'arrGroups' => $arrGroups));

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
		\System::getContainer()
			->get('logger')
			->info('Invoke '.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'varValue' => $varValue));
 
		if (!\Config::get('addLdapFor' . static::$strPrefix . 's')) {
			return $varValue;
		}

        $arrSelectedGroups = deserialize($varValue, true);

        if (!empty($arrSelectedGroups)) {

			foreach($arrSelectedGroups as $k => $v) {
				$arrSelectedGroups[$k] = base64_decode(\Input::decodeEntities($v));
			}

            $strLdapGroupModel = static::$strLdapGroupModel;
            $arrGroups         = $strLdapGroupModel::findAll();

            if (!is_array($arrGroups) || empty($arrGroups))
            {
                return $varValue;
            }

            $strLocalGroupModel = static::$strLocalGroupModel;
            foreach ($arrSelectedGroups as $selectedDN)
            {

				// TODO hier wird über cn statt über dn gematched

                if (in_array($selectedDN, array_keys($arrGroups)))
                {
                    //$decodedDN = base64_decode(str_replace('&#61;', '=', substr($selectedDN,0,strlen($selectedDN)-1)));
                    
                    if (($objGroup = $strLocalGroupModel::findByLdapGid($selectedDN)) === null) {
                        $objGroup = new $strLocalGroupModel();
                        $objGroup->dn = $selectedDN;
                    }

                    $objGroup->tstamp = time();

					$groupLabel = '';
					foreach($arrGroups as $group) {
						if($group['dn'] == $selectedDN) {
                    		$objGroup->name   = $GLOBALS['TL_LANG']['MSC']['ldapGroupPrefix'] . $group['cn'];
                    	}
					}

                    $objGroup->save();
                }
            }
        }

		$strClass = 'Refulgent\ContaoLDAPSupport\Ldap' . static::$strPrefix;
		$strClass::updatePersons($arrSelectedGroups);

        return $varValue;
    }

	/*
	 * load_callback
	 */
    public static function loadPersonGroups($value, $container) {

		\System::getContainer()
			->get('logger')
			->info('Invoke '.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'value' => $value,
					'container' => $container));

		if($value !== null) {

        $arrSelectedGroups = deserialize($value);

			if($arrSelectedGroups !== null) {

				foreach($arrSelectedGroups as $k => $v) {
					$arrSelectedGroups[$k] = base64_decode(\Input::decodeEntities($v));
				}

				$value = serialize($arrSelectedGroups);
			}
		}

		\System::getContainer()
			->get('logger')
			->info('Result '.__FUNCTION__,
				array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL),
					'value' => $value));

		return $value;
    }
}