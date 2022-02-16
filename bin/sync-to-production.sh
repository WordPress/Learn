#!/bin/bash

rm -rf /tmp/learn

git clone https://github.com/WordPress/learn.git /tmp/learn

svn co https://meta.svn.wordpress.org/sites/trunk/wordpress.org/public_html/wp-content/themes/pub/wporg-learn-2020 /tmp/learn/meta-theme
svn co https://meta.svn.wordpress.org/sites/trunk/wordpress.org/public_html/wp-content/plugins/wporg-learn /tmp/learn/meta-plugin

cd /tmp/learn

# Create a .wporg-deps folder for yarn & composer.
mkdir .wporg-deps

YARN=`which yarn`
if [ -z "$YARN" ]; then
	# Install yarn
	echo "Installing Yarn..."

	cd .wporg-deps

	# Avoid installing the projects package.json.
	echo '{}' > package.json

	npm install --no-save yarn

	YARN=`pwd`/node_modules/yarn/bin/yarn

	cd ..
fi;

COMPOSER=`which composer`
if [ -z "$COMPOSER" ]; then
	cd .wporg-deps

	echo "Installing Composer..."

	curl -s https://getcomposer.org/installer | php -- --filename=composer

	COMPOSER=`pwd`/composer

	cd ..
fi

echo "Installing dependancies..."

# Install from yarn
$YARN

# Install v2
$COMPOSER install

echo "Building..."

# Build
$YARN workspaces run build

echo "Syncing to SVN..."

# Sync git to SVN
rm -rf meta-theme/* meta-plugin/*
cp -r wp-content/themes/pub/wporg-learn-2020/* meta-theme
cp -r wp-content/plugins/wporg-learn/* meta-plugin
svn st meta-*/ | grep ^? | cut -c2- | xargs -I% svn add %
svn st meta-*/ | grep ^! | cut -c2- | xargs -I% svn rm %

# Print diff.
echo "Changes:"
svn st meta-*

echo "svn diff /tmp/learn/meta-* to view diff."

COMMIT=`git log | head -1 | cut -d' ' -f2`
MSG="Learn: Sync with git WordPress/learn@$COMMIT"

# pause
echo "Control+C to abort, or Press any key to commit as: '$MSG'"
read

svn ci meta-plugin meta-theme -m "$MSG"
