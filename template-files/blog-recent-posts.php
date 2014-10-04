<?php 

/**
 * Recent Posts template
 * Demo template file. Show a short list of recent posts in a Blog Widget
 *
 */

	//note: in these examples, the code below has now been moved to /site/templates/blog-side-bar.inc
	//we leave this here as an example...
	
	//CALL THE MODULE - MarkupBlog
	$blog = $modules->get("MarkupBlog");

	$limit = $page->blog_quantity;
	
	$posts = $pages->find("template=blog-post, sort=-blog_date, start=0, limit=$limit");	
	
	$parent = null;
	$out = '';

	foreach($posts as $item) {

				$date = $blog->formatDate($item->blog_date);
				$out .= "<li><span class='date'>$date</span> <a href='{$item->url}'>{$item->title}</a></li>";
				$parent = $item->parent; 
	}

	if($out) {
		
				$out = 	"<h4>{$page->title}</h4>" . 
						"<ul class='recent-posts links'>$out</ul>" . 
						"<p>" . 
						"<a class='more' href='{$parent->url}'>" . __('More') . "</a> " . 
						"<a class='rss' href='{$parent->url}rss/'>" . __('RSS') . "</a>" . 
						"</p>";
		
				echo $out; 
	} 

	else {
				echo "<p>No recent posts</p>";
	}

