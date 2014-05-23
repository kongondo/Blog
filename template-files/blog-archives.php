<?php 

/**
 * Archives template
 * Example template file populated with MarkupBlog output and additional custom code for the Blog Archives
 *
 */

	//CALL THE MODULE - MarkupBlog
	$blogOut = $modules->get("MarkupBlog");
        
    //nav
    $blogHome = $pages->get("/blog/");
    $topNavItems = $blogHome->children('name!=posts');//we exclude the 'posts' page
    $topNav = $blogOut->renderNav('', $topNavItems, $page);
    $subNav = ''; 

    //main content
    $content = '';
    
    //Render
    //year and month specified, e.g. /archives/2007/2/ (i.e. February 2007) => 2 urlSegments
    if($input->urlSegment1 && $input->urlSegment2) {
            
            //year and month
            $year = (int) $input->urlSegment1; 
            $month = (int) $input->urlSegment2; 
            $firstDate = strtotime("$year-$month-01");
            $lastDate = strtotime("+1 month", $firstDate);
            
            $selector = "template=blog-post, blog_date>=$firstDate, blog_date<$lastDate, sort=-blog_date";
            
            //grab some posts
            $posts = $pages->find($selector);
            $content .= "<h2>" . strftime("%B %Y", $firstDate) . "</h2>";//this is the'July 2012'
            $content .= $blogOut->renderPosts($posts, true);
            
            //this is for the subnav
            $archives = $blogOut->getArchives();
            $yearsNav = array();
            $monthsNav = array();

            foreach($archives as $y) {
                
                $yearsNav[$y['url']] = $y['name'];
                
                if($y['name'] == $year) {
                    foreach($y['months'] as $m) $monthsNav[$m['url']] = $m['name'];
                }
            }

            //render subnav
            $subNav .= '<div id="sub-nav">' . $blogOut->renderNav($page->title, $yearsNav, $page->url . "$year/") . $blogOut->renderNav($year, $monthsNav, $page->url . "$year/$month/") . '</div><!-- #sub-nav -->';
            
    } 

    //only year specified, e.g. /archives/2007/ => only 1 urlSegment
    elseif($input->urlSegment1) {
            // year
            $year = (int) $input->urlSegment1; 
                        
            $archives = $blogOut->getArchives();
            $yearsNav = '';

            foreach($archives as $key => $y) {
                $yearsNav[$y['url']] = $y['name']; 
                if($key != $year) unset($archives[$key]); 
            }
            
            $content .= "<h2>$page->title</h2>";
            $content .= $blogOut->renderArchives($archives);
            
            //render subnav
            $subNav .= '<div id="sub-nav">' . $blogOut->renderNav($page->title, $yearsNav, $page->url . "$year/")  . '</div><!-- #sub-nav -->';

    } 

    //no year or month specified, i.e. no urlSegment specified
    else {
            
            // root, no date specified => display all archives. No subnav in this case
            $content .= "<h2>$page->title</h2>";
            $content .= $blogOut->renderArchives($blogOut->getArchives()); 
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
                    <?php if(!empty($subNav)) echo $subNav;//subNav only if showing 'year' or 'year and month' archives?>
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