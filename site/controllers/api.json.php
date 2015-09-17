<?php
/**
 * @version     1.0.0
 * @package     com_citybranding
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE Version 3; see LICENSE
 * @author      Ioannis Tsampoulatidis <tsampoulatidis@gmail.com> - https://github.com/itsam
 */

// No direct access.
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/controller.php';
require_once JPATH_COMPONENT_SITE . '/helpers/citybranding.php';
require_once JPATH_COMPONENT_SITE . '/helpers/MCrypt.php';
require_once JPATH_COMPONENT_SITE . '/models/tokens.php';

/**
 * CITYBRANDING API controller class.
 * Make sure you have mcrypt module enabled
 * e.g. $ sudo php5enmod mcrypt
 *
 * Every request should contain token, m_id, l
 * where *token* is the m-crypted "json_encode(array)" of username, password, timestamp, randomString in the following form:
 * {'u':'username','p':'plain_password','t':'1439592509','r':'i452dgj522'}
 * all casted to strings including the UNIX timestamp time()
 * where *m_id* is the modality ID according to the REST/API key definition in the administrator side
 * where *l* is the 2-letter language code used for for the responses translation (en, el, de, es, etc)
 *
 * Every token is allowed to be used ^only once^ to avoid MITM attacks
 *
 * Check helpers/MCrypt.php for details on how to use Rijndael-128 AES encryption algorithm
 *
 * Please note that for better security it is highly recommended to protect your site with SSL (https)
 */

class CitybrandingControllerApi extends CitybrandingController
{
    private $mcrypt;

    private $keyModel;

    function __construct()
    {
    	$this->mcrypt = new MCrypt();
        JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');
        $this->keyModel = JModelLegacy::getInstance( 'Key', 'CitybrandingModel', array('ignore_request' => true) );
    	parent::__construct();
    }

    public function exception_error_handler($errno, $errstr, $errfile, $errline){
        $ee = new ErrorException($errstr, 0, $errno, $errfile, $errline);
        JFactory::getApplication()->enqueueMessage($ee, 'error');
        throw $ee;
    }

    private function validateRequest($isNew = false)
    {
        $app = JFactory::getApplication();
        $token = $app->input->getString('token');
        $m_id  = $app->input->getInt('m_id');
        $l     = $app->input->getString('l');

        //1. check necessary arguments are exist
        if(is_null($token) || is_null($m_id) || is_null($l) ){
            $app->enqueueMessage('Either token, m_id (modality), or l (language) are missing', 'error');
            throw new Exception('Request is invalid');
        }

        //set language
        CitybrandingFrontendHelper::setLanguage($app->input->getString('l'), array('com_users', 'com_citybranding'));

        //check for nonce (existing token)
        if(CitybrandingModelTokens::exists($token)){
            throw new Exception('Token is already used');
        }

        //2. get the appropriate key according to given modality
        $result = $this->keyModel->getItem($m_id);
        $key = $result->skey;
        if(strlen($key) < 16){
            $app->enqueueMessage('Secret key is not 16 characters', 'error');
            throw new Exception('Secret key is invalid. Contact administrator');
        }
        else {
            $this->mcrypt->setKey($key);
        }

        //3. decrypt and check token validity
        $decryptedToken = $this->mcrypt->decrypt($token);
        $objToken = json_decode($decryptedToken);

        if(!is_object($objToken)){
            throw new Exception('Token is invalid');
        }

        if(!isset($objToken->u) || !isset($objToken->p) || !isset($objToken->t) || !isset($objToken->r)) {
            throw new Exception('Token is not well formatted');
        }

        //TODO: Set timeout at options
        if((time() - $objToken->t) > 3 * 60){
            throw new Exception('Token has expired');
        }

        //4. authenticate user
        $userid = JUserHelper::getUserId($objToken->u);
        $user = JFactory::getUser($userid);
		$userInfo = array();
		if ($isNew) {
			$userInfo['username'] =$objToken->u;
			$userInfo['password'] =$objToken->p;
		}
		else
		{
			if($objToken->u == 'citybranding-guest' && $objToken->p == 'citybranding-guest')
			{
				$userid = 0;
			}
			else
			{
		        $match = JUserHelper::verifyPassword($objToken->p, $user->password, $userid);
		        if(!$match){
		            $app->enqueueMessage('Either username or password do not match', 'error');
		            throw new Exception('Token does not match');
		        }

		        if($user->block){
		            $app->enqueueMessage('User is found but probably is not yet activated', 'error');
		            throw new Exception('User is blocked');
		        }
			}
		}

        //5. populate token table
        $record = new stdClass();
        $record->key_id = $m_id;
        $record->user_id = $userid;
        //$record->json_size = $json_size;
        $record->method = $app->input->getMethod();
        $record->token = $token;
        $record->unixtime = $objToken->t;
        CitybrandingModelTokens::insertToken($record); //this static method throws exception on error

        return $isNew ? $userInfo : (int)$userid;
    }

    public function languages()
    {
		$result = null;
		$app = JFactory::getApplication();
		try {
		    self::validateRequest();

			if($app->input->getMethod() != 'GET')
			{
			    throw new Exception('You cannot use other method than GET to fetch languages');
			}

            $availLanguages = JFactory::getLanguage()->getKnownLanguages();
            $languages = array();
            foreach ($availLanguages as $key => $value) {
                array_push($languages, $key);
            }

            $result = $languages;
			echo new JResponseJson($result, 'Languages fetched successfully');
		}
		catch(Exception $e)	{
			header("HTTP/1.0 202 Accepted");
			echo new JResponseJson($e);
		}
    }

	public function pois($json = true)
	{
		$result = null;
		$app = JFactory::getApplication();
		try {
		    $userid = self::validateRequest();

			if($app->input->getMethod() != 'GET')
			{
			    throw new Exception('You cannot use other method than GET to fetch pois');
			}

			//get necessary arguments
			$minLat = $app->input->getString('minLat');
			$maxLat = $app->input->getString('maxLat');
			$minLng = $app->input->getString('minLng');
			$maxLng = $app->input->getString('maxLng');
			$owned = $app->input->get('owned', false);
			$lim = $app->input->getInt('lim', 0);
			$ts = $app->input->getString('ts');

            //get pois model
            $poisModel = JModelLegacy::getInstance( 'Pois', 'CitybrandingModel', array('ignore_request' => true) );
            //set states
            $poisModel->setState('filter.owned', ($owned === 'true' ? 'yes' : 'no'));
            $poisModel->setState('filter.citybrandingapi.userid', $userid);
            //$poisModel->setState('filter.citybrandingapi.ordering', 'id');
            //$poisModel->setState('filter.citybrandingapi.direction', 'DESC');
            $poisModel->setState('list.limit', $lim);

			if(!is_null($minLat) && !is_null($maxLat) && !is_null($minLng) && !is_null($maxLng))
			{
				$poisModel->setState('filter.citybrandingapi.minLat', $minLat);
				$poisModel->setState('filter.citybrandingapi.maxLat', $maxLat);
				$poisModel->setState('filter.citybrandingapi.minLng', $minLng);
				$poisModel->setState('filter.citybrandingapi.maxLng', $maxLng);
			}

			if(!is_null($ts))
			{
				$poisModel->setState('filter.citybrandingapi.ts', $ts);
			}

            //handle unexpected warnings from model
            set_error_handler(array($this, 'exception_error_handler'));
			//get items and sanitize them
			$data = $poisModel->getItems();
			$result = CitybrandingFrontendHelper::sanitizePois($data, $userid);
			restore_error_handler();

			if($json)
			{
				echo new JResponseJson($result, 'Pois fetched successfully');
			}
			else
			{
				return $result;
			}
		}
		catch(Exception $e)	{
			header("HTTP/1.0 202 Accepted");
			echo new JResponseJson($e);
		}
	}	

	public function poi()
	{
		$result = null;
		$app = JFactory::getApplication();
		try {
		    $userid = self::validateRequest();
            //get necessary arguments
            $id = $app->input->getInt('id', null);

            switch($app->input->getMethod())
            {
                //fetch existing poi
                case 'GET':
                    if ($id == null){
                        throw new Exception('Id is not set');
                    }

                    //get poi model
                    $poiModel = JModelLegacy::getInstance( 'Poi', 'CitybrandingModel', array('ignore_request' => true) );

                    //handle unexpected warnings from model
                    set_error_handler(array($this, 'exception_error_handler'));
                    $data = $poiModel->getData($id);
                    restore_error_handler();

                    if(!is_object($data)){
                        throw new Exception('Poi does not exist');
                    }

                    $result = CitybrandingFrontendHelper::sanitizePoi($data, $userid);

                    //check for any restrictions
                    if(!$result->myPoi && $result->moderation){
                        throw new Exception('Poi is under moderation');
                    }
                    if($result->state != 1){
                        throw new Exception('Poi is not published');
                    }

                    //be consistent return as array (of size 1)
                    $result = array($result);

                break;
                //create new poi
                case 'POST':
                    if ($id != null){
                        throw new Exception('You cannot use POST to fetch poi. Use GET instead');
                    }

                    //guests are not allowed to post pois
                    //TODO: get this from settings
                    if($userid == 0)
                    {
                        throw new Exception('Guests are now allowed to post new pois');
                    }

                    //get necessary arguments
                    $args = array (
                        'catid' => $app->input->getInt('catid'),
                        'title' => $app->input->getString('title'),
                        'description' => $app->input->getString('description'),
                        'address' => $app->input->getString('address'),
                        'latitude' => $app->input->getString('lat'),
                        'longitude' => $app->input->getString('lng')
                    );
                    CitybrandingFrontendHelper::checkNullArguments($args);

                    //check if category exists
                    if( is_null(CitybrandingFrontendHelper::getCategoryNameByCategoryId($args['catid'])) )
                    {
                        throw new Exception('Category does not exist');
                    }

                    $args['userid'] = $userid;
                    $args['created_by'] = $userid;
                    $args['stepid'] = CitybrandingFrontendHelper::getPrimaryStepId();
                    $args['id'] = 0;
                    $args['created'] = CitybrandingFrontendHelper::convert2UTC(date('Y-m-d H:i:s'));
                    $args['updated'] = $args['created'];
                    $args['note'] = 'modality='.$app->input->getInt('m_id');
                    $args['language'] = '*';
                    $args['subgroup'] = 0;

                    $tmpTime = time(); //used for temporary id
                    $imagedir = 'images/citybranding';

                    //check if post contains files
                    $file = $app->input->files->get('files');
                    if(!empty($file))
                    {
                        require_once JPATH_ROOT . '/components/com_citybranding/models/fields/multiphoto/server/UploadHandler.php';
                        $options = array(
                                    'script_url' => JRoute::_( JURI::root(true).'/administrator/index.php?option=com_citybranding&task=upload.handler&format=json&id='.$tmpTime.'&imagedir='.$imagedir.'&'.JSession::getFormToken() .'=1' ),
                                    'upload_dir' => JPATH_ROOT . '/'.$imagedir . '/' . $tmpTime.'/',
                                    'upload_url' => JURI::root(true) . '/'.$imagedir . '/'.$tmpTime.'/',
                                    'param_name' => 'files',
                                    'citybranding_api' => true

                                );
                        $upload_handler = new UploadHandler($options);
                        if(isset($upload_handler->citybranding_api))
                        {
                            $files_json = json_decode($upload_handler->citybranding_api);
                            $args['photo'] = json_encode( array('isnew'=>1,'id'=>$tmpTime,'imagedir'=>$imagedir,'files'=>$files_json->files) );
                            $app->enqueueMessage('File(s) uploaded successfully', 'info');
                        }
                        else
                        {
                            throw new Exception('Upload failed');
                        }
                    }
                    else
                    {
                        $args['photo'] = json_encode( array('isnew'=>1,'id'=>$tmpTime,'imagedir'=>$imagedir,'files'=>array()) );
                    }

                    //get poiForm model and save
                    $poiFormModel = JModelLegacy::getInstance( 'PoiForm', 'CitybrandingModel', array('ignore_request' => true) );

                    //handle unexpected warnings from model
                    set_error_handler(array($this, 'exception_error_handler'));
                    $poiFormModel->save($args);
                    $insertid = JFactory::getApplication()->getUserState('com_citybranding.edit.poi.insertid');

                    //call post save hook
                    require_once JPATH_COMPONENT . '/controllers/poiform.php';
                    $poiFormController = new CitybrandingControllerPoiForm();
                    $poiFormController->postSaveHook($poiFormModel, $args);
                    restore_error_handler();

                    $result = array('poiid' => $insertid);
                break;
                //update existing poi
                case 'PUT':
                case 'PATCH':
                    if ($id == null){
                        throw new Exception('Id is not set');
                    }
                break;
                default:
                    throw new Exception('HTTP method is not supported');
            }

            echo new JResponseJson($result, 'Poi action completed successfully');
		}
		catch(Exception $e)	{
			header("HTTP/1.0 202 Accepted");
			echo new JResponseJson($e);
		}
	}

	public function steps($json = true)
	{
		$result = null;
		$app = JFactory::getApplication();
		try {
		    self::validateRequest();

			if($app->input->getMethod() != 'GET')
			{
			    throw new Exception('You cannot use other method than GET to fetch steps');
			}

			//get necessary arguments
			$ts = $app->input->getString('ts');

            //get steps model
            $stepsModel = JModelLegacy::getInstance( 'Steps', 'CitybrandingModel', array('ignore_request' => true) );
            //set states
            $stepsModel->setState('filter.state', 1);
            //$stepsModel->setState('filter.citybrandingapi.ordering', 'ordering');
            //$stepsModel->setState('filter.citybrandingapi.direction', 'ASC');

			if(!is_null($ts))
			{
				$stepsModel->setState('filter.citybrandingapi.ts', $ts);
			}

            //handle unexpected warnings from model
            set_error_handler(array($this, 'exception_error_handler'));
			//get items and sanitize them
			$data = $stepsModel->getItems();
			restore_error_handler();
			$result = CitybrandingFrontendHelper::sanitizeSteps($data);

			if($json){
				echo new JResponseJson($result, 'Steps fetched successfully');
			}
			else
			{
				return $result;
			}
		}
		catch(Exception $e)	{
			header("HTTP/1.0 202 Accepted");
			echo new JResponseJson($e);
		}
	}

	public function categories()
	{
		$result = null;
		$app = JFactory::getApplication();
		try {
		    self::validateRequest();

			if($app->input->getMethod() != 'GET')
			{
			    throw new Exception('You cannot use other method than GET to fetch categories');
			}

            //handle unexpected warnings from JCategories
            set_error_handler(array($this, 'exception_error_handler'));
            $result = CitybrandingFrontendHelper::getCategories(true);
			restore_error_handler();

			echo new JResponseJson($result, 'Categories fetched successfully');
		}
		catch(Exception $e)	{
			header("HTTP/1.0 202 Accepted");
			echo new JResponseJson($e);
		}
	}

	public function userexists()
	{
		$result = null;
		$usernameExists = false;
		$emailExists = false;

		$app = JFactory::getApplication();
		try {
		    self::validateRequest();

			if($app->input->getMethod() != 'GET')
			{
			    throw new Exception('You cannot use other method than GET to check userexists');
			}

            //get necessary arguments
            $args = array (
                'username' => $app->input->getString('username'),
                'email' => $app->input->getString('email')
            );
            CitybrandingFrontendHelper::checkNullArguments($args);
			$userid = JUserHelper::getUserId($args['username']);
			if($userid > 0)
			{
				$app->enqueueMessage('Username exists', 'info');
				$usernameExists = true;
			}

			if(CitybrandingFrontendHelper::emailExists($args['email']))
			{
				$app->enqueueMessage('Email exists', 'info');
				$emailExists = true;
			}

			$result = ($usernameExists || $emailExists);

            echo new JResponseJson($result, 'Check user action completed successfully');
		}
		catch(Exception $e)	{
			header("HTTP/1.0 202 Accepted");
			echo new JResponseJson($e);
		}

	}

	public function user()
	{
		$result = null;
		$app = JFactory::getApplication();

		try {
            switch($app->input->getMethod())
            {
                case 'GET':
					$userid = self::validateRequest();
                    $app->enqueueMessage('User is valid', 'info');
                    $result = array('userid' => $userid);

                break;
                //create new user
                case 'POST':
                    $userInfo = self::validateRequest(true);

					if(JComponentHelper::getParams('com_users')->get('allowUserRegistration') == 0) {
						throw new Exception('Registration is not allowed');
					}

                    //get necessary arguments
                    $args = array (
                        'name' => $app->input->getString('name'),
                        'email' => $app->input->getString('email')
                    );
                    CitybrandingFrontendHelper::checkNullArguments($args);

					//populate other data
                    $args['username'] = $userInfo['username'];
                    $args['password1'] = $userInfo['password'];
                    $args['email1'] = $args['email'];
                    $args['phone'] = $app->input->getString('phone', '');
                    $args['address'] = $app->input->getString('address', '');

                    //handle unexpected warnings from model
                    set_error_handler(array($this, 'exception_error_handler'));

					JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_users/models/');
					$userModel = JModelLegacy::getInstance( 'Registration', 'UsersModel');
					$result = $userModel->register($args);
					if (!$result)
					{
						throw new Exception($userModel->getError());
					}
                    restore_error_handler();

					if ($result === 'adminactivate')
					{
						$app->enqueueMessage(JText::_('COM_USERS_REGISTRATION_COMPLETE_VERIFY'), 'info');
					}
					elseif ($result === 'useractivate')
					{
						$app->enqueueMessage(JText::_('COM_USERS_REGISTRATION_COMPLETE_ACTIVATE'), 'info');
					}
					else
					{
						$app->enqueueMessage(JText::_('COM_USERS_REGISTRATION_SAVE_SUCCESS'), 'info');
					}

                break;
                //update existing poi
                case 'PUT':
                case 'PATCH':
                    $id = $app->input->getInt('id', null);
                    if ($id == null){
                        throw new Exception('Id is not set');
                    }
                break;
                default:
                    throw new Exception('HTTP method is not supported');
            }

            echo new JResponseJson($result, $msg = 'User action completed successfully');
		}
		catch(Exception $e)	{
			header("HTTP/1.0 202 Accepted");
			echo new JResponseJson($e);
		}
	}

	public function modifications()
	{
		$result = null;
		$app = JFactory::getApplication();
		try {
		    self::validateRequest();

			if($app->input->getMethod() != 'GET')
			{
			    throw new Exception('You cannot use other method than GET to fetch timestamp');
			}

            $args = array (
                'ts' => $app->input->getString('ts'),
            );
            CitybrandingFrontendHelper::checkNullArguments($args);

            if(!CitybrandingFrontendHelper::isValidTimeStamp($args['ts']))
            {
                throw new Exception('Invalid timestamp');
            }

            //handle unexpected warnings
            set_error_handler(array($this, 'exception_error_handler'));
			$result = self::getTimestamp($args['ts']);
            restore_error_handler();

			echo new JResponseJson($result, 'Updates since timestamp fetched successfully');
		}
		catch(Exception $e)	{
			header("HTTP/1.0 202 Accepted");
			echo new JResponseJson($e);
		}
	}

	private function getTimestamp($ts)
	{
		$app = JFactory::getApplication();

		$tsDate = date("Y-m-d H:i:s", $ts);
		$offsetDate = JDate::getInstance($tsDate, JFactory::getConfig()->get('offset') );

		$updates = array(
			'newestTS'   => $ts, //used mainly for backwards compatibility
			'ts'         => $ts,
			'offset'     => $offsetDate,
			'pois'     => null,
			'categories' => null,
			'steps'      => null,
			'votes'      => null,
			'comments'   => null,
		);

		$app->input->set('ts', $ts);
		//$updates['pois'] = self::pois(false);
		//$updates['categories'] = self::categories(false);
		//$updates['steps'] = self::steps(false);
		//$updates['votes'] = self::votes(false);
		//$updates['comments'] = self::comments(false);



		return $updates;
	}

}