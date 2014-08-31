<?php

/**
*
* This is an include file for ProcessBlog ___install(). Hence, it is only run once when installing ProcesBlog
*
* It installs 'fields', 'templates', 'template files', 'blog pages' and a 'role' in the ProcessWire site
* for use with the modules ProcessBlog and if needed, its sister module, MarkupBlog.
*
* If the above already exist (i.e., same names); this installer aborts but the module itself is installed.
* If installer proceeds, 'template-files' are only copied if they do not exist at destination, i.e. '/site/templates/'. We don't want to overwrite users files!
*
* @author Kongondo
* @version 1.3 - 31 August 2014
*
* https://github.com/kongondo/Blog
* Created February 2014
*
*/

	//1. ###### First we check if our parent blog page, role [blog-author], fields and templates already exist. 
		//If yes to any of these, we abort installation and return informative error messages

	//check if a page named 'blog' already exists
	$p = wire('pages')->get('name=blog, parent=/');

	$pageExists = $p->id ? true : false;

	//check if fields 'blog_images', etc, already exist
	$fields  = array(
					'blog_body',
					'blog_categories',
					'blog_comments',
					'blog_comments_view',
					'blog_comments_max',
					'blog_quantity',
					'blog_date',
					'blog_files',
					'blog_headline',
					'blog_href',
					'blog_images',
					'blog_links',
					'blog_note',
					'blog_summary',
					'blog_tags',
	);


	$fieldsCheck = array();
	foreach ($fields as $field) {

				if(wire('fields')->get($field))	$fieldsCheck [] = wire('fields')->get($field)->name;
	}


	$fieldsExist = count($fieldsCheck) ? true : false;


	//check if templates, e.g. blog-posts, etc, already exist
	$templates = array(
						'blog',
						'blog-archives',
						'blog-authors',
						'blog-categories',
						'blog-category',
						'blog-comments',
						'blog-links',
						'blog-post' ,
						'blog-posts',
						'blog-recent-comments',
						'blog-recent-posts',
						'blog-recent-tweets',
						'blog-tag'	,
						'blog-tags',
						'blog-widgets',
						'blog-widget-basic',
						'blog-basic',
						'repeater_blog-links',
	);


	$templatesCheck = array();
	foreach ($templates as $template) {
	
				if(wire('templates')->get($template)) $templatesCheck [] = wire('templates')->get($template)->name;
	}


	$templatesExist = count($templatesCheck) ? true : false;

	//check if role 'blog-author' already exists
	$r = wire('roles')->get('blog-author');

	$roleExists = $r->id ? true : false;

	//prepare error messages
	if($pageExists == true) $this->error($this->_('A page named "blog" under the root page already exists!'));
	
	if($fieldsExist == true){
			$failedFields = implode(', ', $fieldsCheck);
			$this->error($this->_("Cannot install Blog fields. Some field names already in use. These are: {$failedFields}."));
	}
	
	if($templatesExist == true){				
			$failedTemplates = implode(', ', $templatesCheck);
			$this->error($this->_("Cannot install Blog templates. Some template names already in use. These are: {$failedTemplates}."));
	}
	
	if($roleExists == true) $this->error($this->_('A role called "blog-author" already exists!'));

	
	//if any of our checks returned true, we abort early
	if($pageExists == true || $fieldsExist == true || $templatesExist == true || $roleExists == true) {
		
		$this->error($this->_('Due to the above errors, installer did not finish. You probably could still use the Blog Manager fully (with some adjustments; see Readme). Otherwise, to remove the module, uninstall in the usual way.'));
		
		//due to above errors, we stop executing install of the following 'role', 'templates', 'template files', 'fields' and 'pages'
		return false;

	}

	#################################################################################################################################

	//2. ###### If we are good, we create the role 'blog-author' that we will later add to some of our templates ######

	$r = new Role();
	$r->name = 'blog-author';
	$r->save();

	//create an array of guest and author roles IDs to later add to our new templates
	/*
	foreach (wire('roles')->find('name=guest|author') as $r) {
			$templateRoles[] = $r->id;//EDIT; DON'T REALLY NEED THIS ARRAY. I ONLY NEED TO GIVE VIEW ACCESS TO 'guest' FOR ALL OTHER ROLES TO ALSO GET ACCESS
	}
	*/

	//3. ###### We create the fields we will need to add to our templates ######

	/*
			Prepare the array (with properties) we will use to create fields.
			We will modify some properties later for different contexts (templates).

			Additional Settings
				*	Some fields will need additional settings. 
				*	The Page Fields will need to be configured right at the end after creating blog fields, templates and pages
	*/

	$fields = array(

			'body' => array('name'=>'blog_body', 'type'=> 'FieldtypeTextarea', 'label'=>'Body'),
			'categories' => array('name'=>'blog_categories', 'type'=> 'FieldtypePage', 'label'=>'Categories', 'description'=>'Select one or more categories below and drag to sort them in order of relevance. If you want a category that doesn\'t already exist, create a new one.'),
			'comments' => array('name'=>'blog_comments', 'type'=> 'FieldtypeComments', 'label'=>'Comments', 'collapsed'=>2),
			'comments view' => array('name'=>'blog_comments_view', 'type'=> 'FieldtypePage', 'label'=>'Comments visibility'),
			'comments max' => array('name'=>'blog_comments_max', 'type'=> 'FieldtypeInteger', 'label'=>'Maximum comments allowed per post', 'autojoin'=>1),
			'quantity' => array('name'=>'blog_quantity', 'type'=> 'FieldtypeInteger', 'label'=>'Quantity of items to show', 'autojoin'=>1),
			'date' => array('name'=>'blog_date', 'type'=> 'FieldtypeDatetime', 'label'=>'Date', 'description'=>'This field will be automatically filled with the current time and date when your post is published. Unpublishing your post will not change the date. You can do so manually.', 'autojoin'=>1 ),
			'files' => array('name'=>'blog_files', 'type'=> 'FieldtypeFile', 'label'=>'Files',  'collapsed'=>2, 'entityencodedesc'=>1),
			'headline' => array('name'=>'blog_headline', 'type'=> 'FieldtypeText', 'label'=>'Headline', 'textformatters'=>'TextformatterEntities', 'collapsed'=>2, 'maxlength'=>1024),
			'href' => array('name'=>'blog_href', 'type'=> 'FieldtypeURL', 'label'=>'Website URL', 'autojoin'=>1, 'maxlength'=>1024),
			'images' => array('name'=>'blog_images', 'type'=> 'FieldtypeImage', 'label'=>'Images',  'collapsed'=>2, 'entityencodedesc'=>1),
			'links' => array('name'=>'blog_links', 'type'=> 'FieldtypeRepeater', 'label'=>'Links'),
			'note' => array('name'=>'blog_note', 'type'=> 'FieldtypeText', 'label'=>'Note', 'textformatters'=>'TextformatterEntities', 'autojoin'=>1, 'maxlength'=>1024),
			'summary' => array('name'=>'blog_summary', 'type'=> 'FieldtypeTextarea', 'label'=>'Summary', 'textformatters'=>'TextformatterEntities', 'collapsed'=>2, 'autojoin'=>1),
			'tags' => array('name'=>'blog_tags', 'type'=> 'FieldtypePage', 'label'=>'Tags'),
	);

	foreach ($fields as $field) {

		$f = new Field(); // create new field object
		$f->type = $this->modules->get($field['type']); // get a field type
		$f->name = $field['name'];
		$f->label = $field['label'];
		if (isset($field['description'])) $f->description = $field['description'];
		if (isset($field['textformatters'])) $f->textformatters = array($field['textformatters']);//needs to be an array
		if (isset($field['collapsed'])) $f->collapsed = $field['collapsed'];
		if (isset($field['autojoin'])) $f->flags = $f->flags | Field::flagAutojoin;
		if (isset($field['maxlength'])) $f->maxlength = $field['maxlength'];
		if (isset($field['entityencodedesc'])) $f->entityEncode = $field['entityencodedesc'];

		//additional settings for comments field
		if ($field['name'] == 'blog_comments') {
				$f->deleteSpamDays = 3;
				$f->moderate = 2;//moderate new users only
		}

		//additional settings for body field
		if ($field['name'] == 'blog_body') {
				$f->rows = 10;
				$f->inputfieldClass = 'InputfieldTinyMCE';
		}

		//additional settings for date field
		if ($field['name'] == 'blog_date') {
				$f->dateOutputFormat = 'j F Y g:i a';//8 April 2012 - in the details tab, combines date and time code in this field
				$f->dateInputFormat = 'j F Y';
				//$f->timeOutputFormat = 'g:i a';// 5:10 pm - for output, the code fields are combined - no need for this
				$f->timeInputFormat = 'g:i a';
				$f->datepicker = 3;//Date/Time picker on field focus
				$f->size = 30;
				$f->defaultToday = 0;
		}

		//additional settings for the blog_summary
		if($field['name'] == 'blog_summary') $f->rows = 3;

		//additional settings for the files and images fields
		if($field['name'] == 'blog_files') $f->extensions = 'pdf doc docx xls xlsx gif jpg jpeg png mp3 wav';//need string
		
		if($field['name'] == 'blog_images') {
				$f->extensions = 'gif jpg jpeg png';
				$f->adminThumbs = 1;//display thumbnails in page editor

		}

		$f->tags = 'blog';//tag our fields for grouping in admin
		$f->save(); // save the field
	
	}//end foreach fields

	//grab our newly created fields, assigning them to variables. We'll later add the fields to our templates
	$f = wire('fields');

	$title = $f->get('title');
	$body = $f->get('blog_body');
	$categories = $f->get('blog_categories');
	$comments = $f->get('blog_comments');
	$commentsView = $f->get('blog_comments_view');
	$commentsMax = $f->get('blog_comments_max');
	$quantity = $f->get('blog_quantity');
	$date = $f->get('blog_date');
	$files = $f->get('blog_files');
	$headline = $f->get('blog_headline');
	$href = $f->get('blog_href');
	$images = $f->get('blog_images');
	$links = $f->get('blog_links');//repeater field
	$note = $f->get('blog_note');
	$summary = $f->get('blog_summary');
	$tags = $f->get('blog_tags');
	
	
	//4. ###### We create the templates needed by the Blog ######

	/* 
		The template properties (indices) for the $templates array below
		Leave blank for defaults
			[0]	= label => string
			[1] = useRoles => boolean (0/1)
			[2] = noChildren 
			[3] = noParents
			[4] = allowPageNum
			[5] = urlSegments
			[6] = allowChangeUser//add later to post
		
		These three template properties are added later [out of preference, rather than creating too complex a $templates array]:
		childTemplates => array;
		parentTemplates => array;
		roles => array;
	*/

	//array for creating new templates: $k=template name; $v=template properties + fields
	$templates = array(

			'blog' => array('Blog', 0, '', 1, 1, 0, 0, 'fields' => array($title, $headline, $summary, $note, $quantity, $body)),
			'blog-archives' => array('Blog Archives', 0, 1, 1, 1, 1, 0, 'fields' => array($title)),
			'blog-authors' => array('Blog Authors', 0, 1, 1, 1, 1, 0, 'fields' => array($title)),
			'blog-basic' => array('Blog Basic', 0, '', '', 0, 0, 0, 'fields' => array($title)),
			'blog-categories' => array('Blog Categories', 1, '', 1, 0, 0, 0, 'fields' => array($title)),
			'blog-category' => array('Blog Category', 1, 1, '', 1, 1, 0, 'fields' => array($title, $body)),
			'blog-comments' => array('Blog Comments (List)', 0, 1, 1, 1, 1, 0, 'fields' => array($title, $headline, $quantity, $commentsView, $commentsMax)),
			'blog-links' => array('Blog Widget: Links', 0, 1, '', 0, 0, 0, 'fields' => array($title, $links, $summary)),
			'blog-post' 	=> array('Blog Post', 1, 1, '', 0, 0, 1, 'fields' => array($date, $title, $body, $images, $files, $categories, $tags, $commentsView, $comments) ),
			'blog-posts' => array('Blog Posts', 0, '', 1, 1, 1, 0, 'fields' => array($title, $headline, $quantity)),
			'blog-recent-comments' => array('Blog Widget: Recent Comments', 0, 1, 1, 0, 0, 0, 'fields' => array($title, $summary, $quantity)),
			'blog-recent-posts' => array('Blog Widget: Recent Posts', 0, 1, '', 0, 0, 0 , 'fields' => array($title, $summary, $quantity)),
			'blog-recent-tweets' => array('Blog Widget: Recent Tweets', 0, 1, 1, 0, 0, 0 , 'fields' => array($title, $note, $summary, $quantity)),
			'blog-tag' => array('Blog Tag', 0, 1, '', 1, 1, 0 , 'fields' => array($title)),
			'blog-tags' => array('Blog Tags', 1, '', 1, 0, 0, 0 , 'fields' => array($title)),
			'blog-widgets' => array('Blog Widgets', 1, '', 1, 0, 0, 0, 'fields' => array($title)),
			'blog-widget-basic' => array('Blog Widget: Basic', 0, 1, '', 0, 0, 0, 'fields' => array($title, $summary)),
			'repeater_blog-links' => array('', 0, 1, 1, 0, 0, 0, 'fields' => array($headline, $href)),

	);

	//create new fieldgroups and templates and add fields
	foreach ($templates as $k => $v) {

		//new fieldgroup
		$fg = new Fieldgroup();
		$fg->name = $k;

		//we loop through the fields array in each template array and add them to the fieldgroup
		foreach ($v['fields'] as $field) {
				$fg->add($field);
		}

		//$fg->add($this->fields->get('title')); //example if we needed title field
		$fg->save();

		//create a new template to use with this fieldgroup
		$t = new Template();
		$t->name = $k;
		$t->fieldgroup = $fg; //add the fieldgroup
		
		//add template settings we need
		if (!empty($v[0])) $t->label = $v[0];
		$t->useRoles = $v[1];
		$t->noChildren = $v[2];
		$t->noParents = $v[3];
		$t->allowPageNum = $v[4];
		$t->urlSegments = $v[5];
		$t->allowChangeUser = $v[6];
		$t->tags = 'blog';//tag our templates for grouping in admin
		if ($k == 'repeater_blog-links') $t->flags = 8;//if repeater template, designate it as a system template

		//save new template with fields and settings now added
		$t->save();
			
	}//end templates foreach

	
	//5. ###### post-creating our templates: additional settings for some templates ######

	//prepare arrays for some templates' childTemplates AND parentTemplates

	//childTemplates: key = template name; value = allowed child templates
	$childTemplates = array(
							'blog-categories' => 'blog-category',
							'blog-posts' => 'blog-post',
							'blog-tags' => 'blog-tag',
	);

	
	//add allowed child templates as applicable
	foreach ($childTemplates as $k => $v) {
		
				$t = wire('templates')->get($k);
				$t->childTemplates = array(wire('templates')->get($v)->id);//needs to be added as array of template IDs
				$t->save();//save the template

	}

	//parentTemplates: key = template name; value = allowed parent templates
	$parentTemplates = array(
							'blog-category' => 'blog-categories',
							'blog-post' => 'blog-posts',
							'blog-tag' => 'blog-tags',
	);

	//add allowed parent templates as applicable
	foreach ($parentTemplates as $k => $v) {
		
				$t = wire('templates')->get($k);
				$t->parentTemplates = array(wire('templates')->get($v)->id);//needs to be added as array of template IDs
				$t->save();//save the template
	}

	//array of templates that define view access
	$templatesViewAccess = array('blog-categories', 'blog-category', 'blog-post', 'blog-tags');

	//add role guest to each in order for view access to be applied
	foreach ($templatesViewAccess as $template) {
				
				$t = wire('templates')->get($template);
				$t->roles = array(wire('roles')->get('guest')->id);//we only need to add 'guest' role for other roles to get view access too
				$t->save();
	}


	//###### Set some in-context field widths, labels and descriptions for some templates #####

	//Labels and Descriptions: in-context values of some fields in some templates
	//$v[0]=template; $v[1]=field; $v[2]=label; $v[3]=description
	$templates = array(
						array('blog', 'blog_headline', 'Blog Title', 'You can use this (e.g. in the masthead) as a title for your blog.'),
						array('blog', 'blog_summary', 'Blog Tagline', 'An optional sentence or two of text that you can use as your blog\'s tagline.'),
						array('blog', 'blog_note', 'Footer', 'You can use this for footer messages (e.g. copyright notice).'),
						array('blog', 'blog_quantity', 'Quantity of posts to show on Blog homepage.'),
						array('blog-posts', 'blog_quantity', 'Posts truncate length.'),
						array('blog-post', 'blog_comments_view', '', 'Comments are visible by default. This setting overrides the global setting.'),
						
						//comments + widget pages
						array('blog-comments', 'blog_quantity', 'Comments per page in Comments page'),
						array('blog-comments', 'blog_comments_view', '', 'Comments are visible by default. Individual post\'s setting overrides what you specify here.'),
						array('blog-recent-comments', 'blog_quantity', 'Total Comments to show in widget'),	
						array('blog-recent-posts', 'blog_quantity', 'Total Posts to show in widget'),	
						array('blog-recent-tweets', 'blog_note', 'Twitter Screen Name'),	
						array('blog-recent-tweets', 'blog_quantity', 'Total Tweets to show in widget'),	
						
						//repeater 
						array('repeater_blog-links', 'blog_headline', 'Website Title'),
						array('repeater_blog-links', 'blog_href', 'Website URL'),
	);

	foreach ($templates as $v) {
			
			$t = wire('templates')->get($v[0]);
			$f = $t->fieldgroup->getField($v[1], true);
			if (!empty($v[2])) $f->label = $v[2];
			if (isset($v[3])) $f->description = $v[3];

			//for the repeater template we also set some field widths
			if ($v[0] == 'repeater_blog-links') $f->columnWidth = 50;//50%
					
			/*
			For the repeater template, we could do some extra tasks such as create the repeater page in the admin. 
			But PW will do that automatically if either the repeater field is accessed in setup or
			a page using the repeater field is added or edited
			*/
			
			wire('fields')->saveFieldgroupContext($f, $t->fieldgroup);//save settings in context
	}

	//Labels only: only for 'blog_summary' field for widgets templates
	$templates = array('blog-links', 'blog-recent-comments', 'blog-recent-posts', 'blog-recent-tweets', 'blog-widget-basic');

	foreach ($templates as $template) {
		
			$t = wire('templates')->get($template);
			$f = $t->fieldgroup->getField('blog_summary', true);
			$f->label = 'Widget Description';
			wire('fields')->saveFieldgroupContext($f, $t->fieldgroup);//save the in context label
	
	}

	//For the repeater, we need to add the fields for the repeater. We can only do it after those fields have been created and saved (above).
	//But we need to first create the repeater page (the 'for-field-id' page)

	$adminRoot = wire('config')->adminRootPageID;
	
	//get the repeaters page in admin [will only work if repeater module is installed!]
	
	$repeaterPage = new Page();

	//$repeaterPage->template = wire('templates')->get(2);//admin template

	$repeaterPage->template = wire('pages')->get($adminRoot)->template->id;//admin template
	$repeaterPage->parent =  wire('pages')->get("name=repeaters, parent_id=$adminRoot");//the "repeaters" page in Admin (parent of all repeaters)
	$repeaterPage->title = 'Blog Links';
	$name = "for-field-" . wire('fields')->get('blog_links')->id;//we'll need this later in a selector so we save to a variable
	$repeaterPage->name = $name;
	$repeaterPage->save();
			
	//get the repeater field to add to our repeater page
	$f = wire('fields')->get('blog_links');
	$f->parent_id = wire('pages')->get("name=$name")->id;//in db stored in 'fields' within the data for the 'FieldtypeRepeater'
	$f->template_id = wire('templates')->get('repeater_blog-links')->id;
	$f->repeaterReadyItems = 3;

	//We need the IDs of the fields to add to the repeater [adding as field objects didn't work]
	$fields = array('blog_headline', 'blog_href');
	foreach ($fields as $field) {
				$fieldsIDs[] = wire('fields')->get($field)->id;	
	}
	
	//add fields to the repeater page
	$f->repeaterFields =  $fieldsIDs;//array of field IDs to add to repeater
	$f->save();

	//We need to add some fields to 'user' template [for the Blog Authors] + sort fields + some custom labels for some fields

	//$k=field name => $v=label
	$userFieldsExtra = array(

							'title' => 'Display name (first and last name)',
							'blog_images' => '',//no label to add
							'blog_body' => 'Biography',
	);

	$t = wire('templates')->get('user');//
	$fg = $t->fieldgroup;
	
	//first, we add the extra fields we need
	foreach ($userFieldsExtra as $k => $v) {
				
				//if it is the title field, we prepend it (at the top) (it goes before 'pass'); 
				if ($k == 'title') {
						$fg->prepend(wire('fields')->get($k));
				}
			
				else {

					$fg->add(wire('fields')->get($k));//images and body fields get added to the bottom
				}
	}
	
	$fg->save();

	
	//add the in context labels
	foreach ($userFieldsExtra as $k => $v) {
				$f = $t->fieldgroup->getField($k, true);
				if (!empty($v)) $f->label = $v;
				wire('fields')->saveFieldgroupContext($f, $t->fieldgroup);//save in context settings

	}


	//6. ###### Create needed parent pages and some example child pages (total = 15 pages) ######
	// array of pages to add
	//first create 'blog' page; then other parent pages; then child pages

	
	//create the top blog page = 'blog'

	$p = new Page();
	$p->template = wire('templates')->get('blog');
	$p->parent = wire('pages')->get('/');
	$p->title = 'Blog';
	$p->save();

	//create top and some parent blog pages under 'blog' page
	//$k=title => $v = template (they share a common parent)
	$blogTopPages = array(

						'Posts' => 'blog-posts',
						'Categories' => 'blog-categories',
						'Tags' => 'blog-tags',
						'Comments' => 'blog-comments',
						'Widgets' => 'blog-widgets',
						'Authors' => 'blog-authors',
						'Archives' => 'blog-archives',
	);

	$parent = wire('pages')->get('name=blog, parent=/');//the 'Blog' page
	
	foreach ($blogTopPages as $k => $v) {
				
				$p = new Page();
				$p->template = wire('templates')->get($v);
				$p->parent = $parent;
				$p->title = $k;
				if ($k == 'Widgets') $p->addStatus(Page::statusHidden);//hidden page
				$p->save();
	}

	//$k=title => $v[0]=template; $v[1]=parent page (name)
	$blogChildPages = array(

						'Example Post' =>  array('blog-post', 'posts'),
						'Example Category' =>  array('blog-category', 'categories'),
						'Example Tag' =>  array('blog-tag', 'tags'),
						'Always Show Comments' =>  array('blog-basic', 'comments'),
						'Disable New Comments' =>  array('blog-basic', 'comments'),
						'Disable Comments' =>  array('blog-basic', 'comments'),
						'Recent Posts' =>  array('blog-recent-posts', 'widgets'),
						'Recent Comments' =>  array('blog-recent-comments', 'widgets'),
						'Blogroll' =>  array('blog-links', 'widgets'),
						'Recent Tweets' =>  array('blog-recent-tweets', 'widgets'),
						'Post Author' =>  array('blog-widget-basic', 'widgets'),
	);


	foreach ($blogChildPages as $k => $v) {
				
				$p = new Page();
				$p->template = wire('templates')->get($v[0]);
				$p->parent = wire('pages')->get("name=$v[1], parent=$parent");//$parent=blog
				$p->title = $k;
				//for 'Example Post' page, we need to add the date properly
				if($k == 'Example Post') $p->blog_date = date('j F Y g:i a');// e.g '8 April 2012 11:15 am'
				$p->save();
	}

	/* OUR PAGE STRUCTURE

		Blog
			Posts
				Example Post
			Categories
				Example category
			Tags
				Example Tag
			Comments
				Always Show Comments
				Disable New Comments
				Disable Comments
			Widgets
				Recent Posts
				Recent Comments
				Blogroll
				Recent Tweets
				Post Author
			Authors
			Archives

	*/

	
	//#### additional settings/values for some page properties and field values ####

	//$v[0]=page name; $v[1]=field/property; $v[2]=field/property value
	$pagesExtras = array(
							//sortfields
							array('posts', 'sortfield', '-blog_date'),//for 'posts', sort its children by date, descending
							array('categories', 'sortfield','title'),
							array('tags', 'sortfield', 'title'),

							//main blog page
							array('blog', 'blog_headline', 'My Awesome Blog (optional)'),//dummy text for Blog Title
							array('blog', 'blog_summary', 'This is a blog about this and that (optional tagline)'),//dummy text
							array('blog', 'blog_quantity', 3),

							//comments page + widget pages: some info values
							array('comments', 'blog_headline', 'Recent Comments'),
							array('comments', 'blog_quantity', 10),//some initial value
							array('recent-comments', 'blog_summary', 'Shows a limited number of the most recent comments in a list. Set this number in General Settings.'),
							array('recent-comments', 'blog_quantity', 3),
							array('recent-posts', 'blog_summary', 'Shows a limited number of your most recent posts in a list. Set this number in General Settings.'),
							array('recent-posts', 'blog_quantity', 3),
							array('recent-tweets', 'blog_summary', 'Shows a limited list of your most recent tweets. Set this number in General Settings.'),
							array('recent-tweets', 'blog_quantity', 3),
							array('blogroll', 'blog_summary', 'Shows links to other blogs that you like.'),
							array('post-author', 'blog_summary', 'Renders Post\'s author biography.'),

	);

	//by this point, we know there is only our page named 'blog' under root
	$s = 'has_parent=' . wire('pages')->get("name=blog, parent=/");//we need this selector to be extra sure we are getting the right page to set values to

	foreach ($pagesExtras as $v) {

				//if we are on the 'blog' page, selector to use is parent
				if ($v[0]=='blog') {

						$selector = 'parent=/';
				} 

				//else we use has_parent selector. 
				//We know this will work since there is only 1 page under root named 'blog'. We also know it doesn't have any children except the ones we installed
				else {

						$selector = $s;
				}

		$p = wire('pages')->get("name=$v[0], $selector"); 
		$p->$v[1] = $v[2];//set the field name $v[1] to have the value $v[2]
		//$p->set($v[1], $v[2]);//alternative syntax to above
		$p->save();

	}

	//additional settings for the two Page fields created earlier [blog_categories,blog_tags & blog_comments_view]
	//these can only be done here since we first need blog templates, fields and pages created and saved!
	//we are setting: selectable parent, selectable templates, create new, AsmSelect, PageAutomplete, etc
	
	//blog_categories
	$f = wire('fields')->get('blog_categories');//
	$f->parent_id = wire('pages')->get('name=categories, parent.name=blog')->id;//parent of pages that are selectable: 'categories'
	$f->template_id = wire('templates')->get('blog-category')->id;//template of pages that are selectable: 'category'
	$f->labelFieldName = 'title';
	$f->addable = 1;//allow new pages to be created from field
	$f->derefAsPage = 0;//multiple pages (PageArray) - i.e. FieldtypePage::derefAsPageArray;
	//$f->derefAsPage = FieldtypePage::derefAsPageArray;
	$f->inputfield = 'InputfieldAsmSelect';
	$f->save();

	//blog_tags
	$f = wire('fields')->get('blog_tags');//
	$f->parent_id = wire('pages')->get('name=tags, parent.name=blog')->id;//parent of pages that are selectable: 'tags'
	$f->template_id = wire('templates')->get('blog-tag')->id;//template of pages that are selectable: 'tag'
	$f->addable = 1;//allow new pages to be created from field
	$f->derefAsPage = 0;//multiple pages (PageArray) - i.e. FieldtypePage::derefAsPageArray;
	//$f->derefAsPage = FieldtypePage::derefAsPageArray;
	$f->labelFieldName = 'title';
	$f->operator = "%=";
	$f->searchFields = 'title';
	$f->inputfield = 'InputfieldPageAutocomplete';
	$f->save();	

	//blog_comments_view
	$f = wire('fields')->get('blog_comments_view');//
	$f->derefAsPage = 1;//single page (PageArray) or boolean false when none selected - i.e. FieldtypePage::derefAsPageOrFalse
	//$f->derefAsPage = FieldtypePage::derefAsPageOrFalse;
	$f->findPagesCode = 'return $page->path == "/blog/comments/" ? $pages->get("/blog/comments/")->children("name!=always-show-comments") : $pages->get("/blog/comments/")->children();';
	$f->labelFieldName = 'title';
	$f->inputfield = 'InputfieldSelect';
	$f->save();	
	
	//7. ###### Copy the 14 'template files' for Blog templates that need them [note: these are almost empty files for user to later populate with markup] ######
	//the 'template files' contain example MarkupBlog code for various aspects of the blog, e.g. Show 'Tags', 'Posts', etc.

	//array of template files we want to copy to /site/templates/ (we only copy files if they DO NOT exist at destination!!!)
	$templateFiles = array(
							'blog.php', 'blog-archives.php', 'blog-authors.php', 'blog-categories.php', 'blog-category.php',
							'blog-comments.php', 'blog-links.php', 'blog-post.php', 'blog-posts.php', 'blog-recent-comments.php',
							'blog-recent-posts.php', 'blog-recent-tweets.php', 'blog-tag.php', 'blog-tags.php', 'blog-side-bar.inc',
	);

	$sourcepath = dirname(__FILE__) . '/template-files/';//source of the template files to copy over [this is 'ProcessBlog/template-files/']
	
	$destinationpath = wire('config')->paths->templates;//destination: '/site/templates/'

	foreach ($templateFiles as $templateFile) {

				if(is_file($destinationpath . $templateFile)) continue;//if a file with the same name already exists, skip to next file. We don't want to overwrite users files!
				
				copy($sourcepath . $templateFile, $destinationpath . $templateFile);//copy only those files that do not yet exist at destination.
	}


	//if we made it here return success message!
	return $this->message("Blog Module Successfully Installed. Fields, Templates, Template Files, Pages and a Role created."); 
