<?php 

/**
 * Recent Tweets template
 * Demo template file. Show a short list of recent tweets in a Blog Widget
 * See all options and usage info here: http://modules.processwire.com/modules/markup-twitter-feed/
 *
 */

	$twitterName = $page->blog_note;

	$t = $modules->get('MarkupTwitterFeed');
	$t->limit = $page->blog_quantity;
	$t->cacheSeconds = 3600;
	$t->dateFormat = wire('fields')->get('blog_date')->dateOutputFormat;
	$t->showHashTags = true; 
	$t->showDate = 'before';
	$t->listOpen = "<ul class='MarkupTwitterFeed links'>";
	$t->listItemDateOpen = "<span class='date'>";
	$t->listItemDateClose = "</span><br />";

	echo "<h4 class='twitter-headline'>" . __('Recent Tweets') . "</h4>"; 
	echo $t->render("https://api.twitter.com/1/statuses/user_timeline.rss?screen_name=$twitterName");
	echo "<p><a class='more more-twitter' href='http://twitter.com/$twitterName/'>" . __('More') . "</a></p>";

