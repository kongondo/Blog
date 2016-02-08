<?php

/**
*
* This is an include class for ProcessBlog cleanup($form). It can only be run by supersuser in the blog dashboard
*
*This utility will irreversibly delete the following Blog Components in case user wishes to afterward uninstall OR reinstall Blog:
* 	Fields
*	Templates
*	Optionally Template Files (in case they installed the blank/demo Template Files)
*	Pages
*	Role
*
* @author Francis Otieno (Kongondo)
* @version 2.3.7
*
* https://github.com/kongondo/Blog
* Created February 2014
*
*/

class BlogCleanup extends ProcessBlog {

	// whether to remove template files.
	private $removeTplFiles;

	/**
	* 	Prepare cleaning up.
	*
	*	@access public
	*
	*/	
	public function cleanUp($form) {

			$form->processInput($this->input->post);

			$cleanupBtn = $this->input->post->cleanup_btn;
			$this->removeTplFiles = $this->input->post->remove_tpl_files;

			// was the right button pressed
			if($cleanupBtn && $cleanupBtn == 'Cleanup') {

				// Get the module config data
				$this->data = wire('modules')->getModuleConfigData(get_parent_class($this));

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

		// grab the main blog parent pages IDs where blogStyle == 3. 
		// Similar for if blogStyle == 4 but we'll unset 'blog-posts' in that case
		$pages = array(

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
			
				$p = wire('pages')->get($data['blog']);
				// we only proceed if we found the page /blog/
				// recursively delete the blog page - i.e., including its children
				if ($p->id)	wire('pages')->delete($p, true);	

		}

		// if blogStyle == 3: root is the main parent of all blog pages
		elseif ($this->blogStyle == 3) {
			
				foreach ($pages as $page) {
					
							$p = wire('pages')->get($page);
							// recursively delete the blog pages - i.e., including their children
							if ($p->id) wire('pages')->delete($p, true);

				}

		}

		// if blogStyle == 4: root is the main parent of all blog pages but 'blog-post' pages live directly under root
		elseif ($this->blogStyle == 4) {
			
				// remove 'blog-posts' since that page does not exist in this case
				unset($pages[0]);

				foreach ($pages as $page) {
					
							$p = wire('pages')->get($page);
							// recursively delete the blog pages - i.e., including their children
							if ($p->id) wire('pages')->delete($p, true);

				}

				// additionally in this case since parent of each blog post is root, 
				// we delete all pages using the template 'blog-post'
				foreach (wire('pages')->find('template=blog-post, include=all') as $p) {if ($p->id) wire('pages')->delete($p);}				
			
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

		// we delete our repeater page: admin/repeaters/for-field-xxx
		$repeaterID = wire('fields')->get('blog_links')->id;
		$repeaterPage = wire('pages')->get("parent.name=repeaters, name=for-field-$repeaterID");// making sure we are getting the right page
		if($repeaterPage->id) wire('pages')->delete($repeaterPage);

		return $this->cleanUpTemplates();

	}

	/**
	* 	Delete blog templates.
	*
	*	@access private
	*
	*/
	private function cleanUpTemplates() {

		$templates = array(
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
		if ($this->blogStyle == 3 || $this->blogStyle == 4)	unset($templates[0]);// blog template
		if ($this->blogStyle == 2 || $this->blogStyle == 4)	unset($templates[8]);// blog-posts template
		if ($this->commentsUse !=1) {
					unset($templates[5]);// blog-comments
					unset($templates[9]);// blog-recent-comments
					unset($templates[17]);// blog-basic
		}	

		// delete each found template one by one
		foreach ($templates as $template) {

					$t = wire('templates')->get($template);

					if ($t->id) {
						
							// two step process to delete system template (repeater_blog-links)
							if ($t->flags == 8) {
									$t->flags = Template::flagSystemOverride;
									$t->flags = 0;
									wire('templates')->delete($t);
							}

							wire('templates')->delete($t);
							wire('fieldgroups')->delete($t->fieldgroup);// delete the associated fieldgroups

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

		// remove some fields from 'user' template before deleting them. The fields were added by ProcessBlog during install
		$t = wire('templates')->get('user');
		$fg = $t->fieldgroup;
		$fg->remove(wire('fields')->get('blog_body'));
		$fg->remove(wire('fields')->get('blog_images'));

		// we also remove title since we added it BUT WE WONT BE DELETING IT
		$fg->remove(wire('fields')->get('title'));		
		$fg->save();

		// array of blog fields. We'll use this to delete each, one by one as applicable
		$fields = array(

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
					unset($fields[2]);// blog_comments
					unset($fields[3]);// blog_comments_view
					unset($fields[4]);// blog_comments_max
		}
		
		// delete each found field
		foreach ($fields as $field) {

					$f = wire('fields')->get($field);
					if($f->id) wire('fields')->delete($f);	
		
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
			$r = wire('roles')->get('blog-author');
			$r->delete();

			return $this->cleanUpTemplateFiles();

	}

	/**
	* 	Delete blog template files (optional).
	*
	*	@access private
	*
	*/
	private function cleanUpTemplateFiles() {

		$this->deleteTf = false;

		// if user has chosen to also delete template files AND these were installed (blank or demo)
		if (($this->removeTplFiles && $this->templateFilesInstall == 1) || ($this->removeTplFiles && $this->templateFilesInstall == 2)) {
		
				$this->deleteTf = true;

				$templateFiles = array(
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

				$sourcepath = wire('config')->paths->templates;// source: '/site/templates/'

				foreach ($templateFiles as $tf) {

							if(is_file($sourcepath . $tf)) unlink($tf);// we delete the template file
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

		// reset to original/default state ProcessBlog configs!
		$reset = parent::configDefaults();

		// get ProcessBlog class
		$pb = wire('modules')->get(get_parent_class($this));

		// save to ProcessBlog config data (reset)
		wire('modules')->saveModuleConfigData($pb, $reset);

		// true if template files were deleted = only true if checkbox was selected
		$tf = $this->deleteTf == true ? ' Template Files,' : '';

		// if we made it here return success message!
		$this->message("Blog Components successfully removed. Fields, Templates," .  $tf . " Pages and a Role deleted.");
		// redirect to landing page// we want the page to reload so that user can now see blog first execute screen
		// they'll get an error that they must first configure blogStyle in module config
		$this->session->redirect(wire('page')->url);

	}

}