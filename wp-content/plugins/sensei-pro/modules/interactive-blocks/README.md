# Sensei Interactive Blocks

This module contains Sensei's interactive blocks. The module is released in two flavors, as part of Sensei Pro and as a standalone 
plugin.

## Release process

To generate the standalone plugin file, run `npm run build:interactive-blocks`. The steps that are followed by the
script are:
- The minimized files of Sensei Pro and the composer production dependencies are built.
- The following directories are copied to the main directory of the built version of the plugin:
    - Every file in modules/interactive-blocks
    - modules/shared-module
    - vendor
- The following directories are copied to the `assets/dist` directory:
    - assets/dist/interactive-blocks 
    - assets/dist/shared-module 
    - assets/dist/fonts 
    - assets/dist/images
- The non-minified files are removed.
- The new directory is archived.

## Directory structure

The directory structure of the standalone plugin is enforced in `sensei-interactive-blocks.php` as this file is only loaded
by core WP. The directory structure of the standalone plugin is the following:

```bash
sensei-interactive-blocks
├── assets
│   ├── dist
│   │   ├── fonts
│   │   ├── images
│   │   ├── interactive-blocks
│   │   └── shared-module
│   ├── flashcard-block
│   ├── hotspots-block
│   ├── question
│   └── tasklist-block
├── includes
├── shared-module
└── vendor
```
