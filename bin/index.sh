#!/bin/bash

# Exit if any command fails.
set -e

# Setup the environment
npm run wp-env start

# Update wp configs
npm run wp-env run cli wp config set JETPACK_DEV_DEBUG true
npm run wp-env run cli wp config set WPORG_SANDBOXED true

# Activate plugins
npm run wp-env run cli wp plugin activate wordpress-importer jetpack wporg-learn/wporg-learn.php wporg-markdown/plugin.php

# Activate theme
npm run wp-env run cli wp theme activate pub/wporg-learn-2020

## Install dependencies
yarn

## Import lesson plans
# Disabled since markdown import is currently disabled in the plugin.
#npm run wp-env run cli wp cron event run wporg_learn_manifest_import
#npm run wp-env run cli wp cron event run wporg_learn_markdown_import

## Activate jetpack modules
npm run wp-env run cli wp jetpack module activate contact-form
npm run wp-env run cli wp jetpack module activate markdown

## Change permalinks
npm run wp-env run cli wp rewrite structure '/%postname%/'
