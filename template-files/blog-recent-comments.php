<?php 

/**
 * Recent Comments template
 * Demo template file. Show a short list of recent comments in a Blog Widget
 *
 */

	//note: in these examples, the code below has now been moved to /site/templates/blog-side-bar.inc
	//we leave this here as an example...
	
	//CALL THE MODULE - MarkupBlog
	$blog = $modules->get("MarkupBlog");

	$url = $pages->get('template=blog-comments')->url;//we adapt selector to blog style

	$out =	"<h4>" . $page->title . "</h4>";

	$limit = $page->blog_quantity;

	$comments = $blog->findRecentComments($limit, 0, false);//false = in the sidebar, do not show pending or spam comments whether admin is logged in or not

	if(count($comments)) {

			$out .= "<ul class='links'>";

			foreach($comments as $comment) {

						$cite = htmlentities($comment->cite, ENT_QUOTES, "UTF-8");
						$date = $blog->formatDate($comment->created, 2); 

						$out .= "<li><span class='date'>$date</span><br />" . 
								"<a href='{$comment->page->url}#comment{$comment->id}'>$cite &raquo; {$comment->page->title}</a>" . 
								"</li>";
			}

			$out .= "</ul>";


			$out .= "<p>" . 
				"<a class='more' href='$url'>" . __('More') . "</a>  " . 
				"<a class='rss' href='{$url}rss/'>" . __('RSS') . "</a>" . 
				"</p>";

	} 

	else {
		
			$out .= "<p>" . __('No comments yet') . "</p>";
	}

	echo $out;

