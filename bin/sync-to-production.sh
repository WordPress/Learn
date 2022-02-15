#!/bin/bash

rm -rf /tmp/learn

git clone https://github.com/WordPress/learn.git /tmp/learn

svn co https://meta.svn.wordpress.org/sites/trunk/wordpress.org/public_html/wp-content/themes/pub/wporg-learn-2020 /tmp/learn/meta-theme
svn co https://meta.svn.wordpress.org/sites/trunk/wordpress.org/public_html/wp-content/plugins/wporg-learn /tmp/learn/meta-plugin

cd /tmp/learn

# Install
yarn

# Install v2
composer install

# Build
yarn workspaces run build

# Sync git to SVN
rm -rf meta-theme/*
cp -r wp-content/themes/pub/wporg-learn-2020/* meta-theme
svn st meta-theme/ | grep ^? | cut -c2- | xargs -I% svn add %
svn st meta-theme/ | grep ^! | cut -c2- | xargs -I% svn rm %

rm -rf meta-plugin/*
cp -r wp-content/plugins/wporg-learn/* meta-plugin
svn st meta-plugin/ | grep ^? | cut -c2- | xargs -I% svn add %
svn st meta-plugin/ | grep ^! | cut -c2- | xargs -I% svn rm %

# Print diff.
svn st meta-*

echo "svn diff /tmp/learn/meta-* to view diff."

COMMIT=`git log | head -1 | cut -d' ' -f2`
MSG="Learn: Sync with git WordPress/learn@$COMMIT"

# pause
echo "Control+C to abort, or Press any key to commit as: '$MSG'"
read

svn ci meta-plugin meta-theme -m "$MSG"
