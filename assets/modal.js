function buildFMAModal_Twitter_User(twitterUser, isFavoritesView, highlightValue) {

	if(!fmaFavorites) loadUserInfo();
	if(!twitterAuth) getTwitterAuth();

        var handle = getTwitterUserHandle(twitterUser.user.channelUserSiteUrl);

	var html = jQuery('#fma-modal-twitter-user').html();

	// For favorited users, find the user's ID from the list of favorites
	// For non-favorited tiles, use the default ID
	var fmaUserId = getTwitterUserId(twitterUser.user.channelUserId);
	if( ! fmaUserId ) fmaUserId = twitterUser.user.id;

	if(twitterUser.user.about == "null") twitterUser.user.about = '';

	html = html.replace(/{userModalId}/g,twitterUser.user.channelUserId);
	html = html.replace('{highlightValue}',highlightValue);
	html = html.replace(/{handle}/g,handle);
	html = html.replace(/{twitterUser.user.id}/g, fmaUserId);
	html = html.replace(/{twitterUser.user.name}/g,twitterUser.user.name);
	html = html.replace('{twitterUser.user.about}', twitterUser.user.about);
	html = html.replace('{twitterUser.user.imageUrl}', twitterUser.user.imageUrl+'" onerror="this.src=\'{Location}/images/twitter-default-person-48.png\'');

	html = html.replace('{numTweets}', convertToCommaSeparatedNumber(twitterUser.user.statusCount.toString()));
	html = html.replace('{numFollowing}', convertToCommaSeparatedNumber(twitterUser.user.friendsCount.toString()));
	html = html.replace('{numFollowers}', convertToCommaSeparatedNumber(twitterUser.user.followersCount.toString()));

        html = html.replace('{isFavorite}',isTwitterUserFavorite(twitterUser.user.channelUserId));
        html = html.replace('{rmTile}', Boolean(isFavoritesView));
        html = html.replace(/{Location}/g,PLUGIN_URL);

        if( isTwitterUserFavorite(twitterUser.user.channelUserId) ) {
                html = html.replace('fma-icon-heart','fma-icon-heart isFavorite');
        }

        html = html.replace('{highlightValue}',highlightValue);


	html = jQuery(html);
	if(isFavoritesView) {
		html.find('.badge').remove();
	}

	var faveTags = [];
	var faveFormattedTweets = [];

	/**
	 * Modal originating from favorite tile
	 */
	if(isFavoritesView) {
		for (var postIndex = 0; postIndex < twitterUser.postings.length; ++postIndex) {
			var tweetHtml = formatTweet(twitterUser.postings[postIndex].posting.text, faveTags);
			faveFormattedTweets.push(tweetHtml);
		}

		var tweetsHtml = fave_tc_getHtmlForRelevantTweets(twitterUser, faveFormattedTweets);

	} else {
	/**
	 * Modal originating from search tile
	 */

		// add known tags to the array (those associated with search terms)
		for ( var i = 0; i < twitterUser.aggregateMatches.length; i++ )
		{
			var agMatch = twitterUser.aggregateMatches[i];
			faveTags.push(agMatch.searchTerm.text );
		}

		// Add non-search terms to the array
		for (var postIndex = 0; postIndex < twitterUser.matches.length; ++postIndex) {
			var tweetHtml = formatTweet(twitterUser.matches[postIndex].tweet.text, faveTags);
		}

		var tweetsHtml = people_getHtmlForRelevantTweets(twitterUser);

	}

	html.find('.relevent-tweets ul').html(tweetsHtml);

	// set the number of rows based on tags
	var numRows = (faveTags.length / 2) + (faveTags.length % 2);

	var rowHtml = '';

	for (var rowNum = 0; rowNum < numRows && rowNum < 4; ++rowNum) {
		// add the new row
		rowHtml += '<div class="fma-row">';

		// add the tag listing structure
		rowHtml += '<div class="fma-col-md-5 fma-col-sm-5">';
		rowHtml += '<ul class="matching-tags">';

		var tagIndex = rowNum * 2;

		// now see if we have tags
		if (tagIndex < faveTags.length) {
			var display = faveTags[tagIndex];
			if (display.length > 15)
				display = (display.substring(0, 13) + '...');
			rowHtml += '<li style="font-size: 16px;">' + display + '</li>';
		}

		if (tagIndex + 1 < faveTags.length) {
			var display = faveTags[tagIndex + 1];
			if (display.length > 15)
				display = (display.substring(0, 13) + '...');
			rowHtml += '<li style="font-size: 16px;">' + display + '</li>';
		}



		rowHtml += '</ul>';
		rowHtml += '</div>';
		rowHtml += '</div>';
	}

	html.find('.tweetList').html(rowHtml);
	html.addClass('loadedModal');

	if( isFmaUserConnectedToTwitter() ) {
		html.find('.twitterEngage-disabled').addClass('fma-hidden');
	} else {
		html.find('.twitterEngage-enabled').addClass('fma-hidden');
	}


	return html;
}

function buildFMAModal_Twitter_Conversation(twitterConvo, isFavoritesView, highlightValue) {

	if(!fmaFavorites) loadUserInfo();
	if(!twitterAuth) getTwitterAuth();

	var searchTermDisplay = ( twitterConvo.searchTerm.text.length > 23 ) ?
		(twitterConvo.searchTerm.text.substring(0,20) + '...' ) :
		twitterConvo.searchTerm.text;

	var html = jQuery('#fma-modal-twitter-conv').html();

	// Search and replace variables
	html = html.replace(/{twitterConvo.searchTerm.id}/g,twitterConvo.searchTerm.id);
	html = html.replace(/{searchTermDisplay}/g,searchTermDisplay);
	html = html.replace(/{convoCardId}/g, twitterConvo.searchTerm.id);

	html = html.replace('{isFavorite}',isFavoritesView);
	html = html.replace('{rmTile}', Boolean(isFavoritesView));
	html = html.replace('{numUsers}',twitterConvo.numUsers);
	html = html.replace('{mostRecentPost}',formatLongFormRelativeDate(twitterConvo.mostRecentPost));
	html = html.replace(/{Location}/g,PLUGIN_URL);

	// Search results vs. favorites use different keys
	if(twitterConvo.numTweets) {
		html = html.replace('{numTweets}',twitterConvo.numTweets);
	} else {
		html = html.replace('{numTweets}',twitterConvo.numPosts);
	}

	if( isTwitterConversationFavorite(twitterConvo.searchTerm.id) ) {
		html = html.replace('fma-icon-heart','fma-icon-heart isFavorite');
	}

	html = html.replace('{highlightValue}',highlightValue);


	/**
	 * Create jQuery object to interact with the DOM
	 */
	html = jQuery(html);
	html.addClass('loadedModal');

	/**
	 * Remove the percent / score badge on favorites
	 */
	if(isFavoritesView) {
		html.find('.badge').remove();
	}

	if( isFmaUserConnectedToTwitter() ) {
		html.find('.twitterEngage-disabled').addClass('fma-hidden');
	} else {
		html.find('.twitterEngage-enabled').addClass('fma-hidden');
	}

	return html;

}



