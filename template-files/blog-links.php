<?php 

/**
 * Blogroll template
 * Demo template file. Show a short list of blogroll in a Blog Widget
 *
 */

	//note: in these examples, the code below has now been moved to /site/templates/blog-side-bar.inc
	//we leave this here as an example...
	echo "<h4>{$page->title}</h4>";

	if(count($page->blog_links)) {
			echo "<ul class='links'>";
			foreach($page->blog_links as $link) {echo "<li><a target='_blank' href='{$link->blog_href}'>{$link->blog_headline}</a></li>";}
			echo "</ul>";
	} 

	else {echo "<p>" . __('No links yet.') . "</p>";}

