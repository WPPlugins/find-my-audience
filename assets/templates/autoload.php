<?php
/**
 * This file contains the HTML templates for tiles and fma-modals,
 * and the container into which fma-modals are (invisibly) placed.
 */
?>
<div class="fma-plugin-container">
	<div class="fma-hidden">

		<!-- User tile --!>
		<div id="fma-tile-twitter-user"><?php include("twitterUser.php")?></div>

		<!-- Conversation tile --!>
		<div id="fma-tile-twitter-conv"><?php include("twitterConversation.php")?></div>

		<!-- User modal --!>
		<div id="fma-modal-twitter-user"><?php include("twitterUserModal.php")?></div>

		<!-- Conversation modal --!>
		<div id="fma-modal-twitter-conv"><?php include("twitterConversationModal.php")?></div>

		<!-- Tiles for the Favorites list view --!>
		<table>
			<tbody id="fma-list-twitter-user"><?php include("twitterUser-List.php")?></tbody>
			<tbody id="fma-list-twitter-conv"><?php include("twitterConversation-List.php")?></tbody>
		</table>

	</div>
	<div id="audienceModalContainer"></div>
	<div id="confirmEngagement"><p></p></div>
</div>

