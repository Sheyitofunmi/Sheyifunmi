jQuery(document).ready(function( $ ) {
	var admin_opt = $("#role option[value='administrator']");
	var admin_list_link = $('.subsubsub li.administrator');

	// Remove from user-new.php screen
	if ( admin_opt.length > 0 ) {
		admin_opt.remove();
	}
	// Remove from users.php
	if ( admin_list_link.length > 0 ) {
		admin_list_link.remove();
	}

});
