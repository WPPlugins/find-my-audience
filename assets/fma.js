/**
 * This page contains variables and functions from the various
 * original FMA scripts, merged and converted to plugin-compatible
 * formatting.
 *
 *    NOTE: we MAY want to split this back into multiple files (favorites.js, user.js, global.js)
 *    because it's going to get really messy when we incorporate Facebook and Wordpress functions
 *
 */

/* Added by Jason */
var fmaSession = null;
var SHOW_RESULTS = 9;
var twitterResults = null;
var twitterConversationResults = null;

/* from twitter-engage.js */
var myTweetConnections = null;

/* from user.js */
var fmaFavorites = null;
var fmaFavoriteCategories = null;
var user = null;


/* from global.js */
var TWITTER_VIEW_ID = 'fma-twitter-users';
var TWITTER_CONVERSATION_VIEW_ID = 'fma-twitter-groups';
var FAVORITES_VIEW_ID = 'fma-favorites';
var FAVORITES_LIST_VIEW_ID = 'fma-favorites-list';

var currentView = '';

var showLoginForm = function() {
    jQuery('#fma-register-form').hide();
    jQuery('#fma-login-form').fadeIn();
};
var showRegisterForm = function() {
    jQuery('#fma-login-form').hide();
    jQuery('#fma-register-form').fadeIn();
};

function getTwitterUserHandle(handleUrl) {
    var lastSlash = handleUrl.lastIndexOf('/');
    var handle = '@' + (( lastSlash == -1 ) ? handleUrl : handleUrl.substring(lastSlash + 1));
    return handle;
}

var twitterAuth = null;

/* from view.js */

function showAudience(view) {
    
    //When we change the view, reset the tiles for the view
    if( view === TWITTER_CONVERSATION_VIEW_ID ) {
        tc_processResults( twitterConversationResults );
        showMore( twitterConversationResults );
    }
    if( view === TWITTER_VIEW_ID ) {
        processResults( twitterResults );
        showMore( twitterResults );
    }
    
    if (view === currentView) {
        return;
    } else {
        // hide the current view and sidebar
        if (currentView != '') {
            jQuery('#' + currentView).hide();
        }

        // reset the current view and now display it and its associated sidebar
        currentView = view;
        // show the current view
        jQuery('#' + currentView).show();
    }

}

/*
 * changed to live() rather than loading function on page load,
 * which will make this function persist across DOM changes
 */
jQuery("#audienceSortBy").live('change', function () {

//	if ( currentView !== TWITTER_VIEW_ID )
//		return;

    if (twitterUsers === null || twitterUsers.length == 0)
        return;

    sortOption = jQuery("#audienceSortBy option:selected").text();

    // only support for now
    if (sortOption === 'Score') {
        twitterUsers.sort(function (tu1, tu2) {
            return tu2.score - tu1.score;
        });
        scoreHoverToolTip = defaultUserToolTip;
        processResults(twitterUsers);


        conversationHoverToolTip = defaultConversationToolTip;
        twitterConversations.sort(function (tc1, tc2) {
            return tc2.score - tc1.score;
        });
        tc_processResults(twitterConversations);


    }
    else if (sortOption === 'Followers') {
        twitterUsers.sort(function (tu1, tu2) {
            return tu2.user.followersCount - tu1.user.followersCount;
        });
        scoreHoverToolTip = "This Twitter user's number of followers.";
        processResults(twitterUsers);


        conversationHoverToolTip = "The total numbers of users engaged in this conversation.";
        twitterConversations.sort(function (tc1, tc2) {
            return tc2.numUsers - tc1.numUsers;
        });
        tc_processResults(twitterConversations);


    }
    else if (sortOption === 'Tweets') {
        twitterUsers.sort(function (tu1, tu2) {
            return tu2.user.statusCount - tu1.user.statusCount;
        });
        scoreHoverToolTip = 'The total number of tweets posted by this Twitter user.';
        processResults(twitterUsers);


        conversationHoverToolTip = "The total numbers of tweets in this conversation.";
        twitterConversations.sort(function (tc1, tc2) {
            return tc2.numTweets - tc1.numTweets;
        });
        tc_processResults(twitterConversations);


    }
    else if (sortOption === 'Date') {
        twitterUsers.sort(function (tu1, tu2) {

            var tu2Date = new Date(tu2.matches[0].tweet.postedDate);
            var tu1Date = new Date(tu1.matches[0].tweet.postedDate);

            if (tu2Date > tu1Date)
                return 1;

            if (tu1Date > tu2Date)
                return -1;

            return 0;

        });
        scoreHoverToolTip = "The date of this Twitter user's most recent tweet.";
        processResults(twitterUsers);


        conversationHoverToolTip = "The date of this Twitter conversation's most recent tweet.";
        twitterConversations.sort(function (tc1, tc2) {
            return tc2.mostRecentPost - tc1.mostRecentPost;
        });
        tc_processResults(twitterConversations);


    }
});

// add view listener
//Change View - People or Conversations
jQuery("#twitterViewOptions").live('change', function () {

    viewOption = jQuery("#twitterViewOptions option:selected").text();

    var isTwitterCurrentChannel = (currentView == TWITTER_VIEW_ID || currentView == TWITTER_CONVERSATION_VIEW_ID);

    // only support for now
    if (viewOption === 'People') {
        //twitterResults
        if (isTwitterCurrentChannel) {
            if (currentView != TWITTER_VIEW_ID) {
                showAudience(TWITTER_VIEW_ID);
            }
        }
    } else if (viewOption === 'Conversations') {
        //twitterConversationResults
        if (isTwitterCurrentChannel) {
            if (currentView != TWITTER_CONVERSATION_VIEW_ID) {
                showAudience(TWITTER_CONVERSATION_VIEW_ID);
            }
        }
    }
});

jQuery(function () {
    // set the current view
    showAudience(TWITTER_VIEW_ID);
});


/**
 * utility function to format the given tweet
 * @param tweet
 * @returns {String}
 */
function formatTweet(tweet, tags) {
    var tag = null;

    var frmt = '';
    var isLinkable = false;
    for (var index = 0; index < tweet.length; ++index) {
        var c = tweet.charAt(index);
        switch (c) {
            case '#' :
            case '@' :
                if (!isLinkable) {
                    // start the tag
                    tag = c;
                    // todo
                    frmt += '<a href="javascript:void(0)">';
                    isLinkable = true;
                }
                frmt += c;
                break;
            // check for end of tweet charactrers
            case ' ':
            case ':':
            case '.':
            case ';':
            case '?':
            case '\n':
            case '\t':
            case ',':
                if (isLinkable) {
                    frmt += '</a>';
                    isLinkable = false;
                    // add the tag to the array
                    if (tag != null && shouldTagBeAddedToArray(tag, tags))
                        tags.push(tag);
                    tag = null
                }
                frmt += c;
                break;
            default :
                if (tag != null)
                    tag += c;
                frmt += c;
                break;
        }

    }

    // close the last anchor
    if (isLinkable) {
        // see if we should add the last tag to the array
        if (tag != null && shouldTagBeAddedToArray(tag, tags))
            tags.push(tag);

        frmt += '</a>';
        isLinkable = false;
    }

    return frmt;
}

/**
 * return an html data for the given java date
 * @param javadate
 * @returns {String}
 */
function formatDate(javadate) {
    var dStr = new Date(javadate).toUTCString();
    var segments = dStr.split(' ');

    var format = '';
    for (var index = 0; index < segments.length && index < 4; ++index)
        format += (segments[index] + ' ');

    return format;
}

function shouldTagBeAddedToArray(tag, tags) {
    if (tags == null || tag == null || tag.length == 0)
        return false;
    var tagTl = tag.toLowerCase();

    for (var i = 0; i < tags.length; i++) {
        if (tagTl == tags[i].toLowerCase())
            return false;
    }
    return true;
}


/* from twitter.js */

var audience = null;
var twitterUsers = null;
var twitterConversations = null;
var MODAL_DETAILS_SUFFIX = '-audienceModal';
var RELEVANT_TWEETS_SUFFIX = '-relevantTweets';
var RECENT_TWEETS_SUFFIX = '-recentTweets';
var DIRECT_MESSAGES_SUFFIX = '-directMessages';
var AUDIENCE_MODAL_CONTAINER = "#audienceModalContainer";

var CONVOS_MODAL_DETAILS_SUFFIX = '-tconvosModal';
var CONVOS_MODAL_CONTAINER = "#convosModalContainer";
var CONVOS_RELEVANT_TWEETS_SUFFIX = '-convoTweets';


/* from twitter-conversations.js */
var CONVOS_MODAL_DETAILS_SUFFIX = '-tconvosModal';
var CONVOS_MODAL_CONTAINER = "#convosModalContainer";
var PEOPLE_TAB_SUFFIX = '-people';
var TW_CONVOS_WAIT_CURSOR_ID = 'tw-convos-search-spinner';
var TW_CONVOS_CONTAINER_PEOPLE_WAIT_CURSOR_ID = 'twitterConvosSpinner';
var tc_tags = [];


var TW_WAIT_CURSOR_ID = 'tw-search-spinner';
var TW_CONTAINER_PEOPLE_WAIT_CURSOR_ID = 'twitterPeopleSpinner';
var sortOption = 'Score';
var viewOption = 'People';
var tags = [];

var defaultUserToolTip = 'This Match Score is based on interests related to your book profile expressed by this audience member on Twitter.';
var defaultConversationToolTip = "This Match Score is determined by the volume and date of tweets related to your book, based on hashtags and other keywords.";

var scoreHoverToolTip = defaultUserToolTip;
var conversationHoverToolTip = defaultConversationToolTip;

function formatRelativeDate(javadate) {
    if (javadate == null)
        return 'Unknown';

    var today = new Date();
    var compareTime = new Date(javadate);

    var diffMs = (today - compareTime); // milliseconds between now and the given date
    var diffDays = Math.round(diffMs / 86400000); // days
    var diffHrs = Math.round((diffMs % 86400000) / 3600000); // hours
    var diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000); // minutes

    if (diffDays >= 4)
        return ('' + (compareTime.getUTCMonth() + 1) + '/' + (compareTime.getUTCDate()) + '/' + (compareTime.getFullYear() - 2000));

    if (diffDays >= 1)
        return diffDays + 'd ago';

    if (diffHrs >= 1)
        return diffHrs + 'h ago';

    return diffMins + 'm ago';
}

function formatLongFormRelativeDate(javadate) {
    if (javadate == null)
        return 'Unknown';

    var today = new Date();
    var compareTime = new Date(javadate);

    var diffMs = (today - compareTime); // milliseconds between now and the given date
    var diffDays = Math.round(diffMs / 86400000); // days
    var diffHrs = Math.round((diffMs % 86400000) / 3600000); // hours
    var diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000); // minutes

    if (diffDays >= 4)
        return formatDate(javadate);

    if (diffDays >= 2)
        return diffDays + ' days ago';

    if (diffDays == 1)
        return diffDays + '1 day ago';

    if (diffHrs >= 2)
        return diffHrs + ' hours ago';

    if (diffHrs == 1)
        return diffHrs + '1 hour ago';

    if (diffMins <= 1)
        return '1 minute ago';

    return diffMins + ' minutes ago';
}


function convertToCommaSeparatedNumber(numAsStr) {
    if (!numAsStr || numAsStr == undefined)
        return '0';
    return numAsStr.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}


/**
 * After favoriting/unfavoriting a tile, toggle the heart glyph
 */
function updateFavoriteHTML(faveId, newFavorite) {

    var faveWidget = jQuery(AUDIENCE_MODAL_CONTAINER).find('[data-favoriteid="' + faveId + '"]');
    if (faveWidget.length > 0)
        faveWidget.attr('data-favoriteid', newFavorite);

    faveWidget = jQuery('#' + TWITTER_VIEW_ID).find('[data-favoriteid="' + faveId + '"]');
    if (faveWidget.length > 0)
        faveWidget.attr('data-favoriteid', newFavorite);
}

/**
 * Favorite a tile
 */
function user_requestFmaFavorite(faveId, faveType) {

	postRequest("Twitter", "favoriteTile", { _data: {id:faveId, type:faveType} },
        function (response) {
                tw_popConfirmMsg('Added to leads successfully.');
		updateFavoriteHTML(faveId, response[0]);
		getFmaFavorites();
        }, 'json');
}

/**
 * Unfavorite a tile
 */
function user_requestFmaUnfavorite(faveId, faveType) {

	postRequest("Twitter", "unfavoriteTile", { _data: {id:faveId, type:faveType} },
        function (response) {
                tw_popConfirmMsg('Removed from leads successfully.');
		getFmaFavorites();
        });
}

/**
 * Follow a user
 */
function followUser(handle, callback) {

	postRequest("Twitter", "followUser", { _data: {screenname:handle} },
	function () {
        }, 'json')
        .done(function (data, textStatus, jqXHR) {
            if (callback) callback(data);
        })
        .fail(function (data, textStatus, errorThrown) {
            if (callback) return callback({error: errorThrown});
        });

}

/**
 * Unfollow a user
 */
function unfollowUser(handle, callback) {

	postRequest("Twitter", "unfollowUser", { _data: {screenname:handle} },
	function () {
        }, 'json')
        .done(function (data, textStatus, jqXHR) {
            if (callback) callback(data);
        })
        .fail(function (data, textStatus, errorThrown) {
            if (callback) return callback({error: errorThrown});
        });
}


/**
 * Toggle tile favorite / unfavorite
 */
function tileToggleFavorite(img, rmTile) {

    img = jQuery(img);
    var faveType = img.data('favoritetype');
    var faveId = img.data('favoriteid');

    var tileId = img.data('id'); // channelUserId

    var state = img.data('favoritestate');

    if (!state) {
        jQuery('.fav[data-id="' + tileId + '"]').data('favoritestate', true).find('.fma-icon-heart').addClass('isFavorite');
//		img.blur().data('favoritestate',true).find('.fma-icon-heart').addClass('isFavorite');
        //user_requestFmaFavorite(faveId, faveType);
        user_requestFmaFavorite(tileId, faveType);

        if (rmTile === true) {
            jQuery('.tile-' + tileId).show();
        }

    } else {
        var response = confirm('Are you sure you want to remove this lead?');
        if (response) {

            if (rmTile === true) {
                /**
                 * Remove the associated tile and modal
                 */
                    jQuery('.tile-' + tileId).hide();
            }

            user_requestFmaUnfavorite(tileId, faveType);
//			img.blur().data('favoritestate',false).find('.fma-icon-heart').removeClass('isFavorite');

            jQuery('.fav[data-id="' + tileId + '"]').data('favoritestate', false).find('.fma-icon-heart').removeClass('isFavorite');
        }
    }

    img.blur();
}

function setSessionId(id) {
    fmaSession = id;
}

var init = false;

function loadUserInfo() {

	if(!init) {

		initializeFmaFavorites();

		asyncRequest('FMA_Global', 'getUserData', '',
		function(response) {
			user = response[0];
		});


		tw_requestMyConnections();
		init = true;
	}
}

function tw_requestMyConnections() {

	asyncRequest('Twitter', 'getTwitterConnections', '',
        function (data) {
		myTweetConnections = data[0] || true;
	});
}

/**
 * This function needs to be synchronous because
 * we need to set $fmaFavorites on page load, before
 * anything else
 */
function initializeFmaFavorites() {

	asyncRequest('FMA_Global', 'getPluginUserCategories', '',
        function (response) {
		fmaFavoriteCategories = response;
		populateFavoriteCategoryDialog();
	});

	asyncRequest('FMA_Global', 'getUserFavorites', '',
        function (response) {
	    	fmaFavorites = response[0];
	});
}

/**
 * This function is identical to initializeFmaFavorites(), but
 * asynchronous, so that toggling favorites on/off won't
 * freeze the browser.
 */
function getFmaFavorites() {
	postRequest("FMA_Global", "getUserFavorites", '',
        function (response) {
            fmaFavorites = response[0];
        }, 'json');
}

/**
 * Get my retweeted tweets, favorited tweets,
 * and my own tweets
 */
function getTwitterConnections() {
	postRequest("Twitter", "getTwitterConnections", '',
        function (data) {
            myTweetConnections = data[0];
        }, 'json');
}

/**
 * Check whether this user is following me and if so,
 * get messages sent between us
 */
function getUserConnections(cardId) {
	postRequest("Twitter", "getUserConnections", { _data: {id: cardId} },
        function (data) {
		tw_setUserConnections(cardId, data[0]);
        }, 'json');
}

/**
 * Get Twitter OAuth data
 */
function getTwitterAuth(override) {

	if(!twitterAuth || override) {
		asyncRequest('FMA_Global', 'getTwitterAuth', '',
		function(response) {
			twitterAuth = response;
		},'json');
	}
}

/**
 * Check whether a tile is in my list of favorites
 * Original function used twitterUser.user.id instead of user.channelUserId
 */
function isTwitterUserFavorite(faveId) {
    if (!faveId)
        return false;

    if (!fmaFavorites)
        return false;

    var twitterUsers = fmaFavorites.twitterUsers;
    if (twitterUsers == null || twitterUsers.length == 0)
        return false;

    for (var index = 0; index < twitterUsers.length; ++index) {
//                if ( faveId == twitterUsers[index].user.id )
        if (faveId == twitterUsers[index].user.channelUserId)
            return true;
    }
    return false;

}

/**
 * Used in tiles.js and modal.js
 *
 * FMA stores faves by user.id, but we need them by user.channelUserId.
 * This function merely iterates through the favorites and checks
 * whether a matching user.id is found.
 */
function getTwitterUserId(faveId) {
    if (!faveId)
        return false;

    if (!fmaFavorites)
        return false;

    var twitterUsers = fmaFavorites.twitterUsers;
    if (twitterUsers == null || twitterUsers.length == 0)
        return false;

    for (var index = 0; index < twitterUsers.length; ++index) {
//                if ( faveId == twitterUsers[index].user.id )
        if (faveId == twitterUsers[index].user.channelUserId)
            return twitterUsers[index].user.id;
    }
    return false;
}

/**
 * Used in tiles.js and modal.js
 *
 * Check if we have favorited this conversation
 */
function isTwitterConversationFavorite(faveId) {
    if (!fmaFavorites)
        return false;

    var twitterConversations = fmaFavorites.twitterConversations;
    if (twitterConversations == null || twitterConversations.length == 0)
        return false;

    for (var index = 0; index < twitterConversations.length; ++index) {
        if (faveId == twitterConversations[index].searchTerm.id)
            return true;
    }
    return false;
}


/**
 * Non-bootstrap callback for modal close button
 */
jQuery('button.close').live('click', function ( event ) {
    //Close Modal
    var Dialog = jQuery(this).data('id');
    jQuery('#' + Dialog).animate({opacity: 0}).hide();
    jQuery("body").css("overflow", "auto");
});

/**
 * Non-bootstrap callback for switching tabs within modal
 */
jQuery('.fma-modal-tabs a').live('click', function () {
    jQuery(this).blur();
    var Container = jQuery(this).parent().parent();
    var Tab = jQuery(this).data('href');
    jQuery(Container).find('li').removeClass('active');
    jQuery(Container).find('a[data-href="' + Tab + '"]').parent().addClass('active');

    jQuery(Container).siblings().find('.tab-pane').removeClass('active');
    jQuery(Tab).addClass('active');
});

function loadRecentTweets(name, handle, userId) {

    var Container = jQuery('#' + userId + '-recentTweets-listing').find('ul');

	postRequest("Twitter", "requestRecentTweets", { _data: {id: userId} },
        function (response) {
            if (response.error) {
                jQuery(Container).html(response.error);
            } else {
                var recentTweets = people_getRecentTweetHtml(userId, name, handle, response);
                jQuery(Container).html(recentTweets);
            }
        }, 'json');

}

function loadTweetTerms(searchTermId, isFavoritesView) {

    var Container = jQuery('#' + searchTermId + CONVOS_RELEVANT_TWEETS_SUFFIX).find('.recent-tweets ul');

	postRequest("Twitter", "requestSearchTerms", { _data: {id: searchTermId} },
        function (response) {
            if (response.error) {
                jQuery(Container).html("No tweets found. You may have searched for a term or tag that is not yet searchable (i.e. posted too recently). Please try your search again later.");
            } else {
                var recentTweets = fave_updateConversationsWithTweets(searchTermId, response, isFavoritesView);
                jQuery(Container).html(recentTweets);
            }
        }, 'json');


	postRequest("Twitter", "requestConversation", { _data: {id: searchTermId} },
        function (response) {
            if (!response.error) {
                var recentTweets = fave_updateConversationsWithTweets(searchTermId, response, isFavoritesView);
                jQuery(Container).append(recentTweets);
            }
        });

}

function appendKeyword(searchTermId) {

//	jQuery('#fmaSearchButton').attr('disabled','disabled');

	postRequest("Twitter", "appendKeyword", { _data: {keyword: encodeURIComponent(searchTermId)} },
        function (response) {
//		jQuery('#fmaSearchButton').removeAttr('disabled');
            jQuery('li[data-id="' + searchTermId + '"]').find('.remove').removeClass('searching');
        });
}

function fave_tc_getHtmlForRelevantTweets(twitterUser, faveFormattedTweets) {
    var handle = getTwitterUserHandle(twitterUser.user.channelUserSiteUrl);

    var html = '';

    for (var i = 0; i < twitterUser.postings.length; i++) {
        html += fave_tc_getTweetListItemHtml(twitterUser.user.channelUserId, twitterUser.user.name, handle,
            twitterUser.postings[i], faveFormattedTweets[i]);
    }

    return html;

}


function fave_tc_getTweetListItemHtml(userId, name, handle, tweet, formattedTweet) {
    return MakeTweet(userId, name, handle, tweet, formattedTweet);
}


function fave_updateConversationsWithTweets(convoTileId, postingDetails, isFavoritesView) {
    var card = jQuery('#' + convoTileId + CONVOS_MODAL_DETAILS_SUFFIX);
    var relevantTweets = jQuery('#' + convoTileId + CONVOS_RELEVANT_TWEETS_SUFFIX + '-listing');
    var convoPeople = jQuery('#' + convoTileId + PEOPLE_TAB_SUFFIX);
    var reasonsPane = card.find('.tweetList');

    if (reasonsPane.html() == '') {

        convoPeople.empty();
        relevantTweets.empty();

        var cardDesc = card.find('.twitter-desc');
        var descHtml = cardDesc.html();
        var elementReference = postingDetails[0].matchingSearchTerms[0].elementReference;
        descHtml = descHtml.replace('null', elementReference);
        cardDesc.html(descHtml);

        // get formatted tweets
        var faveTags = [];
//	var faveFormattedTweets = [];
        var userIds = [];
        var userHtml = '';


        for (var postIndex = 0; postIndex < postingDetails.length; ++postIndex) {
            var posting = postingDetails[postIndex].posting;
            var user = postingDetails[postIndex].user;
            var formattedTweetHtml = formatTweet(posting.text, faveTags);


            // 2015-03-30 09:30
            var handle = getTwitterUserHandle(user.channelUserSiteUrl);
            var liTweetHtml = MakeTweet(user.userChannelId, user.name, handle, posting, formattedTweetHtml);
            // End


            relevantTweets.append(liTweetHtml);

            // check for users
            var count = userIds.length;
            if (count < 4) {

                // see if we've already processed this user
                if (userIds.indexOf(user.id) == -1) {
                    if (( count % 2 ) == 0) {
                        userHtml += '<div class="fma-row">';
                        userHtml += tc_getPersonHtml(user);

                    }
                    else {
                        userHtml += tc_getPersonHtml(user);
                        userHtml += '</div>';
                    }
                    userIds.push(user.id);

                }

            }
        }

        // close the last "row" div if necessary
        if ((userIds.length % 2) != 0)
            userHtml += '</div>';

        // update the people tab
        convoPeople.append(userHtml);


        //


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
                if (display.length > 35)
                    display = (display.substring(0, 33) + '...');
                rowHtml += '<li style="font-size: 16px;">' + display + '</li>';
            }

            if (tagIndex + 1 < faveTags.length) {
                var display = faveTags[tagIndex + 1];
                if (display.length > 35)
                    display = (display.substring(0, 33) + '...');
                rowHtml += '<li style="font-size: 16px;">' + display + '</li>';
            }

            // close the tag elements
            rowHtml += '</ul>';
            rowHtml += '</div>';


            // close out row
            rowHtml += '</div>';
        }

//	alert("cur reasons pane html " + reasonsPane.html());
        reasonsPane.append(rowHtml);

    }
}

/**
 * This is ONLY used for MakeTweet()
 *
 * Any changes here MUST be duplicated in both assets/templates/twitterConversation.php
 * and assets/templates/twitterUser.php
 */
function MakeTweetComposeReply(cardId, handle, twitterUser) {

    var html = '<div class="fma-hidden">';

    html += '<form role="form">'
        + '<div style="background: #f0f0f0;" class="engage">'
        + '	<div class="fma-row after">'
        + '		<div class="fma-col-md-12">'
        + '			<div class="tweet-control" data-id="' + handle + '">'
        + '				<textarea onkeyup="tw_countCharacters(this);" onfocus="javascript:tw_composeMessage(this);" id="' + cardId + '-compose" class="form-control" rows="3" placeholder="Compose Message..."></textarea>'
        + '				<div class="action-bar"><span class="charCount">140</span> &nbsp;&nbsp;&nbsp;<button type="button" onclick="javascript:tw_sendReply(this);" class="button-primary"><img src="' + PLUGIN_URL + '/images/icon_tweet.png">REPLY</button></div>'
        + '			</div>'
        + '		</div>'
        + '	</div>'
        + '</div>'
        + '</form>'

    html += '</div>';

    return html;
}

function toggleEngageBox(anchor) {
    jQuery(anchor).closest(".engage").toggleClass("collapsed");
}

/**
 * Count remaining characters for tweets
 */
function tw_countCharacters(messageArea) {

    var maxChars = 140;
    var charCount = jQuery(messageArea).val().length;

    var remainingChars = parseInt(maxChars) - parseInt(charCount);

    jQuery(messageArea).parent().find('.charCount').html(remainingChars);
}

function tw_composeMessage(messageArea) {
    var parentDiv = jQuery(messageArea).parent();
    var msg = jQuery(messageArea).val();
    if (msg == null || msg.length == 0)
        jQuery(messageArea).val(parentDiv.data('id') + ' ');
}

function haveIFavorited(tweetId) {
    if (tweetId == null || myTweetConnections == null)
        return false;
    var favoritedIds = myTweetConnections.favoriteTweetIds;
    if (!favoritedIds)
        return false;

    for (var index = 0; index < favoritedIds.length; index++) {
        if (tweetId == favoritedIds[index])
            return true;
    }
    return false;

}

function haveIRewtweeted(tweetId) {
    if (tweetId == null || myTweetConnections == null)
        return false;
    var retweetedIds = myTweetConnections.retweetedTweetIds;
    if (!retweetedIds)
        return false;

    for (var index = 0; index < retweetedIds.length; index++) {
        if (tweetId == retweetedIds[index])
            return true;
    }
    return false;
}

function isMyTweet(tweetId) {
    if (tweetId == null || myTweetConnections == null)
        return false;
    var myTweetIds = myTweetConnections.myTweetIds;
    if (!myTweetIds)
        return false;

    for (var index = 0; index < myTweetIds.length; index++) {
        if (tweetId == myTweetIds[index])
            return true;
    }
    return false;
}


function tw_sendMessage(userCardId, audienceMemberId) {

    var composeId = audienceMemberId + '-compose';
    var composeElement = jQuery('#' + composeId);

//	var toUser = getTwitterHandleViaJqueryElement( composeElement );

    var toUser = jQuery(composeElement).parent().data('id').replace('@', '');

    var composeMessageParent = composeElement.parent();
    composeMessageParent.removeClass("compose-expanded");
    msgBox = jQuery('#' + composeId);

    var msg = msgBox.val();

    if (msg != null && msg.length > 0) {

	postRequest("Twitter", "sendMessage", { _data: {touser: toUser, text: (msg)} },
            function (data) {
                tw_popConfirmMsg('Your message was sent!');
                msgBox.val('');
            });

    }

}


function tw_sendTweet(userCardId) {
    var composeId = userCardId + '-compose';

    var composeMessageParent = jQuery('#' + composeId).parent();
    composeMessageParent.removeClass("compose-expanded");
    msgBox = jQuery('#' + composeId);

    var msg = msgBox.val();

    if (msg != null && msg.length > 0) {
	postRequest("Twitter", "sendTweet", { _data: {text:(msg)} },
            function (response) {
                if (response && response.error) {
                    tw_popConfirmMsg(response.error);
                } else {
                    tw_popConfirmMsg('Your tweet was posted!');
                    msgBox.val('');
                }
            }, 'json');
    }
}


function tw_reply(img, handle) {

    var liElement = jQuery(img).closest('li');
    var formEl = liElement.find('form').parent();
    if (formEl.is(":visible")) {
        formEl.hide();
    } else {
        formEl.show();
        var taElement = jQuery(formEl).find('textarea');

        var replyText = taElement.val();
        if (replyText == null || replyText.length == 0) {
            taElement.val(handle + ' ');
        }
    }
}

function tw_retweet(img) {
    // have i already retweeted?
    if (jQuery(img).data('iveretweeted') === true)
        return;

    var liElement = jQuery(img).closest('li');

    var retweetId = liElement.attr('id');

	postRequest("Twitter", "retweetTweet", { _data: {tweetid:retweetId} },
        function (data) {
            jQuery(img).attr("data-iveretweeted", true);
            tw_popConfirmMsg('Your tweet was posted!');
            getTwitterConnections();
            msgBox.val('');
        });

}

function tw_favorite(img) {

    var liElement = jQuery(img).closest('li');
    var favId = liElement.attr('id');

	postRequest("Twitter", "favoriteTweet", { _data: {tweetid:favId} },
        function (data) {
            jQuery(img).attr("data-myfavorite", true);
            tw_popConfirmMsg('The tweet has been favorited.');
            getTwitterConnections();
        });
}

function tw_sendReply(replyButton) {
    var formEl = jQuery(replyButton).closest('form').parent();
    var taElement = jQuery(formEl).find('textarea');

    var replyText = taElement.val();


    if (replyText != null && replyText.length > 0) {
        var liElement = jQuery(replyButton).closest('li');
        var replyTweetId = liElement.attr('id');

	postRequest("Twitter", "sendReplyTweet", { _data: {tweetid: replyTweetId, text: replyText} },
            function (data) {
                //alert(data);
                tw_popConfirmMsg('Your tweet was posted!');
                taElement.val('');
                formEl.hide();
            });
    }
}

/**
 * From at4.html
 */
function showFavorites() {
    return showFavorites_list();
}

var hasFmaFavorites = function() {
 if( fmaFavorites && fmaFavorites.twitterUsers && fmaFavorites.twitterUsers.length  ) {
     return true;
 }
    return false;
};

function showFavorites_list() {

    if (!hasFmaFavorites() ) {
        loadUserInfo();
    }

    if (hasFmaFavorites()) {
        var twitterUsers = [fmaFavorites.twitterUsers];
        setTwitterUsers(twitterUsers);
        var twitterConversations = [fmaFavorites.twitterConversations];
    } else {
        return false;
    }

    if ((twitterUsers) && (twitterUsers[0].length > 0)) {

        jQuery('#' + FAVORITES_LIST_VIEW_ID).append('<tbody class="faves-section-header"><tr><td colspan="100%" class="faves-section-header">People</td></tr><tr><td></td><td>Score</td><td>Name</td><td>Handle</td><td>Key Terms</td></tr></tbody>');

        var Tbody = jQuery('<tbody></tbody>');

        jQuery.each(twitterUsers[0], function (twNo, twUser) {
            var cardHtml = buildFMATile_Twitter_User(twUser, true, twUser.percentageRank, true);
            cardHtml.appendTo(Tbody);
        });

        jQuery('#' + FAVORITES_LIST_VIEW_ID).append(Tbody);
    }

    if ((twitterConversations) && (twitterConversations[0].length > 0)) {
        jQuery('#' + FAVORITES_LIST_VIEW_ID).append('<tbody class="faves-section-header"><tr><td colspan="100%" style="padding-top:50px;" class="faves-section-header">Conversations</td></tr><tr><td></td><td>Score</td><td>Keyword</td><td>Tweets</td><td colspan="2">Most Recent Post</td></tr></tbody>');

        var Tbody = jQuery('<tbody></tbody>');

        jQuery.each(twitterConversations[0], function (twNo, twConvo) {
            var cardHtml = buildFMATile_Twitter_Conversation(twConvo, true, twConvo.percentageRank, true);
            cardHtml.appendTo(Tbody);
        });

        jQuery('#' + FAVORITES_LIST_VIEW_ID).append(Tbody);
    }

    if (jQuery('.fma-fave-list-row').length == 0) {
        return false;
    }

    return true;

}

function showFavorites_tiles() {

    if (!fmaFavorites) {
        loadUserInfo();
    }

    if (fmaFavorites) {
        var twitterUsers = [fmaFavorites.twitterUsers];
        setTwitterUsers(twitterUsers);
        var twitterConversations = [fmaFavorites.twitterConversations];
    } else {
        return false;
    }

    if ((twitterUsers) && (twitterUsers[0].length > 0)) {
        var Row = jQuery('<div class="fma-fave-row"></div>');
        jQuery.each(twitterUsers[0], function (twNo, twUser) {
            var cardHtml = buildFMATile_Twitter_User(twUser, true, twUser.percentageRank);
            Row.append(cardHtml);
        });

        jQuery('#' + FAVORITES_VIEW_ID).append('<div class="faves-section-header">People</div>').append(Row);
    }

    if ((twitterConversations) && (twitterConversations[0].length > 0)) {
        var Row = jQuery('<div class="fma-fave-row"></div>');
        jQuery.each(twitterConversations[0], function (twNo, twConvo) {
            var cardHtml = buildFMATile_Twitter_Conversation(twConvo, true, twConvo.percentageRank);
            Row.append(cardHtml);
        });

        jQuery('#' + FAVORITES_VIEW_ID).append('<div class="faves-section-header">Conversations</div>').append(Row);
    }

    if (jQuery('.fma-fave-row').length == 0) {
        return false;
    }

    return true;
}

function faves_isListViewCurrent() {
    var isListViewCurrent = jQuery('#faves-list_view_button').hasClass('btn-active');
    return isListViewCurrent;
}

function faves_toggleView(button) {

    jQuery('#' + FAVORITES_VIEW_ID).empty();
    jQuery('#' + FAVORITES_LIST_VIEW_ID).empty();

    if (faves_isListViewCurrent()) {
        jQuery('#faves-list_view_button').removeClass('btn-active').blur();
        jQuery('#faves-tile_view_button').addClass('btn-active').blur();

        showFavorites_tiles();
    } else {
        jQuery('#faves-tile_view_button').removeClass('btn-active').blur();
        jQuery('#faves-list_view_button').addClass('btn-active').blur();

        showFavorites_list();
    }
}


