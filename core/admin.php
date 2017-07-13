<?php
/**
 * Messy, but there's not really any other way to save this data,
 * because the Twitter OAuth always redirects back to this exact page.
 */

if(isset($_GET['oauth_token'])) {

	try {
		$Data['oauth_token'] = strip_tags($_GET['oauth_token']);
		$Data['oauth_verifier'] = strip_tags($_GET['oauth_verifier']);

		$OAuth = new \FindMyAudience\OAuth;

		$response = $OAuth->verifyToken( $Data );

		// In case the <script> doesn't work, at least show the user a success message
		?>
		<div id="message" class="success"><p>Successfully signed in to Twitter.</p></div><?php
		?>
		<script type="text/javascript">
			window.location.href = '<?php echo get_admin_url()?>/admin.php?page=<?php echo plugin_basename(__FILE__)?>&update=TRUE';
		</script>
		<?php

	} catch (Exception $e) {
		?><div id="message" class="error"><p><?php echo esc_html($e->getMessage())?></p></div><?php
	}
}

use FindMyAudience\FMA_Global as FMA;
/**
 * Admin page, for settings and meta data
 */

//error_log( '/admin.php');
$user_id = get_current_user_id();
$user_data = wp_get_current_user();

//error_log( 'admin.php got user and user_data');

if(FMA::isFMAUserRegistered() === FALSE) {
	\FindMyAudience::$NewInstall = TRUE;
} else {
	\FindMyAudience::$NewInstall = FALSE;
}

if(isset($_GET['update'])) {
	?><div id="message" class="updated"><p><?php echo 'Settings updated.'?></p></div><?php
} else {
	?><div style="display:none;" id="message" class="error"><p></p></div><?php
}
?>

<div class="wrap">
<h2><?php 
	//error_log('admin.php title');
	echo esc_html( $title ); ?></h2>

<div style="position:relative;">
<div style="display:none;" id="login-progress">Logging in <img src="<?php
	//error_log('admin.php dot-white.gif');
	echo \FindMyAudience::$AppLocation."/assets/images/dots-white.gif"?>"></div>

<form id="fma-login" method="post" action="#" novalidate="novalidate" autocomplete="off">
<?php
error_log(print_r(settings_fields('fma'),TRUE));
settings_fields('fma'); ?>

<h3 style="margin-bottom:0px;">Plugin Settings</h3>
<?php

if (FMA::isFMAUserLoggedIn() === FALSE) {
	include(\FindMyAudience::$AppDir.'assets/templates/login.php');
}

//error_log('admin.php if!newinstall');
if(!\FindMyAudience::$NewInstall) {
if (FMA::isFMAUserLoggedIn() === TRUE) {
	?>
	<table class="form-table _fma_login" style="margin-left:15px">
	<tr>
		<th scope="row"><label for="fma_blog_categories">FMA Login</label></th>
		<td width="300px"><?php
			//error_log('admin.php decryptString');
			echo esc_html(FMA::decryptString(get_user_option('fma_login_user',$user_id)))?>
		</td>
		<td>
			<!-- <input type="button" value="Change credentials" class="button" onclick="toggleRegistration();"> -->
			<input type="button" value="Log out from FMA" class="button" onclick="logOutFMAUser();">
		</td>
	</tr>
	</table>
	<?php
	}
}
?>

<?php
//error_log('admin.php do_settings');
do_settings_sections('fma'); ?>

</form>
</div>

<?php
//error_log('admin.php twitterSettings');
if(!\FindMyAudience::$NewInstall) {

	//error_log('admin.php fma_twitter_oauth');
	$TwitterAuth = get_user_option('fma_twitter_oauth',$user_id);

	?>

	<hr/>
	
	<form method="post" action="" novalidate="novalidate" onsubmit="return false;">

	<?php
	//error_log('admin.php settings_fileds');
	settings_fields('fma'); ?>
	<h3 class="fma-hidden" style="margin-bottom:0px;">Connections</h3>
	<table class="form-table" style="margin-left:15px;">
	<tr>
		<th scope="row">Social Connection</th>
		<?php
		//error_log('admin.php oauth_token');
		if(!empty($TwitterAuth['oauth_token'])) {
			?>
			<td width="300px">
				Signed into Twitter as
				<b>
					<?php
					//error_log('admin.php Twitterauht');
					echo esc_html($TwitterAuth['screen_name']);
					?>
				</b>.
			</td>
			<td><input type="button" class="button" value="Sign out" onclick="deauthorizeTwitter();"></td>
			<?php
		} else {
			if (FMA::isFMAUserLoggedIn() === TRUE) {
				?>
				<td>
					<img style="cursor:pointer;" src="<?php echo \FindMyAudience::$AppLocation."/assets/images/sign-in-with-twitter-gray.png"?>" onclick="authorizeTwitter();">
				</td>
				<?php
			} else {
				?>
				<td>
					<img style="cursor:not-allowed;opacity:0.6" src="<?php echo \FindMyAudience::$AppLocation."/assets/images/sign-in-with-twitter-gray.png"?>"><p><em style=padding-right:20px;">You must login to FMA before authorizing Twitter</em></p>
				</td>
			<?php
			}
		}
		?>
	</tr>
	</table>
	</form>

	<hr/>

	<?php } ?>
	
	<form method="post" novalidate="novalidate" onsubmit="return false;">
	<input type="hidden" name="Setting[fma_similar_blogs]" value="" />
	<input type="hidden" name="Setting[fma_blog_categories]" value="" />
	<?php
	//error_log('admin.php settings_fiels2');
	settings_fields('fma'); ?>
        <!--
		<?php
		//error_log('admin.php do_settings_sections');
		do_settings_sections('fma'); ?>
		<?php
		//error_log('admin.php submit_button');
		submit_button(); ?>
     -->	
	</form>
	</div>
	
	<br><br>
	<br><br>
	<br><br>

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
var Keywords = [];

/**
 * Close the pop-up window if we are redirecting here
 * after signing into Twitter via a search
 */
jQuery(function() {
	if (window.opener) {
		//alert('inside a pop-up window or target=_blank window');
		window.close();
	} else if (window.top !== window.self) {
		//alert('inside an iframe');
	} else {
		//alert('this is a top level window');
	}

	populateTagAutocomplete();
});


jQuery('.token-input-delete').live('click',function() {
	jQuery(this).parent().remove();
});

function split( val ) {
	return val.split( /,\s*/ );
}

function extractLast( term ) {
	return split( term ).pop();
}

function autocomplete_all(request,response) {

	var matcher = new RegExp(jQuery.ui.autocomplete.escapeRegex(request.term), "i");

	var results = jQuery.grep(Keywords, function(value) {
		return matcher.test(value.value)
	});

	response(results);
}

function autocomplete_slice(request,response) {

	var matcher = new RegExp(jQuery.ui.autocomplete.escapeRegex(request.term), "i");

	var results = jQuery.grep(Keywords, function(value) {
		return matcher.test(value.value)
	});

	response(results.slice(0, 10));
}

/*
jQuery( "#fma_blog_tags" )
	// don't navigate away from the field on tab when selecting an item
	.autocomplete({
		delay:0,
		selectFirst: true,
		minLength: 0,
		source: autocomplete_slice,
		open: function(event, ui) {
			jQuery('.ui-autocomplete').prepend('<li style="box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;border:none !important;margin-bottom:2px;border-bottom:1px dotted #222222 !important;background: white !important;cursor:normal !important;text-align:left;font-size:12px;padding:2px;" class="perfectfit round2 ui-menu-item" role="menuitem"><a style="color:#aaaaaa !important">Suggested tags based on your category selections</a></div>');
			if( jQuery('#fma_blog_tags').hasClass('ui-autocomplete-source-all') ) {
				jQuery('.ui-autocomplete').append('<li onclick="showLessTagResults()" style="box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;border:none !important;border-top:1px dotted #222222 !important;margin-top:2px;background: white !important;cursor:normal !important;text-align:left;font-size:12px;padding:2px;" class="perfectfit round2 ui-menu-item" role="menuitem"><a style="color:#000000 !important">Show top results only</a></div>');
			} else {
				jQuery('.ui-autocomplete').append('<li onclick="showAllTagResults()" style="box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;border:none !important;border-top:1px dotted #222222 !important;margin-top:2px;background: white !important;cursor:normal !important;text-align:left;font-size:12px;padding:2px;" class="perfectfit round2 ui-menu-item" role="menuitem"><a style="color:#000000 !important">Show all...</a></div>');
			}
		},
		select: function( event, ui ) {
			var term = ui.item.value;
			jQuery(this).val('');
			jQuery(".ui-autocomplete").hide();

			var li = jQuery('<li style="display:none;" class="token-input"><input type="text" name="Setting[fma_blog_tags][]" style="display:none;" value="'+term+'"><p>'+term+'</p><span class="token-input-delete"><b>&times;</b></span></li>');
			jQuery('#token-input-area').append(li).find(li).fadeIn();
			event.preventDefault();

		},
	}).on('focus, click', function(event) {
		jQuery(this).autocomplete( "search", "");
	})
	.data("ui-autocomplete")._renderItem = function (ul, item) {
		return jQuery("<li></li>")
			.data("item.autocomplete", item)
			.append("<a>" + item.label + "</a>")
			.appendTo(ul);
	};

*/

function showAllTagResults() {
	jQuery('#fma_blog_tags').addClass('ui-autocomplete-source-all').removeClass('ui-autocomplete-source-slice').autocomplete( "option", "source", autocomplete_all);
	jQuery('#fma_blog_tags').autocomplete( "search", jQuery('#fma_blog_tags').val() );
}

function showLessTagResults() {
	jQuery('#fma_blog_tags').addClass('ui-autocomplete-source-slice').removeClass('ui-autocomplete-source-all').autocomplete( "option", "source", autocomplete_slice);
	jQuery('#fma_blog_tags').autocomplete( "search", jQuery('#fma_blog_tags').val() );
}

function append_keyword(input) {

	if( jQuery(input).val() ) {
		var term = jQuery(input).val();
		jQuery(input).val('');
		jQuery(".ui-autocomplete").hide();

		var li = jQuery('<li style="display:none;" class="token-input"><input type="text" name="Setting[fma_blog_tags][]" style="display:none;" value="'+term+'"><p>'+term+'</p><span class="token-input-delete"><b>&times;</b></span></li>');
		jQuery('#token-input-area').append(li).find(li).fadeIn();
	}
}

function searchKeyPress(e,input) {
    if (e.keyCode === 13) {
	append_keyword(input);
    } else {
	return e.keyCode;
    }
    e.preventDefault();
}


function toggleFMACategory(ID) {
	jQuery('.blogCategorySelector[data-id="'+ID+'"]').toggleClass('fma-hidden');
	jQuery('.blogCategorySelector.fma-hidden').removeClass('selected').find('input[type=checkbox]').removeAttr('checked');


	populateTagAutocomplete();
}

function populateTagAutocomplete() {

	Keywords.splice(0,Keywords.length);

	var items = jQuery('.blogSubCategories').get();
	items.sort(function(a,b){
		var keyA = jQuery(a).text();
		var keyB = jQuery(b).text();
	
		if (keyA < keyB) return -1;
		if (keyA > keyB) return 1;
		return 0;
	});

	jQuery.each(items, function(i, li) {
		if( !jQuery(li).hasClass('fma-hidden') ) {
			var text = jQuery(li).text().replace('#','');
			var parentText = jQuery(li).attr('data-parent');

			var fullText = {"value" : text, "id" : text, "label" : '<div style="vertical-align:middle;line-height:18px;float:left;width:50px;white-space:no-wrap;text-overflow:ellipsis;font-size:11px;color:#aaaaaa;">'+parentText+'</div> <div style="vertical-align:middle;line-height:18px;float:left;">'+text+'</div>'};

			Keywords.push( fullText );
		}
	});
}
</script>

<style type="text/css">

.ui-autocomplete li.ui-menu-item {
	padding:2px;
	border:1px solid transparent;
}

.ui-autocomplete li.ui-state-focus {
	background: rgba(56,117,215,0.5);
	border:1px solid #3875D7;
	color:white;
	box-sizing:border-box;
	-moz-box-sizing:border-box;
	-webkit-box-sizing:border-box;
	border-radius:2px;
}

.ui-autocomplete li.ui-state-focus div {
	color:white;
}

.similarBlog-addon h4 {
	margin:5px;
}

.similarBlog-addon {
	position:relative;
	background:white;
	width:80%;
	border:1px solid rgba(0,0,0,0.1);
	border-top:none;
}

.similarBlog-addon:before {
        content:url('images/tinyarrow-up.png');
        position:absolute;
        display:block;
        nowrap;
	left:15px;
	top:-13px;
}

.blogCategorySelector {
	list-style-type:none;
	margin:0px;
	margin:1px;
	padding:3px;
	position:relative;
}

.blogCategorySelector label {
	width:100%;
	padding:0px;
	display:block;
}

.blogCategorySelector.selected, .blogCategorySelector.selected a {
	background:#3875D7;
	color:white;
}

.blogSubCategorySelector {
	margin-left:25px;
}

.blogCategorySelector-header {
	padding:5px;
	background:white;
	margin-bottom:2px;
	border:1px solid rgba(0,0,0,0.2);
	font-weight:bold;
}

</style>

<?php
// Sort array by one of its keys
function array_sort(&$arr, $col) {
        if($arr) {
                $sort_col = array();
                foreach ($arr as $key=> $row) {
                        $sort_col[$key] = $row[$col];
                }

                array_multisort($sort_col, SORT_ASC, $arr);
        }
}
?>

