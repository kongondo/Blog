<?php 

/**
 * Archives template
 * Example template file populated with MarkupBlog output and additional custom code for the Blog Archives
 *
 */

	//CALL THE MODULE - MarkupBlog
	$blog = $modules->get("MarkupBlog");
    
    //subnav
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
            $content .= $blog->renderPosts($posts, true);
            
            //this is for the subnav
            $archives = $blog->getArchives();
            $yearsNav = array();
            $monthsNav = array();

            foreach($archives as $y) {
                
                $yearsNav[$y['url']] = $y['name'];
                
                if($y['name'] == $year) {
                    foreach($y['months'] as $m) $monthsNav[$m['url']] = $m['name'];
                }
            }

            //render subnav
           # $subNav .= '<div id="sub-nav">' . $blog->renderNav($page->title, $yearsNav, $page->url . "$year/") . $blog->renderNav($year, $monthsNav, $page->url . "$year/$month/") . '</div><!-- #sub-nav -->';
			$subNav .= $blog->renderNav($page->title, $yearsNav, $page->url . "$year/") . $blog->renderNav($year, $monthsNav, $page->url . "$year/$month/");
            
    } 

    //only year specified, e.g. /archives/2007/ => only 1 urlSegment
    elseif($input->urlSegment1) {
            // year
            $year = (int) $input->urlSegment1; 
                        
            $archives = $blog->getArchives();
            $yearsNav = '';

            foreach($archives as $key => $y) {
                $yearsNav[$y['url']] = $y['name']; 
                if($key != $year) unset($archives[$key]); 
            }
            
            $content .= "<h2>$page->title</h2>";
            $content .= $blog->renderArchives($archives);
            
            //render subnav
            #$subNav .= '<div id="sub-nav">' . $blog->renderNav($page->title, $yearsNav, $page->url . "$year/")  . '</div><!-- #sub-nav -->';
			$subNav .= $blog->renderNav($page->title, $yearsNav, $page->url . "$year/");

    } 

    //no year or month specified, i.e. no urlSegment specified
    else {
            
            // root, no date specified => display all archives. No subnav in this case
            $content .= "<h2>$page->title</h2>";
            $content .= $blog->renderArchives($blog->getArchives()); 
    }

    //include the main/common markup
    require_once("blog-main.inc");

