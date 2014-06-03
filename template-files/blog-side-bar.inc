<?php

	//side-bar.inc - <!-- RIGHT COLUMN - SIDEBAR --> 
?>
        

        <?php

                if ($page->name !=='blog') {
                    
                                    //$out = '<div id="recent-posts"><!-- #recent-posts -->';
                                    
                                    $p = $pages->get("/blog/widgets/recent-posts/");
                                    $limit = $p->blog_quantity;
                                    $posts = $pages->find("template=blog-post, sort=-blog_date, start=0, limit=$limit");    
                                    $parent = null;
                                    $t = '';

                                    foreach($posts as $item) {
                                                $date = $blogOut->formatDate($item->blog_date);
                                                $t .= "<li><span class='date'>" . $date. "</span><br> <a href='{$item->url}'>{$item->title}</a></li>";
                                                $parent = $item->parent; 
                                    }

                                    if($t) {
                                                $out =  "<div id='recent-posts'><!-- #recent-posts --><h4>{$p->title}</h4>" . 
                                                "<ul class='recent-posts links'>$t</ul>" . 
                                                "<p>" . 
                                                    "<a class='more' href='{$parent->url}'>" . __('More') . "</a> " . 
                                                    "<a class='rss' href='{$parent->url}rss/'>" . __('RSS') . "</a>" . 
                                                "</p></div><!-- end #recent-posts -->";
                                                
                                    } 

                                    else {
                                                $out = "<div id='recent-posts'><!-- #recent-posts --><h4>{$p->title}</h4><p>No recent posts</p></div><!-- end #recent-posts -->";
                                    }
                

                        

                }

                else {

                    $out = '';
                }

                echo $out; 

                


        ?>

    

    <div id="recent-comments"><!-- #recent comments -->

        <?php
                $c = $pages->get("/blog/widgets/recent-comments/");
                $url = $config->urls->root . 'blog/comments/';
                $out =  "<h4>" . $c->title . "</h4>";
                $limit = $c->blog_quantity;
                $comments = $blogOut->findRecentComments($limit, 0, false);
                if(count($comments)) {
                        $out .= "<ul class='links'>";
                        foreach($comments as $comment) {
                                    $cite = htmlentities($comment->cite, ENT_QUOTES, "UTF-8");
                                    $date = $blogOut->formatDate($comment->created); 
                                    $out .= "<li><span class='date'>$date</span><br />" . 
                                                "<a href='{$comment->page->url}#comment{$comment->id}'>$cite &raquo; {$comment->page->title}</a>" . 
                                            "</li>";
                        }

                        $out .= "</ul>";
                        $out .= "<p>" . 
                                    "<a class='more' href='$url'>" . __('More') . "</a>  " . 
                                    "<a class='rss' href='{$url}rss/'>" . __('RSS') . "</a>" . 
                                "</p>";
                } 

                else {
                        $out .= "<p>" . __('No comments yet') . "</p>";
                }

                echo $out;
        ?>

    </div><!-- end #recent-comments -->
               
    <div id="blogroll"><!-- #blogroll -->

        <?php
                $br = $pages->get("/blog/widgets/blogroll/");
                echo "<h4>{$br->title}</h4>";
                if(count($br->blog_links)) {
                        echo "<ul class='links'>";
                        foreach($br->blog_links as $link) {
                            echo "<li><a target='_blank' href='{$link->blog_href}'>{$link->blog_headline}</a></li>";
                        }
                        echo "</ul>";
                } 

                else {
                        echo "<p>" . __('No links yet.') . "</p>";
                }
        ?>

    </div><!-- end #blogroll -->