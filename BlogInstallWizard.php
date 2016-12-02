<?php

/**
*
* This is an include class for ProcessBlog installWizard($form). It is only run once when finilasing installing Blog
*
* It selectively installs 'fields', 'templates', 'template files', 'blog pages' and a 'role' in the ProcessWire site
* for use with the modules ProcessBlog and if needed, its sister module, MarkupBlog.
*
* If the above already exist (i.e., same names); this installer aborts wholesale.
* If installer proceeds and if user selected the option, 'template-files' are only copied if they do not exist at destination, i.e. '/site/templates/'. 
* We don't want to overwrite users files!
*
* @author Francis Otieno (Kongondo)
* @version 2.4.0 
*
* https://github.com/kongondo/Blog
* Created February 2014
*
*/

class BlogInstallWizard extends ProcessBlog {

	
	// the type of blog style selected by user (1-4)
	private $blogStyle;

	// install scheduled auto-publish/unpublish feature (0/1)
	private $schedulePages;

	// install commenting feature (0/1)
	private $commentsUse;

	// type of template files to install (blank, demo, don't install)
	private $templateFilesInstall;

	// whether to copy over the demo 'blog.js' javascript file to /site/templates/scripts/
	private $demoBlogScriptsInstall;

	// whether to copy over the demo css folder and its contents to /site/templates/css/
	private $demoBlogCSSInstall;

	// tag for templates and fields
	private $tagTemplatesFields;

	// module config settings
	protected $data = array();

	// this will store MAIN Blog PAGES IDs to be stored in module config
	private $blogPagesIDs = array();

	// this will carry Blog's parent pages titles
	private $parents = array();

	/**
	* 	Check if similar fields, templates, template files, pages and role exist before install.
	*
	*	@access public
	*
	*/	
	public function verifyInstall($form) {

		// 1. ###### First we check if our parent BLOG PAGE(S), role [blog-author], fields and templates already exist. 
			// If yes to any of these, we abort installation and return error messages - note: we check according to selected blogStyle!
			// NOTE: Comments only applicable if feature was selected


		/*
				NOTE!!! Different/customised titles could have been specified! e.g. instead of 'posts', can be 'items' - so we check $input->post values.

				style 1: enough to only check 'blog'; - this is the top most parent all blog pages
				style 2: enough to check 'blog'; - this is the top most parent of all blog pages
				style 3: top most parent = root; There is no 'blog'. Check Posts, Categories, Tags, Comments, Widgets, Authors and Archives - NOTE: Comments only applicable if feature was selected
				style 4: top most parent = root; similar to #3 except there is no 'Posts' too: Check Example Post, Categories, Tags, Comments, Widgets, Authors and Archives				
		*/

		$sanitizer = $this->wire('sanitizer');
		$input = $this->wire('input')->post;
		$pages = $this->wire('pages');
		$templates = $this->wire('templates');
		$fields = $this->wire('fields');
		
		$form->processInput($input);

		$installWizardBtn = $input->install_wizard_btn;

		// posts bulk actions
		if($installWizardBtn && $installWizardBtn == 'Run install wizard') {// was the right button pressed

			// Get the module config data
			$data = $this->wire('modules')->getModuleConfigData(get_parent_class($this));

			// selected blog style (1-4)
			$this->blogStyle = $data['blogStyle'];

			// install comments? - will determine if we are to verify comment page's title below. Will also use this later
			$this->commentsUse = $data['commentsUse'];

			// sanitized titles of parent pages
			$parents = array();

			// $k = template and $v = title - saves time later. Also $k = our identifier when saving IDs to module config settings. See below in createPages() $blogPagesIDs	
			
			$parents['blog'] = $sanitizer->text($input->blog);
			$parents['blog-posts'] = $sanitizer->text($input->posts);
			$parents['blog-categories'] = $sanitizer->text($input->categories);
			$parents['blog-tags'] = $sanitizer->text($input->tags);
			$parents['blog-comments'] = $sanitizer->text($input->comments);
			$parents['blog-widgets'] = $sanitizer->text($input->widgets);
			$parents['blog-authors'] = $sanitizer->text($input->authors);
			$parents['blog-archives'] = $sanitizer->text($input->archives);
			$parents['blog-settings'] = $sanitizer->text($input->settings);

			// remove irrelevant parent titles/pages depending on blogStyle
			if ($this->blogStyle == 2 || $this->blogStyle == 4) unset($parents['blog-posts']);
			if ($this->blogStyle == 3 || $this->blogStyle == 4) unset($parents['blog']);
			if ($this->commentsUse != 1) unset($parents['blog-comments']);
			
			// add example post at top of $parents if blogStyle == 2 || blogStyle == 4. we'll need it later
			if ($this->blogStyle == 2 || $this->blogStyle == 4) {
					$examplePost = array('blog-post' => 'Example Post', );
					$parents = array_merge($examplePost, $parents);
			}

			// our $parents array is ready for some checks

			// array of existing pages
			$pagesCheck = array();

			// if $blogStyle == 1 || $blogStyle == 2 it means our strucute is /blog/ - so 'blog' is main parent of all blog pages
			if ($this->blogStyle == 1 || $this->blogStyle == 2) {// 
				
					# check only for 'blog' as child of root
					# BUT REMEMBER - we may be checking against the title set by the user! so, we grab value from $parents array
			
						foreach ($parents as $key => $value) {	
									$pTitle = $value;
									// if no title provided throw an error
									if (!$pTitle ) {
										$this->error($this->_("Parent pages titles are required.")); 
										return false;
									}
						}

					// here we only need to check for existence of a similarly named 'blog' page
					$bTitle = $parents['blog'];
					
					// if a title was provided, we sanitize it and convert it to a URL friendly page name to later check if such a page already exists under this parent page			
					$bName = $sanitizer->pageName($bTitle);

					$pageID = $pages->get('/')->child("name={$bName}, include=all")->id;// check by ID to be absolutely sure

					if($pageID) $pagesCheck [] = $pages->get($pageID)->title;// show them the title since this is what they would have entered in form	
					
					$pagesExist = count($pagesCheck) ? true : false;// we'll use this later + $pagesCheck to show errors

			
			}// end if $blogStyle == 1 or $blogStyle == 2

			// if $blogStyle == 3 it means our structure is /posts/ - but root is main parent of all blog pages - so /posts/; /categories/, etc
			// if $blogStyle == 4 it means our structure is /example-post/ - but root is main parent of all blog pages - so /example-post/; /categories/, etc
			elseif ($this->blogStyle == 3 || $this->blogStyle == 4) {

						// array of existing pages
						$pagesCheck = array();

						foreach ($parents as $key => $value) {

									$pTitle = $value;
									
									// if no title provided throw an error
									if (!$pTitle ) {

												$this->error($this->_("Parent pages titles are required.")); 
												return false;

									}
					
									// if a title was provided, we sanitize it and convert it to a URL friendly page name to later check if such a page already exists under this parent page
									$pName = $sanitizer->pageName($pTitle);

									$pageID = $pages->get('/')->child("name={$pName}, include=all")->id;// check by ID to be absolutely sure

									if($pageID) $pagesCheck [] = $pages->get($pageID)->title;// show them the title since this is what they would have entered in form							
						
						}// end foreach checks of all parent pages (i.e. posts, categories, tags,comments [if applicable], widgets, authors, archives and settings)

						$pagesExist = count($pagesCheck) ? true : false;// we'll use this later + $pagesCheck to show errors


			}// end if $blogStyle = 3 || $blogStyle = 4



			################################ fields check################################

			// check if fields 'blog_images', etc, already exist
			$fieldsArray  = array(
							'body' => 'blog_body',
							'categories' => 'blog_categories',
							'comments' => 'blog_comments',
							'comments view' => 'blog_comments_view',
							'comments max' => 'blog_comments_max',
							'quantity' => 'blog_quantity',
							'date' => 'blog_date',
							'files' => 'blog_files',
							'headline' => 'blog_headline',
							'href' => 'blog_href',
							'images' => 'blog_images',
							'links' => 'blog_links',
							'note' => 'blog_note',
							'summary' => 'blog_summary',
							'tags' => 'blog_tags',
							'small' => 'blog_small',
			);

			
			// remove comments fields if $commentsUse!=1
			if ($this->commentsUse != 1) {
						unset($fieldsArray['comments']);
						unset($fieldsArray['comments view']);
						unset($fieldsArray['comments max']);
			}

			$fieldsCheck = array();
			foreach ($fieldsArray as $key => $value) {if($fields->get($value)) $fieldsCheck [] = $fields->get($value)->name;}
			$fieldsExist = count($fieldsCheck) ? true : false;

			$templatesArray = array(
								'blog' => 'blog',
								'archives' => 'blog-archives',
								'authors' => 'blog-authors',
								'categories' => 'blog-categories',
								'category' => 'blog-category',
								'comments' => 'blog-comments',
								'link' => 'blog-links',
								'post' => 'blog-post' ,
								'posts' => 'blog-posts',
								'recent comments' => 'blog-recent-comments',
								'recent posts' => 'blog-recent-posts',
								'recent tweets' => 'blog-recent-tweets',
								'tag' => 'blog-tag'	,
								'tags' => 'blog-tags',
								'widgets' => 'blog-widgets',
								'widget basic' => 'blog-widget-basic',
								'settings' => 'blog-settings',
								'basic' => 'blog-basic',
								'repeater blog links' => 'repeater_blog-links',

			);      

			// remove irrelevant templates depending on blogStyle
			if ($this->blogStyle == 2 || $this->blogStyle == 4) unset($templatesArray['posts']);
			if ($this->blogStyle == 3 || $this->blogStyle == 4) unset($templatesArray['blog']);


			// remove comment-related templates if $commentsUse!=1
			if ($this->commentsUse != 1) {
						unset($templatesArray['comments']);
						unset($templatesArray['recent comments']);
						unset($templatesArray['basic']);						
			}

			$templatesCheck = array();
			foreach ($templatesArray as $tpl) {if($templates->get($tpl)) $templatesCheck [] = $templates->get($tpl)->name;}
			$templatesExist = count($templatesCheck) ? true : false;

			// check if role 'blog-author' already exists
			$r = $this->wire('roles')->get('blog-author');
			$roleExists = $r->id ? true : false;

			if($pagesExist == true){
					$failedPages = implode(', ', $pagesCheck);
					$this->error($this->_("Cannot install Blog pages. Some page names already in use. These are: {$failedPages}."));
			}
			
			if($fieldsExist == true){
					$failedFields = implode(', ', $fieldsCheck);
					$this->error($this->_("Cannot install Blog fields. Some field names already in use. These are: {$failedFields}."));
			}
			
			if($templatesExist == true){				
					$failedTemplates = implode(', ', $templatesCheck);
					$this->error($this->_("Cannot install Blog templates. Some template names already in use. These are: {$failedTemplates}."));
			}
			
			if($roleExists == true) $this->error($this->_("A role called 'blog-author' already exists!"));

			
			// if any of our checks returned true, we abort early
			if($pagesExist == true || $fieldsExist == true || $templatesExist == true || $roleExists == true) {
				
				$this->error($this->_('Due to the above errors, the install wizard did not run. Make necessary changes and try again.'));
				
				// due to above errors, we stop executing install of the following 'role', 'templates', 'template files', 'fields' and 'pages'
				return false;

			}

			### GOOD TO GO ###

			/* ############## - set some properties and pass on to $this->createRole() - ############## */

			// set some remaining properties we didn't set earlier that we'll need later on			
			$this->parents = $parents;// array of final parents: set to use later down
			$this->schedulePages = $data['schedulePages'];// install auto-publishing/unpublishing scheduling feature?				
			$this->templateFilesInstall = $data['templateFilesInstall'];// type of template files to install (blank, demo, don't install)
			$this->demoBlogScriptsInstall = $data['demoJS'];// install js script for demo
			$this->demoBlogCSSInstall = $data['demoCSS'];// install css for demo
			$this->tagTemplatesFields = $data['tagTemplatesFields'];// tag for templates and fields
			
			// pass on to first step of install
			return $this->createRole();

		}// end if install wizard button pressed
	
	}

	/**
	* 	Create role 'blog-author'.
	*
	*	@access private
	*
	*/	
	private function createRole() {

		#################################################################################################################################

		// 2. ###### If we are good, we create the role 'blog-author' that we will later add to some of our templates ######

		$r = new Role();
		$r->name = 'blog-author';
		$r->save();

		// create an array of guest and author roles IDs to later add to our new templates
		/*
		foreach ($this->wire('roles')->find('name=guest|author') as $r) {
				$templateRoles[] = $r->id;// EDIT; DON'T REALLY NEED THIS ARRAY. I ONLY NEED TO GIVE VIEW ACCESS TO 'guest' FOR ALL OTHER ROLES TO ALSO GET ACCESS
		}
		*/

		// pass on to creating fields
		return $this->createFields();

	}

	/**
	* 	Create several blog fields.
	*
	*	Created fields match the selected blog style.
	*
	*	@access private
	*
	*/	
	private function createFields() {

		// 3. ###### We create the fields we will need to add to our templates ######
		$fields = $this->wire('fields');

		/*
				Prepare the array (with properties) we will use to create fields.
				We will modify some properties later for different contexts (templates).

				Additional Settings
					*	Some fields will need additional settings. 
					*	The Page Fields will need to be configured right at the end after creating blog fields, templates and pages
		*/

		$fieldsArray = array(

				'body' => array('name'=>'blog_body', 'type'=>'FieldtypeTextarea', 'label'=>'Body'),
				'categories' => array('name'=>'blog_categories', 'type'=>'FieldtypePage', 'label'=>'Categories', 'description'=>'Select one or more categories below and drag to sort them in order of relevance. If you want a category that doesn\'t already exist, create a new one.'),
				'comments' => array('name'=>'blog_comments', 'type'=>'FieldtypeComments', 'label'=>'Comments', 'collapsed'=>2),
				'comments view' => array('name'=>'blog_comments_view', 'type'=> 'FieldtypePage', 'label'=>'Comments visibility'),
				'comments max' => array('name'=>'blog_comments_max', 'type'=> 'FieldtypeInteger', 'label'=>'Maximum comments allowed per post', 'autojoin'=>1),
				'quantity' => array('name'=>'blog_quantity', 'type'=>'FieldtypeInteger', 'label'=>'Quantity of items to show', 'autojoin'=>1),
				'date' => array('name'=>'blog_date', 'type'=>'FieldtypeDatetime', 'label'=>'Date', 'description'=>'This field will be automatically filled with the current time and date when your post is published. Unpublishing your post will not change the date. You can do so manually.', 'autojoin'=>1 ),
				'files' => array('name'=>'blog_files', 'type'=>'FieldtypeFile', 'label'=>'Files',  'collapsed'=>2, 'entityencodedesc'=>1),
				'headline' => array('name'=>'blog_headline', 'type'=>'FieldtypeText', 'label'=>'Headline', 'textformatters'=>'TextformatterEntities', 'collapsed'=>2, 'maxlength'=>1024),
				'href' => array('name'=>'blog_href', 'type'=>'FieldtypeURL', 'label'=>'Website URL', 'autojoin'=>1, 'maxlength'=>1024),
				'images' => array('name'=>'blog_images', 'type'=>'FieldtypeImage', 'label'=>'Images',  'collapsed'=>2, 'entityencodedesc'=>1),
				'links' => array('name'=>'blog_links', 'type'=>'FieldtypeRepeater', 'label'=>'Links'),
				'note' => array('name'=>'blog_note', 'type'=>'FieldtypeText', 'label'=>'Note', 'textformatters'=>'TextformatterEntities', 'autojoin'=>1, 'maxlength'=>1024),
				'summary' => array('name'=>'blog_summary', 'type'=>'FieldtypeTextarea', 'label'=>'Summary', 'textformatters'=>'TextformatterEntities', 'collapsed'=>2, 'autojoin'=>1),
				'tags' => array('name'=>'blog_tags', 'type'=>'FieldtypePage', 'label'=>'Tags'),
				'small' => array('name'=>'blog_small', 'type'=>'FieldtypeInteger', 'label'=>'Posts truncate length', 'autojoin'=>1),
		);

		// remove comments fields if $commentsUse!=1
		if ($this->commentsUse != 1) {
					unset($fieldsArray['comments']);
					unset($fieldsArray['comments view']);
					unset($fieldsArray['comments max']);
		}

		foreach ($fieldsArray as $fld) {

			$f = new Field(); // create new field object
			$f->type = $this->wire('modules')->get($fld['type']); // get a field type
			$f->name = $fld['name'];
			$f->label = $fld['label'];
			if (isset($fld['description'])) $f->description = $fld['description'];
			if (isset($fld['textformatters'])) $f->textformatters = array($fld['textformatters']);// needs to be an array
			if (isset($fld['collapsed'])) $f->collapsed = $fld['collapsed'];
			if (isset($fld['autojoin'])) $f->flags = $f->flags | Field::flagAutojoin;
			if (isset($fld['maxlength'])) $f->maxlength = $fld['maxlength'];
			if (isset($fld['entityencodedesc'])) $f->entityEncode = $fld['entityencodedesc'];

			// additional settings for comments field if commenting feature was selected (i.e. $this->commentsUse ==1)
			if ($this->commentsUse == 1 && $fld['name'] == 'blog_comments') {
					$f->deleteSpamDays = 3;
					$f->moderate = 2;// moderate new users only
			}

			// additional settings for body field - changed to CKEditor from TinyMCE after PW 2.5 release! It is now core
			if ($fld['name'] == 'blog_body') {
					$f->rows = 10;
					$f->inputfieldClass = 'InputfieldCKEditor';
			}

			// additional settings for date field
			if ($fld['name'] == 'blog_date') {
					$f->dateOutputFormat = 'j F Y g:i a';// 8 April 2012 - in the details tab, combines date and time code in this field
					$f->dateInputFormat = 'j F Y';
					// $f->timeOutputFormat = 'g:i a';// 5:10 pm - for output, the code fields are combined - no need for this
					$f->timeInputFormat = 'g:i a';
					$f->datepicker = 3;// Date/Time picker on field focus
					$f->size = 30;
					$f->defaultToday = 0;
			}

			// additional settings for the blog_summary
			if($fld['name'] == 'blog_summary') $f->rows = 3;

			// additional settings for the files and images fields
			if($fld['name'] == 'blog_files') $f->extensions = 'pdf doc docx xls xlsx gif jpg jpeg png mp3 wav';// needs string
			
			if($fld['name'] == 'blog_images') {
					$f->extensions = 'gif jpg jpeg png';
					$f->adminThumbs = 1;// display thumbnails in page editor

			}

			$f->tags = $this->tagTemplatesFields;
			$f->save(); // 
		
		}// end foreach fields

		// grab our newly created fields, assigning them to variables. We'll later add the fields to our templates		

		// set some class properties on the fly. We will use this in createTemplates()
		$this->title = $fields->get('title');
		$this->body = $fields->get('blog_body');
		$this->categories = $fields->get('blog_categories');
		
		// only set if commenting feature available otherwise empty
		$this->comments = $this->commentsUse == 1 ? $fields->get('blog_comments') : '';
		$this->commentsView = $this->commentsUse == 1 ? $fields->get('blog_comments_view') : '';
		$this->commentsMax = $this->commentsUse == 1 ?  $fields->get('blog_comments_max') : '';

		$this->quantity = $fields->get('blog_quantity');
		$this->small = $fields->get('blog_small');
		$this->date = $fields->get('blog_date');
		$this->blogfiles = $fields->get('blog_files');// @note: using 'blogfiles' rather than 'files' to avoid nameclass with ProcessWire
		$this->headline = $fields->get('blog_headline');
		$this->href = $fields->get('blog_href');
		$this->images = $fields->get('blog_images');
		$this->links = $fields->get('blog_links');// repeater field
		$this->note = $fields->get('blog_note');
		$this->summary = $fields->get('blog_summary');
		$this->tags = $fields->get('blog_tags');

		// lets create some templates and add our fields to them
		return $this->createTemplates();

	}

	/**
	* 	Create several blog templates.
	*
	*	Created templates match the selected blog style.
	*
	*	@access private
	*
	*/	
	private function createTemplates() {

		// 4. ###### We create the templates needed by the Blog ######

		/* 
			The template properties (indices) for the $templates array below
			Leave blank for defaults
				[0]	= label => string
				[1] = useRoles => boolean (0/1)
				[2] = noChildren 
				[3] = noParents
				[4] = allowPageNum
				[5] = urlSegments
				[6] = allowChangeUser// add later to post
			
			These three template properties are added later [out of preference, rather than creating too complex a $templates array]:
			childTemplates => array;
			parentTemplates => array;
			roles => array;
		*/
		
		// these are field objects we set earlier. We assign them to variables for simplicity
		$title = $this->title;
		$body = $this->body;
		$categories = $this->categories;
		
		// will be empty if commenting feature not chosen (see above)
		$comments = $this->comments;
		$commentsView = $this->commentsView;
		$commentsMax = $this->commentsMax;

		$quantity = $this->quantity;
		$small = $this->small;
		$date = $this->date;
		$files = $this->blogfiles;// @note: using 'blogfiles' rather than 'files' to avoid nameclass with ProcessWire
		$headline = $this->headline;
		$href = $this->href;
		$images = $this->images;
		$links = $this->links;// repeater field
		$note = $this->note;
		$summary = $this->summary;
		$tags = $this->tags;

		// array for creating new templates: $k=template name; $v=template properties + fields
		$templatesArray = array(

				'blog' => array('Blog', 0, '', 1, 1, 0, 0, 'fields' => array($title, $body)),// moved $summary, $note and $quantity to settings page
				'blog-archives' => array('Blog Archives', 0, 1, 1, 1, 1, 0, 'fields' => array($title)),
				'blog-authors' => array('Blog Authors', 0, 1, 1, 1, 1, 0, 'fields' => array($title)),
				'blog-basic' => array('Blog Basic', 0, '', '', 0, 0, 0, 'fields' => array($title)),
				'blog-categories' => array('Blog Categories', 1, '', 1, 0, 0, 0, 'fields' => array($title)),
				'blog-category' => array('Blog Category', 1, 1, '', 1, 1, 0, 'fields' => array($title, $body)),
				'blog-comments' => array('Blog Comments (List)', 0, 1, 1, 1, 1, 0, 'fields' => array($title, $headline, $quantity, $commentsView, $commentsMax)),
				'blog-links' => array('Blog Widget: Links', 0, 1, '', 0, 0, 0, 'fields' => array($title, $links, $summary)),
				'blog-post' 	=> array('Blog Post', 1, 1, '', 0, 0, 1, 'fields' => array($date, $title, $body, $images, $files, $categories, $tags, $commentsView, $comments) ),
				'blog-posts' => array('Blog Posts', 0, '', 1, 1, 1, 0, 'fields' => array($title, $headline)),// moved quantity to settings page as $small
				'blog-recent-comments' => array('Blog Widget: Recent Comments', 0, 1, 1, 0, 0, 0, 'fields' => array($title, $summary, $quantity)),
				'blog-recent-posts' => array('Blog Widget: Recent Posts', 0, 1, '', 0, 0, 0 , 'fields' => array($title, $summary, $quantity)),
				'blog-recent-tweets' => array('Blog Widget: Recent Tweets', 0, 1, 1, 0, 0, 0 , 'fields' => array($title, $note, $summary, $quantity)),
				'blog-tag' => array('Blog Tag', 0, 1, '', 1, 1, 0 , 'fields' => array($title)),
				'blog-tags' => array('Blog Tags', 1, '', 1, 0, 0, 0 , 'fields' => array($title)),
				'blog-widgets' => array('Blog Widgets', 1, '', 1, 0, 0, 0, 'fields' => array($title)),
				'blog-widget-basic' => array('Blog Widget: Basic', 0, 1, '', 0, 0, 0, 'fields' => array($title, $summary)),
				'repeater_blog-links' => array('', 0, 1, 1, 0, 0, 0, 'fields' => array($headline, $href)),
				'blog-settings' => array('Blog Settings', 1, 1, 1, 0, 0, 0, 'fields' => array($title, $headline, $summary, $note, $quantity, $small)),

		);		
			
		// remove irrelevant templates depending on blogStyle
		if ($this->blogStyle == 2 || $this->blogStyle == 4) unset($templatesArray['blog-posts']);
		if ($this->blogStyle == 3 || $this->blogStyle == 4) unset($templatesArray['blog']);

		// remove some templates if $commentsUse!=1
		if ($this->commentsUse != 1) {
					unset($templatesArray['blog-comments']);// for ,comments'
					unset($templatesArray['blog-recent-comments']);// for 'recent comments'
					unset($templatesArray['blog-basic']);// for 'comments' page children: 'Always Show Comments'; 'Disable New Comments'; 'Disable Comments'			
		}

		// create new fieldgroups and templates and add fields
		foreach ($templatesArray as $k => $v) {

			// new fieldgroup
			$fg = new Fieldgroup();
			$fg->name = $k;

			// we loop through the fields array in each template array and add them to the fieldgroup
			foreach ($v['fields'] as $field) {
					if (!empty($field)) $fg->add($field);
			}

			// $fg->add($this->fields->get('title')); // example if we needed title field
			$fg->save();

			// create a new template to use with this fieldgroup
			$t = new Template();
			$t->name = $k;
			$t->fieldgroup = $fg; // add the fieldgroup
			
			// add template settings we need
			if (!empty($v[0])) $t->label = $v[0];
			$t->useRoles = $v[1];
			$t->noChildren = $v[2];
			$t->noParents = $v[3];
			$t->allowPageNum = $v[4];
			$t->urlSegments = $v[5];
			$t->allowChangeUser = $v[6];
			$t->tags = $this->tagTemplatesFields;// tag our templates for grouping in admin using the tag set by the user in final install
			if ($k == 'repeater_blog-links') $t->flags = 8;// if repeater template, designate it as a system template

			// save new template with fields and settings now added
			$t->save();
				
		}// end templates foreach

		return $this->extraTemplateSettings();

	}

	/**
	* 	Add extra settings from some template.
	*
	*	@access private
	*
	*/	
	private function extraTemplateSettings() {	

		// 5. ###### post-creating our templates: additional settings for some templates ######
		$templates = $this->wire('templates');
		$fields = $this->wire('fields');

		// prepare arrays for some templates' childTemplates AND parentTemplates

		// childTemplates: key = template name; value = allowed child templates
		$childTemplates = array(
								'blog-categories' => 'blog-category',
								'blog-posts' => 'blog-post',
								'blog-tags' => 'blog-tag',
		);

		// remove blog-posts if blogStyle = 2 || blogStyle = 4:
		// if == 2 it means 'example-post' will be child of Blog; it can have other children. 
		// If == 4, 'example-post' is child of root. It must be allowed to have other children
		if ($this->blogStyle == 2 || $this->blogStyle == 4) {unset($childTemplates['blog-posts']);}

		// add allowed child templates as applicable
		foreach ($childTemplates as $k => $v) {

					$t = $templates->get($k);
					$t->childTemplates = array($templates->get($v)->id);// needs to be added as array of template IDs
					$t->save();// save the template

		}

		// if blogStyle == 2, 'blog' is the parent of 'example-post', $parentTemplate = 'blog';
		if ($this->blogStyle == 2) {$parentTemplate = 'blog';}
		// if blogStyle == 4, 'root' is the parent of 'example-post', $parentTemplate = 'template of root' - we have to get this using API;
		elseif ($this->blogStyle == 4) {$parentTemplate = $this->wire('pages')->get('/')->template->name;}
		else{$parentTemplate = 'blog-posts';}// else we default to blog-posts = blogStyle=1 || blogStyle=3

		// parentTemplates: key = template name; value = allowed parent templates
		$parentTemplates = array(
								'blog-category' => 'blog-categories',
								'blog-post' => $parentTemplate,// varies depending on blogStyle 1&3='blog-posts'; 2='blog'; 4='root's template'; 
								'blog-tag' => 'blog-tags',
		);

		// add allowed parent templates as applicable
		foreach ($parentTemplates as $k => $v) {

					$t = $templates->get($k);
					$t->parentTemplates = array($templates->get($v)->id);// needs to be added as array of template IDs
					$t->save();// save the template
		}

		// array of templates that define view access
		$templatesViewAccess = array('blog-categories', 'blog-category', 'blog-post', 'blog-tags');

		// add role guest to each in order for view access to be applied
		foreach ($templatesViewAccess as $template) {

					$t = $templates->get($template);
					$t->roles = array($this->wire('roles')->get('guest')->id);// we only need to add 'guest' role for other roles to get view access too
					$t->save();
		}

		// add SchedulePages fields to blog-post template if $this->schedulePages==1
		if ($this->schedulePages == 1) {

				$t = $templates->get('blog-post');
				$fg = $t->fieldgroup;
				$fg->prepend($fields->get('publish_until')); // prepend publish_until field at the top
				$fg->prepend($fields->get('publish_from')); // prepend publish_until field at the very top
				$fg->save();

		}

		return $this->inContextFieldSettings();

	}	

	/**
	* 	Add some in-context field settings.
	*
	*	@access private
	*
	*/	
	private function inContextFieldSettings() {

		// 6. ###### Set some in-context field widths, labels and descriptions for some templates #####
		$pages = $this->wire('pages');
		$templates = $this->wire('templates');
		$fields = $this->wire('fields');
		

		// Labels and Descriptions: in-context values of some fields in some templates
		// $v[0]=template; $v[1]=field; $v[2]=label; $v[3]=description
		$templatesArray = array(
							
							array('blog-settings', 'blog_headline', 'Blog Title', 'You can use this (e.g. in the masthead) as a title for your blog.'),// 0
							array('blog-settings', 'blog_summary', 'Blog Tagline', 'An optional sentence or two of text that you can use as your blog\'s tagline.'),// 1
							array('blog-settings', 'blog_note', 'Footer', 'You can use this for footer messages (e.g. copyright notice).'),// 2
							array('blog-settings', 'blog_quantity', 'Quantity of posts to show on Blog homepage'),// 3
						
							array('blog-post', 'publish_from', 'Auto-publish from'),// 4
							array('blog-post', 'publish_until', 'Auto-unpublish on'),// 5
							array('blog-post', 'blog_comments_view', '', 'Comments are visible by default. This setting overrides the global setting.'),// 6
							
							// comments + widget pages. unset comments ones if commentsUse!=1
							array('blog-comments', 'blog_quantity', 'Comments per page in Comments page'),// 7
							array('blog-comments', 'blog_comments_view', '', 'Comments are visible by default. Individual post\'s setting overrides what you specify here.'),// 8
							array('blog-recent-comments', 'blog_quantity', 'Total Comments to show in widget'),// 9
							array('blog-recent-posts', 'blog_quantity', 'Total Posts to show in widget'),// 10
							array('blog-recent-tweets', 'blog_note', 'Twitter Screen Name'),// 11
							array('blog-recent-tweets', 'blog_quantity', 'Total Tweets to show in widget'),// 12
							
							// repeater 
							array('repeater_blog-links', 'blog_headline', 'Website Title'),// 13
							array('repeater_blog-links', 'blog_href', 'Website URL'),// 14
		);
		
		// remove blog-post template' in context field setting for field 'blog_comments_view' if $commentsUse!=1
		if ($this->commentsUse != 1) {
				unset($templatesArray[6]);// unset blog-post: blog_comments_view
				unset($templatesArray[7]);// unset blog-comments: blog_quantity
				unset($templatesArray[8]);// unset blog-comments: blog_comments_view
				unset($templatesArray[9]);// unset blog-recent-comments: blog_quantity
		}

		// remove blog-post template' in context field settings for field 'publish_from' and 'publish_until' if $this->schedulePages !=1
		if ($this->schedulePages != 1) {
				unset($templatesArray[4]);// unset blog-post: publish_from
				unset($templatesArray[5]);// unset blog-post: publish_until
		}

		foreach ($templatesArray as $v) {

				$t = $templates->get($v[0]);
				$f = $t->fieldgroup->getField($v[1], true);
				if (!empty($v[2])) $f->label = $v[2];
				if (isset($v[3])) $f->description = $v[3];

				// for the repeater template and blog-post template we also set some field widths
				if ($v[0] == 'repeater_blog-links') $f->columnWidth = 50;// 50%
				if ($v[1] == 'publish_from' || $v[1] == 'publish_until') {

						$f->columnWidth = 50;// 50%
						$f->datepicker = 3;// Date/Time picker on field focus
						$f->collapsed = Inputfield::collapsedYes;

				}
						
				/*
				For the repeater template, we could do some extra tasks such as create the repeater page in the admin. 
				But PW will do that automatically if either the repeater field is accessed in setup or
				a page using the repeater field is added or edited
				*/
				
				$fields->saveFieldgroupContext($f, $t->fieldgroup);// save settings in context
		}

		// Labels only: only for 'blog_summary' field for widgets templates
		$templatesArray = array('blog-links', 'blog-recent-comments', 'blog-recent-posts', 'blog-recent-tweets', 'blog-widget-basic');

		foreach ($templatesArray as $tpl) {

				if ($this->commentsUse !=1 && $tpl == 'blog-recent-comments') continue;
			
				$t = $templates->get($tpl);
				$f = $t->fieldgroup->getField('blog_summary', true);// get field in-context
				$f->label = 'Widget Description';
				$fields->saveFieldgroupContext($f, $t->fieldgroup);// save the in context label
		
		}

		// For the repeater, we need to add the fields for the repeater. We can only do it after those fields have been created and saved (above).
		// But we need to first create the repeater page (the 'for-field-id' page)

		$adminRoot = $this->wire('config')->adminRootPageID;
		
		// get the repeaters page in admin [will only work if repeater module is installed!]
		
		$repeaterPage = new Page();

		// $repeaterPage->template = $templates->get(2);// admin template

		$repeaterPage->template = $pages->get($adminRoot)->template->id;// admin template
		$repeaterPage->parent =  $pages->get("name=repeaters, parent_id=$adminRoot");// the "repeaters" page in Admin (parent of all repeaters)
		$repeaterPage->title = 'Blog Links';
		$name = "for-field-" . $fields->get('blog_links')->id;// we'll need this later in a selector so we save to a variable
		$repeaterPage->name = $name;
		$repeaterPage->save();
				
		// get the repeater field to add to our repeater page
		$f = $fields->get('blog_links');
		$f->parent_id = $pages->get("name=$name")->id;// in db stored in 'fields' within the data for the 'FieldtypeRepeater'
		$f->template_id = $templates->get('repeater_blog-links')->id;
		$f->repeaterReadyItems = 3;

		// We need the IDs of the fields to add to the repeater [adding as field objects didn't work]
		$repeaterFlds = array('blog_headline', 'blog_href');
		foreach ($repeaterFlds as $repeaterFld) {
					$repeaterFieldsIDs[] = $fields->get($repeaterFld)->id;	
		}
		
		// add fields to the repeater page
		$f->repeaterFields =  $repeaterFieldsIDs;// array of field IDs to add to repeater
		$f->save();

		// We need to add some fields to 'user' template [for the Blog Authors] + sort fields + some custom labels for some fields

		// $k=field name => $v=label
		$userFieldsExtra = array(

								'title' => 'Display name (first and last name)',
								'blog_images' => '',// no label to add
								'blog_body' => 'Biography',
		);

		$t = $templates->get('user');
		$fg = $t->fieldgroup;
		
		// first, we add the extra fields we need
		foreach ($userFieldsExtra as $k => $v) {			
					// if it is the title field, we prepend it (at the top) (it goes before 'pass'); 
					if ($k == 'title') {$fg->prepend($fields->get($k));}				
					else {$fg->add($fields->get($k));}// images and body fields get added to the bottom					
		}
		
		$fg->save();
		
		// add the in context labels
		foreach ($userFieldsExtra as $k => $v) {
					$f = $t->fieldgroup->getField($k, true);
					if (!empty($v)) $f->label = $v;
					$fields->saveFieldgroupContext($f, $t->fieldgroup);// save in context settings

		}

		return $this->createPages();

	}

	/**
	* 	Create blog pages.
	*
	*	Number of pages created varies depending on selected blog style.
	*
	*	@access private
	*
	*/	
	private function createPages() {

		// 7. ###### Create needed parent pages and some example child pages (total pages vary depending on blogStyle selected) ######
		// array of pages to add
		// first create 'blog' page if applicable; then other parent pages; then child pages

		$parents = $this->parents;
		$blogPagesIDs = array();
		$pages = $this->wire('pages');
		$templates = $this->wire('templates');
				
		$parent = $pages->get('/');// if $this->blogStyle == 3 || $this->blogStyle == 4 parent is 'root'
		
		// the 'Blog' page
		if ($this->blogStyle == 1 || $this->blogStyle == 2) {

				$p = new Page();
				$p->template = $templates->get('blog');
				$p->parent = $parent;
				$p->title = $parents['blog'];// set by user or defaults to 'Blog' - see verifyInstall();
				$p->save();
				
				// save the ID of the 'blog' page for later saving to module config
				$blogPagesIDs['blog'] = $p->id;

				// unset $parents['blog'] since we no longer need it here. For blogStyle == 3 || blogStyle == 4 we already unset it earlier in verifyInstall()
				unset($parents['blog']);

				$parent = $pages->get($blogPagesIDs['blog']);// if $this->blogStyle == 1 || $this->blogStyle == 2 parent is 'blog'


		}

		// The remaining parent pages. Their parent here will be the above created blog page
		$parentPages = $parents;

		// create the remaining parent pages (i.e. minus 'blog' which we created before)

		// now $k = template and $v = title; $k will also refer to identfier in $blogPagesIDs
		foreach ($parentPages as $k => $v) {
			
				$p = new Page();
				$p->template = $templates->get($k);
				$p->parent = $parent;
				$p->title = $v;
				if ($k == 'blog-widgets' || $k == 'blog-settings') $p->addStatus(Page::statusHidden);// hidden page - using $k since $v will vary

				// for 'Example Post' page, we need to add the demo date properly. Otherwise, normally, the date field will be populated when post published
				if ($k == 'blog-post') $p->blog_date = date('j F Y g:i a');// e.g '8 April 2012 11:15 am'				
				$p->save();

				/*	we grab the id of each top page for later saving to module config
					e.g. $blogPagesIDs['blog-settings'] will store ID of 'Settings' page
					but we skip 'Example Post' {available if blogStyle = 2} since not needed
				*/
				if ($k!= 'blog-post') $blogPagesIDs[$k] = $p->id;// no ID to be saved if 'Example Post'
		}

		// set parent pages IDs of below $childPages
		$posts = $this->blogStyle == 1 || $this->blogStyle == 3 ? $blogPagesIDs['blog-posts'] : '';
		$categories = $blogPagesIDs['blog-categories'];
		$tags = $blogPagesIDs['blog-tags'];
		$comments = $this->commentsUse == 1 ? $blogPagesIDs['blog-comments'] : '';
		$widgets = $blogPagesIDs['blog-widgets'];

		// $k=for parentsID [where applicable] => $v[0]=title; $v[1]=template; $v[2]=parent page ID
		$childPages = array(

							'Example Post' =>  array('Example Post', 'blog-post', $posts),// if blogStyle = 2 or 4, already created! so unset below
							'Example Category' =>  array('Example Category', 'blog-category', $categories),
							'Example Tag' =>  array('Example Tag', 'blog-tag', $tags),
							'blog-asc' =>  array('Always Show Comments', 'blog-basic', $comments),
							'blog-dnc' =>  array('Disable New Comments', 'blog-basic', $comments),
							'blog-dc' =>  array('Disable Comments', 'blog-basic', $comments),
							'blog-rposts' =>  array('Recent Posts', 'blog-recent-posts', $widgets),
							'blog-rcomments' =>  array('Recent Comments', 'blog-recent-comments', $widgets),
							'blog-broll' =>  array('Blogroll', 'blog-links', $widgets),
							'blog-tweets' =>  array('Recent Tweets', 'blog-recent-tweets', $widgets),
							'blog-pauthor' =>  array('Post Author', 'blog-widget-basic', $widgets),
		);


		// unset 'Example Post' if blogSyle = 2 || blogSyle = 4 since already created earlier as 'parent page'
		if ($this->blogStyle == 2 || $this->blogStyle == 4) {unset($childPages['Example Post']);}

		// unset comments-related child pages if commenting feature is off, i.e. if commentsUse !=1
		if ($this->commentsUse !=1) {

				unset($childPages['blog-asc']);
				unset($childPages['blog-dnc']);
				unset($childPages['blog-dc']);
				unset($childPages['blog-rcomments']);

		}

		// create the child pages: // $k=for parentsID [where applicable] => $v[0]=title; $v[1]=template; $v[2]=parent page (OBJECT)
		foreach ($childPages as $k => $v) {
					
					$p = new Page();
					$p->template = $templates->get($v[1]);
					$p->parent = $pages->get($v[2]);// get parent
					$p->title = $v[0];
					// for 'Example Post' page, we need to add the demo date properly. Otherwise, normally, the date field will be populated when post published
					if($k == 'Example Post') $p->blog_date = date('j F Y g:i a');// e.g '8 April 2012 11:15 am'
					$p->save();

					/*	we grab the id of each child page for later saving to module config
					e.g. $blogPagesIDs['blog-rcomments'] will store ID of 'Recent Comments' page
					but we skip 'Example Post' {available if blogStyle = 1 OR 3}, 'Example Category' and 'Example Tag' since not needed and can be deleted by user
					*/
					if (!in_array($k, array('Example Post', 'Example Category', 'Example Tag'))) $blogPagesIDs[$k] = $p->id;
		}

		
		// we save this to a class property. We'll use this later to save to module config
		$this->blogPagesIDs = $blogPagesIDs;

		return $this->extraPageSettings();

	}

	/**
	* 	Add some extra settings for some pages.
	*
	*	@access private
	*
	*/	
	private function extraPageSettings() {

		// 8. #### additional settings/values for some page properties and field values ####
		$blogPagesIDs = array();
		$blogPagesIDs = $this->blogPagesIDs;
		$pages = $this->wire('pages');
		$templates = $this->wire('templates');
		$fields = $this->wire('fields');
		

		// set some blog pages IDs for use in adding some extras to their fields
		$posts = $this->blogStyle == 1 || $this->blogStyle == 3 ? $blogPagesIDs['blog-posts'] : '';
		$categories = $blogPagesIDs['blog-categories'];
		$tags = $blogPagesIDs['blog-tags'];
		$settings = $blogPagesIDs['blog-settings'];
		$comments = $this->commentsUse == 1 ? $blogPagesIDs['blog-comments'] : '';// only if comments feature available
		$rcomments = $this->commentsUse == 1 ? $blogPagesIDs['blog-rcomments'] : '';// ditto
		$rposts = $blogPagesIDs['blog-rposts'];
		$tweets = $blogPagesIDs['blog-tweets'];
		$broll = $blogPagesIDs['blog-broll'];
		$pauthor = $blogPagesIDs['blog-pauthor'];	

		// $v[0]=page ID; $v[1]=field/property; $v[2]=field/property value
		$pagesExtras = array(
								// sortfields
								array($posts, 'sortfield', '-blog_date'),// for 'posts', sort its children by date, descending// 0
								array($categories, 'sortfield','title'),// 1
								array($tags, 'sortfield', 'title'),// 2

								array($settings, 'blog_headline', 'My Awesome Blog (optional)'),// dummy text for Blog Title// 3
								array($settings, 'blog_summary', 'This is a blog about this and that (optional tagline)'),// dummy text// 4
								array($settings, 'blog_quantity', 3),// 5

								// comments page + widget pages: some info values
								array($comments, 'blog_headline', 'Recent Comments'),// 6
								array($comments, 'blog_quantity', 10),// some initial value// 7
								array($rcomments, 'blog_summary', 'Shows a limited number of the most recent comments in a list. Set this number in General Settings.'),// 8
								array($rcomments, 'blog_quantity', 3),// 9
								
								array($rposts, 'blog_summary', 'Shows a limited number of your most recent posts in a list. Set this number in General Settings.'),// 10
								array($rposts, 'blog_quantity', 3),// 11
								array($tweets, 'blog_summary', 'Shows a limited list of your most recent tweets. Set this number in General Settings.'),// 12
								array($tweets, 'blog_quantity', 3),// 13
								array($broll, 'blog_summary', 'Shows links to other blogs that you like.'),// 14
								array($pauthor, 'blog_summary', 'Renders Post\'s author biography.'),// 15

		);

		// unset $posts page if blogStyle == 2 || blogStyle == 4 since no 'posts page', hence no children
		if ($this->blogStyle == 2 || $this->blogStyle == 4) {unset($pagesExtras[0]);}
		
		// unset comments pages if commentsUse !=1
		if ($this->commentsUse !=1) {

				unset($pagesExtras[6]);
				unset($pagesExtras[7]);
				unset($pagesExtras[8]);
				unset($pagesExtras[9]);

		}

		// $v[0]=page ID; $v[1]=field/property; $v[2]=field/property value
		foreach ($pagesExtras as $v) {

				$p = $pages->get($v[0]);
				$p->$v[1] = $v[2];// set the field name $v[1] to have the value $v[2]
				// $p->set($v[1], $v[2]);// alternative syntax to above
				$p->save();

		}

		// additional settings for the two Page fields created earlier [blog_categories,blog_tags & blog_comments_view]
		// these can only be done here since we first need blog templates, fields and pages created and saved!
		// we are setting: selectable parent, selectable templates, create new, AsmSelect, PageAutomplete, etc
		
		// blog_categories
		$f = $fields->get('blog_categories');// 
		#$f->parent_id = $pages->get('name=categories, parent.name=blog')->id;// parent of pages that are selectable: 'categories'
		$f->parent_id = $pages->get($categories)->id;// parent of pages that are selectable: 'categories'
		$f->template_id = $templates->get('blog-category')->id;// template of pages that are selectable: 'category'
		$f->labelFieldName = 'title';
		$f->addable = 1;// allow new pages to be created from field
		$f->derefAsPage = 0;// multiple pages (PageArray) - i.e. FieldtypePage::derefAsPageArray;
		// $f->derefAsPage = FieldtypePage::derefAsPageArray;
		$f->inputfield = 'InputfieldAsmSelect';
		$f->save();

		// blog_tags
		$f = $fields->get('blog_tags');// 
		#$f->parent_id = $pages->get('name=tags, parent.name=blog')->id;// parent of pages that are selectable: 'tags'
		$f->parent_id = $pages->get($tags)->id;// parent of pages that are selectable: 'tags'
		$f->template_id = $templates->get('blog-tag')->id;// template of pages that are selectable: 'tag'
		$f->addable = 1;// allow new pages to be created from field
		$f->derefAsPage = 0;// multiple pages (PageArray) - i.e. FieldtypePage::derefAsPageArray;
		// $f->derefAsPage = FieldtypePage::derefAsPageArray;
		$f->labelFieldName = 'title';
		$f->operator = "%=";
		$f->searchFields = 'title';
		$f->inputfield = 'InputfieldPageAutocomplete';
		$f->save();	

		// blog_comments_view
		if ($this->commentsUse == 1) {
					
					$path = $pages->get($comments)->path;
					$f = $fields->get('blog_comments_view');// 
					$f->derefAsPage = 1;// single page (PageArray) or boolean false when none selected - i.e. FieldtypePage::derefAsPageOrFalse
					// $f->derefAsPage = FieldtypePage::derefAsPageOrFalse;
					$f->findPagesCode = 'return $page->path == "' . $path . '" ? $pages->get(' . $comments . ')->children("id!=' . $blogPagesIDs["blog-asc"] . '") : $pages->get(' . $comments . ')->children();';
					$f->labelFieldName = 'title';
					$f->inputfield = 'InputfieldSelect';
					$f->save();
		}

		return $this->createTemplateFiles();

	}		
	
	/**
	* 	Optionally create some template files.
	*
	*	Optionally add blank OR demo template files as per user selection.
	*
	*	@access private
	*
	*/	
	private function createTemplateFiles() {

		// 9. ###### Copy the 'template files' for Blog templates that need them as requested by user.
		// the demo template files contain example MarkupBlog code for various aspects of the blog, e.g. Show 'Tags', 'Posts', etc.
		// the blank template files contain only php opening tags.
		$config = $this->wire('config');

		// array of template files we want to copy to /site/templates/ (we only copy files if they DO NOT exist at destination!!!)
		$templateFiles = array(

								'blog' =>'blog.php',
								'archives' => 'blog-archives.php',
								'authors' => 'blog-authors.php',
								'categories' => 'blog-categories.php',
								'category' => 'blog-category.php',
								'comments' => 'blog-comments.php',
								'broll' => 'blog-links.php',
								'posts' => 'blog-posts.php',
								'post' => 'blog-post.php',
								'rcomments' => 'blog-recent-comments.php',
								'rposts' => 'blog-recent-posts.php', 
								'tweets' => 'blog-recent-tweets.php', 
								'tags' => 'blog-tags.php', 
								'tag' => 'blog-tag.php', 
								'side-bar-inc' => 'blog-side-bar.inc',
								'main-inc' => 'blog-main.inc',
		);
		
		// remove irrelevant template files depending on blogStyle
		if ($this->blogStyle == 2 || $this->blogStyle == 4) unset($templateFiles['posts']);
		if ($this->blogStyle == 3 || $this->blogStyle == 4) unset($templateFiles['blog']);

		// unset comments-related template files if $this->commentsUse !=1
		if ($this->commentsUse !=1) {
				
				unset($templateFiles['comments']);
				unset($templateFiles['rcomments']);
		}

		// blank template files [default]
		if ($this->templateFilesInstall == 1) {

					// copy to destination, then rename blank.txt to 'name-of-template.php' for each $templateFiles
					$sourcepath = dirname(__FILE__) . '/template-files/';// source of the template files to copy over [this is 'ProcessBlog/template-files/']					
					$destinationpath = $config->paths->templates;// destination: '/site/templates/'
					$blankFile = $sourcepath . 'blank.txt';

					foreach ($templateFiles as $k => $v) {

								if($k == 'side-bar-inc' || $k == 'main-inc') continue;// no need for a blank of these demo files

								$blankTemplateFile = $v;

								if(is_file($destinationpath . $blankTemplateFile)) continue;// if a file with the same name already exists, skip to next file. We don't want to overwrite users files!	
								copy($blankFile, $destinationpath . $blankTemplateFile);// copy only those files that do not yet exist at destination.

					}

		}
		
		// demo template files
		elseif ($this->templateFilesInstall == 2) {
					
					// source of the template files to copy over [this is 'ProcessBlog/template-files/']	
					$sourcepath = dirname(__FILE__) . '/template-files/';				
					$destinationpath = $config->paths->templates;// destination: '/site/templates/'

					foreach ($templateFiles as $k => $templateFile) {
								// if a file with the same name already exists, skip to next file. We don't want to overwrite users files!	
								if(is_file($destinationpath . $templateFile)) continue;
								// copy only those files that do not yet exist at destination
								copy($sourcepath . $templateFile, $destinationpath . $templateFile);
					}					

					// if also installing demo blog JS file
					if($this->demoBlogScriptsInstall) {
						$jsFile = 'blog.js';
						// source of the demo blog JS files to copy over [this is 'ProcessBlog/scripts/']	
						$sourcepath = dirname(__FILE__) . '/scripts/';
						$destinationpath = $config->paths->templates . 'scripts/';// destination: '/site/templates/scripts/'
						if(!is_dir($destinationpath)) mkdir($destinationpath, 0755);// make the directory if it doesn't exist
						// if a file with the same name already exists don't copy ours
						if(!is_file($destinationpath . $jsFile)) copy($sourcepath . $jsFile, $destinationpath . $jsFile);
					}
			
					// if also installing demo blog CSS files
					if($this->demoBlogCSSInstall) {
						// source of the demo blog CSS files to copy over [this is 'ProcessBlog/css/']	
						$sourcepath = dirname(__FILE__) . '/css/';
						$destinationpath = $config->paths->templates . 'css/';// destination: '/site/templates/css/'
						if(!is_dir($destinationpath)) mkdir($destinationpath, 0755);// make the directory if it doesn't exist
						$cssFiles = array('blog.css', 'pocketgrid.css');						

						foreach ($cssFiles as $cssFile) {
							if(is_file($destinationpath . $cssFile)) continue;// skip if file already exists							
							copy($sourcepath . $cssFile, $destinationpath . $cssFile);
						}

						// source of the demo blog CSS files ICONS to copy over [this is 'ProcessBlog/css/images/']	
						$sourcepath = dirname(__FILE__) . '/css/images/';
						$destinationpath = $config->paths->templates . 'css/images/';// destination: '/site/templates/css/images/'
						if(!is_dir($destinationpath)) mkdir($destinationpath, 0755);// make the directory if it doesn't exist				
						$iconFiles = array('rss-black.png', 'rss-blue.png');

						foreach ($iconFiles as $iconFile) {
							if(is_file($destinationpath . $iconFile)) continue;// skip if file already exists
							copy($sourcepath . $iconFile, $destinationpath . $iconFile);
						}


					}

		}// end if installing demo blog files

		return $this->saveModuleConfigs();

	}

	/**
	* 	Save ProcessBlog module configurations data.
	*
	*	The data varies according to the blog style, selection of commenting and schedule pages features.
	*
	*	@access private
	*
	*/	
	private function saveModuleConfigs() {
		
		$modules = $this->wire('modules');

		$data = $modules->getModuleConfigData(get_parent_class($this));

		// merge ProcessBlog config data with MAIN BLOG pages IDs (these pages should not be deleted but can be renamed)
		$finalConfig = array_merge($data, $this->blogPagesIDs);

		// we add blogFullyInstall = 1 to finalConfig
		$finalConfig['blogFullyInstalled'] = 1;		

		// get ProcessBlog class
		$pb = $modules->get(get_parent_class($this));

		// save to ProcessBlog config data
		$modules->saveModuleConfigData($pb, $finalConfig);

		$tf = $this->templateFilesInstall == 1 || $this->templateFilesInstall == 2 ? ' Template Files,' : '';
		
		// if we made it here return success message!
		$this->message("Blog Module Successfully Installed. Fields, Templates," .  $tf . " Pages and a Role created.");
		// redirect to landing page// we want the page to reload so that user can now see blog dashboard
		$this->wire('session')->redirect($this->wire('page')->url);

	}

}