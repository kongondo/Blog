# Blog

This Blog module enables you to easily create and manage a Blog in ProcessWire. It is based on the [Blog Profile module](http://mods.pw/2M) by Ryan Cramer. The module consists of three separate modules:  

**ProcessBlog**: Manage Blog in the backend/Admin.  
**MarkupBlog**: Display Blog in the frontend.  
**BlogPublishDate**: Automatically sets a publish date when you publish a Blog Post.

## Features

### ProcessBlog
*	Dashboard with quick stats about your Blog.
*	Quick post.
*	Full view, create, edit, delete, update (CRUD) Posts, Categories and Tags within a single interface.
*	Bulk actions.
*	Edit Blog settings.
*	Blog Authors' stats.
* Turn widgets on/off (publish/unpublish).
* Fully uninstall/cleanup Blog templates, fields, pages and optionally template files.


### MarkupBlog
*	Easily output your Blog in the frontend, where and how you wish (e,g, only output part of the Blog).
*	Use any CSS framework (or not!) you wish.
* PocketGrid CSS included with the demo Template Files (fully responsive).

<img src='https://github.com/kongondo/Blog/raw/master/screenshot-demo-frontend.png' />

## How to Install

Install via ProcessWire modules' screen. Once installed, there's two steps involved to finalise the install:

##### A. Configure Blog settings in its module's configuration screen. 

Here you will have to choose from 4 Blog styles to match the Blog URL structure you want:

1. Blog style 1: /mysite/blog/posts/example-post/
2. Blog style 2: /mysite/blog/example-post/
3. Blog style 3: /mysite/posts/example-post/
4. Blog style 4: /mysite/example-post/

On this screen, you also select:

*  Whether you want to enable the commenting feature;
*  The auto-publish/unpublish posts feature (for which you would need the module [SchedulePages](http://mods.pw/1t);
* Whether to install 'no template files', 'blank template files', or 'demo template files'; and
* Optionally specify a tag for grouping Blog Templates and Fields in their respective ProcessWire admin pages

##### B. Install Blog Templates, Pages, Fields, Role and optionally Template Files. 

* In this second step, via the Blog dashboard (**admin/blog/**), you will see an overview of the settings you selected in the first step above. Here you install Blog's components. Before you click the button to run the install wizard, nothing will happen yet. You can still go back and change the module configuration settings. Until you run the install wizard in this second step, you will have no Blog Pages, Fields, Templates, etc. 

* On this page, you will also be able to rename your Blog’s main pages before they are created. If you don’t do it at this stage, you can also rename them post-install in the ProcessWire pages' tree. If you are happy with your settings, click the install wizard to proceed. Blog will be fully installed with your settings within a few seconds and you will then be presented with the Blog dashboard. 

* Only Templates, Pages and Fields necessary for your selected 'Blog style' will be installed. For instance, if you did not enable the commenting feature, related Templates, Pages, etc. will not be created. 

* Note that non-enabled features **cannot** be enabled once the second part of the install is complete. However, using the **'Cleanup'** feature (see below) you can return Blog to the first-step of the installer stage and enable a feature you want or even select a different Blog style.

* If you chose to install the demo Template Files, also manually copy over the **/css/** and **/scripts/** folders present in Blog module's folder to your **/site/templates/** folder.

##### Please note:
If you need to change some configurations, you can go back to the module settings and do it **BEFORE** you finalise step two of the installation. It is important that once the installation is finalised, in case you had left the ProcessBlog's module configuration's browser window open, **NOT** to press submit again. Otherwise, MarkupBlog may not function as expected. However, if you reload the module configurations screen once the second-part of the installer has finished, you will not be able to change the configuration settings. Instead, you will be presented with an overview of your installed settings.

## How to Use

Full documentation is available [here](http://blog.kongondo.com/).

## Notes

* The module Dashboard **will not work** with Blog Profile installs!  
* MarkupBlog replicates the Blog Profile. Hence, the 'items' Fields, Templates, Template Files and a couple of Pages are installed where such, with similar names, do not already exist, i.e., installation is non-destructive. 
* **With the exception of Template Files, note that if even one item with a similar name (and path for Page items) already exists on your site, NONE of the items will be installed**. Instead, step two of the installation will be aborted. 
* For Template Files, if you did not select the 'no template files' option, these are only copied over to **/site/templates/** where no template with an identical name already exists. This means that no Template File gets overwritten. 
* Fields and Templates are prefixed with **blog_** and **blog-** respectively.
* The module also adds 3 Fields (biography [blog_body], image [blog_images] and title) to the user template. These are needed for the Blog Author biography.
* Role **blog-author** and permission **blog** are created on install.
* **Until you set 'author titles' for your Blog Authors (in Admin > Access > Users), a generic 'Author Name' will be used as their display names**.
* In order to use the Recent Tweets Widget, you will need to separately install and setup the module [MarkupTwitterFeed](http://mods.pw/d).

## Uninstall
Uninstalling Blog is a two-step process. If you are logged in as a superuser, you will see a Blog menu item called **Cleanup**. It will lead to a screen with info about all the Fields, Templates, Pages and Role you are about to delete. It will also list the Blog Template Files that, if you wish, you can also delete. This utility is also useful when you want to try out the different Blog styles without uninstalling the whole Blog module. It returns Blog to the state similar to when you first installed the module. Of course, in case you want to remove Blog as well, just go ahead and uninstall in the normal way but **AFTER** you have cleaned-up.

## Changelog

#### Version 2.4.0
1. Fixed sql [error](https://github.com/kongondo/Blog/pull/23) thrown when comments disabled and accessed blog dashboard.
2. Added capability to copy demo JS and CSS files on blog install as well as remove them on uninstall.
3. Full compatibility with ProcessWire 2.8.x and ProcessWire 3.x. See [PR](https://github.com/kongondo/Blog/pull/30).

#### Version 2.3.9
1. Fixed [bug](https://processwire.com/talk/topic/7403-module-blog/?p=119070) where comments' date was being output as a raw timestamp (owing to a miss during the last commit).

#### Version 2.3.8
1. Fixed [bug](https://processwire.com/talk/topic/7403-module-blog/page-30#entry112992) where comments' date were not being correctly output.

#### Version 2.3.7
1. All MarkupPageNav (used in renderPosts()) [options](https://processwire.com/api/modules/markup-pager-nav/) can now be passed to renderPosts() to customise pagination of posts.

#### Version 2.3.6
1. Added option to use Rich Text Editor (CKEditor) in Quick Post. Setting is configurable in ProcessBlog module settings (both pre- and post-install).

#### Version 2.3.5
1. Fixed issue where dashboards were loading very slowly on sites with lots of posts.
2. Added some missing translation strings.
3. Some code optimisations.

#### Version 2.3.4
Preserve all line breaks as paragraphs for posts created via ProcessBlog's quickpost.

#### Version 2.3.3
Added a very visible reminder in Blog's module configuration screen not to uninstall the module BEFORE running the in-built Cleanup Utility.

#### Version 2.3.2
Added a renderRelatedPosts() method.

#### Version 2.3.1
1. Fixed Posts/Categories/Tags dashboard html rendering issue on multilingual sites.
2. Added 'post_small_tag' option to renderPosts() to specify html tag to wrap summary blog posts (small).
3. Some code optimisations.

#### Version 2.3.0
1. Use Blog authors' **display names** in author pages url/links rather than their usernames
2. Fixed renderNextPrevPosts() not sorting/displaying previous/next posts properly

#### Version 2.2.2
1. All main methods that render HTML output are now configurable via a parameter/argument $options.
2. Ability to add a featured image to a post

#### Version 2.0.2
1. Minor updates to demo template files.

#### Version 2.0.1
1. Fixed a comments' visibility issue.
2. Enhancement to comments' visibility status (added status **HIDDEN**).

#### Version 2.0
1. Four Blog styles (URL structures).
2. Two-step installer/uninstaller.
3. Configurable module.
4. Cleaner utility (for Blog fields, templates, template files, etc.).
5. Auto-(un)publish feature.
6. Commenting feature - enable/disable.
7. Other various enhancements.

#### Version 1.3.0
1. Added new small autoload module BlogPublishDate as part of the Blog module suite, that saves a Blog Post's publication date.
2. Enhancements to Posts, Categories and Tags Dashboards: Customisable number of posts/categories/tags to show per page (via a drop-down select). Selected value is preserved via a cookie per context (i.e. can have different values for posts, categories and tags dashboard) and per user.
3. Date column: shows 'Pending' for unpublished posts (never before published ones), 'Expired' (published then unpublished posts) and published Date for currently published posts.
4. Date shown is formatted according to the format set in **'blog_date'**.
5. Fixed sorting by date column.

#### Version 1.2.2
1. Changes to **renderComments()**. Added a 4th **Array $options** argument for customising the texts describing the various comments' statuses.

#### Version 1.2.1
1. Minor styling issues updates.

#### Version 1.2
1. Added comments visibility settings at post and global level.
2. Added Posts' Bulk Actions.

#### Version 1.1
1. Added new widget **'Post Author'**. The widget allows to add a post's author's biography with each post (above or below the post).
2. Made 'posts truncate length' configurable.

#### Version 1.0.1
1. Several strings made translatable

## Resources
* [Support Forum](https://processwire.com/talk/topic/7403-module-blog/)
* Full [announcement](https://processwire.com/talk/topic/7403-module-blog/page-7#entry74237) about Blog version 2
* Video [ProcessBlog demo](http://youtu.be/64XMGLuniqU) (Blog version 1)
* Video [MarkupBlog demo](http://youtu.be/k7aSeL29JPE) (Blog version 1)

## Upgrading from Blog version 1 to version 2
Please refer to this [post](https://processwire.com/talk/topic/7403-module-blog/?p=74245) for full instructions and an upgrade script.

## License
GPL2

## Credits
Ryan Cramer
