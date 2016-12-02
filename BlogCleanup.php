<?php

/**
*
* This is an include class for ProcessBlog cleanup($form). It can only be run by supersuser in the blog dashboard
*
*This utility will irreversibly delete the following Blog Components in case user wishes to afterward uninstall OR reinstall Blog:
* 	Fields
*	Templates
*	Optionally Template Files (in case they installed the blank/demo Template Files) + demo CSS and JS files
*	Pages
*	Role
*
* @author Francis Otieno (Kongondo)
* @version 2.4.0
*
* https://github.com/kongondo/Blog
* Created February 2014
*
*/

class BlogCleanup extends ProcessBlog {

	// whether to remove template files.
	private $removeBlogFiles;

	/**
	* 	Prepare cleaning up.
	*
	*	@access public
	*
	*/	
	public function cleanUp($form) {

		$input = $this->wire('input')->post;
		$form->processInput($input);

		$cleanupBtn = $input->cleanup_btn;
		$this->removeBlogFiles = $input->remove_tpl_files;

		// was the right button pressed
		if($cleanupBtn && $cleanupBtn == 'Cleanup') {

			// Get the module config data
			$this->data = $this->wire('modules')->getModuleConfigData(get_parent_class($this));

			$this->blogStyle = $this->data['blogStyle'];// selected blog style (1-4)
			$this->commentsUse = $this->data['commentsUse'];// commenting feature on/off
			$this->templateFilesInstall = $this->data['templateFilesInstall'];

			return $this->cleanUpPages();

		}

	}

	/**
	* 	Delete blog pages.
	*
	*	@access private
	*
	*/
	private function cleanUpPages() {

		$data = $this->data;
		$pages = $this->wire('pages');

		// grab the main blog parent pages IDs where blogStyle == 3. 
		// Similar for if blogStyle == 4 but we'll unset 'blog-posts' in that case
		$pagesArray = array(

						$data['blog-posts'],			
						$data['blog-categories'],
						$data['blog-tags'],
						$data['blog-comments'],
						$data['blog-widgets'],
						$data['blog-authors'],
						$data['blog-archives'],
						$data['blog-settings'],

		);

		// if blogStyle == 1 or blogStyle == 2: 'blog' is main parent of all blog pages
		if ($this->blogStyle == 1 || $this->blogStyle == 2) {			
				$p = $pages->get($data['blog']);
				// we only proceed if we found the page /blog/
				// recursively delete the blog page - i.e., including its children
				if ($p->id)	$pages->delete($p, true);
		}

		// if blogStyle == 3: root is the main parent of all blog pages
		elseif ($this->blogStyle == 3) {			
				foreach ($pagesArray as $pageName) {					
						$p = $pages->get($pageName);
						// recursively delete the blog pages - i.e., including their children
						if ($p->id) $pages->delete($p, true);
				}
		}

		// if blogStyle == 4: root is the main parent of all blog pages but 'blog-post' pages live directly under root
		elseif ($this->blogStyle == 4) {			
				// remove 'blog-posts' since that page does not exist in this case
				unset($pagesArray[0]);
				foreach ($pagesArray as $pageName) {					
							$p = $pages->get($pageName);
							// recursively delete the blog pages - i.e., including their children
							if ($p->id) $pages->delete($p, true);
				}

				// additionally in this case since parent of each blog post is root, 
				// we delete all pages using the template 'blog-post'
				foreach ($pages->find('template=blog-post, include=all') as $p) {if ($p->id) $pages->delete($p);}			
		}

		return $this->cleanUpRepeater();

	}

	/**
	* 	Delete repeater page.
	*
	*	@access private
	*
	*/
	private function cleanUpRepeater() {
		$pages = $this->wire('pages');
		// we delete our repeater page: admin/repeaters/for-field-xxx		
		$repeaterID = $this->wire('fields')->get('blog_links')->id;
		$repeaterPage = $pages->get("parent.name=repeaters, name=for-field-$repeaterID");// making sure we are getting the right page
		if($repeaterPage->id) $pages->delete($repeaterPage, true);
		return $this->cleanUpTemplates();
	}

	/**
	* 	Delete blog templates.
	*
	*	@access private
	*
	*/
	private function cleanUpTemplates() {

		$templates = $this->wire('templates');

		$templatesArray = array(
							'blog',
							'blog-archives',
							'blog-authors',
							'blog-categories',
							'blog-category',
							'blog-comments',
							'blog-links',
							'blog-post',
							'blog-posts',
							'blog-recent-comments',
							'blog-recent-posts',
							'blog-recent-tweets',
							'blog-tag',
							'blog-tags',
							'blog-widgets',
							'blog-widget-basic',
							'blog-settings',
							'blog-basic',
							'repeater_blog-links',

		);
		
		// unset irrelevant templates depending on blogStyle (important since user could have a template with similar name that is not part of Blog!) AND commentsUse
		if ($this->blogStyle == 3 || $this->blogStyle == 4)	unset($templatesArray[0]);// blog template
		if ($this->blogStyle == 2 || $this->blogStyle == 4)	unset($templatesArray[8]);// blog-posts template
		if ($this->commentsUse !=1) {
					unset($templatesArray[5]);// blog-comments
					unset($templatesArray[9]);// blog-recent-comments
					unset($templatesArray[17]);// blog-basic
		}	

		// delete each found template one by one
		foreach ($templatesArray as $tpl) {
					$t = $templates->get($tpl);
					if ($t->id) {						
							// two step process to delete system template (repeater_blog-links)
							if ($t->flags == 8) {
									$t->flags = Template::flagSystemOverride;
									$t->flags = 0;
									$templates->delete($t);
							}

							$templates->delete($t);
							$this->wire('fieldgroups')->delete($t->fieldgroup);// delete the associated fieldgroups
					}					
		}

		return $this->cleanUpFields();

	}

	/**
	* 	Delete blog fields.
	*
	*	@access private
	*
	*/
	private function cleanUpFields() {

		$templates = $this->wire('templates');
		$fields = $this->wire('fields');

		// remove some fields from 'user' template before deleting them. The fields were added by ProcessBlog during install
		$t = $templates->get('user');
		$fg = $t->fieldgroup;
		$fg->remove($fields->get('blog_body'));
		$fg->remove($fields->get('blog_images'));

		// we also remove title since we added it BUT WE WONT BE DELETING IT
		$fg->remove($fields->get('title'));		
		$fg->save();

		// array of blog fields. We'll use this to delete each, one by one as applicable
		$fieldsArray = array(

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
						'blog_small',
		);

		// unset irrelevant fields depending on commentsUse (important since user could have a field with similar name that is not part of Blog!)
		if ($this->commentsUse !=1) {
					unset($fieldsArray[2]);// blog_comments
					unset($fieldsArray[3]);// blog_comments_view
					unset($fieldsArray[4]);// blog_comments_max
		}
		
		// delete each found field
		foreach ($fieldsArray as $fld) {
					$f = $fields->get($fld);
					if($f->id) $fields->delete($f);		
		}

		return $this->cleanUpRoles();

	}

	/**
	* 	Delete blog role.
	*
	*	@access private
	*
	*/
	private function cleanUpRoles() {
		// delete 'blog-author' role
		$r = $this->wire('roles')->get('blog-author');
		$r->delete();
		return $this->cleanUpFiles();
	}

	/**
	* 	Delete blog template files, demo CSS and JS files (optional).
	*
	*	@access private
	*
	*/
	private function cleanUpFiles() {

		$this->deleteFiles = false;
		$config = $this->wire('config');

		// if user has chosen to also delete template files AND these were installed (blank or demo) as well as the demo JS and CSS files + images
		if (($this->removeBlogFiles && $this->templateFilesInstall == 1) || ($this->removeBlogFiles && $this->templateFilesInstall == 2)) {
		
				$this->deleteFiles = true;

				$templateFiles = array(
										// template files
										'blog.php',
										'blog-archives.php',
										'blog-authors.php',
										'blog-categories.php',
										'blog-category.php',
										'blog-comments.php',
										'blog-links.php',
										'blog-post.php',
										'blog-posts.php',
										'blog-recent-comments.php',
										'blog-recent-posts.php',
										'blog-recent-tweets.php',
										'blog-side-bar.inc',// will only be present if templateFilesInstall == 2 {demo template files}
										'blog-tag.php',
										'blog-tags.php',
										'blog-main.inc',// will only be present if templateFilesInstall == 2 {demo template files}

				);
				
				// remove non-existent template files based on the blogStyle, commentsUse and templateFilesInstall
				// also safeguards againts removing user created template files with similar names!
				if ($this->blogStyle == 2 || $this->blogStyle == 4) unset($templateFiles[8]);// blog-posts.php
				if ($this->blogStyle == 3 || $this->blogStyle == 4) unset($templateFiles[0]);// blog.php					
				if ($this->templateFilesInstall !=2) {
						unset($templateFiles[12]);// blog-side-bar.inc
						unset($templateFiles[15]);// blog-main.inc
				}

				if ($this->commentsUse !=1) {
							unset($templateFiles[5]);// blog-comments.php
							unset($templateFiles[9]);// blog-recent-comments.php
				}				

				// 1. delete template files
				$sourcepath = $config->paths->templates;// source: '/site/templates/'
				foreach ($templateFiles as $templateFile) {						
						if(is_file($sourcepath . $templateFile)) unlink($sourcepath . $templateFile);// delete the file if found
				}

				// 2. delete demo JS file
				$sourcepath = $config->paths->templates . 'scripts/';// source: '/site/templates/scripts/'
				if(is_file($sourcepath . 'blog.js')) unlink($sourcepath . 'blog.js');// delete the file if found

				// 3. delete demo CSS files
				$sourcepath = $config->paths->templates . 'css/';// source: '/site/templates/scripts/'
				$cssFiles = array('blog.css', 'pocketgrid.css');				
				foreach ($cssFiles as $cssFile) {
					if(is_file($sourcepath . $cssFile)) unlink($sourcepath . $cssFile);// delete the file if found					
				}

				// 4. delete demo CSS icon files
				$sourcepath = $config->paths->templates . 'css/images/';// source: '/site/templates/scripts/'
				$iconFiles = array('rss-black.png', 'rss-blue.png');				
				foreach ($iconFiles as $iconFile) {
					if(is_file($sourcepath . $iconFile)) unlink($sourcepath . $iconFile);// delete the file if found					
				}
				

		}

		return $this->saveModuleConfigs();

	}

	/**
	* 	Reset ProcessBlog module configurations.
	*
	*	@access private
	*
	*/
	private function saveModuleConfigs() {

		$modules = $this->wire('modules');

		// reset to original/default state ProcessBlog configs!
		$reset = parent::configDefaults();

		// get ProcessBlog class
		$pb = $modules->get(get_parent_class($this));

		// save to ProcessBlog config data (reset)
		$modules->saveModuleConfigData($pb, $reset);

		// true if template files were deleted = only true if checkbox was selected
		$files = $this->deleteFiles == true ? ' Files (Template Files, CSS and JS),' : '';

		// if we made it here return success message!
		$this->message("Blog Components successfully removed. Fields, Templates," .  $files . " Pages and a Role deleted.");
		// redirect to landing page// we want the page to reload so that user can now see blog first execute screen
		// they'll get an error that they must first configure blogStyle in module config
		$this->wire('session')->redirect($this->wire('page')->url);

	}

}