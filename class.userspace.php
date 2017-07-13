<?php
/**
 * This class handles functions relating to the 'userspace' --
 * how an end-user and/or the Wordpress instance itself
 * interacts with the plugin. 
 */

namespace FindMyAudience;

// Safe to use since shorthand since we are within a namespace
use \FindMyAudience\FMA_Global as FMA_Global;
use \FindMyAudience\postRequest as postRequest;
use \FindMyAudience\getRequest as getRequest;
use \FindMyAudience\curlRequest as curlRequest;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class FMA_Backend {

	public static function fma_logout() {
		\FindMyAudience\Session::setToken('');
		return true;
	}

	public static function fma_activate() {
		return true;
	}

	/**
	 * Delete all FMA data from the DB.
	 */
	public static function fma_deactivate() {

		$user_id = get_current_user_id();

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		delete_user_option( $user_id, 'fma_token' );
		delete_user_option( $user_id, 'fma_login_user' );
		delete_user_option( $user_id, 'fma_login_pass' );
		delete_user_option( $user_id, 'fma_user_profile' );
		delete_user_option( $user_id, 'fma_twitter_oauth' );
		delete_user_option( $user_id, 'fma_first_name' );
		delete_user_option( $user_id, 'fma_last_name' );
		// We should probably delete app data here
		return true;
	}
}

class FMA_Admin extends FMA_Backend {

	public static function validatePluginSettings($Nonce=false) {

		if( ! wp_verify_nonce($Nonce, \FindMyAudience::$Config->getValue('nonce_check').__FUNCTION__) ) {
			throw new \Exception(\FindMyAudience::FMA_NONCE_FAIL." ".__FUNCTION__);
		}

		if($_POST) {

			if( (isset($_POST['Setting'])) AND (is_array($_POST['Setting'])) ) {
				foreach($_POST['Setting'] as $settingField => $settingValues) {
					if(is_array($settingValues)) {

						$settingValues = array_filter(array_map('sanitize_text_field',$settingValues));
						$settingValues = json_encode( $settingValues );

					} else {
						$settingValues = sanitize_text_field($settingValues);
					}

					$Data['Setting'][$settingField] = $settingValues;
				}
			}

			if($_POST['fma_login_user_login']) {
				$Data['fma_login_user'] = sanitize_email($_POST['fma_login_user_login']);
			} else {
				$Data['fma_login_user'] = sanitize_email($_POST['fma_login_user']);
			}

			if($_POST['fma_login_pass_login']) {
				$Data['fma_login_pass'] = $_POST['fma_login_pass_login'];
			} else {
				$Data['fma_login_pass'] = $_POST['fma_login_pass'];
			}

			$Data['_wp_http_referer'] = $_POST['_wp_http_referer'];

			return self::savePluginSettings($Data);
		}

		return true;
	}

	private static function savePluginSettings($Data) {

		$user_id = get_current_user_id();

		if($Data) {

			/*
			 * This only gets sent through a separate request, and does not get
			 * saved/updated along with the rest of the settings, like categories
			 */
			if($Data['fma_login_pass']) {
				update_user_option( $user_id, 'fma_login_user', FMA_Global::encryptString($Data['fma_login_user']) );
				update_user_option( $user_id, 'fma_login_pass', FMA_Global::encryptString($Data['fma_login_pass']) );

				if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Updating fma_login_user to {$Data['fma_login_user']}");
				if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Updating fma_login_pass to {$Data['fma_login_pass']}");
			} 

			if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"================================");
			if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,print_r($Data,true));
			if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"================================");
		

			if($Data['fma_login_pass_login']) {
				update_user_option( $user_id, 'fma_login_user', FMA_Global::encryptString($Data['fma_login_user_login']) );
				update_user_option( $user_id, 'fma_login_pass', FMA_Global::encryptString($Data['fma_login_pass_login']) );
				if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Updating fma_login_user_login to {$Data['fma_login_user_login']}");
				if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Updating fma_login_pass_login to {$Data['fma_login_pass_login']}");
			}

			if($Data['Setting']) {
				foreach($Data['Setting'] as $settingField => $settingValues) {
					update_user_option( $user_id, $settingField, $settingValues );
					if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Updating $settingField to $settingValues");
				}
			}

//			header('Location:'.$Data['_wp_http_referer'].'&update=true');
		}

		return true;
	}

	/**
	 * Initial login for user -- this should only fire once,
	 * the very first time that a user signs in. After that we are
	 * calling \FMA_Global::logInFMAUser()
	 */
	public static function logInFMAUser($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Function: ".get_called_class()."->".__FUNCTION__);

		if( ! wp_verify_nonce($Nonce, \FindMyAudience::$Config->getValue('nonce_check').__FUNCTION__) ) {
			throw new \Exception(\FindMyAudience::FMA_NONCE_FAIL." ".__FUNCTION__);
		}

		$Data = $_POST;

		if(isset($Data['fma_login_user_login'])) {

			$Email = sanitize_email($Data['fma_login_user_login']);
			if(!filter_var($Email,FILTER_VALIDATE_EMAIL)) throw new \Exception("Please provide a valid email address.");

			$validemail = FMA_Global::checkUserRegistration( $Email );

			if( $validemail === TRUE ) {
				//Try to log in
				$Data = json_encode( array('email' => $Email, 'password' => $Data['fma_login_pass_login'], 'keepLoggedIn' => TRUE) );
				$loginResponse = postRequest::getInstance()
					->URL(\FindMyAudience::$Config->getValue('LoginURL'))->Data($Data)
					->request();

					//->keepAlive(TRUE)

				if(isset($loginResponse['error'])) {
					if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__, json_encode($loginResponse) );
					throw new \Exception($loginResponse['error']['message']);
				}

			} else {
				//Email address not registered
				//if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,'NOT found valid email');
				throw new \Exception(\FindMyAudience::FMA_ERR_INVALID_USER);
			}

			// Fetches the user profile from FMA servers in JSON format
			// (FMA server has already sanitized data)

			$Nonce = \FindMyAudience::generateInternalNonce("getUserData", __FUNCTION__);
			$userData = FMA_Global::getUserData($Nonce);

			update_user_option( get_current_user_id(), 'fma_user_profile', $userData );
			return true;
		} else {
			throw new \Exception("No login information given.");
		}

		return false;
	}

	public static function registerFMAUser($Nonce=false) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

		if( ! wp_verify_nonce($Nonce, \FindMyAudience::$Config->getValue('nonce_check').__FUNCTION__) ) {
			throw new \Exception(\FindMyAudience::FMA_NONCE_FAIL." ".__FUNCTION__);
		}

		$Data = $_POST;

		if(isset($Data['fma_login_user'])) {

			$Email = sanitize_email($Data['fma_login_user']);
			if(!filter_var($Email,FILTER_VALIDATE_EMAIL)) throw new \Exception("Please provide a valid email address.");

			$validemail = FMA_Global::checkUserRegistration( $Email );

			if( $validemail === TRUE ) {
				throw new \Exception(\FindMyAudience::FMA_ERR_DUP_USER);
			} else {
				//email not registered - register the user.
				$Data['email'] = $Email; 
				$Data['password'] = $Data['fma_login_pass'];
				$Data['password2'] = $Data['fma_login_pass'];
				$Data['lastName'] = sanitize_text_field(trim($Data['Setting']['fma_last_name']));
				$Data['firstName'] = sanitize_text_field(trim($Data['Setting']['fma_first_name']));
				$Data['keepLoggedIn'] = TRUE;
				$Data['usertype'] = 'wordpress';

				$Data = json_encode($Data);

				$registerResponse = postRequest::getInstance()
					->URL(\FindMyAudience::$Config->getValue('RegisterURL'))->Data($Data)
					->request();

					//->keepAlive(TRUE)

				if($registerResponse['error']) {
					if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__, json_encode($registerResponse) );
					throw new \Exception($registerResponse['error']['message']);
				}
			}

			// Fetches the user profile from FMA servers in JSON format
			// (FMA server has already sanitized data)

			$Nonce = \FindMyAudience::generateInternalNonce("getUserData", __FUNCTION__);
			$userData = FMA_Global::getUserData($Nonce);

			update_user_option( get_current_user_id(), 'fma_user_profile', $userData );
			delete_user_option( get_current_user_id(), 'fma_twitter_oauth' );

			//self::savePluginSettings($Data);

			return true;

		} else {
			throw new \Exception("No login information given.");
		}

		return false;

	}


}

class FMA_Frontend {

	public static function init() {

		// This will handle ALL JavaScript calls for plugun functions.
		add_action('wp_ajax_fma_handle_request',array('FindMyAudience','handleRequest'));

		add_action('wp_logout', array('FindMyAudience\FMA_Backend', 'fma_logout') );

		\FindMyAudience\FMA_Frontend::fma_add_assets();

		add_action('admin_menu', array('\FindMyAudience\FMA_Frontend', 'fma_add_menu') );

		add_action('add_meta_boxes', array('\FindMyAudience\FMA_Frontend','fma_create_meta_box') );

		add_action('save_post', array('\FindMyAudience\FMA_Frontend', 'save_post_hook' ) );
	}

	public static function fma_add_assets() {

		wp_register_style('fma-base', \FindMyAudience::$AppLocation . 'assets/main.css');
		wp_register_style('fma-styles', \FindMyAudience::$AppLocation . 'assets/style.css');
		wp_register_style('fma-modal', \FindMyAudience::$AppLocation . 'assets/modal.css');

		wp_enqueue_style('fma-base');
		wp_enqueue_style('fma-styles');
		wp_enqueue_style('fma-modal');

		wp_enqueue_script('fma-global', \FindMyAudience::$AppLocation . 'assets/global.js', array('jquery'));

		wp_enqueue_script('fma-tiles', \FindMyAudience::$AppLocation . 'assets/tiles.js');
		wp_enqueue_script('fma-modal', \FindMyAudience::$AppLocation . 'assets/modal.js');
		wp_enqueue_script('fma-tw-u', \FindMyAudience::$AppLocation . 'assets/twitterUsers.js');
		wp_enqueue_script('fma-tw-c', \FindMyAudience::$AppLocation . 'assets/twitterConversations.js');

		wp_enqueue_script('fma-main', \FindMyAudience::$AppLocation . 'assets/fma.js', array('jquery'));

		// Do not remove ending slash here or THE WORLD WILL END
		wp_localize_script('fma-main', 'PLUGIN_URL', \FindMyAudience::$AppLocation . 'assets/');

		// All Ajax functions are routed through here using the function 'postRequest' in assets/globals.js
		wp_localize_script('fma-main', 'AJAX_URL', get_bloginfo("url")."/wp-admin/admin-ajax.php" );

		// We use this nonce initially for the postRequest() function, and a new nonce is generated on-the-fly for functions that postRequest calls
		wp_localize_script('fma-main', 'AJAX_NONCE', wp_create_nonce( \FindMyAudience::$Config->getValue('nonce_check') ) );
	}

	/**
	* Menu page for all users
	*/
	public static function fma_add_menu() {
    		add_menu_page('Find My Audience', 'FMA Settings', 'read', '/' . basename(\FindMyAudience::$AppDir) . '/core/admin.php', '', plugins_url(basename(\FindMyAudience::$AppDir) . '/assets/images/fma.png'), 99);
    		add_submenu_page('/' . basename(\FindMyAudience::$AppDir) . '/core/admin.php', 'My Leads', 'My Leads', 'read', __DIR__ . '/core/favorites.php', '');
	}

	public static function fma_create_meta_box() {
		add_meta_box(
			'fma_sectionid',
			__('Find My Audience', 'http://findmyaudience.com'),
			array('\FindMyAudience\FMA_Frontend','fma_plugin_content'),
			'post'
		);
	}

	/**
	 * This is the actual plugin, that displays on the post creation page.
	 */
	public static function fma_plugin_content($post) {

	    wp_nonce_field('fma_meta_box', 'fma_meta_box_nonce');

		$user_id = get_current_user_id();

	    if (isset($_GET['post'])) {

		$allCategories = get_categories('hide_empty=0');
		$postCategories = array();

		//The categories of the post - not fma categories
		$getCats = wp_get_post_categories($post->ID);
		foreach ($getCats as $c) {
		    $catData = get_category($c);
		    $postCategories[$c] = $catData->name;
		}

		//Tags of the post
		$postTags = array();
		$getTags = wp_get_post_tags($post->ID);
		foreach ($getTags as $t) {
		    $postTags[$t->term_id] = $t->name;
		}


		$pluginCategories = json_decode(get_user_option('fma_blog_categories'));
		$pluginTags = get_user_option('fma_blog_tags');
		$pluginBlogs = json_decode(get_user_option('fma_similar_blogs'));

		if (!empty($pluginTags)) {
		    $pluginTags = array_map('trim', json_decode($pluginTags, JSON_NUMERIC_CHECK));
		    $postTags = array_merge($pluginTags, $postTags);
		}


		$MetaCategories = get_post_meta($post->ID, 'wp_categories');
		$MetaTags = get_post_meta($post->ID, 'wp_tags');


		/* If this is the Initial view of the plugin for this post, leave the default values for cats and tags in place */
		/* If this is a subsequent view, then override the default with the saved values */
		$isSaved = get_post_meta($post->ID, 'fma_is_initialized');
		//if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,'is saved already?');
		//if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,print_r( $isSaved, TRUE));
		if( $isSaved ) {
		    //if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,'****IN isSaved');
		    $postCategories = array();
		    if (isset($MetaCategories[0])) {
			unset($postCategories);
			if (is_array($MetaCategories[0])) {
			    foreach ($MetaCategories[0] as $catName) {
				$catID = get_cat_ID($catName);
				$postCategories[$catID] = $catName;
			    }
			}
		    }
		    $postTags = array();
		    if (isset($MetaTags[0])) {
			unset($postTags);
			$postTags = $MetaTags[0];
		    }
		}
	    }

	    ?>

	    <?php include( \FindMyAudience::$AppDir."/assets/templates/autoload.php"); ?>


	    <div style="overflow:hidden" id="fma-meta-box">
	    <?php
	    // Only show widget if the post already exists
	    if (!isset($_GET['post'])) {
		?>
		<div style="padding:10px;">Please save this post as a draft or publish in order to find your audience.</div>
		<?php
	    } else {
		?>

		<div id="formData">
		    <?php
		    if (!empty($pluginCategories)) {
			foreach ($pluginCategories as $catID) {
			    ?>
			    <input type="hidden" name="category_ids[]" value="<?php echo $catID ?>">
			    <?php
			}
		    }

		    if (!empty($pluginBlogs)) {
			foreach ($pluginBlogs as $blogID) {
			    ?>
			    <input type="hidden" name="similar_blogs[]" value="<?php echo $blogID ?>">
			    <?php
			}
		    }
		    ?>
		    <input type="hidden" name="blog_post_id" value="<?php echo $post->ID ?>">


		    <h2 style="padding:0px;margin:0px;margin-bottom:10px;">Profile for this Post</h2>
		    <table width="100%">
			<tr>
				<td width="50%" valign="top">
					<h3 style="padding:0px;margin:2px;margin-bottom:10px;">Categories</h3>
					<select id="fma-cats" style="width:100%;margin:0px;" onchange="append_fma_category(this);">
						<option value="0">Select a Category</option>
						<?php
						foreach ($allCategories as $catNo => $catData) {
							$catID = $catData->term_id;
							$catName = $catData->name;
							?>
							<option value="<?php echo $catID ?>" <?php if( (isset($postCategories)) AND (is_array($postCategories)) ) if (array_key_exists($catID, $postCategories)) echo 'class="fma-hidden"' ?>><?php echo $catName ?></option>
							<?php
						}
						?>
					</select>

					<div style="max-height:425px;padding-right:5px;overflow:auto;">
					<ul id="fma-cat-list">
						<?php
						if (!empty($postCategories)) {
							foreach ($postCategories as $catID => $catName) {
							?>
							<li data-id="<?php echo $catID ?>" class="fma-cat-item"><label><input type="hidden" name="wp_categories[]" value="<?php echo $catID ?>"><?php echo $catName ?></label> <span class="remove"></span></li>
							<?php
							}
						}
						?>
					</ul>
					</div>
				</td>
			    <td style="padding-left:25px;" valign="top">
				<h3 style="padding:0px;margin:2px;margin-bottom:10px;">Tags</h3>
				<div style="position:relative;">
				    <input onKeyPress="return prevent_tag_submit(event);" type="text"
					   style="margin:0px;width:100%" id="fma-tag-input">
				    <a id="fmaTagButton" onclick="append_fma_tag();">Add</a>

				</div>

				<div style="max-height:425px;padding-right:5px;overflow:auto;">
				    <ul id="fma-tag-list">
					<?php
					if (!empty($postTags)) {
					    foreach ($postTags as $tagID => $tagName) {
						?>
						<li data-id="<?php echo $tagName ?>" class="fma-tag-item"><label><input
							    type="hidden" name="wp_tags[]"
							    value="<?php echo $tagName ?>"><?php echo $tagName ?></label> <span
							class="remove"></span></li>
						<?php
					    }
					}
					?>
				    </ul>
				</div>
			    </td>
			</tr>
		    </table>
		</div>

		<div align="center" style="position:relative;">
		    <?php
		    if (\FindMyAudience\FMA_Global::isFMAUserLoggedIn() !== TRUE) {
			?>
			<div style="display:block;" class="fma-error">You must log in to Find My Audience page before using search.</div>
			<?php
		    }
			?>
		</div>

		<div style="padding-top:20px" id="message"></div>
		    <?php 
		    if (FMA_Global::isFMAUserLoggedIn() !== TRUE) {
					$user_id = get_current_user_id();
					$user_data = wp_get_current_user();
					if(!FMA_Global::isFMAUserRegistered()) {
						\FindMyAudience::$NewInstall = TRUE;
					} else {
						\FindMyAudience::$NewInstall = FALSE;
					}
				?>
				<hr />
				<div>
	<form id="fma-login" method="post" action="#" novalidate="novalidate" autocomplete="off">
				<?php 
				settings_fields('fma'); 
					include('assets/templates/login.php');
					?>
		</form>
				<script type="text/javascript">
				<?php
				if( \FindMyAudience::$NewInstall === TRUE ) {
				?>
					showRegisterForm();
				<?php
				} else {
				?>
					showLoginForm();
				<?php
				}
				?>
			</script>
				</div>
			<?php 
			}
			?>
		<?php 
		//if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,'admin.php twitterSettings');
		if(!\FindMyAudience::$NewInstall) {

			//if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,'admin.php fma_twitter_oauth');
			$TwitterAuth = get_user_option('fma_twitter_oauth',$user_id);

			//if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,'admin.php settings_fileds');
			settings_fields('fma'); 
			if(empty($TwitterAuth['oauth_token'])) {
			?>
			<hr/>
			<form method="post" novalidate="novalidate" onsubmit="return false;">
			<table class="form-table" style="margin-left:15px;">
			<tr>
				<th scope="row">Social Connection</th>
				<?php
				$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
				//if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,'admin.php oauth_token');
				if(!empty($TwitterAuth['oauth_token'])) {
					?>
					<?php
				} else {
					if (FMA_Global::isFMAUserLoggedIn() === TRUE) {
						?>
						<td><img style="cursor:pointer;" 
								 src="<?php echo \FindMyAudience::$AppLocation."/assets/images/sign-in-with-twitter-gray.png";?>"
								 onclick="authorizeTwitter('<?php echo $protocol . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ?>');"></td>
						<?php
					} else {
						?>
						<td><img style="cursor:not-allowed;opacity:.6" 
								 src="<?php echo \FindMyAudience::$AppLocation."/assets/images/sign-in-with-twitter-gray.png";?>"
								 ><p><em style=padding-right:20px;">You must login to FMA before authorizing Twitter</em></p></td>
						<?php
					}
				}
				?>
			</tr>
			</table>
			</form>

			<hr></hr>
			<?php
			}
		}
		?>


		<div align="center" style="position:relative;">
		    <?php
		    if (FMA_Global::isFMAUserLoggedIn() === TRUE) {


                        $user_id = get_current_user_id();
                        $isSaved = get_post_meta($post->ID, "fma_is_cached_{$user_id}");

                        if($isSaved) {
                                ?>
                                <script>loadAudience('<?php echo $post->ID ?>');</script>
                                <?php
                        }
                        ?>

			<a id="fmaSearchButton" style="margin-top:10px;" onclick="findMyAudience2('<?php echo $post->ID ?>');">Find
			    My Audience <span class="fma-icon fma-icon-chevron-right"></span></a>
			<div class="fma-loading" id="fma-loading-div">Finding your audience, please wait <img
				src="<?php echo \FindMyAudience::$AppLocation."/assets/images/dots-white.gif" ?>"></div>
			<div class="fma-loading" id="fma-loading-div-retrieving">Retrieving your audience, please wait <img
				src="<?php echo \FindMyAudience::$AppLocation."/assets/images/dots-white.gif" ?>"></div>
			<?php
		    } else {
		    }
			?>
		</div>


		<div id="fma-error"></div>
		<div id="fma-results">
		    <!-- Tabs here -->
		    <!-- Sort/filter options here --!>


				<div class="fma-row audience-type">
					<div class="col-md-5">
						<img style="float:left;margin-top:50px;" src="<?php echo \FindMyAudience::$AppLocation?>/assets/images/twitter_logo_blue.png" class="pull-left" width="60">
						<div style="float:left;">
							<h2>Your Audience for<div class="sm-type twitter">Twitter</div></h2>
						</div>
					</div>
				</div>


				<div class="fma-container fma-plugin-container">
					<div class="sort-by">
						<label>SORT BY:</label>
						<select id="audienceSortBy">
							<option value="Score">Score</option>
							<option value="Followers">Followers</option>
							<option value="Tweets">Tweets</option>
							<option value="Date">Date</option>
						</select>
					</div>
					<!-- /Sort by -->
		    <div class="view-by">
			<label>VIEW:</label>
			<select id="twitterViewOptions">
			    <option id="peopleView">People</option>
			    <option id="conversationsView">Conversations</option>
			</select>
		    </div>
		    <!-- /View by -->
		</div>


		<div id="fma-twitter-users"></div> <?php #people ?>
		<div id="fma-twitter-groups"></div> <?php #conversations ?>

		</div>

	    <?php } ?>

	    </div>

	    <?php
	}

	public static function save_post_hook( $post_id ) {

		if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__);

	    if($_POST) {
			$ID = $_POST['post_ID'];
		    $isSaved = get_post_meta($ID, 'fma_is_initialized');
		    //if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,'save_post_hook');
		    //if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,$ID);
		    //We haven't saved the default tags and categories, save them now
		    if(! $isSaved){
			//if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__, '!$isSaved');
			if ( isset($_POST['post_ID']) or isset($_GET['post']) ) {
			    //if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__, 'post_ID');

			    $allCategories = get_categories('hide_empty=0');
			    $postCategories = array();

			    //The categories of the post - not fma categories
			    $getCats = wp_get_post_categories($ID);
			    foreach ($getCats as $c) {
				//if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,print_r($c, TRUE));
				$catData = get_category($c);
				$postCategories[$c] = $catData->name;
			    }

			    $postTags = array();
			    $getTags = wp_get_post_tags($ID);
			    foreach ($getTags as $t) {
				$postTags[$t->term_id] = $t->name;
			    }

			    //if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,'Save post metadata categories and tags'); 
			    //if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,print_r($postCategories,TRUE));
			    add_post_meta($ID, 'wp_categories', $postCategories, true);
			    add_post_meta($ID, 'wp_tags', $postTags, true);
			    add_post_meta($ID, 'fma_is_initialized', TRUE, true);

				if(WP_DEBUG === true) \FindMyAudience::debug_log(__FUNCTION__,__LINE__,"Adding 'fma_is_initialized' to post $ID");
			}
		    }


		}
	}
}
?>
