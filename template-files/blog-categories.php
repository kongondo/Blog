<?php 

/**
 * Categories template
 * Demo template file populated with MarkupBlog output and additional custom code for the Blog Categories
 *
 */

	//CALL THE MODULE - MarkupBlog
	$blog = $modules->get("MarkupBlog");
        
    //subnav
    $subNav = $blog->renderNav($page->title, $page->children, $page); 

    //main content
    $limit = 3;//number of posts to list per category 
    $content = '';
    $content .= "<h2>$page->title</h2>";
    //Render list of categories showing a maximum of 3 posts titles per category
    $content .= $blog->renderCategories($page->children, $limit);//children => the individual category pages

    //include the main/common markup
    require_once("blog-main.inc");

