# WP - Learn WordPress

The details below will walk you through getting set up to contribute to the **code** behind https://learn.wordpress.org. If you are interested in contributing to the **content** or **translation** of the content hosted on the site, you'll find further guidance in the [Training Team Handbook](https://make.wordpress.org/training/handbook/). Content development is tracked on this GitHub repository [in this project board](https://github.com/orgs/WordPress/projects/33/views/1).

## Getting Started Workshops
If you're interested in contributing to the site, but aren't sure where to start, we have a series of workshops to help you get started. 
- [Contributing to Learn WordPress with code – part 1](https://www.youtube.com/watch?v=3KU0Vdn5_6g)
- [Contributing to Learn WordPress with code – part 2](https://www.youtube.com/watch?v=3Rx2KoZToZk)

## Prerequisites
- [Docker](https://docs.docker.com/get-docker/)
- [Node/NPM](https://nodejs.org/en/download/)
- [Yarn](https://www.npmjs.com/package/yarn)
- [Composer](https://getcomposer.org/download/)
- [SVN](https://subversion.apache.org/packages.html)
- [NVM](https://github.com/nvm-sh/nvm) or [N](https://github.com/tj/n) (optional)

## Setup
1. `nvm use` or ensure you are running the Node version specified in the `.nvmrc` file
2. `yarn`
3. `yarn run create`
4. Visit site at `localhost:8888`
5. To watch for changes `yarn start:theme`

## Stopping Environment

	yarn run wp-env stop`

## Removing Environment

	yarn run wp-env destroy`

## Admin

Since the local environment uses [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/), it automatically comes with an admin user, with `admin`/`password` for the credentials.

## Development

While working on the theme & plugin, you might need to rebuild the CSS or JavaScript.

To build all projects, you can run:

	yarn build

To build one project at a time, run:

	yarn workspace wporg-locale-switcher build
	yarn workspace wporg-learn-2024 build
	yarn workspace wporg-learn-plugin build

If you want to watch for changes, run:

	yarn start:locale-switcher
	yarn start:theme
	yarn start:plugin

### Linting

This project has eslint, stylelint, and phpcs set up for linting the code. This ensures all developers are working from the same style.

To lint everything run:

	yarn lint

To lint one language run one of:

	yarn lint:js
	yarn lint:css
	yarn lint:php

To check an individual project before pushing to the repo, run one of:

	yarn workspace wporg-locale-switcher lint:css
	yarn workspace wporg-locale-switcher lint:js
	yarn workspace wporg-learn-2024 lint:css
	yarn workspace wporg-learn-2024 lint:js
	yarn workspace wporg-learn-plugin lint:css
	yarn workspace wporg-learn-plugin lint:js
	composer run lint

Linting will also be run automatically on each PR.

### Contributing

If you'd like to contribute to the project, please read the [Developing Learn WordPress](https://make.wordpress.org/training/handbook/training-team-how-to-guides/developing-learn-wordpress/) page in our team handbook. 
