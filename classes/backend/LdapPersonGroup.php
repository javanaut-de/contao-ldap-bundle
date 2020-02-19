<?php

namespace HeimrichHannot\Ldap\Backend;

use Contao\CheckBoxWizard;
use Contao\Form;
use Contao\Widget;
//use HeimrichHannot\Ldap;

//use Symfony\Component\Debug\ErrorHandler;

//use Psr\Log\LogLevel;
use Contao\CoreBundle\Monolog\ContaoContext;

class LdapPersonGroup
{
    protected static $strPrefix          = '';
    protected static $strLdapModel       = '';
    protected static $strLocalModel      = '';
    protected static $strLdapGroupModel  = '';
    protected static $strLocalGroupModel = '';

	//public $logger;

	//public function __construct() {
        //$c = \System::getContainer();

		//throw new \Exception("yolo!");
		
		
		//$logger->info('Look! I just used a service');
		//$logger->error('Ein Log-Eintrag',
			//array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL)));
		//throw new \Exception('x');
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

        $strLdapGroupModel = static::$strLdapGroupModel;
        $arrLdapGroups    = $strLdapGroupModel::findAll();

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
		//$handler = new ErrorHandler();
		//ErrorHandler::register($handler);
		//$handler->handleError(E_NOTICE,'yolo','x.php',77);

		//$logger->info('zuul',$varValue);

		//\System::getContainer()->get('logger')->error(yolo', array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL)));
		
		//$this->get('logger')->error('yolo', array('contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL)));

		//throw new \Exception('x');

		Logger::getLogger();

        if (!\Config::get('addLdapFor' . static::$strPrefix . 's')) {
            return $varValue;
        }

        $arrSelectedGroups = deserialize($varValue, true);

		dump($arrSelectedGroups);

		//throw new \Exception("y");

		//\System::log(json_encode($arrSelectedGroups),'$arrSelectedGroups','updatePersonGroups()');

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

            $strClass = 'HeimrichHannot\Ldap\Backend\Ldap' . static::$strPrefix;

            $strClass::updatePersons($arrSelectedGroups);
        }

        return $varValue;
    }

	/*
	 * load_callback
	 */
    public static function loadPersonGroups($value, $container) {

        $arrSelectedGroups = deserialize($value);

		if($arrSelectedGroups !== null) {

			foreach($arrSelectedGroups as $k => $v) {
				$arrSelectedGroups[$k] = base64_decode(\Input::decodeEntities($v));
			}

			$value = serialize($arrSelectedGroups);
		}

		return $value;
    }
}