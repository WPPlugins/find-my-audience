<?php
/**
 * Config file, containing URLs, tokens,
 * etc.
 */

$CONFIG = array(
	'nonce_check'		=> 'fma_nonce',

	'passwordsalt'		=> '74D9CE8B7E4B458C8B3A41110E7A3435A7D1D3BB',
//	'BaseURL'		=> 'http://fmaapi.us-west-2.elasticbeanstalk.com/fma/',
	'BaseURL'		=> 'http://corporate.findmyaudience.com/fma/',
	'XXBaseURL'		=> 'http://findmyaudience.us-west-2.elasticbeanstalk.com/fma/',

	'AppURL'		=> 'wordpress/findmine',
	//'WPCategories'		=> 'wordpress/categories',
	'AccountURL'		=> 'wordpress/verifyemail',
	'LoadAudience'		=> 'wordpress/audience',

	'ProfileURL'		=> 'user',
	'LoginURL'		=> 'user/verifylogin',
	'LogoutURL'		=> 'user/logout',
	'RegisterURL'		=> 'user/register',
	'FavoritesURL'		=> 'userwork/favorites',
	'AddFaveURL'		=> 'userwork/favorite',
	'DelFaveURL'		=> 'userwork/unfavorite',
	'ValidateSession'		=> 'user/validatesession',

	'TwitterPeople'		=> 'profile/newresults',
	'TwitterConvos'		=> 'profile/conversations',
	'TwitterTerms'		=> 'profile/twitterconvo',
	'KeywordURL'		=> 'profile/keywordSearch',

	'TweetsURL'		=> 'twitter/recenttweets',
	'TermsURL'		=> 'twitter/searchtermtweets',

	'Twitter'	=> array(
				'API'		=> 'https://api.twitter.com/1.1',
				'Tweet'		=> '/statuses/update',
				'Retweet'	=> '/statuses/retweet',
				'Favorites'	=> '/favorites/list',
				'AddFave'	=> '/favorites/create',
				'DelFave'	=> '/favorites/destroy',
				'Followers'	=> '/followers/list',
				'Message'	=> '/direct_messages/new',

				'MyTweets'	=> '/statuses/user_timeline',
				'MyMentions'	=> '/statuses/mentions_timeline',
				'MyFavorites'	=> '/favorites/list',
				'MyFollowers'	=> '/followers/list', # or /ids
				'MyMessages'	=> '/direct_messages/sent',
			),
	'EngageURL'		=> 'twitter/tweet',
	'RetweetURL'		=> 'twitter/retweet',
	'AddFaveTweetURL'	=> 'twitter/favorite',
	'DelFaveTweetURL'	=> 'twitter/unfavorite',
	'TweetReplyURL'		=> 'twitter/reply',
	'TweetConnectionsURL'	=> 'twitter/tweetconnections',
	'ConnectionsURL'	=> 'twitter/connections',
	'MessagesURL'		=> 'twitter/message',
	'FollowURL'		=> 'twitter/follow',
	'UnfollowURL'		=> 'twitter/unfollow',
	'OAuthKeysURL'		=> 'twitter/keys',
	'OAuthURL'		=> 'twitter/oauth',
	'DefaultUserCategories'		=> array( array( 'ID' => 0, 'Name'=> 'Promising leads' ),
 						array( 'ID' => 1, 'Name' => 'New People' ),
						array( 'ID' => 2, 'Name' => 'People I follow' ),
						array( 'ID' => 4, 'Name' => 'Everybody else' ),
	)

);

?>
