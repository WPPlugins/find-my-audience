<?php
use FindMyAudience\FMA_Global as FMA;
?>
<div id="login-progress">Loading your leads, please wait.. <img src="<?php echo \FindMyAudience::$AppLocation."/assets/images/dots-white.gif"?>"></div>

<div id="favorites-view-selector" class="row" align="center" style="position:absolute;width:100%;top:0px;left:0px;">
	<div style="padding:5px;background:linear-gradient( rgba(255,255,255,1), rgba(255,255,255,0) );width:300px;border-radius:0px 0px 10px 10px;box-shadow:0px 2px 5px rgba(0,0,0,0.3);">
		Show&nbsp;&nbsp;
		<div class="fma-btn-group" role="group" aria-label="List View Type">
			<button onclick="faves_toggleView(this);" id="faves-tile_view_button" type="button" class="button">
				<span class="fma-icon fma-icon-view-icon" aria-hidden="true"></span>
			</button>
			<button onclick="faves_toggleView(this);" id="faves-list_view_button" type="button" class="button btn-active">
				<span class="fma-icon fma-icon-view-list" aria-hidden="true"></span>
			</button>
		</div>
	</div>  
</div>

<br>

<?php include(\FindMyAudience::$AppDir."/assets/templates/autoload.php");?>
<div id="fma-favorites"></div>
<table id="fma-favorites-list"></table>

<script type="text/javascript">

<?php if( !FMA::isFMAUserLoggedIn() ) { ?>
	jQuery(function() {
		jQuery('#login-progress').fadeOut();
		jQuery('#'+FAVORITES_VIEW_ID).html('<br><br><div id="message" class="error"><p>You are not logged in or a connection could not be established to Find My Audience.</p></div>');
	});
<?php } else { ?>

jQuery(function() {

	if(!showFavorites()) {
		jQuery('#'+FAVORITES_VIEW_ID).html('<div id="message" class="updated"><p>No leads have been added.  Click the "reader" icon on a tile or card to add a lead.</p></div>')
	}

	jQuery('#login-progress').fadeOut();
});
<?php } ?>

</script>

