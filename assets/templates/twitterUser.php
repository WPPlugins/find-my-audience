<div class="fma-col-md-4 fma-tile tile-{tileId}">
	<div class="fma-panel fma-panel-default" id="user-{userCardId}">
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
			<!-- ALPHA CUBE (1/29/2015) - Removed Zoom Icon -->
			<!-- <a href="javascript:;" data-toggle="fma-modal" data-target="#audienceModal" class="fma-pull-right">
				<span class="fma-icon fma-icon-zoom-in"></span>
				</a>  -->
			<!--/ALPHA CUBE -->
			<a class="name" onclick="{popCardHtml}" href="javascript:void(0)" data-toggle="fma-modal" data-target="#audienceModal">{twitterUser.user.name}</a><br>
		</div>
		<div class="fma-panel-body fma-reader-dash-sm">
			<div class="scl-info">
				<div class="fma-pull-left">
					<a onclick="{popCardHtml}" class="handle" href="javascript:void(0)" data-toggle="fma-modal" data-target="#audienceModal">{handle}</a>
				</div>
				<!-- ALPHA CUBE (1/29/2015) - Removed Twitter and email icons -->
				<div class="fma-pull-right">
						<!-- <a class="fav" href="javascript:void(0)" onclick="tileToggleFavoriteCategoryDialog(this); //tileToggleFavorite(this,{rmTile});" data-id="{userCardId}" data-favoritestate="{isFavorite}" data-favoritetype="twu" data-favoriteid="{twitterUser.user.id}"><span class="fma-icon fma-icon-heart"></span> -->
							<a class="fav" href="javascript:void(0)" onclick="tileToggleFavorite(this,{rmTile});" data-id="{userCardId}" data-favoritestate="{isFavorite}" data-favoritetype="twu" data-favoriteid="{twitterUser.user.id}"><span class="fma-icon fma-icon-heart"></span>
					<!-- < ? include('favoriteDialog.php')?> --> 
					</a>
				</div>
				<!--/ ALPHA CUBE (1/29/2015) -->
			</div>
			<div class="fma-tile-avatar">
				<a href="javascript:;" data-toggle="fma-modal" data-target="#audienceModal">
				<img onclick="{popCardHtml}" alt="" src="{twitterUser.user.imageUrl}"/>
				</a>
			</div>
			<div class="fma-reader-info">
				<label>Key Terms:</label>
				<div class="key-terms"></div>
			</div>
			<!-- ALPHA CUBE (1/29/2015) - Added Zoom -->
			<div class="tile-zoom">
				<a href="javascript:;" onclick="{popCardHtml}" data-toggle="fma-modal" data-target="#audienceModal">
					<span class="fma-icon fma-icon-zoom-in"></span>
				</a>
			</div>
			<!-- /ALPHA CUBE -->
		</div>
	</div>
</div>
