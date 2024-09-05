#!/bin/bash

# Exit if any command fails.
set -e

# Setup the environment
npm run wp-env start

# Update wp configs
npm run wp-env run cli wp config set JETPACK_DEV_DEBUG true
npm run wp-env run cli wp config set WPORG_SANDBOXED true

# Import tables
npm run wp-env run cli wp db import wp-content/uploads/wporg_events.sql
npm run wp-env run cli wp db import wp-content/uploads/wporg_locales.sql

# Activate plugins
npm run wp-env run cli wp plugin activate edit-flow jetpack wordpress-importer sensei-lms gutenberg locale-detection code-syntax-block wporg-learn

# Activate jetpack modules
npm run wp-env run cli wp jetpack module activate contact-form

# Activate theme
npm run wp-env run cli wp theme activate pub/wporg-learn-2024

# Change permalinks
npm run wp-env run cli wp rewrite structure '/%postname%/'

# Import content
npm run wp-env run cli php bin/import-test-content.php
