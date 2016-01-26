Contao 4 Crosstabs Bundle
=======================

The Crosstabs Bundle is a helper library for developers to realize cross tables in Contao

Installation
------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require craffft/crosstabs-bundle "dev-master"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Craffft\CrosstabsBundle\CraffftCrosstabsBundle(),
        );

        // ...
    }

    // ...
}
```

Documentation
-------------

### Usage for TabelLookupWizard

Usage in the config file

```php
// system/modules/mymodule/config/config.php

// Define left table
$GLOBALS['BE_MOD']['accounts']['member'] = array
(
    // Add the cross table to the allowed tables
    'tables' => array('tl_member', 'tl_my_cross_table')
);

// Define right table
$GLOBALS['BE_MOD']['accounts']['mgroup'] = array
(
    // Add the cross table to the allowed tables
    'tables' => array('tl_member_group', 'tl_my_cross_table')
);
```

Usage in the left table

```php
// system/modules/mymodule/dca/tl_member.php

// Copyies the cross table data, if the current record of the left table will be copied
$GLOBALS['TL_DCA']['tl_member']['config']['oncopy_callback'][] = array('\\Craffft\\CrosstabsBundle\\Util\\LeftTable', 'copyCallback');
// Deletes the cross table data, if the current record of the left table will be deleted
$GLOBALS['TL_DCA']['tl_member']['config']['ondelete_callback'][] = array('\\Craffft\\CrosstabsBundle\\Util\\LeftTable', 'deleteCallback');

// ...

// Adds a link icon to the cross table on each left table item
$GLOBALS['TL_DCA']['tl_member']['list']['operations']['my_cross_table_button'] = array
(
    'label'               => &$GLOBALS['TL_LANG']['tl_member']['my_cross_table_button'],
    'href'                => 'table=tl_my_cross_table',
    'icon'                => 'icon.gif'
);

// ...

// Adds a field to select the related items from the right table
$GLOBALS['TL_DCA']['tl_member']['fields']['groups'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_member']['groups'],
    'exclude'                 => true,
    'filter'                  => true,
    // This example uses the tablelookupwizard from terminal42
    'inputType'               => 'tableLookup',
    'foreignKey'              => 'tl_member_group.name',
    // Name of the cross table
    'crossTable'              => 'tl_my_cross_table',
    // Key from the left table
    'crossCurrentKey'         => 'member',
    // Key from the right table
    'crossForeignKey'         => 'mgroup',
    'eval'                    => array
    (
        'tl_class'            => 'clr',
        'foreignTable'        => 'tl_member_group',
        'fieldType'           => 'checkbox',
        'listFields'          => array('name'),
        'searchFields'        => array('name'),
        'matchAllKeywords'    => true
    ),
    // Loads the data from the cross table
    'load_callback'           => array
    (
        array('\\Craffft\\CrosstabsBundle\\DataHandler\\TableLookupWizard', 'load')
    ),
    // Saves the data into the cross table and truncates the data in the current field
    // The data will be only stored in the cross table and not in this field
    'save_callback'           => array
    (
        array('\\Craffft\\CrosstabsBundle\\DataHandler\\TableLookupWizard', 'save')
    ),
    'sql'                     => "tinyint(1) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'belongsToMany', 'load'=>'lazy')
);
```


Usage in the cross table
```php
// system/modules/mymodule/dca/tl_my_cross_table.php

$GLOBALS['TL_DCA']['tl_my_cross_table'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'enableVersioning'            => true,
        // Close the cross table if you only want to read (recommend)
        'closed'                      => true,
        'notEditable'                 => true,
        'notDeletable'                => true,
        'onload_callback'             => array
        (
            array('tl_my_cross_table', 'checkPermission'),
            // Filters the cross table with the given id and the here defined fields
            function (\DataContainer $dc) {
                \Craffft\CrosstabsBundle\Util\CrossTable::filter($dc, array
                (
                    // If the "do" param is "member", the cross table will be filtered by the given id on the cross table field "member"
                    'member' => 'member',

                    // If the "do" param is "mgroup", the cross table will be filtered by the given id on the cross table field "mgroup"
                    'mgroup' => 'mgroup',

                    // If the "do" param is "do_param", the cross table will be filtered by the given id on the cross table field "cross_table_field"
                    'do_param' => 'cross_table_field',
                ));
            }
        ),

        // ...

        'global_operations' => array
        (
            // Adds a button to go to the left table
            'left_table_list' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cross_table']['left_table_list'],
                'href'                => 'do=member&table=tl_member',
                'class'               => 'header_left_table_list',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ),

            // Adds a button to go to the right table
            'right_table_list' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cross_table']['right_table_list'],
                'href'                => 'do=mgroup&table=tl_member_group',
                'class'               => 'header_right_table_list',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ),

            // ...
        ),

        // ...

        'operations' => array
        (
            // ...

            // Adds a left table edit icon to the records in the cross table list
            'edit_left_table_item' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cross_table']['edit_left_table_item'],
                'href'                => 'do=member&amp;table=tl_member&amp;act=edit',
                'icon'                => 'member.gif',
                'button_callback'     => function ($row, $href, $label, $title, $icon) {
                        // Return an icon which opens the edit mode in a popup
                        return \Craffft\CrosstabsBundle\Util\CrossTable::icon($row['member'], $href, $label, $title, $icon);
                    }
            ),

            // Adds a right table edit icon to the records in the cross table list
            'edit_right_table_item' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cross_table']['edit_right_table_item'],
                'href'                => 'do=mgroup&amp;table=tl_member_group&amp;act=edit',
                'icon'                => 'mgroup.gif',
                'button_callback'     => function ($row, $href, $label, $title, $icon) {
                        // Return an icon which opens the edit mode in a popup
                        return \Craffft\CrosstabsBundle\Util\CrossTable::icon($row['mgroup'], $href, $label, $title, $icon);
                    }
            )
        ),

        // ...

        'fields' => array
        (
            // ...

            // Add the left table field relation to the cross table
            'member' => array
            (
                'label'                   => &$GLOBALS['TL_LANG']['tl_cross_table']['member'],
                'foreignKey'              => "tl_member.CONCAT(firstname, ' ', lastname)",
                'sql'                     => "int(10) unsigned NOT NULL default '0'",
                'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
            ),

            // Add the right table field relation to the cross table
            'mgroup' => array
            (
                'label'                   => &$GLOBALS['TL_LANG']['tl_cross_table']['mgroup'],
                'foreignKey'              => 'tl_member_group.name',
                'sql'                     => "int(10) unsigned NOT NULL default '0'",
                'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
            )
        )

        // ...
    )
);
```

### Usage for MultiColumnWizard

MultiColumnWizard field definition example:

```php
$GLOBALS['TL_DCA']['tl_member']['fields']['training_courses'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_member']['training_courses'],
    'exclude'                 => true,
    'crossTable'              => 'tl_member_training_course',
    'crossCurrentKey'         => 'member',
    // Remove the crossForeignKey field, if you have a 1:n (only to crossTable) and not a n:m connection
    'crossForeignKey'         => 'training_course',
    'inputType'               => 'multiColumnWizard',
    'eval'                    => array
    (
        'columnFields' => array
        (
            'training_course' => array
            (
                // Field definition ...
            ),
            'dateOfCompletion' => array
            (
                // Field definition ...
            ),
            'location' => array
            (
                // Field definition ...
            )
        )
    ),
    'load_callback'           => array
    (
        array('\\Craffft\\CrosstabsBundle\\DataHandler\\MultiColumnWizard', 'load')
    ),
    'save_callback'           => array
    (
        array('\\Craffft\\CrosstabsBundle\\DataHandler\\MultiColumnWizard', 'save')
    ),
    'sql'                     => "tinyint(1) unsigned NOT NULL default '0'"
);
```

Cross Table DCA definition example:

```php
<?php

$GLOBALS['TL_DCA']['tl_member_training_course'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'enableVersioning'            => false,
        'onload_callback' => array
        (
            array('tl_member_training_course', 'checkPermission')
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id'                        => 'primary',
                'member'                    => 'index',
                'training_course'           => 'index',
                'member,training_course'    => 'unique'
            )
        )
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'sorting' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'member' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'training_course' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'dateOfCompletion' => array
        (
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'location' => array
        (
            'sql'                     => "varchar(255) NOT NULL default ''"
        )
    )
);

```

Do not forget to define the model classes for your cross tables!