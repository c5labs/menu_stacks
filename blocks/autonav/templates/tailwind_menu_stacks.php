<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php

$view = View::getInstance();

$templatePath = DIR_BASE . $view->getThemePath();

$navItems = $controller->getNavItems();

$megaMenus = [];

/**
 * The $navItems variable is an array of objects, each representing a nav menu item.
 * It is a "flattened" one-dimensional list of all nav items -- it is not hierarchical.
 * However, a nested nav menu can be constructed from this "flat" array by
 * looking at various properties of each item to determine its place in the hierarchy
 * (see below, for example $navItem->level, $navItem->subDepth, $navItem->hasSubmenu, etc.)
 *
 * Items in the array are ordered with the first top-level item first, followed by its sub-items, etc.
 *
 * Each nav item object contains the following information:
 *  $navItem->url        : URL to the page
 *  $navItem->name       : page title (already escaped for html output)
 *  $navItem->target     : link target (e.g. "_self" or "_blank")
 *  $navItem->level      : number of levels deep the current menu item is from the top (top-level nav items are 1, their sub-items are 2, etc.)
 *  $navItem->subDepth   : number of levels deep the current menu item is *compared to the next item in the list* (useful for determining how many <ul>'s to close in a nested list)
 *  $navItem->hasSubmenu : true/false -- if this item has one or more sub-items (sometimes useful for CSS styling)
 *  $navItem->isFirst    : true/false -- if this is the first nav item *in its level* (for example, the first sub-item of a top-level item is TRUE)
 *  $navItem->isLast     : true/false -- if this is the last nav item *in its level* (for example, the last sub-item of a top-level item is TRUE)
 *  $navItem->isCurrent  : true/false -- if this nav item represents the page currently being viewed
 *  $navItem->inPath     : true/false -- if this nav item represents a parent page of the page currently being viewed (also true for the page currently being viewed)
 *  $navItem->attrClass  : Value of the 'nav_item_class' custom page attribute (if it exists and is set)
 *  $navItem->isHome     : true/false -- if this nav item represents the home page
 *  $navItem->cID        : collection id of the page this nav item represents
 *  $navItem->cObj       : collection object of the page this nav item represents (use this if you need to access page properties and attributes that aren't already available in the $navItem object)
 */


/** For extra functionality, you can add the following page attributes to your site (via Dashboard > Pages & Themes > Attributes):
 *
 * 1) Handle: exclude_nav
 *    (This is the "Exclude From Nav" attribute that comes pre-installed with concrete5, so you do not need to add it yourself.)
 *    Functionality: If a page has this checked, it will not be included in the nav menu (and neither will its children / sub-pages).
 *
 * 2) Handle: exclude_subpages_from_nav
 *    Type: Checkbox
 *    Functionality: If a page has this checked, all of that pages children (sub-pages) will be excluded from the nav menu (but the page itself will be included).
 *
 * 3) Handle: replace_link_with_first_in_nav
 *    Type: Checkbox
 *    Functionality: If a page has this checked, clicking on it in the nav menu will go to its first child (sub-page) instead.
 *
 * 4) Handle: nav_item_class
 *    Type: Text
 *    Functionality: Whatever is entered into this textbox will be outputted as an additional CSS class for that page's nav item (NOTE: you must un-comment the "$ni->attrClass" code block in the CSS section below for this to work).
 */


/*** STEP 1 of 2: Determine all CSS classes (only 2 are enabled by default, but you can un-comment other ones or add your own) ***/
foreach ($navItems as $k => $ni) {
    $classes = array('flex-grow', 'px-4');

    if ($ni->isCurrent) {
        //class for the page currently being viewed
        $classes[] = 'default-nav-selected';
        $classes[] = 'nav-selected';
    }

    if ($ni->inPath && 'home' !== strtolower($ni->name)) {
        //class for parent items of the page currently being viewed
        $classes[] = 'nav-path-selected';
    }

    if ($stack = $ni->cObj->getAttribute('menu_stacks_col_1')) {
        $classes[] = 'nav-has-mega';

        $megaMenus[$ni->cID] = array_filter([
            $ni->cObj->getAttribute('menu_stacks_col_1'),
            $ni->cObj->getAttribute('menu_stacks_col_2'),
            $ni->cObj->getAttribute('menu_stacks_col_3'),
            $ni->cObj->getAttribute('menu_stacks_col_4'),
            $ni->cObj->getAttribute('menu_stacks_col_5'),
            $ni->cObj->getAttribute('menu_stacks_col_6'),
            $ni->cObj->getAttribute('menu_stacks_col_7'),
            $ni->cObj->getAttribute('menu_stacks_col_8'),
        ]);
    }

    $navItems[$k]->hasMegaMenu = isset($stack);

    /*
    if ($ni->isFirst) {
        //class for the first item in each menu section (first top-level item, and first item of each dropdown sub-menu)
        $classes[] = 'nav-first';
    }
    */

    /*
    if ($ni->isLast) {
        //class for the last item in each menu section (last top-level item, and last item of each dropdown sub-menu)
        $classes[] = 'nav-last';
    }
    */


    if ($ni->hasSubmenu) {
        //class for items that have dropdown sub-menus
        $classes[] = 'nav-dropdown';
    }


    /*
    if (!empty($ni->attrClass)) {
        //class that can be set by end-user via the 'nav_item_class' custom page attribute
        $classes[] = $ni->attrClass;
    }
    */

    /*
    if ($ni->isHome) {
        //home page
        $classes[] = 'nav-home';
    }
    */

    /*
    //unique class for every single menu item
    $classes[] = 'nav-item-' . $ni->cID;
    */

    //Put all classes together into one space-separated string
    $ni->classes = implode(" ", $classes);
}


//*** Step 2 of 2: Output menu HTML ***/

echo '<nav id="navigation" class="header-navigation ccm-responsive-navigation original mr-6"><ul class="flex">'; //opens the top-level menu

foreach ($navItems as $ni) {

    echo '<li id="megaNavItem' . $ni->cID . '" data-cid="' . $ni->cID . '" class="' . $ni->classes . '">'; //opens a nav item
    $name = (isset($translate) && $translate == true) ? t($ni->name) : $ni->name;
    echo '<a href="' . $ni->url . '" target="' . $ni->target . '"><span>' . $name . '</span></a>';

    if ($ni->hasSubmenu) {
        echo '<div><ul class="nav-sub-menu">'; //opens a dropdown sub-menu
    } else {
        echo '</li>'; //closes a nav item
        echo str_repeat('</ul></div></li>', $ni->subDepth); //closes dropdown sub-menu(s) and their top-level nav item(s)
    }
}

echo '</ul>'; //closes the top-level menu

foreach ($megaMenus as $cID => $cols) {
    $possibleTemplateName = 'mega_menu_' . $cID . '.php';
    $possibleTemplatePath = $templatePath . DIRECTORY_SEPARATOR . $possibleTemplateName;
    $rows = array_chunk($cols, 4);


    echo '<div id="megaMenu' . $cID . '" class="nav-mega-menu hidden md:block bg-black bg-opacity-50 bottom-0 background-blur-5px"><div class="max-w-8xl 2xl:max-w-8xl m-auto overflow-y-auto h-full"><div class="mx-8 bg-white shadow-2xl leave-target">';

    if (file_exists($possibleTemplatePath)) {
        $view->inc($possibleTemplateName);
    } else {
        foreach ($rows as $row) {
            $rowClass = 'grid-cols-' . count($row);
            $colClass = 'col-span-1';

            echo '<div class="' . $rowClass . '">';
            foreach ($row as $col) {
                echo '<div class="' . $colClass . '">';
                $col->display();
                echo '</div>';
            }
            echo '</div>';
        }
    }

    echo '</div></div></div>';
}

echo '</nav>'; //closes the top-level menu
echo '<div class="ccm-responsive-menu-launch"><i></i></div>'; // empty i tag for attaching :after or :before psuedos for things like FontAwesome icons.
?>
<style>
    .nav-mega-menu {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 200;
        /*background: #fff;*/
        color: #000;
        text-align: left;
        /*padding: 30px 0;*/
        /*box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.35);*/
        opacity: 0;
        transition: opacity ease-in-out .25s;
        overflow: hidden;
        z-index: -1;
    }

    .nav-has-mega .nav-sub-menu {
        display: none !important;
    }

    .ccm-toolbar-visible .nav-mega-menu {
        top: 0;
    }

    .nav-mega-menu.nav-mega-open {
        opacity: 1;
        z-index: 101;
    }
</style>

<script>
    window.addEventListener('DOMContentLoaded', function() {
        var $navItems = document.querySelectorAll('.nav-has-mega')

        // Close menus & remove selection when another item is hovered over.
        document.querySelectorAll('#navigation > ul > li').forEach(function($this) {
            $this.addEventListener('mouseover', function() {
                document.querySelectorAll('.nav-mega-open').forEach($this => {
                    $this.classList.remove('nav-mega-open');
                });

                document.querySelectorAll('.nav-selected').forEach($this => {
                    $this.classList.remove('nav-selected');
                });
            })
        })

        // Centralize the menu if it's not full width.
        document.querySelectorAll('.nav-mega-menu').forEach(function($this) {
            const viewportWidth = window.innerWidth
            const menuWidth = $this.getBoundingClientRect().width
            const width = viewportWidth - menuWidth

            if (width > 0) {
                $this.style.left = Math.min(width / 2) + 'px'
            }
        })

        $navItems.forEach($this => {
            var cid = $this.getAttribute('data-cid')

            // Find the corresponding mega menu container.
            var $el = document.querySelector('#megaMenu' + cid)

            if (!$el) {
                return
            }

            // Re-attach each menu to the page container rather than the navigation.
            document.querySelector('.ccm-page').append(
                $el.parentElement.removeChild($el)
            )

            const closeMenus = function(event) {
                document.querySelectorAll('.nav-mega-open').forEach($this => {
                    $this.classList.remove('nav-mega-open')
                    document.querySelectorAll('.nav-selected').forEach($this => {
                        if (!$this.classList.contains('.default-nav-selected')) {
                            $this.classList.remove('nav-selected')
                            $this.classList.add('nav-selected')
                        }
                    })
                })
            }

            // Show the mega menu when this navigation item is hovered over.
            $this.addEventListener('mouseover', function(event) {
                // Position the mega menu directly under the first <header> tag.
                $el.style.top = document.querySelector('header > nav').getBoundingClientRect().bottom + 'px'

                $el.classList.add('nav-mega-open')
                $this.classList.add('nav-selected')
            })

            // Hide the mega menu when the mouse leaves it's bounds.
            $el.querySelector('.leave-target').addEventListener('mouseleave', closeMenus)

            // Close the mega manus if an element with a specifc class 
            // selector is clicked.
            document.querySelectorAll('.close-mega-menus').forEach($this => {
                $this.addEventListener('click', closeMenus)
            })

            // Hide the mega menu on close.
            window.addEventListener('scroll', function() {
                // Position the mega menu directly under the first <header> tag.
                $el.style.top = document.querySelector('header > nav').getBoundingClientRect().bottom + 'px'
            })
        })
    })
</script>