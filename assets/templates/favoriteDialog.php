<div class="fmaUserCategoriesContainer" onclick="event.stopPropagation();">
	<div class="fmaUserCategories">
	<!--<div class="fmaDialogArrow"></div>--!>
		<div data-id="{twitterUser.user.id}" class="fmaUserCategory-selector"></div>
		<div class="fmaUserCategory-create">
			<li onclick="jQuery(this).parent().find('li').toggleClass('fma-hidden');">Create new category</li>
			<li class="fma-hidden"><input style="width:100px;" type="text" placeholder=""> <input type="button" class="button-primary" value="Create"></li>
		</div>
	</div>
</div>



