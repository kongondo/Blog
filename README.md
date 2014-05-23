#Blog
This module enables you to create and manage a Blog in a unified interface. It is based on the [Blog Profile module](http://mods.pw/2M) by Ryan Cramer. The module consists of two parts:  

**ProcessBlog**: Manage Blog in the backend/Admin.  
**MarkupBlog**: Display Blog in the frontend.


##Features

###ProcessBlog
*	Dashboard with quick stats about your Blog.
*	Quick post.
*	Full view, create, edit, delete, update (CRUD) Posts, Categories and Tags within a single interface.
*	Bulk actions.
*	Edit Blog settings.
*	Blog Authors' stats.
* Turn widgets on/off (publish/unpublish).


###MarkupBlog
*	Easily output your Blog in the frontend, where and how you wish (e,g, only output part of the Blog).
*	Use any CSS framework (or not!) you wish.
* PocketGrid CSS included with the Template Files as a demo (fully responsive).

##Notes

#####Currently, this module will not work with existing Blog Profile installs! This may change in the future if there's demand.
In comparison to the Blog Profile, there are differences in the Blog Pages' structure and names, Fields, Templates and Template Files names.  

* MarkupBlog replicates the Blog Profile. Hence, Fields, Templates, Template Files and a couple of Pages are installed where such, with similar names, do not already exist, i.e., installation is non-destructive.
* Fields and Templates are prefixed with **blog_** and **blog-** respectively. They are also tagged **'blog'** for grouping in the ProcessWire Admin.
* The module also adds 3 fields (biography, image and title) to the user template. These are needed for the Blog Author biography.
* **On uninstall, all installed Fields, Templates, Template Files and Blog Pages are left untouched. If you need to remove them, you have to do it manually (or using the API)**. This is in order to protect existing data.
* Role 'author' and permission 'blog' are created on install.


##How to Install

1.	Download the module and copy the file contents to **/site/modules/Blog/**.
2.	In Admin, click Modules > check for new modules.
3.	Click install **ProcessBlog**. The module will automatically install **MarkupBlog**.
4.  Copy both the folders **/js/** and **/css/** and their contents to **/site/templates/**. These are needed for the example Blog output. Edit them as you wish.
5.	Go to Admin > Blog to start managing your Blog.
6. View the Page 'Blog' to see the included demo of **MarkupBlog**.

##How to Use

**MarkupBlog** is a collection of methods (functions) that help to output your Blog in the frontend using template files. You can use your own JS and CSS as you wish. Example template files are installed by the module. To use **MarkupBlog** in the frontend, you call it as any other ProcessWire module in a template file as follows.

````php

$blog = $modules->get('MarkupBlog');//Load the module. You then have access to its methods and properties. 
echo $blog->renderPosts("limit=5");//render 5 latests posts. See the example Template Files code for more examples.
````

The CSS is up to you, of course.


##Resources
*	[Support Forum](http://processwire.com/talk/topic/xxxx/)
*	Video [ProcessBlog demo](http://youtu.be/64XMGLuniqU)
*	Video [MarkupBlog demo](http://youtu.be/k7aSeL29JPE)

##License
GPL2

##Credits
Ryan Cramer
