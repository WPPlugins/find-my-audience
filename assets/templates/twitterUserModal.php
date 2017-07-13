<div class="fma-modal fma-modal-fade" id="{userModalId}-audienceModal" tabindex="-1" role="dialog" aria-labelledby="Audience Details">
	<div class="fma-modal-dialog fma-modal-lg" style="margin-top:50px;">
		<div class="fma-modal-content">
			<div class="fma-modal-header">
				<button type="button" class="close" data-dismiss="fma-modal" 
						data-tileid="{userModalId}"
						data-id="{userModalId}-audienceModal">
					<span aria-hidden="true">&times;</span><span class="sr-only">Close</span> 
				</button> 

				<div class="fma-pull-right btn-actions">
					<button data-id="{handle}" onclick="clickTwitterFollow(this);" type="button" class="followButton button"><img src="{Location}/images/icon_follow.png"><span>Follow</span></button>
				</div>

				<h4 class="fma-modal-title" id="audienceName"><span class="badge">98</span>&nbsp;{twitterUser.user.name}</h4>
			</div>
			<div class="fma-modal-body">
				<div class="fma-row">
					<div class="fma-col-md-6 fma-col-sm-6">
						<div class="fma-row">
							<div class="fma-col-md-2 fma-col-sm-2"><img src="{twitterUser.user.imageUrl}" alt="{twitterUser.user.name}" class="profile-img" height="43" width="43"></div>
							<div class="fma-col-md-10 fma-col-sm-10">

								<h3>{handle}<div class="fma-pull-right">
									<a class="fav" href="javascript:void(0)" onclick="tileToggleFavorite(this,{rmTile});" data-id="{userModalId}" data-favoritestate="{isFavorite}" 
									   data-favoritetype="twu" 
									   data-favoriteid="{twitterUser.user.id}"
									><span class="fma-icon fma-icon-heart large"></span></a>

								</div>&nbsp;</h3>
								<div id="{userModalId}-following-me" style="display:none; font-size: 13px; color: #8899a6;">FOLLOWS YOU</div>
								<p class="twitter-desc">{twitterUser.user.about}</p>
								<p></p>
							</div>
						</div>
						<div class="fma-row">
							<hr>
							<div class="fma-col-md-10 fma-col-md-offset-2">
								<ul class="twitter-info">
									<li><label for="">Tweets</label><span>{numTweets}</span></li>
									<li><label for="">Following</label><span>{numFollowing}</span></li>
									<li><label for="">Followers</label><span>{numFollowers}</span></li>
								</ul>
							</div>
						</div>
					</div>
					<div class="fma-col-md-6 fma-col-sm-6">
						<div class="fma-row match-reasons">
							<div class="fma-row" style="margin:0px;">
								<div class="fma-col-md-12">
									<h3>{twitterUser.user.name} is tweeting about:</h3>
								</div>
							</div>


							<div class="fma-row">
								<div class="fma-col-md-5 fma-col-sm-5">
									<u style="font-size:14px;">Most Used Tags</u>
								</div>
							</div>

							<div class="tweetList"></div>

						</div>
					</div>
				</div>


				<div class="twitterEngage">

					<div class="twitterEngage-enabled">

							<form role="form">
								<div style="background: #f0f0f0;" class="engage">
									<!--
									<a class="twitter-share-button"
									   href="https://twitter.com/intent/tweet?text={handle}">
										Tweet</a>
										-->
									<div class="initial"><a href="javascript:;" onclick="toggleEngageBox(this);"><i></i>Compose message to {twitterUser.user.name} on Twitter</a></div>
									<div class="fma-row after">
										<div class="fma-col-md-12">
											<div class="tweet-control" data-id="{handle}">
												<textarea onkeyup="tw_countCharacters(this);" 
														  onfocus="javascript:tw_composeMessage(this);" 
														  id="{userModalId}-compose" class="form-control" rows="3" 
														  placeholder="Compose Message..."></textarea>
												<div class="action-bar"><span class="charCount">140</span> &nbsp;&nbsp;&nbsp;<button type="button" onclick="javascript:tw_sendTweet('{userModalId}');" 
																																	 class="button-primary"><img src="{Location}/images/icon_tweet.png">TWEET</button><button 
														type="button" style="margin-left:2px;" onclick="javascript:tw_sendMessage('{twitterUser.user.id}','{userModalId}');" 
														style="display:none" class="button-primary twitter-send-message">DIRECT MESSAGE <i class="fma-icon fma-icon-chevron-right"></i></button></div>
											</div>
										</div>
									</div>
								</div>
							</form>

					</div>

					<div class="twitterEngage-disabled">

							<form role="form">
								<div style="background: #f0f0f0;" class="engage collapsed inactive">
									<!--
									<a class="twitter-share-button"
									   href="https://twitter.com/intent/tweet?text={handle}">
										Tweet</a>
										-->
									<div class="initial"><a href="javascript:;" onclick="toggleEngageBox(this);"><i></i>Compose message to {twitterUser.user.name} on Twitter</a></div>
									<div class="fma-row after">
										<div class="fma-col-sm-8">
											<div class="tweet-control" data-id="{handle}">
												<textarea class="form-control" disabled="disabled" placeholder="{handle}"></textarea>
												<div class="action-bar">140 &nbsp;&nbsp;&nbsp;<button type="button" class="button-primary" disabled="disabled"><img src="{Location}/images/icon_tweet.png">TWEET</button></div>
											</div>
										</div>
										<div class="fma-col-sm-4">
											<div class="engage-mesg">
												<p>Sign in to Twitter to authorize Find My Audience to interact with Twitter.</p>
												<a href="javascript:" onclick="authorizeTwitterRemote('<?php echo get_admin_url()?>/admin.php?page=<?php echo dirname(dirname(dirname(plugin_basename(__FILE__))))?>/core/admin.php')"><img src="<?php echo \FindMyAudience::$AppLocation."/assets/images/sign-in-with-twitter-gray.png"?>" alt="Sign in with Twitter" title="Sign in with Twitter"></a>
											</div>
										</div>
									</div>
								</div>
							</form>









					</div>

				</div>



				<ul class="fma-modal-nav fma-modal-nav-pills fma-modal-tabs" role="tablist">
					<li class="active"><a href="javascript:" data-href="#{userModalId}-relevantTweets" role="tab" data-toggle="tab">Relevant Tweets</a></li>
					<li><a href="javascript:" onclick="loadRecentTweets('{twitterUser.user.name}', '{handle}', '{userModalId}')" data-href="#{userModalId}-recentTweets" role="tab" data-toggle="tab">Recent Tweets</a></li>
					<!-- <li><a href="javascript:" data-href="#{userModalId}-directMessages" role="tab" data-toggle="tab">My Messages to/from {twitterUser.user.name}</a></li> -->
				</ul>
				<div class="tab-content fma-modal-tabs-content">
					<div class="tab-pane active" id="{userModalId}-relevantTweets">
						<div class="tweet-pane relevent-tweets" id="{userModalId}-relevantTweets-listing">
							<ul></ul>
						</div>
					</div>
					<div class="tab-pane" id="{userModalId}-recentTweets">
						<div class="tweet-pane recent-tweets" id="{userModalId}-recentTweets-listing">
							<ul>
								<div style="padding:10px;font-size:16px;">Loading... <img src="{Location}/images/dots-white.gif"></div>
							</ul>
						</div>
					</div>
					<div class="tab-pane" id="{userModalId}-directMessages">
						<div class="tweet-pane direct-tweets" id="{userModalId}-directMessages-listing">
							<ul></ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
