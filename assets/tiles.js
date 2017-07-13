/**
 * This page exists solely to populate HTML tiles
 * for Twitter users and conversations.
 * Original HTML for the tile *MUST* be included in the PHP file
 * calling this function! (into div #fma-tile-twitter-user)
 */

function buildFMATile_Twitter_User(twitterUser, isFavoritesView, highlightValue, isListView) {

	if(!fmaFavorites) loadUserInfo();


	var userCategory = fmaFavoriteCategories[Math.floor(Math.random()*fmaFavoriteCategories.length)];


	var handle = getTwitterUserHandle(twitterUser.user.channelUserSiteUrl);

        var modalDetailsId = '#' + twitterUser.user.id + MODAL_DETAILS_SUFFIX;
        var params = "('" + twitterUser.user.id + "','" + twitterUser.user.channelUserId  + "','" + twitterUser.user.name + "','" + handle + "')";
        var popCardHtml = 'javascript:personCardPopped' + params + '; jQuery(this).blur()';

	// Get the html for a new tile
	if(isListView) {
		var html = jQuery('#fma-list-twitter-user').html();
	} else {
		var html = jQuery('#fma-tile-twitter-user').html();
	}

	// For favorited users, find the user's ID from the list of favorites
	// For non-favorited tiles, use the default ID
	var fmaUserId = getTwitterUserId(twitterUser.user.channelUserId);
	if( ! fmaUserId ) fmaUserId = twitterUser.user.id;

	// Search and replace variables
	html = html.replace(/{twitterUser.user.id}/g, fmaUserId);
	html = html.replace(/{twitterUser.user.name}/g,twitterUser.user.name);
	html = html.replace(/{userCardId}/g, twitterUser.user.channelUserId);
	html = html.replace(/{popCardHtml}/g, popCardHtml);
	html = html.replace(/{handle}/g, handle);
	html = html.replace(/{twitterUser.user.imageUrl}/g,twitterUser.user.imageUrl+'" onerror="this.src=\'{Location}/images/twitter-default-person-48.png\'');

	html = html.replace(/{scoreHoverToolTip}/g,scoreHoverToolTip);
	html = html.replace(/{isFavorite}/g,isTwitterUserFavorite(twitterUser.user.channelUserId));
	html = html.replace(/{rmTile}/g, Boolean(isFavoritesView));
	html = html.replace(/{Location}/g, PLUGIN_URL);

	if( isTwitterUserFavorite(twitterUser.user.channelUserId) ) {
		html = html.replace(/fma-icon-heart/g,'fma-icon-heart isFavorite');
	}

	html = html.replace(/{highlightValue}/g,highlightValue);

	html = html.replace(/{userCategory}/g,userCategory.Name);
	if(isFavoritesView) {
		html = html.replace(/{tileId}/g, twitterUser.user.channelUserId);
	} else {
		html = html.replace(/{tileId}/g, '');
	}

	/**
	 * Create jQuery object to interact with the DOM
	 */
	html = jQuery(html);

	/**
	  * Append either the matching terms or the recent tweets
	  * to the tile
	  */
	tweetContainer = html.find('.key-terms');


	if(isFavoritesView) {
		/**
	       	* Favorites tile
		*/

		var faveTags = [];
		var faveFormattedTweets = [];

		for (var postIndex = 0; postIndex < twitterUser.postings.length; ++postIndex) {
			var tweetHtml = formatTweet(twitterUser.postings[postIndex].posting.text, faveTags);
			faveFormattedTweets.push(tweetHtml);
		}

		if(isListView) {
			var faveTags = faveTags.join(', ');
			jQuery(tweetContainer).append('<span href="javascript:" style="color:#990700;">' + faveTags + '</span><br/>');
		} else {
			for (var i = 0; i < faveTags.length && i < 2; i++) {
				var display = faveTags[i];
				if (display.length > 15) display = (display.substring(0, 13) + '...');
				jQuery(tweetContainer).append('<span href="javascript:" style="color:#990700;">' + display + '</span><br/>');
			}
		}

		if (faveTags.length < 2) jQuery(tweetContainer).append('<br/>');

	} else {
		/**
		  * Normal tile (search matches)
		  */ 
		if(twitterUser.aggregateMatches) {
			for ( var i = 0; i < twitterUser.aggregateMatches.length && i < 2; i++ )
			{
				var agMatch = twitterUser.aggregateMatches[i];
				var display = agMatch.searchTerm.text;
				if ( display.length > 15)
					display = (display.substring(0,13) + '...');
			    jQuery(tweetContainer).append('<span style="color:#990700;">' + display + '</span><br/>');
			}

			if ( twitterUser.aggregateMatches.length < 2 ) jQuery(tweetContainer).append('<br/>');
		}

	}

	/* End favorites / terms */


	/* Create modal */

        var audienceModalContainer = jQuery(AUDIENCE_MODAL_CONTAINER);

	// build the people card
	var userModalDetails = buildFMAModal_Twitter_User(twitterUser, isFavoritesView, highlightValue);
	audienceModalContainer.append( userModalDetails );

	/* End modal */


	/* Tmp until we get categories up and running on the server */
	jQuery(html).find('.fmaUserCategory-selector li[data-id="'+userCategory.ID+'"]').addClass('fmaCategorySelected');



	return html;
}

function buildFMATile_Twitter_Conversation(twitterConvo, isFavoritesView, highlightValue, isListView) {

	if(!fmaFavorites) loadUserInfo();

	var searchTermDisplay = ( twitterConvo.searchTerm.text.length > 23 ) ?
		(twitterConvo.searchTerm.text.substring(0,20) + '...' ) :
		twitterConvo.searchTerm.text;

	var modalDetailsId = twitterConvo.searchTerm.id + CONVOS_MODAL_DETAILS_SUFFIX;
	var params = "('" + twitterConvo.searchTerm.id + "', '" + searchTermDisplay + "', "+isFavoritesView+")";
	var popCardHtml = 'javascript:convoCardPopped' + params + ';';

	// Get the html for a new tile
	if(isListView) {
		var html = jQuery('#fma-list-twitter-conv').html();
	} else {
		var html = jQuery('#fma-tile-twitter-conv').html();
	}

	// Search and replace variables
	html = html.replace('{twitterConvo.searchTerm.id}',twitterConvo.searchTerm.id);
	html = html.replace('{searchTermDisplay}',searchTermDisplay);
	html = html.replace(/{convoCardId}/g, twitterConvo.searchTerm.id);
	html = html.replace(/{popCardHtml}/g, popCardHtml);

	html = html.replace('{scoreHoverToolTip}',scoreHoverToolTip);
	html = html.replace('{isFavorite}',isFavoritesView);
	html = html.replace('{rmTile}', Boolean(isFavoritesView));
	html = html.replace('{numUsers}',twitterConvo.numUsers);
	html = html.replace('{mostRecentPost}',formatRelativeDate(twitterConvo.mostRecentPost));
	html = html.replace(/{Location}/g,PLUGIN_URL);

	// Search results vs. favorites use different keys
	if(twitterConvo.numTweets) {
		html = html.replace('{numTweets}',twitterConvo.numTweets);
	} else {
		html = html.replace('{numTweets}',twitterConvo.numPosts);
	}

	if( isTwitterConversationFavorite(twitterConvo.searchTerm.id) ) {
		html = html.replace(/fma-icon-heart/g,'fma-icon-heart isFavorite');
	}

	html = html.replace('{highlightValue}',highlightValue);


	/**
	 * Create jQuery object to interact with the DOM
	 */
	html = jQuery(html);

	/**
	  * Append either the matching terms or the recent tweets
	  * to the tile
	  */
	tweetContainer = html.find('.key-terms');



	/* Create modal */

        var audienceModalContainer = jQuery(AUDIENCE_MODAL_CONTAINER);

	// build the people card
//	if(isFavoritesView) {
	var convoModalDetails = buildFMAModal_Twitter_Conversation(twitterConvo, isFavoritesView, highlightValue);
	audienceModalContainer.append( convoModalDetails );
//	}
	/* End modal */


	return html;
}

