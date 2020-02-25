<?php

namespace Refulgent\ContaoLDAPSupport;

use Contao\CheckBoxWizard;
use Contao\Form;
use Contao\Widget;

//use Refulgent\ContaoLDAPSupportBundle\Service\Logr;

//use Psr\Log\LoggerInterface;

//use Refulgent\ContaoLDAPSupportBundle\Logr;
//use Refulgent\ContaoLDAPSupportBundle\LogrFactory;

//use Symfony\Component\Debug\ErrorHandler;

//use Psr\Log\LogLevel;
use Contao\CoreBundle\Monolog\ContaoContext;

//use Psr\Log\LoggerInterface;

class LdapPersonGroup
{
    protected static $strPrefix          = '';
    protected static $strLdapModel       = '';
    protected static $strLocalModel      = '';
    protected static $strLdapGroupModel  = '';
    protected static $strLocalGroupModel = '';

	//private $container;
	//private $logger;

    /**
     * Constructor assigns service container to private container. 
     */
    //public function __construct() {

        //global $kernel;
       // $this->container = $kernel->getContainer();
		//$logger = $this->container->get('monolog.logger');
    //}


	//private $logger = null;

	//public function __construct(LoggerInterface $logger) {
		//$this->logger = $logger;
		//\System::log(json_encode($logger),'setLogger()','debug');
	//}

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

        //$strLdapGroupModel = static::$strLdapGroupModel;
        //$arrLdapGroups    = $strLdapGroupModel::findAll();

        if (!is_array($arrLdapGroups))
        {
            return [];
        }

        foreach ($arrLdapGroups as $strId => $arrGroup) {
			$encodedDN = \Input::encodeSpecialChars(base64_encode($arrGroup['dn']));
			$arrGroups[$encodedDN] = $arrGroup['cn'];
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
	
 
		if (!\Config::get('addLdapFor' . static::$strPrefix . 's')) {
			return $varValue;
		}

        $arrSelectedGroups = deserialize($varValue, true);
		
		$strLocalGroupModel = static::$strLocalGroupModel;
		$objGroup          = new $strLocalGroupModel();

		\System::getContainer()->get('logger')->error('yolo', [$objGroup]);

		\System::getContainer()->get('logger')->info('yolo', array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL), 'obj' => $objGroup));

		\System::getContainer()->get('ldap.logger')->logger->critical('!!');

		dump($varValue);

		//throw new \Exception('x');

        if (!empty($arrSelectedGroups))
        {

			foreach($arrSelectedGroups as $k => $v) {
				$arrSelectedGroups[$k] = base64_decode(\Input::decodeEntities($v));
			}

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
                        $objGroup->dn = $selectedDN;
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

            $strClass = 'Refulgent\ContaoLDAPSupportBundle\Backend\Ldap' . static::$strPrefix;

            $strClass::updatePersons($arrSelectedGroups);
        }

        return $varValue;
    }

	/*
	 * load_callback
	 */
    public static function loadPersonGroups($value, $container) {

		if($value !== null) {

        $arrSelectedGroups = deserialize($value);

			if($arrSelectedGroups !== null) {

				foreach($arrSelectedGroups as $k => $v) {
					$arrSelectedGroups[$k] = base64_decode(\Input::decodeEntities($v));
				}

				$value = serialize($arrSelectedGroups);
			}
		}
		return $value;
    }
}