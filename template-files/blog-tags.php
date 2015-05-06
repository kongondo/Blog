<?php 

/**
 * Tags template
 * Demo template file populated with MarkupBlog output and additional custom code for the Blog Tags
 *
 */

    //CALL THE MODULE - MarkupBlog
    $blog = $modules->get("MarkupBlog");        
   
    //main content
    $content = '';
    $content .= "<h2>$page->title</h2>";
    //Render alphabetical list of tags + show number of posts for each tag + render an alphabetical jumplist of tags
    $content .= $blog->renderTags($page->children);//children => the individual tag pages        

    //include the main/common markup
    require_once("blog-main.inc");

