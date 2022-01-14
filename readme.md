# WP - Learn

## Prerequisites
- [Docker](https://docs.docker.com/get-docker/)
- [Node/NPM](https://nodejs.org/en/download/)
- [Yarn](https://www.npmjs.com/package/yarn)
- [Composer](https://getcomposer.org/download/)
- [SVN](https://subversion.apache.org/packages.html)
- [NVM](https://github.com/nvm-sh/nvm) or [N](https://github.com/tj/n) (optional)

## Setup
1. `yarn`
2. `yarn run create`
3. Visit site at `localhost:8888`

⚠️ [`node-sass` is very picky about node versions](https://www.npmjs.com/package/node-sass). If you get errors, [make sure your node version is supported](https://stackoverflow.com/questions/60394291/error-node-modules-node-sass-command-failed) by the version of `node-sass` in `package.json`. e.g., `nvm use lts/fermium`.


## Stopping Environment
run `yarn run wp-env stop`

## Removing Environment
run `yarn run wp-env destroy`

## Admin

Since the local environment uses [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/), it automatically comes with an admin user, with `admin`/`password` for the credentials.

## Development

While working on the theme & plugin, you might need to rebuild the CSS or JavaScript.

To build both projects, you can run:

	yarn workspaces run build

To build one at a time, run

	yarn workspace wporg-learn-theme build
	yarn workspace wporg-learn-plugin build

If you want to watch for changes, run `start`. This can only be run in one project at a time:

	yarn workspace wporg-learn-theme start
	yarn workspace wporg-learn-plugin start

### Linting

This project has eslint, stylelint, and phpcs set up for linting the code. This ensures all developers are working from the same style. To check your code before pushing it to the repo, run

	yarn workspace wporg-learn-theme lint:css
	yarn workspace wporg-learn-plugin lint:css
	yarn workspace wporg-learn-plugin lint:js
	composer run lint

These checks will also be run automatically on each PR.
