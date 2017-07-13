<?php
/**
 * Functions for FMA class. In here so they don't
 * clutter up the main class.
 */

namespace FindMyAudience;

// Safe to use since shorthand since we are within a namespace
use \FindMyAudience\FMA_Global as FMA_Global;
use \FindMyAudience\postRequest as postRequest;
use \FindMyAudience\getRequest as getRequest;
use \FindMyAudience\curlRequest as curlRequest;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Session {

	private $Token = false;

	public static function setToken($Token) {
		return update_user_option( get_current_user_id(), 'fma_token', $Token );
	}

	public static function getToken() {
		return get_user_option( 'fma_token', get_current_user_id() );
	}
}

abstract class curlRequest {

	public $URL;
	public $Data = false;
	public $isPOST = false;
	public $keepAlive = false;
	public $returnHeaders = false;
	public $saveResponse = false;

	public static function getInstance() {
		return new self();
	}

	public function URL($URL) { $this->URL = $URL; return $this; }
	public function Data($Data) { $this->Data = $Data; return $this; }
	public function isPOST($isPOST) { $this->isPOST = $isPOST; return $this; }
	public function keepAlive($keepAlive) { $this->keepAlive = $keepAlive; return $this; }
	public function returnHeaders($returnHeaders) { $this->returnHeaders = $returnHeaders; return $this; }
	public function saveResponse($saveResponse) { $this->saveResponse = $saveResponse; return $this; }

	public function request() {

		try {

			if(is_array($this->Data)) $this->Data = json_encode($this->Data);

			// This is our login token for FMA. This replaces the $_SESSION['_fma_sessid'] variable.
			$Token = \FindMyAudience\Session::getToken();

			// We need a way to get the session ID in here
			// return either response, or error and die()

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,'----->curlRequest');
		    
			if(!strstr($this->URL,'http')) {
				$this->URL(\FindMyAudience::$Config->getValue('BaseURL').$this->URL);
			}

		    if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,$this->URL);
		    if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,print_r($this->Data,TRUE));

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $this->URL);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			// Set to 1 to enable HTTP response codes
			curl_setopt($ch, CURLOPT_HEADER, $this->returnHeaders);

			// Using an existing session to get an OK from the server
			// Remove this to generate a 'no sessionID in header' error
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: '.strlen( $this->Data ),
				'sessionId: '.$Token
				)
			);
			//if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,$Token);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

			//if(empty($Token)) {
				curl_setopt($ch, CURLOPT_HEADERFUNCTION, "\FindMyAudience\curlRequest::headerSearch");
			//}

			if( ($this->isPOST) AND ($this->Data) ) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $this->Data);
				curl_setopt($ch, CURLOPT_POST, 1);
			}

			$response = curl_exec($ch);	
			$httpCode = curl_getinfo ($ch, CURLINFO_HTTP_CODE );
			curl_close($ch);

			$response = (array) json_decode($response, JSON_NUMERIC_CHECK);

			// Assume true if we receive nothing, but a 200 httpCode
			if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,'----->curlRequest response '.$httpCode);

			if($httpCode != '200') {

				if(is_array($response) && (isset($response['error'])) ) {
					// If the server has returned an error, pass that through.
					if(isset($response['error'])) $response = "The API responded with an error. Response code: $httpCode. Error: ".$response['error']['message'];

					/** These are less useful in a class-based plugin. We probably don't need them. */
					if(!$this->saveResponse) {
						throw new \Exception( (is_array($response)) ? json_encode($response) : $response );
					}
					if(!$this->keepAlive) {
						//return $response;
						throw new \Exception( (is_array($response)) ? json_encode($response) : $response );
					}
				}

				if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"----->API responded with an http $httpCode, but there is no error message. Proceeding anyway.");
				//return false;
			}

			if(empty($response)) $response = true;

		} catch (Exception $e) {
			if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"# ----------> Received error ".$e->getMessage()." from ".__FUNCTION__);
			return array('error' => $e->getMessage());
		}

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__, "Response: ".(empty($response)) ? __FUNCTION__.' Response was empty.' : print_r($response,true) );

		return $response;
	}

	/**
	 * Fetches our login session ID from the FMA server.
	 */
	private function headerSearch($response, $headerLine) {

		if(!empty($headerLine)) {
			if(strstr($headerLine,'sessionId')) {
				if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"----> Found sessionId in $headerLine");
				$sessID = array_map('trim',explode(' ',$headerLine));
				$sessID = array_pop($sessID);
				if(!empty($sessID)) {
					if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,'----> Set sessId '.$sessID);
					\FindMyAudience\Session::setToken($sessID);
				}
			}
		}

		// No longer using cookies, don't need this.
		/*
		if (strstr($headerLine,'cookie')) {
		//if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,'cookie header line');
		//if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,print_r($headerLine,TRUE));
			$cookie = array_map('trim',explode(' ',$headerLine));
			$cookie = array_pop($cookie);
			if(!empty($cookie)) {
				$_SESSION['_fma_cookie'] = $cookie;
			}
		}
		*/

		return strlen($headerLine);
	}

}

class getRequest extends curlRequest {

	public static function getInstance() {
		return new self();
	}

	public function request() {
		$this->isPOST = false;
		return parent::request();
	}
}

class postRequest extends curlRequest {

	public static function getInstance() {
		return new self();
	}

	public function request() {
		$this->isPOST = true;
		return parent::request();
	}
}

class FMA_Global {

	/**
	 * Query FMA for blog categories and subcategories
	 * returns multidimensional array
	 *
	 * no nonce because we do not call this via POST
	 */
	public static function getPluginCategories() {
		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);
		$URL = \FindMyAudience::$Config->getValue('WPCategories');
		if( (!$URL) OR (!filter_var($URL,FILTER_VALIDATE_URL)) ) return false;

		return getRequest::getInstance()->URL($URL)->request();
	}

	/**
	 * Query FMA for the user's defined
	 * favorite/lead categories
	 */
	public static function getPluginUserCategories($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception(\FindMyAudience::FMA_NONCE_FAIL." ".__FUNCTION__);
		}


		#1) get User categories from DBd
		$cats = get_user_meta(get_current_user_id(), 'FMA_USER_CATEGORIES');

		if( !$cats ) {
			//Get default categories
			$cats = \FindMyAudience::$Config->getValue('DefaultUserCategories');
		} else {
			$cats = unserialize( $cats );
		}

		if(!$cats) throw new \Exception("No post categories could be found.");

		#3 return as JSON
		wp_send_json( $cats, JSON_NUMERIC_CHECK );
	}

	/**
	 * Return the name of a category given the ID
	 *
	 * No nonce because we do not call this via POST
	 */
	public static function getPluginCategoryName($ID, $Categories=FALSE) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if(!$Categories) {
			$Categories = json_decode(self::getPluginCategories(),JSON_NUMERIC_CHECK);
		}

		if($Categories) {
			foreach($Categories as $catData) {
				if($catData['id'] == $ID) {
					return $catData['name'];
					break;
				}
			}
		}

		return false;
	}

	/**
	 * Checks whether an email address is already registered with FMA
	 * returns boolean
	 *
	 * No nonce because we do not call this via POST
	 */
	public static function checkUserRegistration($Email) {

		if( (empty($Email)) OR (!filter_var($Email,FILTER_VALIDATE_EMAIL)) ) return false;

		$Data = json_encode( array('email' => $Email) );
		//$response = reset(json_decode( postRequest(\FindMyAudience::$Config->getValue('AccountURL'),$Data) ));
		$response = postRequest::getInstance()
			->URL( \FindMyAudience::$Config->getValue('AccountURL') )
			->Data($Data)
			->request();

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__, print_r($response,true) );

		if(!$response) return false;

		$return = ($response['validemail']) ? TRUE : FALSE;
		return $return;
	}

	/**
	 * Logs user into FMA, if we have previously configured our account
	 */
	public static function logInFMAUser($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception(\FindMyAudience::FMA_NONCE_FAIL." ".__FUNCTION__);
		}



		//if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,'logInFMAUser');
		$Data = json_encode(
			array(
				'email' => \FindMyAudience\FMA_Global::decryptString(get_user_option('fma_login_user',get_current_user_id())),
				'password' => \FindMyAudience\FMA_Global::decryptString(get_user_option('fma_login_pass',get_current_user_id())),
				'keepLoggedIn' => TRUE)
		);

		$response = postRequest::getInstance()
			->URL(\FindMyAudience::$Config->getValue('LoginURL'))
			->Data($Data);

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__, (empty($response)) ? __FUNCTION__.' Response was empty.' : print_r($response,true) );

		$response = $response->request();
		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__, (empty($response)) ? __FUNCTION__.' Response was empty.' : print_r($response,true) );

		if(!$response) return false;
		return $response;
	}

	/**
	 * Logs user out of FMA
	 */
	public static function logOutFMAUser($Nonce) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception(\FindMyAudience::FMA_NONCE_FAIL." ".__FUNCTION__);
		}

		postRequest::getInstance()
			->URL( \FindMyAudience::$Config->getValue('LogoutURL') )
			->Data(TRUE)
			->keepAlive(TRUE)
			->saveResponse(TRUE)
			->request();

		//update_user_option(get_current_user_id(),'fma_login_pass','');

		// Un-set our login token (this replaces $_SESSION['_fma_cookie'])
		update_user_option(get_current_user_id(),'fma_token','');
	}

	/**
	 * Check whether we are currently logged into FMA
	 *
	 * No nonce because we do not call this via POST
	 */
	public static function isFMAUserLoggedIn( $retry=TRUE ) {

		$hasSession = (!empty(get_user_option('fma_token'))) ? TRUE : FALSE;
		if( ! $hasSession ) return FALSE;
		//return $hasSession;

		// This will always return false the first time -- e.g. on initial page load.
		// For subsequent requests after that on the same page, it will return true.
		if(!\FindMyAudience::$isLoggedIn) {
			//Session is there - now we have to check if it's still valid
			$result = postRequest::getInstance()
				->URL(\FindMyAudience::$Config->getValue('ValidateSession'))
				->returnHeaders(TRUE)
				->saveResponse(TRUE)
				->keepAlive(TRUE)
				->request();

				if(!$result) {
					if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Result was: ".json_encode($result).". User is not logged in.");
					return false;
				}

				//if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,print_r($result,TRUE));
				if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__, (empty($result)) ? __FUNCTION__.' Response was empty. This usually means the user is logged in.' : print_r($result,true) );

		} else {
			$result = array(true);
		}
	    
	    //If there was an error, and the httpCode was 401, not logged in.
	    if( isset($result['error']) ) {
		if( $result['code'] == 401 ){
				//If user is not logged in, try and log them in.
				if( $retry == TRUE ) {

					$Nonce = \FindMyAudience::generateInternalNonce("logInFMAUser", __FUNCTION__);
					FMA_Global::logInFMAUser( $Nonce );
					//See if they're logged in again, but don't allow a retry
					return FMA_Global::isFMAUserLoggedIn( FALSE );
				}
		    return FALSE;
		}
	    }

		\FindMyAudience::$isLoggedIn = TRUE;

	    return TRUE;
	}

	/**
	 * Check whether the user has set up their account with FMA
	 *
	 * No nonce because we do not call this via POST
	 */
	public static function isFMAUserRegistered() {

		$fmaUser = get_user_option('fma_login_user');
		$fmaPass = get_user_option('fma_login_pass');

		if( (empty($fmaUser)) AND (empty($fmaPass)) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Queries FMA for the user's favorites
	 * returns multidimensional array
	 */
	public static function getUserFavorites($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception(\FindMyAudience::FMA_NONCE_FAIL." ".__FUNCTION__);
		}


		$response = getRequest::getInstance()
			->URL(\FindMyAudience::$Config->getValue('FavoritesURL'))
			->keepAlive(TRUE)
			->request();
		//return postRequest(\FindMyAudience::$Config->getValue('FavoritesURL'),TRUE);

		wp_send_json($response);
	}

	/**
	 * Get the FMA user data
	 */
	public static function getUserData($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception(\FindMyAudience::FMA_NONCE_FAIL." ".__FUNCTION__);
		}

		$response = getRequest::getInstance()
			->URL(\FindMyAudience::$Config->getValue('ProfileURL'))
			->saveResponse(TRUE)
			->request();
		//$URL = \FindMyAudience::$Config->getValue('ProfileURL');
		//return getRequest($URL,FALSE);

		if($Nonce) {
			wp_send_json($response);
		} else {
			return $response;
		}
	}


	/**
	 * Get the user's Twitter OAuth data
	 */
	public static function getTwitterAuth($Nonce=false) {

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception(\FindMyAudience::FMA_NONCE_FAIL." ".__FUNCTION__);
		}


		/**
		 * Use below if we are storing Twitter auth data locally
		 * (using separate OAuth data for WP/app)
		 */
			$TwitterAuth = get_user_option('fma_twitter_oauth', get_current_user_id());
			// We have to return true upon failure, otherwise the functions that use " if(!twitterAuth) "
			// will cause an infinitely failing loop and crash the browser
			if(empty($TwitterAuth)) wp_send_json(true);

		//	return json_encode($TwitterAuth);
			wp_send_json($TwitterAuth);

		/**
		 * Use below if we want to get Twitter auth data from FMA
		 */
		/*
			$Nonce = \FindMyAudience::generateInternalNonce("getUserData", __FUNCTION__);
			$UserData = (array) json_decode(\FindMyAudience\FMA_Global::getUserData($Nonce));

			$TwitterAuth['oauth_token'] = $UserData[0]->twitterAccessToken;
			$TwitterAuth['oauth_token_secret'] = $UserData[0]->twitterAccessSecret;
			//return json_encode($TwitterAuth);
			wp_send_json($TwitterAuth);
		*/
	}

	/**
	 * Delete Twitter credentials from local DB
	 */
	public static function deauthorizeTwitter($Nonce=false) {

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception(\FindMyAudience::FMA_NONCE_FAIL." ".__FUNCTION__);
		}

		update_user_option(get_current_user_id(),'fma_twitter_oauth','');
		$Data = json_encode( array('accesstoken' => '', 'accesssecret' => '') );
		$URL = \FindMyAudience::$Config->getValue('OAuthURL');
		$response = postRequest::getInstance()
			->URL($URL)
			->Data($Data)
			->keepAlive(TRUE)
			->returnHeaders(TRUE)
			->saveResponse(TRUE)
			->request();

		return true;
	}



	/**
	 * Encrypt a string using the unique hash ID
	 * generated during installation
	 * returns string
	 *
	 * No nonce because we do not call this via POST
	 */
	public static function encryptString($Str) {
		$Key = \FindMyAudience::$Config->getValue('passwordsalt');
		return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($Key), $Str, MCRYPT_MODE_CBC, md5(md5($Key))));
	}

	/**
	 * Decrypt a string using the unique hash ID
	 * generated during installation
	 * returns string
	 *
	 * No nonce because we do not call this via POST
	 */
	public static function decryptString($Str) {
		if(!empty($Str)) {
			$Key = \FindMyAudience::$Config->getValue('passwordsalt');
			return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($Key), base64_decode($Str), MCRYPT_MODE_CBC, md5(md5($Key))));
		}
	}
}

/**
 * Placeholder class for the functions that actually
 * interact with the FMA server, using the social media APIs.
 */
abstract class FMA_API_User {
}

class Twitter extends FMA_API_User {

	/**
	 * Get the audience for a NEW post, or for any post for the first time.
	 */
	public static function findMyAudience($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		$Data = $_POST;
		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,print_r($Data,true));

		/* =============================================================== */

		/**
		 * Make sure the user has registered with FMA
		 */
		if( \FindMyAudience\FMA_Global::isFMAUserRegistered() === FALSE) {
			throw new \Exception(\FindMyAudience::FMA_ERR_NO_USER);
			die();
		}

		/**
		 * Log in if we have no FMA session
		 */
		if( \FindMyAudience\FMA_Global::isFMAUserLoggedIn() === FALSE) {

			$Nonce = \FindMyAudience::generateInternalNonce("logInFMAUser", __FUNCTION__);
			\FindMyAudience\FMA_Global::logInFMAUser($Nonce);
		}

		if(!isset($Data['wp_categories'][0]) AND (!isset($Data['wp_tags'][0]))) {
			throw new \Exception('Please add a category or tag.');
			die();
		}

		if(isset($Data['wp_categories'][0])) {
			foreach($Data['wp_categories'] as $c) {
				$catData = get_category($c);
				if($catData) {
					$postCategories[$c] = $catData->name; // WP already sanitized
					if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"---> Looking for $c");
					if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"----> Found $catData->name");
				}
			}

			$Data['wp_categories'] = array_values($postCategories);
		}



		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__," === Requesting fresh audience search from FMA.....");

		/**
		 * Send search query to FMA
		 */
	    //
		$URL = \FindMyAudience::$Config->getValue('AppURL');
		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);

		$response = postRequest::getInstance()
			->URL($URL)
			->Data($Data)
			->isPOST(TRUE)
			->request();

			//->keepAlive(TRUE)
			//->saveResponse(TRUE)

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,'Return from calling '.__FUNCTION__);
		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,$URL);
		//if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,print_r($response,TRUE));

		/* Added 2016-10-25 */
			/* 'Cache' this post, marking it as having had an audience
			 * found -- but ONLY for this user, because the audience will not
			 * be accessible cross-user
			 */
			$ID = $Data['blog_post_id'];
			$user_id = get_current_user_id();
			add_post_meta($ID, "fma_is_cached_{$user_id}", TRUE, true);
			if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Marking post ID #{$ID} as 'fma_is_cached_{$user_id}'");
		/* End */


		wp_send_json($response);
	}

	/**
	 * Load an audience from a previously-searched post.
	 */
	public static function loadAudience($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		/* =============================================================== */

		/**
		 * Make sure the user has registered with FMA
		 */
		if( \FindMyAudience\FMA_Global::isFMAUserRegistered() === FALSE) {
			throw new \Exception(\FindMyAudience::FMA_ERR_NO_USER);
			die();
		}

		/**
		 * Log in if we have no FMA session
		 */
		if( \FindMyAudience\FMA_Global::isFMAUserLoggedIn() === FALSE) {
			$Nonce = \FindMyAudience::generateInternalNonce("logInFMAUser", __FUNCTION__);
			\FindMyAudience\FMA_Global::logInFMAUser($Nonce);
		}

		/**
		 * Send search query to FMA
		 */
		//
	    
		$postID = intval($_POST['blog_post_id']);
		if(empty($postID)) throw new \Exception("An invalid post ID was given.");

		$user_id = get_current_user_id();
		$isSaved = get_post_meta($postID, "fma_is_cached_{$user_id}");

		if(!$isSaved) {
			if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Post ID #$postID is not cached for user ID $user_id, but 'loadAudience' has been requested; calling for new search results...");
			$UniqueNonce = wp_create_nonce( \FindMyAudience::$Config->getValue('nonce_check')."findMyAudience");
			return self::findMyAudience($UniqueNonce);
		}



		$URL = \FindMyAudience::$Config->getValue('LoadAudience');
		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);

		$URL = "$URL/{$postID}";

		$response = getRequest::getInstance()->URL($URL)->request();
		//$response = array('0' => array('audience' => 'test data') );
		/*
		if(!file_exists('/tmp/results.txt')) {
			throw new \Exception("File /tmp/results.txt does not exist");
		}

		$response = json_decode( file_get_contents('/tmp/results.txt'), JSON_NUMERIC_CHECK );
		*/
		wp_send_json($response);
	}


	/**
	 * Get search results for Twitter
	 */
	public static function getTwitterResults($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		/* =============================================================== */



		$URL = \FindMyAudience::$Config->getValue('TwitterPeople');
		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);
		$TwitterResults['People'] = json_decode(getRequest::getInstance()->URL($URL));

		$URL = \FindMyAudience::$Config->getValue('TwitterConvos');
		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);
		$TwitterResults['Conversations'] = json_decode(getRequest::getInstance()->URL($URL));

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,'RETURN getTwitterResults ');
		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,print_r($TwitterResults,TRUE));

		//return $TwitterResults;
		wp_send_json($TwitterResults);
	}

	/**
	 * Favorite a user/convo
	 */
	public static function favoriteTile($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		$Data = $_POST['_data'];

		/* =============================================================== */


		$URL = \FindMyAudience::$Config->getValue('AddFaveURL');
		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);

		$response = postRequest::getInstance()
			->URL($URL)
			->Data($Data)
			->request();

			//->keepAlive(TRUE)
			//->saveResponse(TRUE)

		wp_send_json($response);
	}

	public static function unfavoriteTile($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		$Data = $_POST['_data'];

		/* =============================================================== */


		$URL = \FindMyAudience::$Config->getValue('DelFaveURL');
		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);

		$response = postRequest::getInstance()
			->URL($URL)
			->Data($Data)
			->request();

			//->keepAlive(TRUE)
			//->saveResponse(TRUE)

		wp_send_json($response);
	}

	/**
	 * Follow / unfollow user
	 */
	public static function followUser($Nonce=false) {
		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		$Data = $_POST['_data'];

		/* =============================================================== */


		$URL = \FindMyAudience::$Config->getValue('FollowURL');
		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);

		$response = postRequest::getInstance()
			->URL($URL)
			->Data($Data)
			->request();

			//->keepAlive(TRUE)

		//return json_encode($response);
		wp_send_json($response);
	}

	public static function unfollowUser($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		$Data = $_POST['_data'];

		/* =============================================================== */


		$URL = \FindMyAudience::$Config->getValue('UnfollowURL');
		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);

		$response = postRequest::getInstance()
			->URL($URL)
			->Data($Data)
			->request();

			//->keepAlive(TRUE)

		//return json_encode($response);
		wp_send_json($response);
	}



	public static function savePostProfile($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}


		$ID = intval($_POST['blog_post_id']);
		if(empty($ID)) throw new \Exception("An invalid post ID was given.");

		$postCategories = array();
		if( (isset($_POST['wp_categories'])) AND (is_array($_POST['wp_categories'])) AND (!empty($_POST['wp_categories'])) ) {
			$postCategories = array_filter( array_map('intval',$_POST['wp_categories']) );
		}

		if(!empty($postCategories)) {
			foreach($postCategories as $c) {
				$catData = get_category($c);
				if($catData) {
					$updateCategories[$c] = $catData->name; // WP already sanitized
					if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"---> Looking for $c");
					if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"----> Found $catData->name");
				}
			}
		}

		if(!isset($updateCategories)) $updateCategories = false;

		add_post_meta( $ID, 'wp_categories', $updateCategories, true ) || update_post_meta( $ID, 'wp_categories', $updateCategories );

		$updateTags = is_array($_POST['wp_tags']) ? array_map('sanitize_text_field',array_filter($_POST['wp_tags'])) : '';
		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Setting tags to ".json_encode($updateTags));

		add_post_meta( $ID, 'wp_tags', $updateTags, true ) || update_post_meta( $ID, 'wp_tags', $updateTags );

		return true;
	}

	/**
	 * Get recent tweets for a user (live)
	 */
	public static function requestRecentTweets($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		$Data = $_POST['_data'];

		/* =============================================================== */


		$ID = $Data['id'];
		$URL = \FindMyAudience::$Config->getValue('TweetsURL');
		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);
		$URL = "$URL/$ID";

		$response = postRequest::getInstance()->URL($URL)->request();
		//return $response;
		wp_send_json($response);
	}

	/**
	 * Conversation search terms
	 */
	public static function requestSearchTerms($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		$Data = $_POST['_data'];

		/* =============================================================== */


		$ID = $Data['id'];
		$URL = \FindMyAudience::$Config->getValue('TermsURL');
		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);
		$URL = "$URL/$ID";

		$response = postRequest::getInstance()->URL($URL)->request();
		//return $response;
		wp_send_json($response);
	}

	public static function requestConversation($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		$Data = $_POST['_data'];

		/* =============================================================== */


		$ID = $Data['id'];
		$URL = \FindMyAudience::$Config->getValue('TwitterTerms');
		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);
		$URL = "$URL/$ID";

		$response = postRequest::getInstance()->URL($URL)->request();
		//return $response;
		wp_send_json($response);
	}

	/**
	 * Get following status and messages sent between me
	 * and specified user
	 */
	public static function getUserConnections($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		$Data = $_POST['_data'];

		/* =============================================================== */


		$ID = $Data['id'];
		$URL = \FindMyAudience::$Config->getValue('ConnectionsURL');
		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);
		$URL = "$URL/$ID";

		$response = postRequest::getInstance()->URL($URL)->request();
		//return $response;
		wp_send_json($response);
	}

	/**
	 * Get my tweets, favorited tweets and retweets
	 */
	public static function getTwitterConnections($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		/* =============================================================== */


		$URL = \FindMyAudience::$Config->getValue('TweetConnectionsURL');
		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);
		$response = postRequest::getInstance()->URL($URL)->request();
		//return $response;
		wp_send_json($response);
	}


	/**
	 * ========== Twitter engage functions ==========
	 */

	/**
	 * Create a new tweet
	 */
	public static function sendTweet($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		$Data = $_POST['_data'];

		/* =============================================================== */


		$URL = \FindMyAudience::$Config->getValue('EngageURL');
		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);
		$response = postRequest::getInstance()->URL($URL)->Data($Data)->request();
		//return $response;
		wp_send_json($response);
	}

	/**
	* Reply to an existing tweet
	*/
	public static function sendReplyTweet($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		$Data = $_POST['_data'];

		/* =============================================================== */


		$URL = \FindMyAudience::$Config->getValue('TweetReplyURL');
		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);
		$response = postRequest::getInstance()->URL($URL)->Data($Data)->request();
		//return $response;	
		wp_send_json($response);
	}

	/** 
	 * Send a private message to a user
	 */
	public static function sendMessage($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		$Data = $_POST['_data'];

		/* =============================================================== */


		$URL = \FindMyAudience::$Config->getValue('MessagesURL');

		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);
		$response = postRequest::getInstance()->URL($URL)->Data($Data)->request();
		//return $response;
		wp_send_json($response);
	}

	/** 
	 * Retweet somebody's tweet
	 */
	public static function retweetTweet($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		$Data = $_POST['_data'];

		/* =============================================================== */


		$URL = \FindMyAudience::$Config->getValue('RetweetURL');
		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);

		$response = postRequest::getInstance()->URL($URL)->Data($Data)->request();
		//return $response;
		wp_send_json($response);
	}

	/**
	 * Favorite somebody's tweet
	 */
	public static function favoriteTweet($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		$Data = $_POST['_data'];

		/* =============================================================== */


		$URL = \FindMyAudience::$Config->getValue('AddFaveTweetURL');

		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);
		$response = postRequest::getInstance()->URL($URL)->Data($Data)->request();
		//return $response;
		wp_send_json($response);
	}

	public static function unfavoriteTweet($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception("Nonce check failed for ".__FUNCTION__);
		}

		$Data = $_POST['_data'];

		/* =============================================================== */


		$URL = \FindMyAudience::$Config->getValue('DelFaveTweetURL');

		if(!$URL) throw new \Exception(\FindMyAudience::FMA_ERR_BROKEN_CONFIG);
		$response = postRequest::getInstance()->URL($URL)->Data($Data)->request();
		//return $response;
		wp_send_json($response);
	}
}

class OAuth {

	public function verifyToken($Data=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		require_once(\FindMyAudience::$AppDir."/assets/twitteroauth/autoload.php");

		if(!$Data) {
			throw new \Exception("Empty token data given.");
		}

		list($consumer_key, $consumer_secret) = array_values($this->fetchOAuthKeys());
		$connection = new \Abraham\TwitterOAuth\TwitterOAuth($consumer_key, $consumer_secret);

		$params = array("oauth_verifier" => $Data['oauth_verifier'], "oauth_token" => $Data['oauth_token']);
		$access_token = $connection->oauth("oauth/access_token", $params);

		/**
		 * Get the user's account info
		 */
		$connection = new \Abraham\TwitterOAuth\TwitterOAuth($consumer_key, $consumer_secret, $access_token['oauth_token'],$access_token['oauth_token_secret']);
		$content = $connection->get("account/verify_credentials");

		/**
		 * Store the OAuth token in the DB
		 */
		$user_id = get_current_user_id();
		update_user_option( $user_id, 'fma_twitter_oauth', ($access_token) );



		/* Update the FMA user profile */
		$Data = json_encode( array('accesstoken' => $access_token['oauth_token'], 'accesssecret' => $access_token['oauth_token_secret']) );
		$URL = \FindMyAudience::$Config->getValue('OAuthURL');
		$response = \FindMyAudience\postRequest::getInstance()
			->URL($URL)
			->Data($Data)
			->request();


			if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__, print_r($response, true) );
		/* End */

		return true;
	}

	public function requestToken($Nonce=false) {
	
		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( !\FindMyAudience::verifyAjaxNonce($Nonce, __FUNCTION__) ) {
			throw new \Exception(\FindMyAudience::FMA_NONCE_FAIL." ".__FUNCTION__);
		}

		require_once(\FindMyAudience::$AppDir."/assets/twitteroauth/autoload.php");

		$Data = $_POST['_data'];

		$URL = $Data['twitter_auth_redir'];
		if( (empty($URL)) OR (!filter_var($URL,FILTER_VALIDATE_URL)) ) {
			throw new \Exception("Invalid redirect URL given: $URL. ".\FindMyAudience::FMA_ERR_BROKEN_CONFIG);
		}

		list($consumer_key, $consumer_secret) = array_values($this->fetchOAuthKeys());


		$connection = new \Abraham\TwitterOAuth\TwitterOAuth($consumer_key, $consumer_secret);
		$temporary_credentials = $connection->oauth('oauth/request_token', array("oauth_callback" => $URL));

		$Return['oauth_token'] = $temporary_credentials['oauth_token'];
		$Return['oauth_token_secret'] = $temporary_credentials['oauth_token_secret'];
		$Return['URL'] = $connection->url("oauth/authorize", array("oauth_token" => $temporary_credentials['oauth_token']));
		$Return['twitter_auth_redir'] = $Data['twitter_auth_redir'];

		wp_send_json($Return);
	}

	/**
	 * Never called except privately from requestToken().
	 *
	 * This function requires the user to have already signed into
	 * the FMA server and received a valid session ID. Function will
	 * not work for signed-out users.
	 *
	 */
	private function fetchOAuthKeys() {

		/*
		return array(
			'ConsumerKey' => '[key]',
			'ConsumerSecret' => '[secret]'
		);
		*/

		$response = getRequest::getInstance()->URL( \FindMyAudience::$Config->getValue('OAuthKeysURL') )->request();
		return $response;
	}

}




