/**
 * This script contains all plugin-specific functions --
 * logging in, user functions, internal scripts to make the plugin
 * work, etc.
 *
 * Tiles, favorites, and app functions are in fma.js
 *
 */

function postRequest(Method, Action, Data, Callback) {

	jQuery.post(AJAX_URL, { action:"fma_handle_request", nonce: AJAX_NONCE, fma_wp_method: Method, fma_wp_action: Action, data: Data},
	function(response) {
		if( (response.error ) || (response.data && response.data.error) ) {

			// Override for the plugin page
			if( (Action == "loadAudience") || (Action == "findMyAudience") ) {

				tw_popConfirmMsg("Error: "+response.error || response.data.error || +'An unknown error has occurred');
				setTimeout( function() {
					jQuery('#fma-results').hide();
					jQuery('#fma-loading-div').hide();
					jQuery('#fma-loading-div-retrieving').hide();
				},1000);

			} else {
				alert( "Error: "+response.error || response.data.error || +'An unknown error has occurred' );
			}

			if( jQuery('#login-progress').length > 0 ) jQuery('#login-progress').hide();

		} else {

			if(Callback) {
				Callback(response);
			} else {
				return true;
			}
		}

	},'json');
}

function asyncRequest(Method, Action, Data, Callback, json) {

    jQuery.ajax({
        url: AJAX_URL,
        type: 'post',
        dataType: 'json',
        data: { action: 'fma_handle_request', nonce: AJAX_NONCE, fma_wp_method: Method, fma_wp_action: Action, data: Data},
        async: false,
	dataType: 'json',
	error: function (xhr, ajaxOptions, thrownError) {
		tw_popConfirmMsg("Error: "+xhr.status+': '+thrownError || 'An unknown error has occurred');
		jQuery('#login-progress').hide();
	},
        success: function (response) {
		if( (response.error) || (response.data && ( response.data.error) ) ) {
			tw_popConfirmMsg("Error: "+response.error || response.data.error || 'An unknown error has occurred');
			jQuery('#login-progress').hide();
		} else {
			if(Callback) {
				Callback(response);
			}
		}
        }
    });

}

SHOW_RESULTS_STEP = 9;
jQuery( document ).ajaxError(function( event, jqxhr, settings, thrownError ) {
	if( jqxhr.status > 200 ) {
		var error = thrownError || 'Unknown';
		tw_popConfirmMsg('Error: ' + error);
	}
});

/**
 * Prevent form submit when clicking 'Add' button for keywords
 */
function prevent_tag_submit(e) {

	var key;

	if(window.event) {
		key = window.event.keyCode; //IE
	} else {
		key = e.which; //firefox     
	}

	if(key == 13) {
		append_fma_tag();
		return (key != 13);
	} else {
		return (key != 13);
	}

}

/**
 * Called when clicking the X on a tag
 */
jQuery('.fma-tag-item .remove').live('click',function() {
	jQuery(this).parent().remove();
	savePostProfile();
});

/**
 * Called when clicking the X on a category
 */
jQuery('.fma-cat-item .remove').live('click',function() {
	var catID = jQuery(this).parent().attr('data-id');
	jQuery('#fma-cats').find('option[value="'+catID+'"]').removeClass('fma-hidden');
	jQuery(this).parent().remove();
	savePostProfile();
});

/**
 * Append a category to the post profile
 */
function append_fma_category(obj) {

	var catID = jQuery(obj).val();
	var catName = jQuery(obj).find('option:selected').text();

	if( catID != '0') {
		var catObj = jQuery('<li data-id="'+catID+'" class="fma-cat-item"><label><input type="hidden" name="wp_categories[]" value="'+catID+'">'+catName+'</label> <span class="remove"></span></li>');
		jQuery('#fma-cat-list').append(catObj);
		jQuery(obj).val('0').find('option[value="'+catID+'"]').addClass('fma-hidden');
	}

	savePostProfile();
}

/**
 * Append a tag to the post profile
 */
function append_fma_tag() {

	var tagName = jQuery('#fma-tag-input').val();

	if(tagName != '') {
		//var tagObj = jQuery('<li data-id="'+tagName+'" class="fma-tag-item"><label><input type="hidden" name="wp_tags[]" value="'+tagName+'">'+tagName+'</label> <span class="remove searching"></span></li>');
		var tagObj = jQuery('<li data-id="'+tagName+'" class="fma-tag-item"><label><input type="hidden" name="wp_tags[]" value="'+tagName+'">'+tagName+'</label> <span class="remove"></span></li>');
		jQuery('#fma-tag-input').val('');
		jQuery('#fma-tag-list').append(tagObj);
	}

	savePostProfile();
	//appendKeyword(tagName);
	//jQuery('li[data-id="'+tagName+'"]').find('.remove').removeClass('searching');
}

/**
 * Save the tags and categories of this post
 */
function savePostProfile() {
	var formData = jQuery('#formData').find('input,select').serialize();
	postRequest('Twitter', 'savePostProfile', formData);
}







/**
 * Get tiles from FMA
 */
function findMyAudience2(postID) {

	SHOW_RESULTS = 9;

	jQuery('#fma-loading-div').fadeIn();

	var formData = jQuery('#formData').find('input,select').serialize();

	postRequest('Twitter', 'findMyAudience', formData,
	function(data) {
		if(data.error) {
			jQuery('#fma-error').show().html(data.error.message || data.error);
			jQuery('#fma-results').hide();
			jQuery('#fma-loading-div').hide();
			jQuery('#fma-loading-div-retrieving').hide();
		} else {
			twitterResults = data[0].audience.twitterUsers;
			twitterConversationResults = data[0].conversations;
			// Twitter users
			processResults(twitterResults);
			showMore(twitterResults);
			// Twitter conversations
			tc_processResults(twitterConversationResults.slice(0,SHOW_RESULTS));
			/* END */
		}

	},'json');

}

function loadAudience(postID) {

	if(!postID) return;
	SHOW_RESULTS = 9;
	var loaded = false;
	//Give time for the DOM to load
	setTimeout( function(){
		if(!loaded) jQuery('#fma-loading-div-retrieving').fadeIn();
	},500);

	var formData = jQuery('#formData').find('input,select').serialize();

	postRequest('Twitter', 'loadAudience', formData,
	function(data) {
		if(data.error) {

			jQuery('#fma-error').show().html(data.error.message || data.error);
			jQuery('#fma-results').hide();
			jQuery('#fma-loading-div').hide();
			jQuery('#fma-loading-div-retrieving').hide();

		} else {

			twitterResults = data[0].audience.twitterUsers;
			twitterConversationResults = data[0].conversations;
			// Twitter users
			processResults(twitterResults);
			showMore(twitterResults);
			// Twitter conversations
			tc_processResults(twitterConversationResults.slice(0,SHOW_RESULTS));
			/* END */
		}
	},'json');

}

function isFmaUserConnectedToTwitter() {
	if(!twitterAuth) getTwitterAuth();
	if(!twitterAuth.oauth_token) return false;
	return ( twitterAuth.oauth_token_secret != null && twitterAuth.oauth_token != null);	
}

function toggleRegistration() {
	jQuery('._fma_login').toggleClass('fma-hidden').find('input').removeAttr('disabled');
	jQuery('.fma-hidden').find('input').attr('disabled','disabled');
}

function validateRegistrationForm() {

	var Pass1 = jQuery('#fma_login_pass').val();
	var Pass2 = jQuery('#fma_login_confirm').val();
	var first = jQuery('#fma_first_name').val();
	var last = jQuery('#fma_last_name').val();
	var email = jQuery('#fma_login_user').val();

	if(! ( Pass1 && Pass2 && first && last && email )) {
		return false;
	}
	if(Pass1 && Pass2) {
		if( Pass1 != Pass2 ) {
			return false
		}
	}
	return true;
}
function validateLoginForm() {

        var Pass1 = jQuery('#fma_login_pass_login').val();
        var email = jQuery('#fma_login_user_login').val();

	if(! ( Pass1 && email )) {
		return false;
	}

	return true;
}

function verifyFMAPassword() {

	var Pass1 = jQuery('#fma_login_pass').val();
	var Pass2 = jQuery('#fma_login_confirm').val();

	if(Pass1 && Pass2) {

		if( Pass1 != Pass2 ) {
				jQuery('#fma-login-error').show().html('Passwords do not match.');
		} else {
			jQuery('#fma-login-error').hide();
		}
	}
}

function loginFMAUser(Referer) {

	jQuery('#message').hide();
	if( ! validateLoginForm() ) {
		jQuery('#message').show().removeClass('updated').addClass('error').html('<p>All fields are required.</p>');
	    return;	
	}
	jQuery('#login-progress').fadeIn();

	jQuery('#fma_login_pass').val('');
	var formData = jQuery('#fma-login').serialize();
	console.log(formData);

	postRequest('FMA_Admin', 'logInFMAUser', formData,
	function(loginresponse) {

		postRequest('FMA_Admin', 'validatePluginSettings', formData,
		function(response) {
			window.location.href = Referer+'&update=TRUE';
		},'json');

		jQuery('#login-progress').fadeOut();

	},'json');
}

function registerFMAUser(Referer) {
	jQuery('#message').hide();
	if( ! validateRegistrationForm() ) {
		jQuery('#message').show().removeClass('updated').addClass('error').html('<p>All fields are required.</p>');
	    return;	
	}
	jQuery('#login-progress').fadeIn();
	//jQuery('#fma_login_pass').val('');

	var formData = jQuery('#fma-login').serialize();
	console.log(formData);

	postRequest('FMA_Admin', 'registerFMAUser', formData,
	function(response) {
		if(response.data.error) {
			var message = response.data.error || 'An unknown error has occurred, please try again.';
			setTimeout( function() {
				jQuery('#message').show().removeClass('updated').addClass('error').html('<p>' + message + '</p>');
			},500);

		} else {
			jQuery('#message').hide();
			postRequest('FMA_Admin', 'validatePluginSettings', formData,
			function(response) {
				alert(JSON.stringify(response));
				window.location.href = Referer+'&update=TRUE';
			},'json');
		}

		jQuery('#login-progress').fadeOut();

	},'json');
}

function logOutFMAUser() {

	postRequest('FMA_Global', 'logOutFMAUser', '',
	function(response) {
		window.location.href = window.location.href;
	},'json');
}

/**
 * Sign into Twitter via a user modal,
 * where we don't want to have to reload /
 * navigate away from the page, and need
 * the modal to reload dynamically.
 */
function authorizeTwitterRemote( redir ) {

	var child;
	var timer;

	postRequest("OAuth", "requestToken", { _data: {twitter_auth_redir: redir} },
	function(response) {
		child = window.open(response.URL,'','toolbar=0,status=0,width=900,height=500');
		timer = setInterval(checkChild, 500);
	},'json');

	function checkChild() {

		if (child.closed) {
			//alert('child closed');

		       	getTwitterAuth(true);

			if( isFmaUserConnectedToTwitter() ) {
				jQuery('.twitterEngage-disabled').addClass('fma-hidden');
				jQuery('.twitterEngage-enabled').removeClass('fma-hidden');
			} else {
				jQuery('.twitterEngage-disabled').removeClass('fma-hidden');
				jQuery('.twitterEngage-enabled').addClass('fma-hidden');
			}

			clearInterval(timer);
		}
	}
}

function authorizeTwitter( redir ) {

	postRequest("OAuth", "requestToken", { _data: {twitter_auth_redir: (redir) ? redir : location.href} },
	function(response) {
		window.location.href = response.URL;
	},'json');

	/*
	var url = location.href+'&oauth_request';
	if( redir ) url = url +'&twitter_auth_redir='+ encodeURIComponent(redir);

	var child = window.open(url,'','toolbar=0,status=0,width=900,height=500');
	*/
}

function deauthorizeTwitter() {

	postRequest('FMA_Global', 'deauthorizeTwitter', '',
	function(response) {
		window.location.href = window.location.href+'&update=TRUE';
	});
}

function getPluginUserCategories() {

	postRequest('FMA_Global', 'getPluginUserCategories', '',
	function(response) {
		fmaFavoriteCategories = response;
	},'json');
}

function populateFavoriteCategoryDialog() {

	jQuery('.fmaUserCategory-selector').empty();

	jQuery.each(fmaFavoriteCategories,function(catNo,catData) {
		jQuery('.fmaUserCategory-selector').append('<li onclick="appendFavoriteToCategory(this,\''+catData.ID+'\')" data-id="'+catData.ID+'">'+catData.Name+'</li>');
	});
}

function appendFavoriteToCategory(Li,ID) {
	jQuery(Li).siblings('li').removeClass('fmaCategorySelected');
	jQuery(Li).addClass('fmaCategorySelected');
}

jQuery('a.fav').live('click',function(e) {
	jQuery('.fmaUserCategories').hide();
	jQuery(this).find('.fmaUserCategories').show();
	e.stopPropagation();
	return false;
});

jQuery(document).live('click',function() {
	jQuery('.fmaUserCategories').hide();
});

