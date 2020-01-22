<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php View::getInstance()->requireAsset('javascript', 'jquery');

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
    $classes = array();

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

    $navItems[$k]->hasMegaMenu = isset($stacks);

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

echo '<nav id="navigation" class="header-navigation ccm-responsive-navigation original"><ul>'; //opens the top-level menu

foreach ($navItems as $ni) {

    echo '<li id="megaNavItem' . $ni->cID . '" data-cid="' . $ni->cID . '" class="' . $ni->classes . '">'; //opens a nav item
    $name = (isset($translate) && $translate == true) ? t($ni->name) : $ni->name;
    echo '<a href="' . $ni->url . '" target="' . $ni->target . '" class="' . $ni->classes . '"><span>' . $name . '</span></a>';

    if ($ni->hasSubmenu) {
        echo '<div><ul class="nav-sub-menu">'; //opens a dropdown sub-menu
    } else {
        echo '</li>'; //closes a nav item
        echo str_repeat('</ul></div></li>', $ni->subDepth); //closes dropdown sub-menu(s) and their top-level nav item(s)
    }
}

echo '</ul>'; //closes the top-level menu

foreach ($megaMenus as $cID => $cols) {
    $rows = array_chunk($cols, 4);

    $className = 'col-sm-3';

    if (count($rows) === 1) {
        if (count($rows[0]) === 1) {
            $className = 'col-sm-12';
        } elseif (count($rows[0]) === 2) {
            $className = "col-sm-6";
        } elseif (count($rows[0]) === 3) {
            $className = "col-sm-4";
        }
    }

    echo '<div id="megaMenu' . $cID . '" class="nav-mega-menu">';
    echo '<div class="container">';

    foreach ($rows as $row) {
        echo '<div class="row">';
        foreach ($row as $col) {
            echo '<div class="'. $className .'">';
            $col->display();
            echo '</div>';
        }
        echo '</div>';
    }

    echo '</div>';
    echo '</div>';
}

echo '</nav>'; //closes the top-level menu
echo '<div class="ccm-responsive-menu-launch"><i></i></div>'; // empty i tag for attaching :after or :before psuedos for things like FontAwesome icons.
?>
<style>
    .nav-mega-menu { 
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 200;
        background: #fff;
        color: #000;
        text-align: left;
        padding: 30px 0;
        box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.35);
        opacity: 0;
        transition: opacity ease-in-out .25s;
        overflow: hidden;
        z-index: -1;
    }

    .nav-has-mega .nav-sub-menu { display: none !important; }

    .nav-mega-menu h1 { font-size: 18px;  }

    .ccm-toolbar-visible .nav-mega-menu {
        top: 0;
    }

    .nav-mega-menu.nav-mega-open {
        opacity: 1;
        z-index: 101;
    }
</style>

<script>
    $(function() {
        var $navItems = $('.nav-has-mega')

        // Close menus & remove selection when another item is hovered over.
        $('#navigation li').each(function() {
            $(this).mouseover(function() {
                $('.nav-mega-open').removeClass('nav-mega-open')
                $('.nav-selected').removeClass('nav-selected')
            })
        })

        $navItems.each(function() {
            var cid = $(this).data('cid')

            // Find the corresponding mega menu container.
            var $el = $('body').find('#megaMenu' + cid)

            // Re-attach each menu to the page container rather than the navigation.
            $('.ccm-page').append($el.detach())

            // Position the mega menu directly under the first <header> tag.
            $el.css({ top: $('header')[0].getBoundingClientRect().bottom })

            // Show the mega menu when this navigation item is hovered over.
            $(this).mouseover(function(event) {
                $el.addClass('nav-mega-open')
                $(this).addClass('nav-selected')
            })

            // Hide the mega menu when the mouse leaves it's bounds.
            $el.mouseleave(function(event) {
                $('.nav-mega-open').removeClass('nav-mega-open')
                $('.nav-selected').not('.default-nav-selected').removeClass('nav-selected')
                $('.default-nav-selected').addClass('nav-selected');
            });
        })
    });
</script>