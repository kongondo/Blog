<?php

/**
 * Post template
 * Demo template file populated with MarkupBlog output and additional custom code for a Blog Post
 *
 */

	//CALL THE MODULE - MarkupBlog
	$blog = $modules->get("MarkupBlog");

    //subnav
    $subNav = '';

    //subnav: get date info for creating link to archives page in subnav
    $date = $page->getUnformatted('blog_date');
    $year = date('Y', $date);
    $month = date('n', $date);

    //subnav: if there are categories and/or tags, then make a separate nav for them
    if(count($page->blog_categories)) $subNav .= $blog->renderNav(__('Related Categories'), $page->blog_categories);
    if(count($page->blog_tags)) $subNav .= $blog->renderNav(__('Related Tags'), $page->blog_tags);

    //subnav: contains authors, archives and categories links
    $authorsURL = $pages->get('template=blog-authors')->url;
    $archivesURL = $pages->get('template=blog-archives')->url;
    $authorURL = $sanitizer->pageName($page->createdUser->title) ? $sanitizer->pageName($page->createdUser->title) . '/' : '';//use pageName sanitized author title as PART of URL, else empty
    $authorName = $page->createdUser->title ? $page->createdUser->title : 'Author Name';//use generic 'Author Name' if author title not yet set

    $subNavItems = array(
                            $authorsURL . $authorURL => $authorName,
                            $archivesURL . $year . "/" . $month . "/" => strftime('%B %Y', $date)
    );

    $subNav .= $blog->renderNav(__('See Also'), $subNavItems);

     //main content

    //render a single full post including title, comments, comment form + next/prev posts links, etc
    //$blog->postAuthor(): if available, add 'post author widget' at the end (or at the top if desired) of each post
   // $content = $blog->renderPosts($page) . $blog->renderComments($page->blog_comments) . $blog->renderNextPrevPosts($page);//without post author

    /*
        for this demo, renderComments() has to adapt to whether blog commenting feature was installed or not whilst remaining blog structure/style-agnostic
        in your own blog install, you would know if you enabled the feature so there would be no need for such a check
		in addition, our 'check' code is not code you would normally use in a template file.
		we use such code here to be both foolproof that the commenting feature is installed and blog structure-agnostic
    */
    #not foolproof; user could have post-installed custom commenting feature (e.g. Disqus) with a similar field blog_comments
	//$renderComments = $page->template->hasField('blog_comments') ? $blog->renderComments($page->blog_comments) : '';

    $blogConfigs = $modules->getModuleConfigData('ProcessBlog');

    $renderComments = $blogConfigs['commentsUse'] == 1 ? $blog->renderComments($page->blog_comments) : '';

    $content = $blog->renderPosts($page) . $blog->postAuthor() . $renderComments . $blog->renderNextPrevPosts($page);//with post author widget

    //include the main/common markup
    require_once("blog-main.inc");

