<?php

/**
 * Blog Demo main markup include
 * Demo template file include
 *
 */

    //nav
    $topNavItems = array();

    /*
        we need to get topNav items by their templates in order to adapt to different blog styles (1-4)
        of course, in your blog install, you would use a different method since you would know the blog style you selected!
    */
    $templates = array('blog-categories','blog-tags', 'blog-comments', 'blog-authors', 'blog-archives');

    foreach ($templates as $t) {

        $p = $pages->get("template=$t");
        if($p->id)  $topNavItems[] = $p;

    }

    $topNav = $blog->renderNav('', $topNavItems, $page);


?>

    <!doctype html>
	<html lang="en-gb" dir="ltr" class="uk-notouch">
    	<head>
        	<meta charset="utf-8">
            <?php header('X-UA-Compatible: IE=edge,chrome=1');//taming IE ?>
        	<meta name="viewport" content="width=device-width, initial-scale=1.0">

        	<title><?php echo $page->title;?></title>

        	<!-- Google Webfonts -->
            <link href='http://fonts.googleapis.com/css?family=Shadows+Into+Light' rel='stylesheet' type='text/css'><!--  Main Menu -->
            <link href='http://fonts.googleapis.com/css?family=Archivo+Narrow:400,400italic,700,700italic' rel='stylesheet' type='text/css'><!-- Body Copy, etc -->

            <!-- Style Sheets -->
        	<link rel="stylesheet" href="<?php echo $config->urls->templates;?>css/pocketgrid.css" /><!-- The PocketGrid -->
            <link rel="stylesheet" href="<?php echo $config->urls->templates;?>css/blog.css" /><!-- Custom Styles -->

            <!-- Scripts -->
            <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
            <script src="<?php echo $config->urls->templates;?>scripts/blog.js"></script>

        </head>

        <body>

            <div id="wrapper" class="block-group"> <!-- #wrapper -->

                <div id="header" class="block"> <!-- header -->
						<ul id="navbar">
                            <li><a href="<?php echo $pages->get('/')->url ?>">Home</a></li>
                            <li><a href="#">About</a></li>
                            <li><a href="#">Blog</a></li>
                            <li><a href="#">Contact</a></li>
                        </ul>
                </div> <!-- end #header -->

                <!-- LEFT COLUMN - NAV -->
            	<div id ="nav" class="block"><!-- #nav -->
                    <div id="top-nav"><?php echo $topNav;?></div><!-- #top-nav -->

                    <?php

						$noSubNav = array('blog-comments','blog-posts', 'blog-tag', 'blog-tags');

                        if (!in_array($page->template->name, $noSubNav)) {

						      //subnav only on certain pages and if not empty
                              if(!empty($subNav)) echo '<div id="sub-nav">' . $subNav  . '</div><!-- #sub-nav -->';
                        }


                    ?>

                </div><!-- end #nav -->

                <!-- CENTRE COLUMN - MAIN -->
                <?php
                        //if 'post author widget' is disabled, we want to style the end of the post using the css class 'no-author' (see further below in CENTRE COLUMN output)
                        //only applies to 'blog-post' pages
                        $noAuthor = $pages->get('template=blog-widget-basic, name=post-author, include=all')->is(Page::statusUnpublished) ? ' no-author' : '';
                 ?>

                <div id="main" class="block<?php echo $noAuthor?>"><?php echo $content?></div> <!-- #main -->

            	<!-- RIGHT COLUMN - SIDEBAR -->
            	<div id="sidebar" class="block"><?php include_once("blog-side-bar.inc"); ?></div><!-- #sidebar -->

        <!-- BOTTOM - FOOTER -->

                <div id="footer" class="block"><!-- #footer -->
                    <small id="footer_note">Copyright 2014</small>
                    <small id="processwire">Powered by <a target="_blank" href="http://processwire.com">ProcessWire Open Source CMS</a></small>
                </div><!-- end #footer -->

            </div><!-- end #wrapper -->

        </body>

    </html>