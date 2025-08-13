Honeycomb framework design sub repo
===================================

Honeycomb is the starting point for website builds

Setup a new project
-------------------

Get the developer to create the project repo in Bitbucket

	git clone git@bitbucket.org:fatbeehive/[project-repo].git
	cd to honeycomb subrepo
	run 'make init' (this will pull down dependencies and run gulp)
	change the gulp.js proxy
	run 'gulp dev'

**Gulp tasks**

In the honeycomb repo run 'gulp dev' to watch sass/js/image amends and
open the local server with auto browser reload

**gulp.js**

Change proxy to the address you create in MAMP

	proxy: "your.apache.vhost.url"

Ignoring files

Example below for js that needs overwriting the base js file needs to be
ignored in the gulp task add as below:

	return src([paths.base_js, paths.new_js, `!${dir.src.honeycomb}/js/[file-name].js`])

Setup an existing project
-------------------------

	git clone git@bitbucket.org:fatbeehive/[project-repo].git
	cd to honeycomb subrepo
	run 'make install' (this will pull down dependencies)
	change the gulp.js proxy and add ignores for the js amended in src/js
	run 'gulp dev'

Create the meta images using the Design systems/Meta images template in
FIGMA and create the favicon using a generator then overwrite the images
in the src/images

Change name and theme colour in images/manifest.webmanifest

Overwriting SASS
----------------

Copy the scss file you want to overwrite from the honeycomb directory to
the 'assets/src/sass' folder in the theme

Comment out the base scss file in style.scss and add the new

	@import '../../../assets/src/sass/[folder-name]/[file-name]';

Overriding twig templates
-------------------------

Create a same named twig file in the same sub folder in the overrides
folder

{% extends %} will extend the existing honeycomb file

{% block header %} will override the existing honeycomb block

Putting {{ parent() }} in the block will extend the existing code in the
honeycomb block

Below uses the header.twig as an example

	{% extends '@default/scaffolding/header.twig' %}

	{% block header %}

	{# Override blocks like so: #}
	{% block nav %}

	{# override content here #}

	{% endblock nav %}

	{# Pull in existing code like so: #}
	{% block links %}

	{{ parent() }}

	{% endblock links %}

	{% endblock header %}

Adding twig files
-----------------

To add a new template or component file create the file in the correct
overrides folder, for templates add the directory to the '\$routes =
array' in index.php

	'/new-file' => 'foldername/template-address',

Components
----------

Inline components live inside the 'article' on the page-builder template
below the 'content' div. They include the partial rather than the
component so its not rendered in a 'section' like the full width
components

-   **Pullquote** - Image, blockquote and cite
-   **Media block** - Image with caption

All full width components live inside the {% block components\_content
%} and have a white background by default and the background colour can
be changed to a light background with the section\_class 'light' and a
dark background with the section\_class 'dark'

Full width component also includes the section partial where you can add
a section\_class, title, summary and link

-   **Hero** - Large image with title and summary
-   **WYSIWYG** - full wysiwyg controls for the client along with
	blockquote and media inline blocks
-   **Media block** - Image with title, summary and button (aligned left
	by default or right when you add the section\_class 'right')
-   **Featured promos** - These are built using the cards component, by
	default styled for 3,2 or 1 column add class 'two-col', 'three-col'
	or 'four-col' for locked column amounts
-   **CTA (Call To Action)** - Image, title, summary, button. Add the
	section\_class 'full-width-image' for a full width image and
	'site-width-image' for a site width image
-   **Donate** - Background image with currency, value and description
-   **Accordion** - Title and description(wysiwyg)
-   **Pullquote** - Image, blockquote and cite
-   **Statistics** - Stat value with title and summary
-   **Embed** - A simple layout component that is used to add external
	iFrames

Glossary
--------

1.  **main**: should be present on every page between the global header
	and footer
2.  **article**: written content eg. a basic page or news article
3.  **content**: this class should *only go around a WYSIWYG* as it sets
	basic styles for all text-level elements
4.  **section**: is a grouping of content and should always have a
	header. the class ‘.section’ is full width with top and bottom
	padding and should be used for background colors
5.  **container**: The class ‘.container’ has left and right padding and
	a maximum width of ‘\$page-width’. It works well within a ‘.section’
	to give consistent sizing and padding
6.  **listing**: is the basic listing of content such as news posts,
	more scannable and content heavy than cards
7.  **listing-cards**: is the basic listing of content such as news
	posts using the cards layout
8.  **single**: layout for a news/events post and includes metadata in
	the sidebar
9.  **card**: cards are more visually interesting and content-light than
	a standard listing page
10. **page builder**: has the main wysiwyg and sidenav then the full
	width components below
11. **sidebar**: represents content that is tangentially related to the
	content nearby, such as tags and subnavigation. within the {% block
	page %} the sidebar is optional and if included will trigger a two
	column layout

Adding project repo to demo server:
-----------------------------------

Create folder on demo server that has the same name as the theme folder

**Pull repo into folder:**

	git clone git@bitbucket.org:fatbeehive/[project-name] —recursive

CD to honeycomb and run:

Make install (so gulp processes won’t run)

Wordpress site: [project-name].wp.demo2.fatbeehive.com

Drupal site: [project-name].dru.demo2.fatbeehive.com
