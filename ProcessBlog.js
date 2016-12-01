/** 
*
* Javascript file for PW Module ProcessBlog
*
* @author Francis Otieno (Kongondo) <kongondo@gmail.com>
*
* https://github.com/kongondo/Blog
* Created February 2014
*
*/

$(document).ready(function(){

<<<<<<< HEAD
	/** Setup fancybox for page edits **/
	var h = $(window).height()-65;
   	var w = $(window).width() > 1150 ? 1150 : $(window).width()-100;
	
	$('.editBlog, .addBlog').fancybox({
		type : 'iframe',
		frameWidth : w,
		frameHeight : h,
		callbackOnClose:function () {
						window.location.reload(true);//force parent page refresh on modal close [note: option for version 1.2 fancybox]
		},
=======
	$('.editBlog').on('pw-modal-closed', function(evt, ui) {
		window.location.reload(true);//force parent page refresh on modal close [note: adapted for magnific popup]
>>>>>>> BitPoet-dev-3x-compat
	});

	/** Fix for MarkupAdminDataTable: Don't enable sorting on first column with input checkbox **/
	//if ($.tablesorter != undefined) $.tablesorter.defaults.headers = {0:{sorter:false}};

<<<<<<< HEAD
	//if we are NOT on the widgets or authors tables, then disable sorting on first column
	if (!$('table').hasClass('noDisable')) {
			if ($.tablesorter != undefined) $.tablesorter.defaults.headers = {0:{sorter:false}};//works but requires two clicks to kick-in!
	}
	
	//submit form on select of limit of items to show  - posts, categories, tags
=======
	// if we are NOT on the widgets or authors tables, then disable sorting on first column
	if (!$('table').hasClass('noDisable')) {
			if ($.tablesorter != undefined) $.tablesorter.defaults.headers = {0:{sorter:false}};// works but requires two clicks to kick-in!
	}
	
	// submit form on select of limit of items to show  - posts, categories, tags
>>>>>>> BitPoet-dev-3x-compat
	/*$('#limit').change(function () {
		$(this).closest('form').submit();
	});

	//broken in PW dev 2.5.7. See issue #784 on GitHub
	*/

<<<<<<< HEAD
	$('#limit').change(function(){ $(this).closest("form").removeClass("nosubmit").submit(); });//note workaround for PW issue #784 (GitHub)

});//end jquery
=======
	$('#limit').change(function(){ $(this).closest("form").removeClass("nosubmit").submit(); });// note workaround for PW issue #784 (GitHub)

});
>>>>>>> BitPoet-dev-3x-compat

/** Toggle all checkboxes in th for 'posts', 'categories' and 'tags' tables **/
$(document).on('change', 'input.toggle_all', function() {
	if ($(this).prop('checked')) $('input.toggle').prop('checked', true);
	else $('input.toggle').prop('checked', false);
});