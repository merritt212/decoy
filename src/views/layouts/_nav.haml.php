-# This patial is populated from a view composer
-$auth = App::make('decoy.auth')
.navbar.navbar-default.navbar-fixed-top(role='navigation')
	.container

		-# Brand and hamburger
		.navbar-header
			%button.navbar-toggle.collapsed(type='button' data-toggle='collapse' data-target='#nav-items')
				%span.sr-only Toggle navigation
				%span.icon-bar
				%span.icon-bar
				%span.icon-bar
			%a.navbar-brand(href=route('decoy'))=Config::get('decoy::site_name')

		-# The actual nav items are only shown if user is logged in
		.collapse.navbar-collapse#nav-items
			-if($auth->check())
				%ul.nav.navbar-nav
					-foreach($pages as $page)

						-if (!empty($page->children))

							-# Buffer the output so that it is only shown if children were added.  There
							-# could be none if they were hidden by permissions rules
							-ob_start()
							-$child_added = false

							-# The pulldown
							%li.dropdown(class=$page->active?'active':null)
								%a.dropdown-toggle(href='#' data-toggle='dropdown')
									!=$page->label
									%span.caret

								-# The options
								%ul.dropdown-menu(role="menu")
									-foreach($page->children as $child)
										-if (!empty($child->divider))
											%li.divider
										-elseif($auth->can('read', $child->url))
											-$child_added = true
											%li(class=$child->active?'active':null)
												%a(href=$child->url)=$child->label

							-# Only show the dropdown if a child was added
							-if ($child_added) 
								-ob_end_flush()
							-else 
								-ob_end_clean()

						-else if($auth->can('read', $page->url))
							%li(class=$page->active?'active':null)
								%a(href=$page->url)=$page->label

				-# The account menu
				%ul.nav.navbar-nav.navbar-right
					%li.dropdown
						
						-# Dropdown menu
						%a.dropdown-toggle(data-toggle='dropdown')
							%span Hi, #{$auth->userName()}
							%img.gravatar(src=$auth->userPhoto())
							%span.caret

						-# Options
						%ul.dropdown-menu

							-if(is_a($auth, 'Bkwld\Decoy\Auth\Sentry') && $auth->can('read', 'admins'))
								%li
									-#%a(href=DecoyURL::action('Bkwld\Decoy\Controllers\Admins@index')) Admins
								%li
									%a(href=$auth->userUrl()) Your account
								%li.divider

							-$divider = false
							-if($auth->developer())
								-$divider = true
								%li
									-#%a(href=route('decoy\commands')) Commands

							-if(count(Bkwld\Decoy\Models\Worker::all()))
								-$divider = true
								%li
									-#%a(href=route('decoy\workers')) Workers

							-if($divider)
								%li.divider

							%li
								%a(href='/') Public site
							%li
								%a(href=$auth->logoutUrl()) Log out

	-# Add AJAX progress indicator
	!= View::make('decoy::layouts._ajax_progress')