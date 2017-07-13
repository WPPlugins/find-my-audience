function setTwitterUsers(tu)
{
	twitterUsers = tu;
}

/**
 * iterate through all twitter users and build up related tiles & cards
 */
function processResults(twitterUsersResults)
{

	if(!twitterUsersResults) return;
	var twitterUsers = twitterUsersResults.slice(0,SHOW_RESULTS);

	jQuery('#fma-error').hide();
	jQuery('#'+TWITTER_VIEW_ID).empty();
	setTwitterUsers( twitterUsers );

	// Remove all past modals to prevent duplicates
	jQuery('.loadedModal').remove();

        var audienceModalContainer = jQuery(AUDIENCE_MODAL_CONTAINER);

	// remove user tiles
	var twitterAudience = jQuery('#' + TWITTER_VIEW_ID);
	
	// check for no results
	if ( twitterUsers === null || twitterUsers.length === 0 )
	{
		twitterAudience.append('<h2>No Results Found</h2>');
		return;
	}
	
	var totalFollowers = 0;
	var totalFollowing = 0;
	var maxScore = twitterUsers[0].score;
	//var totalScore = 0;
	for ( var item = 0; item < twitterUsers.length; item++ )
	{
		var tUser = twitterUsers[item];

		var highlightValue = '';
		//totalScore +=  tUser.percentageRank;
		
		// set 'highlight' value that appears in the top-right of the tile
		if ( sortOption === 'Score' ) {
			highlightValue = tUser.percentageRank + '';
		}
		else if ( sortOption === 'Tweets' ) 
		{
			highlightValue = tUser.user.statusCount + '';
		}
		else if ( sortOption === 'Followers')
		{
			highlightValue = tUser.user.followersCount + '';
		}
		else if ( sortOption === 'Date' ) 
		{
			var tweetDate = new Date(tUser.matches[0].tweet.postedDate);
			
			var month = tweetDate.getMonth()+1;
			var day = tweetDate.getDate();
			var yr = tweetDate.getFullYear().toString().substring(2);
			
			highlightValue = month + '/' + day + '/' + yr;
		}
		
		//totalFollowers += tUser.user.friendsCount;
		//totalFollowing += tUser.user.followersCount;
		
		// unique id for card
		var userCardId = 'user-' + item; 
		
		// build the people tile
		//var userCardHtml = people_buildTile( userCardId, twitterUsers[item], highlightValue);
		var userCardHtml = buildFMATile_Twitter_User(twitterUsers[item], false, highlightValue);
		twitterAudience.append( userCardHtml );
	
		/**
		 * This is now handled in tiles.js
		 */
		// build the people card
		//var userModalDetails = people_getDetails(userCardId, twitterUsers[item], tUser.percentageRank);
		//audienceModalContainer.append( userModalDetails );
	}
	//showMore(twitterUsersResults);


}

//Increment the # of items to show, and update the Show More footer
function showMoreIncrement( ) {
	SHOW_RESULTS = SHOW_RESULTS+SHOW_RESULTS_STEP;
	if( currentView === TWITTER_CONVERSATION_VIEW_ID ) {
			tc_processResults( twitterConversationResults );
			showMore( twitterConversationResults );
		}
	if( currentView === TWITTER_VIEW_ID ) {
		processResults( twitterResults );
		showMore( twitterResults );
	}
}
//Display the 'Show More' button with correct #'s
function showMore( collection ) {
	if(!collection) return;
	var results = SHOW_RESULTS;
	//processResults(twitterResults);
	/**
	 * The "Show More" expander for search queries
	 */
	if(results > collection.length) results = collection.length;

	jQuery('#fma-results').find('#showMore').remove();
	jQuery('#fma-results').append('<div onclick="showMoreIncrement();" id="showMore">Showing '+results+' of '+collection.length+' results</div>');

	if(results < collection.length) jQuery('#showMore').append('<button class="button">Show More...</button>');

	jQuery('#fma-results').slideDown();
	jQuery('#fma-loading-div').fadeOut('slow');
	jQuery('#fma-loading-div-retrieving').fadeOut('slow');
}


/**
 * returns the user's tweets html
 * @param twitterUser
 * @returns {String}
 */
function people_getHtmlForRelevantTweets(twitterUser)
{
	var handle = getTwitterUserHandle(twitterUser.user.channelUserSiteUrl);
	
	var html = '';
	
	for ( var i = 0; i < twitterUser.matches.length; i++ )
	{
		html += people_getTweetListItemHtml(twitterUser.user.channelUserId, twitterUser.user.name, handle, twitterUser.matches[i].tweet);
	}
	
	return html;
	
}

function people_getRecentTweetHtml(userId, name, handle, tweets)
{
	var html = '';
	
	for ( var i = 0; i < tweets.length; i++ )
	{
		var tweet = tweets[i];
		html += people_getTweetListItemHtml(userId, name, handle, tweet);
	}
	
	return html;


}

function people_getTweetListItemHtml(userId, name, handle, tweet)
{
	return MakeTweet(userId, name, handle, tweet, formatTweet(tweet.text,tags));
}


function MakeTweet(userId, name, handle, tweet, formattedTweet)
{

	// 2015-07-10
	if(!tweet.postedDate) {
		tweet.postedDate = tweet.posting.postedDate;
	}

	var html = '<li id="' + tweet.channelPostingIdAsStr + '"><strong>' + name + '</strong><span>&nbsp;' + handle +
	'&nbsp;<a href="javascript:;">' + formatDate( tweet.postedDate ) +  '</a></span>' + 
	'<p>' + formattedTweet + '</p>';

	/*	
	if ( ! isFmaUserConnectedToTwitter() )
	{
		html += '</li>';
		return html;
	}
	*/

	html += '<div class="twitterEngage-enabled">';

		var myTweet = isMyTweet(tweet.channelPostingIdAsStr);

		var myFavorite = haveIFavorited(tweet.channelPostingIdAsStr);
		var iveRetweeted = haveIRewtweeted(tweet.channelPostingIdAsStr);

		var replyClickParams = '(this,\'' + handle + '\')';





		// 2015-03-30 08:30
		var isDisabled = false;
		if ( myTweet ) isDisabled = true;

		var rt_Params = (isDisabled) ? 'return false;' : 'tw_retweet(this)';
		var fav_Params = (isDisabled) ? 'return false;' : 'tw_favorite(this)';

		var rt_html = '<a data-iveretweeted="'+iveRetweeted+'" onclick="'+rt_Params+'" class="twitter-can-retweet"></a>';
		var fav_html = '<a data-myfavorite="'+myFavorite+'" onclick="'+fav_Params+'" class="twitter-can-favorite"></a>';
		var rp_html = '<a onclick="tw_reply'+replyClickParams+';" class="twitter-can-reply"></a>';

		html += '<div class="tweetActions">'+
			rp_html+
			rt_html+
			fav_html+
		'</div>';
		// End



		// 2015-03-30 10:00

		var twitterUser = [];
		twitterUser.user = [];

		twitterUser.user.channelUserId = userId;
		twitterUser.user.name = name;

		// End

		//var composeWid = tw_getComposeWidgetHtml(true, userId, handle, twitterUser);
		var composeWid = MakeTweetComposeReply(userId, handle, twitterUser);

		html += composeWid;

	// twitterEngage-enabled
	html += '</div>';

	html += '<div class="twitterEngage-disabled"></div>';

		
	html += '</li>';
	return html;

}



function tw_getDirectMessagesHtml(connectionInfo)
{
	var html = '';
	if ( connectionInfo != null && connectionInfo.directMessages != null )
	{
		for ( var i = 0; i < connectionInfo.directMessages.length; i++ )
			html += tw_getDirectMessageListItemHtml( connectionInfo.directMessages[i] );
		
	}
	return html;
}

function tw_getDirectMessageListItemHtml( msg )
{
	return '<li><span>&nbsp;@' + msg.recipientScreenName +
	'&nbsp;' + formatDate( msg.createdDate ) +  '</span>' + 
	'<p>' + msg.text + '</p>' +
	'<div>&nbsp;</div></li>';

}


/**
 * returns the tile html for the given twitter user
 * @param userCardId
 * @param twitterUser
 * @param percent
 * @returns {String}
 */
function people_buildTile(userCardId, twitterUser, percent) {
//
}

function people_getDetails(userCardId, twitterUser, rank)
{
//     
}





/**
 * user clicked to pop audience/person card
 * @param cardId
 * @param userId
 * @param name
 * @param handle
 */
function personCardPopped(cardId, userId, name, handle)
{
//	alert('omg you opened the card for '+name+' (ID #'+userId+')');

	/**
	 * This is not REALLY necessary, but if a user already
	 * has a search open and authorizes/unauthorizes Twitter
	 * from the admin page via a separate tab/window, the
	 * modals still use the original data at the time the
	 * page was loaded.
	 * So we force a fresh check for auth data every time a
	 * modal opens.
	 */	
	getUserConnections(userId);
	getTwitterAuth();

	if( isFmaUserConnectedToTwitter() ) {
		jQuery('.twitterEngage-disabled').addClass('fma-hidden');
		jQuery('.twitterEngage-enabled').removeClass('fma-hidden');
	} else {
		jQuery('.twitterEngage-disabled').removeClass('fma-hidden');
		jQuery('.twitterEngage-enabled').addClass('fma-hidden');
	}

	var modalDetailsId = '#' + userId + MODAL_DETAILS_SUFFIX;	
	jQuery(modalDetailsId).show().animate({opacity: 1.0});

	jQuery("body").css("overflow","hidden");

}

function tw_setUserConnections(cardId, connectionInfo)
{
	if ( connectionInfo.amFollowing) setFollowingButton(cardId);

	var followingMeText = jQuery( '#' + cardId + '-following-me');

	//var lastBut = jQuery('#'+cardId+'-audienceModal .action-bar').find('button:last');
	var lastBut = jQuery('#'+cardId+'-audienceModal .twitter-send-message');

	if ( connectionInfo.followingMe )
	{
		followingMeText.show();
		lastBut.show();
	} else {
		followingMeText.hide();
		lastBut.hide();
	}

	// update direct message tab
	tw_setAudienceDirectMessages(cardId,connectionInfo);
}


function tw_setAudienceDirectMessages(cardId,connectionInfo)
{
	var directMessagesListing = jQuery('#' + cardId + DIRECT_MESSAGES_SUFFIX + '-listing').find('ul');
	var children = directMessagesListing.children();
	for ( var i = 0; i < children.length; i++ )
		jQuery(children[i]).remove();

	directMessagesListing.append(tw_getDirectMessagesHtml(connectionInfo));

}

function setFollowingButton(userCardId)
{
	var modalDetailsId = '#' + userCardId + MODAL_DETAILS_SUFFIX;	
	var button = jQuery(modalDetailsId).find('.followButton');
	button.text("Following").addClass("twitter-following button-primary");	
}


function tw_popConfirmMsg(msg)
{
        if ( msg != null && msg.length > 0 )
        {
		jQuery("#confirmEngagement p:first-child").html(msg);
		jQuery("#confirmEngagement").slideDown().delay(3000).slideUp(750);
        }
}

function clickTwitterFollow(button)
{
	button = jQuery(button);
	var tuHandle = button.data('id').substring(1,100);

	button.blur();

	var isFollowing = button.hasClass("twitter-following");
	if(!isFollowing) {
		followUser(tuHandle, function( data ) {
			if( !data.error ) button.addClass("twitter-following button-primary").find('span').text('Following');
			else tw_popConfirmMsg( 'Follow not successful: ' + data.error );
		});
	} else {
		unfollowUser(tuHandle, function( data ) {
			if( !data.error ) button.removeClass("twitter-following button-primary").find('span').text('Follow');
			else tw_popConfirmMsg( 'Unfollow not successful: ' + data.error );
		});
	}
}


