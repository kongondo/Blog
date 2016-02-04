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
		});

		/** Toggle all checkboxes in th for 'posts', 'categories' and 'tags' tables **/
		$('input.toggle_all').click(function(){
			if ($(this).prop('checked')) {
				$('.toggle').prop('checked', true);
			} else {
				$('.toggle').prop('checked', false);
			}
		});

		/** Fix for MarkupAdminDataTable: Don't enable sorting on first column with input checkbox **/
		//if ($.tablesorter != undefined) $.tablesorter.defaults.headers = {0:{sorter:false}};

		//if we are NOT on the widgets or authors tables, then disable sorting on first column
		if (!$('table').hasClass('noDisable')) {
				if ($.tablesorter != undefined) $.tablesorter.defaults.headers = {0:{sorter:false}};//works but requires two clicks to kick-in!
		}
		
		//submit form on select of limit of items to show  - posts, categories, tags
		/*$('#limit').change(function () {
			$(this).closest('form').submit();
		});

		//broken in PW dev 2.5.7. See issue #784 on GitHub
		*/

		$('#limit').change(function(){ $(this).closest("form").removeClass("nosubmit").submit(); });//note workaround for PW issue #784 (GitHub)

	});//end jquery