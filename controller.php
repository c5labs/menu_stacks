<?php
/**
 * Menu Stacks Controller File.
 *
 * @author   Oliver Green <oliver@devisegraphics.co.uk>
 * @license  See attached license file
 */
namespace Concrete\Package\MenuStacks;

use Concrete\Core\Attribute\StandardSetManager;
use Concrete\Core\Foundation\Service\ProviderList;
use Concrete\Core\Package\Package;
use Core;
use Illuminate\Filesystem\Filesystem;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Package Controller Class.
 *
 * Mega menus using stacks.
 *
 * @author   Oliver Green <oliver@devisegraphics.co.uk>
 * @license  See attached license file
 */
class Controller extends Package
{
    /**
     * Minimum version of concrete5 required to use this package.
     * 
     * @var string
     */
    protected $appVersionRequired = '5.7.5';

    /**
     * Does the package provide a full content swap?
     * This feature is often used in theme packages to install 'sample' content on the site.
     *
     * @see https://goo.gl/C4m6BG
     * @var bool
     */
    protected $pkgAllowsFullContentSwap = false;

    /**
     * Does the package provide thumbnails of the files 
     * imported via the full content swap above?
     *
     * @see https://goo.gl/C4m6BG
     * @var bool
     */
    protected $pkgContentProvidesFileThumbnails = false;

    /**
     * Should we remove 'Src' from classes that are contained 
     * ithin the packages 'src/Concrete' directory automatically?
     *
     * '\Concrete\Package\MyPackage\Src\MyNamespace' becomes '\Concrete\Package\MyPackage\MyNamespace'
     *
     * @see https://goo.gl/4wyRtH
     * @var bool
     */
    protected $pkgAutoloaderMapCoreExtensions = false;

    /**
     * Package class autoloader registrations
     * The package install helper class, included with this boilerplate, 
     * is activated by default.
     *
     * @see https://goo.gl/4wyRtH
     * @var array
     */
    protected $pkgAutoloaderRegistries = [
        //'src/MyVendor/Statistics' => '\MyVendor\ConcreteStatistics'
    ];

    /**
     * The packages handle.
     * Note that this must be unique in the 
     * entire concrete5 package ecosystem.
     * 
     * @var string
     */
    protected $pkgHandle = 'menu_stacks';

    /**
     * The packages version.
     * 
     * @var string
     */
    protected $pkgVersion = '0.9.0';

    /**
     * The packages name.
     * 
     * @var string
     */
    protected $pkgName = 'Menu Stacks';

    /**
     * The packages description.
     * 
     * @var string
     */
    protected $pkgDescription = 'Mega menus using stacks.';

    /**
     * The packages on start hook that is fired as the CMS is booting up.
     * 
     * @return void
     */
    public function on_start()
    {

        // Add custom logic here that needs to be executed during CMS boot, things
        // such as registering services, assets, etc.
    }

    /**
     * The packages install routine.
     * 
     * @return \Concrete\Core\Package\Package
     */
    public function install()
    {
        $typeFactory = \Core::make(\Concrete\Core\Attribute\TypeFactory::class);
        $setFactory = Core::make(\Concrete\Core\Attribute\SetFactory::class);
        $categoryService = \Core::make(\Concrete\Core\Attribute\Category\CategoryService::class);
        $pageAttributeController = $categoryService->getByHandle('collection')->getController();
        $setManager = $pageAttributeController->getSetManager();

        if (!$typeFactory->getByHandle('stack')) {
            throw new \Exception('This package requires that the \'stack_attribute\' package is installed.');
        }

        $pkg = parent::install();

        // Find or create the set.
        if (!($set = $setFactory->getByHandle('menu_stacks'))) {
            $set = $setManager->addSet('menu_stacks', 'Mega Menu', $pkg, $locked = true);
        }

        // Create the columns
        $numOfColumns = 8;
        for ($i = 0; $i < $numOfColumns; $i++) {
            $akHandle = 'menu_stacks_col_' . ($i + 1);
            $akName = 'Mega Menu Column ' . ($i + 1);;
            
            if (!$pageAttributeController->getAttributeKeyByHandle($akHandle)) {
                $attr = [
                    'akHandle' => $akHandle,
                    'akName' => $akName,
                    'asID' => $set->getAttributeSetID()
                ];

                $key = $pageAttributeController->add('stack', $attr, null, $pkg);
            }
        }

        return $pkg;
    }

    /**
     * The packages upgrade routine.
     * 
     * @return void
     */
    public function upgrade()
    {
        // Add your custom logic here that needs to be executed BEFORE package install.

        parent::upgrade();

        // Add your custom logic here that needs to be executed AFTER package upgrade.
    }

    /**
     * The packages uninstall routine.
     * 
     * @return void
     */
    public function uninstall()
    {
        // Add your custom logic here that needs to be executed BEFORE package uninstall.

        parent::uninstall();

        // Add your custom logic here that needs to be executed AFTER package uninstall.
    }
}
