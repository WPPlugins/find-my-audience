function setTwitterConversations(tc) {
	twitterConversations = tc;
}

function tc_processResults( convoResults ) {

    var results = convoResults.slice(0,SHOW_RESULTS);
        jQuery('#'+TWITTER_CONVERSATION_VIEW_ID).empty();
	setTwitterConversations(results);
	
	var conversations = jQuery('#' + TWITTER_CONVERSATION_VIEW_ID);
	
	// remove modal details
	var convosModalContainer = jQuery(CONVOS_MODAL_CONTAINER);
	
	if ( results === null || results.length === 0 )
	{
		conversations.append('<h2>No Twitter Results</h2>');
		return;
	}
	
	for ( var item = 0; item < results.length; item++ )
	{
		var result =  results[item];
		var convosTileId = 'tc-' + item; 


                var highlightValue = '';

                // set 'highlight' value that appears in the top-right of the tile
                if ( sortOption === 'Score' ) {
                        highlightValue = result.percentageRank + '';
                }
                else if ( sortOption === 'Tweets' )
                {
                        highlightValue = result.numTweets + '';
                }
                else if ( sortOption === 'Followers')
                {
                        highlightValue = result.numUsers + '';
                }
                else if ( sortOption === 'Date' )
                {
                        var tweetDate = new Date(result.mostRecentPost);

                        var month = tweetDate.getMonth()+1;
                        var day = tweetDate.getUTCDate();
                        var yr = tweetDate.getFullYear().toString().substring(2);

                        highlightValue = month + '/' + day + '/' + yr;
		}


		var twitterAudience = jQuery('#'+TWITTER_CONVERSATION_VIEW_ID);

                var userCardHtml = buildFMATile_Twitter_Conversation(results[item], false, highlightValue);
                twitterAudience.append( userCardHtml );

                /**
		 * This is now handled in tiles.js
	         */
		//jQuery('#'+TWITTER_CONVERSATION_VIEW_ID).append( tc_getHtmlForResult( convosTileId, result, highlightValue));
		
		//var convosModalDetails = tc_getDetails(convosTileId, result);
		//convosModalContainer.append( convosModalDetails );
		
	}
    //showMore(convoResults);
}

/**
 * user clicked to pop audience/person card
 * @param cardId
 * @param userId
 * @param name
 * @param handle
 */
function convoCardPopped(searchTermId, searchTermDisplay, isFavoritesView)
{
        var modalDetailsId = '#' + searchTermId + CONVOS_MODAL_DETAILS_SUFFIX;
        jQuery(modalDetailsId).show().animate({opacity: 1.0});

        jQuery("body").css("overflow","hidden");

	loadTweetTerms(searchTermId, isFavoritesView);
}

function tc_getPersonHtml(user)
{
	
	var html = '';
	html += '<div class="fma-col-md-6 gr-member-summary" style="height:100px;">';
	// TODO -- JAB -- if the image is too short, the "Interests" row is left-justified
	html += '<img onerror="this.src=\''+PLUGIN_URL+'/images/twitter-default-person-48.png\';" src="' + user.imageUrl + '" class="fma-pull-left" style="height:70px;" />';
    html += '<h4><a href="javascript:;">' + user.name;
    html += ', ' +  getTwitterUserHandle(user.channelUserSiteUrl);
    
    html += '</a></h4>';
    html += '<ul>';
    

    
    	html += '<li>';
        	html += '<strong>Tweets: </strong>' + convertToCommaSeparatedNumber( user.statusCount.toString() );
        html += '</li>';
        html += '<li>';
        	html += '<strong>Following: </strong>' + convertToCommaSeparatedNumber( user.friendsCount.toString() )
        html += '</li>';
        
        html += '<li>';
        	html += '<strong>Followers: </strong>' + convertToCommaSeparatedNumber( user.followersCount.toString() ) ;
        html += '</li>';
        
    html += '</ul>';
    html += '</div>';
    
    return html;

}



