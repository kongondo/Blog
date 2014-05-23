<?php 

/**
 * Tags template
 * Example template file populated with MarkupBlog output and additional custom code for the Blog Tags
 *
 */

    //CALL THE MODULE - MarkupBlog
    $blogOut = $modules->get("MarkupBlog");
        
    //nav
    $blogHome = $pages->get("/blog/");
    $topNavItems = $blogHome->children('name!=posts');//we exclude the 'posts' page
    //echo $blogOut->renderNav('', $nav->prepend($bloghome), $page);
    $topNav = $blogOut->renderNav('', $topNavItems, $page);
    
    //main content
    $content = '';
    $content .= "<h2>$page->title</h2>";
    //Render alphabetical list of tags + show number of posts for each tag + render an alphabetical jumplist of tags
    $content .= $blogOut->renderTags($page->children);//children => the individual tag pages
        
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
                </div><!-- end #nav -->
                        
                <!-- CENTRE COLUMN - MAIN -->               
                <div id="main" class="block"><?php echo $content?></div> <!-- #main -->
                  
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