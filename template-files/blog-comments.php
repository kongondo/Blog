<?php 

/**
 * Comments template
 * Demo template file populated with MarkupBlog output and additional custom code for the Blog Comments
 *
 */

    //CALL THE MODULE - MarkupBlog
    $blog = $modules->get("MarkupBlog");        
   
    //main content
    $limit = $page->blog_quantity;
    $content = '';
    
    if($input->urlSegment1) { 
                // rss feed
                if($input->urlSegment1 != 'rss') throw new Wire404Exception();
                $blog->renderCommentsRSS($limit);

                return;//this is important: stops output of any other markup except the RSS xml
        } 

        else { 
                $content .= "<h2>{$page->get('blog_headline|title')}</h2>";
                $start = ($input->pageNum-1) * $limit; 
                $content .= $page->blog_body . $blog->renderComments($blog->findRecentComments($limit, $start), $limit);
        }        

    //include the main/common markup
    require_once("blog-main.inc");

