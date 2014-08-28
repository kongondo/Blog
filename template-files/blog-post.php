<?php 

/**
 * Post template
 * Example template file populated with MarkupBlog output and additional custom code for a Blog Post
 *
 */

	//CALL THE MODULE - MarkupBlog
	$blogOut = $modules->get("MarkupBlog");
        
    //nav
    $blogHome = $pages->get("/blog/");
    $topNavItems = $blogHome->children('name!=posts');//we exclude the 'posts' page
    //echo $blogOut->renderNav('', $nav->prepend($bloghome), $page);
    $topNav = $blogOut->renderNav('', $topNavItems, $page);
    $subNav = '';
    
    //subnav: get date info for creating link to archives page in subnav
    $date = $page->getUnformatted('blog_date'); 
    $year = date('Y', $date); 
    $month = date('n', $date); 

    //subnav: if there are categories and/or tags, then make a separate nav for them
    if(count($page->blog_categories)) $subNav .= $blogOut->renderNav(__('Related Categories'), $page->blog_categories); 
    if(count($page->blog_tags)) $subNav .= $blogOut->renderNav(__('Related Tags'), $page->blog_tags); 

    //subnav: contains authors, archives and categories links
    $subNavItems = array(
    "{$config->urls->root}blog/authors/{$page->createdUser->name}/" => $page->createdUser->get('title|name'), 
    "{$config->urls->root}blog/archives/$year/$month/" => strftime('%B %Y', $date)
    );

    $subNav .= $blogOut->renderNav(__('See Also'), $subNavItems);

    //if 'post author widget' is disabled, we want to style the end of the post using the css class 'no-author' (see further below in CENTRE COLUMN output)
    $noAuthor = $pages->get('template=blog-widget-basic, name=post-author, include=all')->is(Page::statusUnpublished) ? ' no-author' : '';

     //main content
    
    //render a single full post including title, comments, comment form + next/prev posts links, etc
    //$blogOut->postAuthor(): if available, add 'post author widget' at the end (or at the top if desired) of each post
   // $content = $blogOut->renderPosts($page) . $blogOut->renderComments($page->blog_comments) . $blogOut->renderNextPrevPosts($page);//without post author

    $content = $blogOut->renderPosts($page) . $blogOut->postAuthor() . $blogOut->renderComments($page->blog_comments) . $blogOut->renderNextPrevPosts($page);//with post author
    	
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
            <script src="<?php echo $config->urls->templates;?>js/blog.js"></script>

        </head>

        <body>

            <div id="wrapper" class="block-group"> <!-- #wrapper -->
            
                <div id="header" class="block"> <!-- header -->                        
						<ul id="navbar">
                            <li><a href="<?php echo $pages->get('/')->url ?>">Home</a></li>
                            <li><a href="#">About</a></li>
                            <li><a href="<?php echo $pages->get('/blog/')->url ?>">Blog</a></li>
                            <li><a href="#">Contact</a></li>
                        </ul>
                </div> <!-- end #header -->
         
                <!-- LEFT COLUMN - NAV -->
            	<div id ="nav" class="block"><!-- #nav -->             
                    <div id="top-nav"><?php echo $topNav;?></div><!-- #top-nav -->
                    <div id="sub-nav"><?php echo $subNav;?></div><!-- #sub-nav -->
            	</div><!-- end #nav -->
            			
                <!-- CENTRE COLUMN - MAIN -->				
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