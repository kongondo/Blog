$(document).ready(function() {

	/* ORIGINAL CODE FROM THE BLOG PROFILE BY RYAN CRAMER. Added and amended here by Kongondo */

	// mobile navigation
	$("select.nav").change(function() {
		window.location.href = $(this).val();
	}); 

	// Move navigation and search around, depending on whether mobile or desktop
	$(window).resize(function() {
		if($("#top-nav").find("form.mobile").is(":visible")) {
			var $subnav = $("#nav #sub-nav"); 
			if($subnav.size() > 0) {
				$("#sidebar").prepend($subnav); 
				//$("#sidebar").append($("#site-search-form")); 
				//$("#site-search").prepend($("#topnav")); 
			}
		} else {
			$subnav = $("#sidebar #sub-nav"); 
			if($subnav.size() > 0) {
				$("#nav").append($subnav); 
				/*$("#site-search").prepend($("#site-search-form")); */
				$("#nav").prepend($("#top-nav")); 	
			}
		}
	}).resize();

}); 

