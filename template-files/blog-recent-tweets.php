<?php 

/**
 * recent-tweets template
 * Example template file. Show a short list of recent tweets in a Blog Widget
 *
 */

	//note: in these examples, the code below has now been moved to /site/templates/includes/blog/side-bar.inc
	//we leave this here as an example...
	$limit = $page->blog_quantity;
	$twitterName = $page->note;

	$t = wire('modules')->get('MarkupTwitterFeed');
	$t->limit = $page->quantity;
	$t->cacheSeconds = 3600;
	$t->dateFormat = wire('fields')->get('blog_date')->dateOutputFormat;
	$t->showHashTags = true; 
	$t->showName = false;
	$t->showDate = 'before';
	$t->listOpen = "<ul class='MarkupTwitterFeed links'>";
	$t->listItemDateOpen = "<span class='date'>";
	$t->listItemDateClose = "</span><br />";

	echo "<h4 class='twitter-headline'>" . __('Recent Tweets') . "</h4>"; 
	echo $t->render("https://api.twitter.com/1/statuses/user_timeline.rss?screen_name=$twitterName");
	echo "<p><a class='more more-twitter' href='http://twitter.com/$twitterName/'>" . __('More') . "</a></p>"; 