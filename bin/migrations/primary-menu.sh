#!/bin/bash

# Create Menu
npm run wp-env run cli "wp menu create 'Primary Menu'"

# Add items
npm run wp-env run cli "wp menu item add-custom primary-menu 'Lesson Plans' '/lesson-plans'"
npm run wp-env run cli "wp menu item add-custom primary-menu 'Workshops' '/workshops'"

npm run wp-env run cli "wp menu location assign primary-menu primary"