<div class="fma-modal fma-modal-fade" id="{convoCardId}-tconvosModal" tabindex="-1" role="dialog" aria-labelledby="Conversation Details">
	<div class="fma-modal-dialog fma-modal-lg" style="margin-top:50px;">
		<div class="fma-modal-content">
			<div class="fma-modal-header">
				<button type="button" class="close" data-dismiss="fma-modal" data-id="{convoCardId}-tconvosModal">
					<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
				</button>
				<h4 class="fma-modal-title">{searchTermDisplay}</h4>
			</div>
			<div class="fma-modal-body">
				<div class="fma-row">
					<div class="fma-col-md-6 fma-col-sm-6">
						<div class="fma-row">
							<div class="fma-col-md-2 fma-col-sm-2"><img src="{Location}/images/fma_ic_conv_01tk_.svg" alt="#shoes" class="profile-img" height="43" width="43"></div>
							<div class="fma-col-md-10 fma-col-sm-10">
								
								<h3>keyword conversation<div class="fma-pull-right">
									<!--<a class="fav" href="javascript:void(0)" onclick="tileToggleFavorite(this,{rmTile});" data-id="{convoCardId}" data-favoritestate="{isFavorite}" data-favoritetype="twu" data-favoriteid="{twitterConvo.searchTerm.id}"><span class="fma-icon fma-icon-heart large"></span></a> -->

								</div>&nbsp;</h3>
								<p class="twitter-desc">This conversation includes comments about the {searchTermDisplay} keyword.</p>
							</div>
						</div>
						<div class="fma-row">
							<hr>
							<div class="fma-col-md-10 fma-col-md-offset-2">
								<ul class="twitter-info" style="width:90%;">
									<li><label for="">Tweets</label><span>{numTweets}</span></li>
									<li><label for="">People</label><span>{numUsers}</span></li>
									<li><label for="">Last Post</label><span>{mostRecentPost}</span></li>
								</ul>
							</div>
						</div>
					</div>
					<div class="fma-col-md-6 fma-col-sm-6">
						<div class="fma-row match-reasons">
							<div class="fma-row" style="margin:0px;">
								<div class="fma-col-md-12">
									<h3>This conversation also refers to:</h3>
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
									<div class="initial"><a href="javascript:;" onclick="toggleEngageBox(this);"><i></i>Engage in {searchTermDisplay} conversation on Twitter</a></div>
									<div class="fma-row after">
										<div class="fma-col-md-12">
											<div class="tweet-control" data-id="{searchTermDisplay}">
												<textarea onkeyup="tw_countCharacters(this);" onfocus="javascript:tw_composeMessage(this);" id="{convoCardId}-compose" class="form-control" rows="3" placeholder="{searchTermDisplay}"></textarea>
												<div class="action-bar"><span class="charCount">140</span> &nbsp;&nbsp;&nbsp;<button type="button" onclick="javascript:tw_sendTweet('{convoCardId}');" class="button-primary"><img src="{Location}/images/icon_tweet.png">TWEET</button></div>
											</div>
										</div>
									</div>
								</div>
							</form>

					</div>

					<div class="twitterEngage-disabled">

							<form role="form">
								<div style="background: #f0f0f0;" class="engage inactive">
									<div class="initial"><a href="javascript:;" onclick="toggleEngageBox(this);"><i></i>Engage in {searchTermDisplay} conversation on Twitter</a></div>
									<div class="fma-row after">
										<div class="fma-col-sm-8">
											<div class="tweet-control" data-id="{searchTermDisplay}">
												<textarea class="form-control" disabled="disabled" placeholder="{searchTermDisplay}"></textarea>
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
					<li class="active"><a href="javascript:" data-href="#{convoCardId}-convoTweets" role="tab" data-toggle="tab">Tweets</a></li>
					<li><a href="javascript:" data-href="#{convoCardId}-people" role="tab" data-toggle="tab">People</a></li>
				</ul>
				<div class="tab-content fma-modal-tabs-content">
					<div class="tab-pane active" id="{convoCardId}-convoTweets">
						<div class="tweet-pane recent-tweets">
							<ul id="{convoCardId}-convoTweets-listing">
								<div style="padding:10px;font-size:16px;">Loading... <img src="{Location}/images/dots-white.gif"></div>
							</ul>
						</div>
					</div>
					<div class="tab-pane" id="{convoCardId}-people">
						<div class="fma-row">
							<div class="fma-col-md-6 gr-member-summary" style="height:100px;">
								<img onerror="this.src='images/twitter-default-person-48.png';" src="<?php echo \FindMyAudience::$AppLocation."/assets/images/alAK3Uha_normal.jpeg"?>" class="fma-pull-left" style="height:70px;">
								<h4><a href="javascript:;">Modern Shopping Y, @ModernShoppingY</a></h4>
								<ul>
									<li><strong>Tweets: </strong>255,374</li>
									<li><strong>Following: </strong>7</li>
									<li><strong>Followers: </strong>423</li>
								</ul>
							</div>
							<div class="fma-col-md-6 gr-member-summary" style="height:100px;">
								<img onerror="this.src='images/twitter-default-person-48.png';" src="<?php echo \FindMyAudience::$AppLocation."/assets/images/6YIO6sDa_normal.jpeg"?>" class="fma-pull-left" style="height:70px;">
								<h4><a href="javascript:;">Bjorn, @WeCantStopWont</a></h4>
								<ul>
									<li><strong>Tweets: </strong>40,117</li>
									<li><strong>Following: </strong>5</li>
									<li><strong>Followers: </strong>78</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
