<?php 

/**
 * Posts template
 * Demo template file populated with MarkupBlog output and additional custom code for the Blog Posts
 *
 */

    //CALL THE MODULE - MarkupBlog
    $blog = $modules->get("MarkupBlog");

    //main content
    $content = '';
    $content .= "<h2>{$page->get('blog_headline|title')}</h2>";
    //render a limited number of summarised posts
    $content .= $blog->renderPosts("limit=5", true); 

    //rss
    /** Note, for the RSS to work, you should not output anything further after calling this, as it outputs the RSS directly. 
        If not, you will get an error **/

    //if we want to view the rss of posts
    if($input->urlSegment1) {
        // rss feed
        if($input->urlSegment1 != 'rss') throw new Wire404Exception();
        $homepage = $pages->get('/'); 
        //render rss; just an example...we have no meta_description field
		$blog->renderRSS($page->children("limit=10"), $homepage->get('headline|title'), $homepage->get('summary|meta_description')); 
        
        return;//this is important: stops output of any other markup except the RSS xml
    }        

    //include the main/common markup
    require_once("blog-main.inc");

