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
                if($sanitizer->pageName($a->title)) {

                            $a->authorPageName = $sanitizer->pageName($a->title);//overload each $a with a new property 'authorPageName' for use below
                            $a->url2 = $page->url . $a->authorPageName . '/';

                }

                else {

                        $a->authorPageName = '';
                        $a->url2 = $page->url;

                }

                $authorLinks[$a->url2] = $a->get('title') ? $a->get('title') : 'Author Name';//use generic 'Author Name' if author title not yet set

    }//end foreach $authors as $a

    if($input->urlSegment1) {
        //author specified: display biography and posts by this author, limiting the number of posts to show

                $name = $sanitizer->pageName($input->urlSegment1);

                $authorID = '';

                foreach ($authors as $a) {

                    if($a->authorPageName == $name) {

                        $authorID = $a->id;
                        break;//break out of loop if we've found our author
                    }

                }

                $author = $users->get($authorID);

                if(!$author->id || (!$author->hasRole($authorRole) && !$author->isSuperuser())) throw new Wire404Exception();

                $posts = $pages->find("template=blog-post, created_users_id=$author, sort=-blog_date, limit=10");

                $authorName = $author->get('title');

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
                $subNav .= $blog->renderNav($page->title, $authorLinks, $page->url . $name . '/');//note url contains pageName sanitized author title $name


    }//end if $input->urlSegment1

    else {
                //no author specified: display list of authors
                $content .= "<h2>$page->title</h2>";
                $content .=  $blog->renderAuthors($authors);
    }

    //include the main/common markup
    require_once("blog-main.inc");

