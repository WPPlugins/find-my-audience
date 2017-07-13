<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class FindMyAudience {

	public static $Config;		// An empty reference to the object class.config.php initializes
	public static $AppDir;		// Location of the plugin on the filesystem (plugin_dir_path)
	public static $AppLocation;	// Location of the plugin in the browser (plugin_dir_url)
	public static $isLoggedIn = false;	// Are we logged into the FMA API service?
	public static $NewInstall = false;	// Is this a fresh installation?

	const FMA_ERR_NO_USER = "You have not configured your account with Find My Audience. Please sign-in or register from the plugin settings page.";
	const FMA_ERR_DUP_USER = "This email address is already registered with Find My Audience.";
	const FMA_ERR_INVALID_USER = "This email address is not registered with Find My Audience. Please register before logging in.";
	const FMA_ERR_NOOP = "An invalid method was requested.";
	const FMA_ERR_BROKEN_CONFIG = "This installation of the Find My Audience plugin has not been configured correctly or is missing the configuration file.";
	const FMA_NONCE_FAIL = "Nonce check failed for "; // Individual functions complete this cliffhanger

	/**
	 * Headless init. This initializes the plugin and all associated classes,
	 * but does not load styles, scripts or HTML templates.
	 */
	public static function init() {

		require_once(__DIR__.'/class.config.php');

		self::$AppLocation = plugin_dir_url(__FILE__);
		self::$AppDir = plugin_dir_path(__FILE__);

		self::$Config = new \FindMyAudience\FMA_Config();

		require_once(__DIR__.'/class.fma-global.php');
		require_once(__DIR__.'/class.userspace.php');
	}

	/**
	 * This function handles ALL Ajax interaction with the plugin.
	 * A request is first sent through the postRequst() JS function
	 * to admin-ajax.php, which chains it on through this function,
	 * which calls the final function and sends the result back down the line.
	 *
	 * Nonces will ensure that no (or few) functions can be called from JS directly
	 * without first getting filtered through handleRequest()
	 *
	 */
	public static function handleRequest() {

		try {

			if( ($_POST) AND ($_POST['fma_wp_action']) ) {

				ob_start();
				$Action = (string) strip_tags($_POST['fma_wp_action']); // Little housekeeping, probably unnecessary

				// Pass our POSTed data directly to the receiving function, as-is
				if(isset($_POST['data'])) {

					if(!is_array($_POST['data'])) {
						parse_str($_POST['data'],$Data);
					} else {
						$Data = $_POST['data'];
					}

					unset($_POST['data']);
					foreach($Data as $Key => $Value) {
						$_POST[$Key] = (is_array($Value)) ? array_map('stripslashes',$Value) : stripslashes($Value);
					}
				}

				// Restrict calls to the plugin namespace, so users can't inject
				// JavaScript and call actions outside of FindMyAudience
				$Routine = (string) strip_tags($_POST['fma_wp_method']);
				$Method = "\FindMyAudience\\".$Routine;
				if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"First check: Calling $Method");
				if(!class_exists($Method)) {
					$Method = "\FindMyAudience";
					if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"$Method invalid. Second check: Calling $Method");
				}

				if(!method_exists($Method,$Action)) {
					if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,self::FMA_ERR_NOOP." (Tried $Method->$Action)");
					throw new \Exception(self::FMA_ERR_NOOP);
				}

				// verify intent
				if ( ! wp_verify_nonce($_POST['nonce'], self::$Config->getValue('nonce_check')) ) {
					// the only reason the nonce should EVER fail for handleRequest() is if
					// a user is doing something sneaky with JavaScript injection. So just
					// pretend the called method doesn't even exist in that case.
					if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,self::FMA_NONCE_FAIL." ".__FUNCTION__);
					throw new \Exception(self::FMA_ERR_NOOP);
				}

				if(!empty($Data)) {
					if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"---> Sending data: ".print_r($Data,true));
				}

				$reflection = new ReflectionMethod($Method,$Action);
				if($reflection->isStatic()) {
					if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Method found. Third check: Calling $Method::$Action");
					$Method = "$Method::$Action";
				} else {
					if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Method found. Third check: Calling $Method->$Action");
					$Inst = new $Method();
					$Method = array($Inst,$Action);
				}

				/*
				 * Since we are using one generic nonce for handleRequest(), always,
				 * here we will generate a unique nonce for the function which we are
				 * calling, to ensure no functions ever get called without being passed
				 * through handleRequest() first.
				 */
				$UniqueNonce = wp_create_nonce( \FindMyAudience::$Config->getValue('nonce_check').$Action );

					call_user_func_array( $Method, array('nonce' => $UniqueNonce) );

					$Response = ob_get_contents();
					if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Response: ".print_r($Response,true));

				ob_end_clean();

				return wp_send_json( $Response );
			}

		} catch (Exception $e) {
			if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Received error ".$e->getMessage());
			wp_send_json( array('error' => $e->getMessage() ));
		}

		die();
		//return false;
	}

	// Generate on-the-fly nonces for cases where we are calling a function directly from
	// another PHP function, when it is typically called through Ajax (this does the same
	// thing as the $UniqueNonce generator in handleRequest() )
	public static function generateInternalNonce($Function, $CalledFrom=false) {
		$Nonce = wp_create_nonce( \FindMyAudience::$Config->getValue('nonce_check').$Function );
		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Generating OTF nonce for $Function, calling from $CalledFrom: $Nonce");
		return $Nonce;
	}

	// Verify our $UniqueNonces
	public static function verifyAjaxNonce($Nonce=false, $Function=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Checking nonce '$Nonce' against function '$Function'..");

		if( (empty($Nonce)) OR (empty($Function)) ) return false;

                if( ! wp_verify_nonce($Nonce, \FindMyAudience::$Config->getValue('nonce_check').$Function) ) {
			return false;
                }

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Nonce check succeeded for $Function");
		return true;
	}

	public static function debug_log($Function,$Line,$Msg) {
		if( (file_exists(\FindMyAudience::$AppDir."/debug.log")) AND (is_writeable(\FindMyAudience::$AppDir."/debug.log")) ) {
			$F = fopen(\FindMyAudience::$AppDir."/debug.log", "a");
			fwrite($F, date('[Y-m-d H:i:s]')." From $Function on line $Line: $Msg\n");
			fclose($F);
		} else {
			error_log($Msg);
		}
		return true;
	}
}

