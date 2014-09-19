<?php 

/**
 * Tag template
 * Demo template file populated with MarkupBlog output and additional custom code for a Blog Tag
 *
 */

    //CALL THE MODULE - MarkupBlog
    $blog = $modules->get("MarkupBlog");
    
    //main content
    $posts = $pages->find("blog_tags=$page, limit=10");//grab some posts
    $content = '';
    $content .= "<h2>$page->title</h2>";
    //render a limited number of summarised posts that belong to this tag
    $content .= $page->blog_body . $blog->renderPosts($posts, true);

    //rss
    /** Note, for the RSS to work, you should not output anything further after calling this, as it outputs the RSS directly. 
        If not, you will get an error **/

    //if we want to view the rss of posts in this tag
    if($input->urlSegment1) {
        // rss feed
        if($input->urlSegment1 != 'rss') throw new Wire404Exception();
        
        $blog->renderRSS($posts); 
        
        return;//this is important: stops output of any other markup except the RSS xml
    }        

    //include the main/common markup
    require_once("blog-main.inc");

