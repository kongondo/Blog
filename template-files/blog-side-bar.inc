<?php

    //side-bar.inc - <!-- RIGHT COLUMN - SIDEBAR -->

    /* #################### - recent posts widget - #################### */

    //adapted selector to blog style

    $out = '';
    if ($page->template->name !=='blog') {

        //$out = '<div id="recent-posts"><!-- #recent-posts -->';
        $p = $pages->get("template=blog-recent-posts");//adapt to blog styles for this demo

        //if widget is enabled output it
        if($p->id) {

            $limit = $p->blog_quantity;
            $posts = $pages->find("template=blog-post, sort=-blog_date, start=0, limit=$limit");
            $parent = null;
            $t = '';

            foreach($posts as $item) {
                        $date = $blog->formatDate($item->blog_date);
                        $t .= "<li><span class='date'>" . $date. "</span><br> <a href='{$item->url}'>{$item->title}</a></li>";
                        $parent = $item->parent;
            }

            if($t) {
                        //if blog style is 2 or 4, rss on recent posts will not work since the template blog-posts allows url segments but it is not present on such blog style installs.
                        $tpl = wire('templates')->get('blog-posts');
                        $rss = $tpl ? "<a class='rss' href='{$parent->url}rss/'>" . __('RSS') . "</a>" : '';

                        $out =  "<div id='recent-posts'><!-- #recent-posts --><h4>{$p->title}</h4>" .
                        "<ul class='recent-posts links'>$t</ul>" .
                        "<p>" .
                            "<a class='more' href='{$parent->url}'>" . __('More') . "</a> " .
                           $rss .
                        "</p></div><!-- end #recent-posts -->";

            }

            else {
                        $out = "<div id='recent-posts'><!-- #recent-posts --><h4>{$p->title}</h4><p>No recent posts</p></div><!-- end #recent-posts -->";
            }

        }
    }

    echo $out;

    /* #################### - recent comments widget - #################### */

    /*
        if comments feature installed, show recent comments widget if it is enabled

        for this demo, findRecentComments() has to adapt to whether blog commenting feature was installed or not whilst remaining blog structure/style-agnostic
        in your own blog install, you would know if you enabled the feature so there would be no need for such a check
        in addition, our 'check' code is not code you would normally use in a template file.
        we use such code here to be both foolproof that the commenting feature is installed and blog structure-agnostic

    */

    $blogConfigs = $modules->getModuleConfigData('ProcessBlog');

    //if commenting feature is installed
    if($blogConfigs['commentsUse'] == 1) {

            //if widget is enabled output it
            $c = $pages->get("template=blog-recent-comments");

            if($c->id) {

                    $out = '<div id="recent-comments"><!-- #recent comments -->';

                    $url = $pages->get('template=blog-comments')->url;//we adapt selector to blog style
                    $out .=  "<h4>" . $c->title . "</h4>";
                    $limit = $c->blog_quantity;
                    $comments = $blog->findRecentComments($limit, 0, false);//false = in the sidebar, do not show pending or spam comments whether admin is logged in or not
                    if(count($comments)) {
                            $out .= "<ul class='links'>";
                            foreach($comments as $comment) {
                                        $cite = htmlentities($comment->cite, ENT_QUOTES, "UTF-8");
                                        $date = $blog->formatDate($comment->created, 2);
                                        $out .= "<li><span class='date'>$date</span><br />" .
                                                    "<a href='{$comment->page->url}#comment{$comment->id}'>$cite &raquo; {$comment->page->title}</a>" .
                                                "</li>";
                            }

                            $out .= "</ul>";
                            $out .= "<p>" .
                                        "<a class='more' href='$url'>" . __('More') . "</a>  " .
                                        "<a class='rss' href='{$url}rss/'>" . __('RSS') . "</a>" .
                                    "</p>";
                    }//if count comments

                    else {
                            $out .= "<p>" . __('No comments yet') . "</p>";
                    }

                    $out .= '</div><!-- end #recent-comments -->';

                    echo $out;

            }//end if recent-comments widget is enabled

    }//end if comments feature enabled

    /* #################### - blog roll widget - #################### */

    $br = $pages->get("template=blog-links");//adapt selector to blog styles for this demo

    //if widget is enabled output it
    if ($br->id) {
            $out = '<div id="blogroll"><!-- #blogroll -->';
            $out .= "<h4>{$br->title}</h4>";

            if(count($br->blog_links)) {
                    $out .= "<ul class='links'>";

                    foreach($br->blog_links as $link) {
                            $out .= "<li><a target='_blank' href='{$link->blog_href}'>{$link->blog_headline}</a></li>";
                    }

                    $out .= "</ul>";
            }

            else {
                    $out .= "<p>" . __('No links yet.') . "</p>";
            }

            $out .= '</div><!-- end #blogroll -->';

            echo $out;

    }