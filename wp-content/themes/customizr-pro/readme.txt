# Customizr Pro v1.1.7
> The pro version of the popular Customizr WordPress theme.

## Copyright
**Customizr Pro** is a WordPress theme designed by Nicolas Guillaume in Nice, France. ([website : Press Customizr](http://presscustomizr.com>)) 
Customizr Pro is distributed under the terms of the [GNU GPL v2.0 or later](http://www.gnu.org/licenses/gpl-3.0.en.html)

## Demo, Documentation, FAQs and Support
* DEMO : http://demo.presscustomizr.com/
* DOCUMENTATION : http://doc.presscustomizr.com/customizr-pro
* FAQs : http://doc.presscustomizr.com/customizr-pro/faq
* SUPPORT : http://presscustomizr.com/support-forums/forum/customizr-pro/
* SNIPPETS : http://presscustomizr.com/code-snippets/
* HOOKS API : http://presscustomizr.com/customizr/hooks-api/

## Changelog
= 1.1.7 May 4th 2015 =
* fix : don't show slider in home when no home slider is set

= 1.1.6 May 3rd 2015 =
* fix : revert private taxonomy not printed. Will be added back after more tests.

= 1.1.5 April 29th 2015 =
* fix : no post thumbnail option was not working for the post grid layout
* added : when the grid customizer is enabled, the user can now set a custom excerpt length without limitations
* added: support for the map method in the array prototype for old ie browsers -ie8
* Fix: use the correct post id when retrieving the grid layout
* improved : jquery.fancybox.js loaded separately when required
* updated : underscore to 1.8.3
* added : helper methods to normalize the front scripts enqueuing args
* updated : name of front enqueue scripts / style callbacks
* Fix: use amatic weight 400 instead of 700, workaround for missing question mark
* Fix: remove reference to the tag, use site-description tag
* Fix: display unknown archive types headings; use if/else statement when retrieving archive headings/classes immediatily return the archive class when asked for and achiFix: amend typo in the last commit
* Fix: disable fade hover links for first level menu items in ie
* Fix: add customize code and fix previous errors
* Fix: add gallery options, remove useless rewrite of gallery code
* Fix: scroll top when no dropdown menu sized to viewport and no back-to-top, don't refer to not existing variable
* Fix: consider both header borders and eventual margins when retrieving its height
* Fix : RTL-ing Pre-Phase : setting the correct direction of arrows
* Fix: disabling global tc_post_metas didn't hide metas
* Fix: cache and use cached common jquery elements
* Fix: don't print private taxonomies in post metas tags
* Fix: display other grid options and jumb to the blog design options in customize
* Fix : grid customizer effect-5 title style
* Add: sensei woothemes addon compatibility
* improved : single options can now be filtered individually with tc_opt_{$option_name}
* Add: optimizepress compatibility
* Add: basic buddypress support (don't show comments in buddypress pages)
* Add: partial nextgen gallery compatibility
* Add: tc-mainwrappers methods for plugin compatibilities
* Updated: class-content-post_navigation.php
changed: method TC___::tc_unset_core_classes set to public
Correcting arrows on tranlated phrases
* Add : Featured Pages php code improvements, new way to filter a single option with "tc_fpc_get_opt_{$option_name}" + some handy filters on featured pages number
* Fix: Featured Pages use stronger selectors for fp spans

= 1.1.4 April 17th 2015 =
* fixed : in the customizer, display back other grid options
* fixed : in the customizer, display back other grid options
* added a filter (boolean, default = true) to disable the footer customizer : 'tc_enable_footer_customizer'

= 1.1.3 April 13th 2015 =
* fixed : Black Studio TinyMCE Plugin issue. Load TC_resource class when tinymce_css callback is fired from the customizer

= 1.1.2 April 11th 2015 =
* added : support for polylang and qtranslate-x
* improved : load only necessary classes depending on the context : admin / front / customize
* changed : class-admin-customize.php and class-admin-meta_boxes.php now loaded from init.php.
* updated site name
* fix: reset navbar-inner padding-right when logo centered
* fix: override bootstrap thumbnails left margin for woocommerce ones
* added : helpers tc_is_plugin_active to avoid the inclusion of wp-admin/includes/plugin.php on front end
* added : new class file dedicated to plugin compatibility class-fire-plugin_compat.php
* updated : copyright dates
* fixed : minor hotcrumble css margin bug fix
* fixed : use the css class instead of h2 and remove duplicate
* fixed : Few corrections in the Italian translation
* fixed : allow customizr slider in the woocommerce shop page
* fixed : collapsed customizer panel
* fixed : gallery - handle the case "link to none" (was previously linked to the attachment page)
* added : sidebar and footer widgets removable placeholders
* added : better customizer options for comments. Allow more controls on comment display. Page comments are disabled by default.
* added : customizer link to ratings
* updated : he_IL.po Hebrew translation
* updated : readme changelog
* fixed : rtl customizer new widths and margins
* fixed : use '===' to compare with '0'.
* fixed : fix logo ratio, apply only when no sticky-logo set
* fixed : avoid plugin's conflicts with the centering slides feature: replace the #customizr-slider's 'slide' class with 'customizr-slide'
* fixed : user defined comments setting for a single page in quick edit mode 
* fixed : pre_get_posts as action instead of filter
* fixed : hook post-metas and headings early actions to wp_head instead of wp
* fixed : minor css issues due to the larger width for the customizer controls
* fixed : infinite loop issue with woocommerce compatibility function
* added : tc-smart-loaded class for img loaded with smartloadjs
* added : make grid font-size also dependant of the current layout
* added : css classes filter in index : tc_article_container_class
* added : grid customizer in pro
* added : skin css class to body
* added : disabled WooCommerce default breadcrumb
* improved : better css grid icons
* changed : themesandco to presscustomizr
* updated : Swedish translation. Thanks to Tommy Wikström.
* updated : genericons to v3.3
* changed : attachment in search results is now disabled by default
* updated : layout css class added to body
* changed : .tc-gc class is now attached to the .article-container element
* changed : golden ratio can be overriden (follows the previous commit about this)
* changed : tc__f ( '__ID' ) replaced by TC_utils::tc_id()
* changed : tc__f( '__screen_layout' ) replaced by TC_utils::tc_get_layout( )
* changed : css classes filter 'tc_main_wrapper_classes' and 'tc_column_content_wrapper_classes' now handled as array
* improved : grid thumb golden ratio can be overriden
* updated : disable live icon rendering in post list titles if grid customizer on
* improved : customizer control panel width
* changed : grid controls priorities
* changed : class .tc-grid-excerpt-content to .tc-g-cont
* improved : larger customizer zone + some titles styling
* improved : get the theme name from TC___::$theme_name in system infos
* changed : split the edit link callback. Separate the view and the boolean check into 2 new public methods
* changed : some priority changes in the customizer controls
* improved : grid font sizes now uses ratios
* added :(fp) support for preview context in tc_get_theme_name()
* updated :(fp) site name and copyright dates
* Fix:(fp) use correct callback for fpc_text filter
* Fix:(fp) final lang plugins compatibility
* Add:(fp) initial lang plugins compatibility
* Fix:(fp) properly loading of textdomain when as addon
* Fix:(fp) recenter on fp block class changed
* Fix:(fp) better definition of js deps in front js enqueing 2
* Fix:(fp) italian translation update
* Fix:(fp) better definition of js deps in front js enqueing
* updated :(fc) added missing customizr skins
* updated :(fc) site name and copyright dates
* Fix :(fc) get the right theme name when previewing a non active theme
* Fix :(fc) properly loading of textdomain when used as Customizr Pro addon
* Fix :(fc) extend first-letter fix to customizr-pro
* Fix :(fc) front js register and enqueue jquery if necessary

= 1.1.1 March 27th 2015 =
* added : grid customizer : new options for titles and post backgrounds

= 1.1.0 March 24th 2015 =
* added : Grid Customizer
* updated to Customizr core v3.3.13

= 1.0.17 March 11th 2015 =
* added : customizer previewer filter for custom skins

= 1.0.16 March 9th 2015 =
* replaced in featured pages get_the_excerpt filter by the_excerpt
* fixed : tc_set_post_list_hooks hooked on wp_head. wp was too early => fixes bbpress compatibility
* improved : tc_user_options_style filter now declared in the classes constructor
* fixed : bbpress issue with single user profiles not showing up ( initially reported here : https://wordpress.org/support/topic/bbpress-problems-with-versions-avove-3217?replies=7#post-6669693 )
* fixed : better insertion of font icons and custom css in the custom inline stylesheet
* fixed : bbpress conflict with the post grid
* fixed : the_content and the_excerpt WP filters missing in post list content model
* fixed : smart load issue when .hentry class is missing (in WooCommerce search results for example)
* added : has-thumb class to the grid > figure element
* added : make the expanded class optional with a filter : tc_grid_add_expanded_class
* added : fade background effect for the excerpt in the no-thumbs grid blocks
* changed : TC_post_list_grid::tc_is_grid_enabled shifted from private to public
* improved : jqueryextLinks.js check if the tc-external element already exists before appending it
* fixed : .tc-grid-icon:before better centering

= 1.0.15 March 4th 2015 =
Fix slider img centering bug

= 1.0.14 March 4th 2015 =
Fix: array dereferencing fix for PHP<5.4.0
Fix: typos in webkit transition/transform properties

= 1.0.13 March 2nd 2015 =
* Upgraded to customizr v3.3.6
* added in Featured Pages a dynamic images centering feature as option

= 1.0.12 February 13th 2015 =
* Upgraded to customizr v3.3.1, safe for child theme users : https://github.com/Nikeo/customizr#changelog

= 1.0.11 February 13th 2015 =
* Upgraded to customizr v3.3.0, safe for child theme users
* Font Customizer : hide controls on load when they are not wrapper in zones, since wp 4.1+
* Feat. Pages Fix : use text-domain prefix for translations files
* Feat. Pages Fix : fp button display nothing if empty
* Feat. Pages Fix : perform tc_setup after_setup_theme when as addon in customizr-pro
* Feat. Pages Fix : temporary fix, don't include woocommerce products in fp
* Feat. Pages Fix : use a more proper filter to which pass the ->post_excerpt
* Feat. Pages Fix : don't retrieve already retrieved post/page object when getting the excerpt

= 1.0.10 January 23rd 2015 =
* Fix parse error due to an anonymous function syntax not supported by old version of php

= 1.0.9 January 22nd 2015 =
* Fix warning when attempting to load Google font in the admin editor

= 1.0.8 January 22nd 2015 =
* Updated to the core Customizr theme v3.2.12

= 1.0.7 December 23rd 2014 =
* Safer fix for the hidden font customizer controls. Cross browser compatible and compatible with  WP version 4.1+
* Updated to the core Customizr theme v3.2.10 : http://presscustomizr.com/whats-new-customizr-theme-v3-2-9/

= 1.0.6 December 21st 2014 =
* follow up of the font customizer controls not showing up. Additional delay added to init the sections

= 1.0.5 December 20th 2014 =
* 18db9f3 fix the api.reflowPaneContents bug in the font customizer

= 1.0.4 December 14th 2014 =
* b1e9173 Fix $logos_img instanciation bug in class TC_header_main#148

= 1.0.3 December 12th 2014 =
* 0402f83 fix customizr pro name to customizr-pro in config.json of FPU

= 1.0.2 December 8th 2014 =
* 9ef1370 FPU addon : fix the comment bubble bug. get_the_title() not used anymore.
* f806c18 pages with comments : enable the comment bubble after the title in headings
* 57ac308 admin css : change help buttons and icon to the new set of colors : #27CDA5 #1B8D71
* 786bbbc expand submenus for tablets in landscape mode
* d4bc5eb add a tc-is-mobile class to the body tag if wp_is_mobile()
* d3bb703 Fix the skin dropdown not closing when clicking outside the dropdown
* 094e0b2 Changed author URL to http://presscustomizr.com/
* c6611bb Merge branch 'eri-trabiccolo-android-menu' into dev
* d94fff6 Fix collapsed menu on android devices
* 605a462 Merge branch 'eri-trabiccolo-fp-edit-link' into dev
* 63c6aa0 Featured Pages: fix edit link
* 8e24584 Merge branch 'eri-trabiccolo-parent-menu-item' into dev
* 708b7b1 Merge branch 'eri-trabiccolo-hammer-issue' into dev
* Fix click on slide's call to action buttons in mobile devs
* 7b31410 Merge branch 'eri-trabiccolo-dev' for the sticky logo into dev
* cb77df3 add the title and notice to TC_Customize_Upload_Control
* 62bce18 add the title rendering for some control types
* 48891bb fix the $default_title_length hard coded value

= 1.0.1 December 8th 2014 =
* dd4bfe6 v1.0.1 grunt build
* 90d292e change gitignore settings
* fd10bbf setup the automatic updates and the activation key page admin
* 4b5df34 addons updates

= 1.0.0 December 6th 2014 =
* Initial release