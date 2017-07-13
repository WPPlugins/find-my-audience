<div class="fma-col-md-4 fma-tile">
	<div class="fma-panel fma-panel-default" id="tc-{convoCardId}">
		<div class="fma-panel-heading fma-reader-summary">
			<div class="badge-wrapper fma-pull-right">
                                <span class="badge">{highlightValue}</span>
                                <div class="hovertip">
                                        <div class="tooltip-content">
                                                <p>{scoreHoverToolTip}</p>
                                        </div>
                                        <div class="tip"></div>
                                </div>
                        </div>
			<a onclick="{popCardHtml}" class="name" href="javascript:;" data-toggle="fma-modal" data-target="#tc-0-tconvosModal">{searchTermDisplay}</a><br>
		</div>
		<div class="fma-panel-body fma-reader-dash-sm">
			<div class="scl-info">
				<div class="fma-pull-left"><a class="handle" href="javascript:void(0);">keyword</a></div>
				<div class="fma-pull-right">
					<!-- <a class="fav" href="javascript:void(0)" onclick="tileToggleFavorite(this,{rmTile});" data-id="{convoCardId}" data-favoritestate="{isFavorite}" data-favoritetype="twcv" data-favoriteid="{twitterConvo.searchTerm.id}"><span class="fma-icon fma-icon-heart"></span></a> -->
				</div>

			</div>
			<div class="fma-tile-avatar"><img alt="" src="{Location}/images/fma_ic_conv_01tk_.svg"></div>
			<div class="fma-reader-info">
				<dl class="inverse">
					<dt>TWEETS</dt>
					<dd>{numTweets}</dd>
					<dt>PEOPLE</dt>
					<dd>{numUsers}</dd>
					<dt>LAST POST</dt>
					<dd>{mostRecentPost}</dd>
				</dl>
			</div>

                        <div class="tile-zoom">
                                <a href="javascript:;" onclick="{popCardHtml}" data-toggle="fma-modal" data-target="#audienceModal">
                                <span class="fma-icon fma-icon-zoom-in"></span>
                                </a>
                        </div>

		</div>
	</div>
</div>
