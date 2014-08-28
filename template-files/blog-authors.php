<?php 

/**
 * Authors template
 * Example template file populated with MarkupBlog output and additional custom code for the Blog Authors
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

                $content .= $blogOut->renderPosts($posts, true);
                
                //output subnav if viewing a single author's page
                $subNav .= '<div id="sub-nav">' . $blogOut->renderNav($page->title, $authorLinks, $page->url . $author->name . '/') . '</div><!-- #sub-nav -->';


    } 

    else {
                // no author specified: display list of authors
                $content .= "<h2>$page->title</h2>";
                $content .=  $blogOut->renderAuthors($authors); 
    } 
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
                    <?php if(!empty($subNav)) echo $subNav;//subNav only if viewing single Blog Author page?>
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