<?php 

/**
 * Authors template
 * Demo template file populated with MarkupBlog output and additional custom code for the Blog Authors
 *
 */

    //CALL THE MODULE - MarkupBlog
    $blog = $modules->get("MarkupBlog");        

   //subnav
   $subNav = ''; 

    //main content
    $content = '';
    
    //author stuff
    $authorRole = $roles->get('blog-author');
    $superuserRole = $roles->get('superuser');
    $authors = $users->find("roles=$authorRole|$superuserRole, sort=title"); 
    $authorLinks = array();

    foreach($authors as $a) {
                //we set a separate URL (url2) to reflect the public url of the author, since
                //the author's $author->url is actually a page in the admin
                $a->url2 = $page->url . $a->name . '/';
                $authorLinks[$a->url2] = $a->get('title|name');
    }

    if($input->urlSegment1) {
        //author specified: display biography and posts by this author, limiting the number of posts to show

                $name = $sanitizer->pageName($input->urlSegment1);
                $author = $users->get($name);
                if(!$author->id || (!$author->hasRole($authorRole) && !$author->isSuperuser())) throw new Wire404Exception();

                $posts = $pages->find("template=blog-post, created_users_id=$author, sort=-blog_date, limit=10");
                $authorName = $author->get('title|name'); 

                $authorURL = '';

                $image = $author->blog_images->first();
                
                if($image) {
                        $thumb = $image->width(100);    
                        $photo = "<a class='lightbox' title='$authorName'><img class='author-photo' src='{$thumb->url}' alt='{$thumb->description}' width='100' height='{$thumb->height}' /></a>";
                } 

                else {
                        $photo = '';
                }

                if($authorURL) $authorName = "<a href='$authorURL'>$authorName</a>";

                $content .= "<h2>$page->title</h2>";
                $content .= "<div class='author-bio clearfix'>
                              <h3 class='author-name'>$authorName</h3>" . $photo . $author->blog_body . "</div>";

                $content .= $blog->renderPosts($posts, true);
                
                //output subnav if viewing a single author's page
                $subNav .= '<div id="sub-nav">' . $blog->renderNav($page->title, $authorLinks, $page->url . $author->name . '/') . '</div><!-- #sub-nav -->';


    } 

    else {
                // no author specified: display list of authors
                $content .= "<h2>$page->title</h2>";
                $content .=  $blog->renderAuthors($authors); 
    }

    //include the main/common markup
    require_once("blog-main.inc");

