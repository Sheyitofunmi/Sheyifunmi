// Functionality for Team profile component
jQuery(".profile-item.is-trigger").on("click", function() {
	jQuery(".profile-item").removeClass("active-profile");
  jQuery(".profile-summary").removeClass("active-profile");
  jQuery(".profile-summary[id='" + jQuery(this).attr("data-overlay") + "'], .profile-overlay:first").addClass("active-profile");
  jQuery(this).addClass("active-profile")
});
jQuery(".profile-summary-close, .profile-overlay").on("click", function() {
  jQuery(".profile-summary, .profile-overlay:first, .profile-item").removeClass("active-profile");
});
