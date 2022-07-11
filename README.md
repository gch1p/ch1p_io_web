# ch1p_io_web

This is complete code of ch1p.io website.

## Features
- it's not just blog, you can create any page with any address
- posts and pages are written in Markdown:
	- supports syntax highlighting in code blocks
	- supports embedding of uploaded files and image resizing
 - tags
 - rss feed
 - dark theme
 - ultra fast on backend:
	- written from scratch
	- no heavy frameworks
	- no "classic" template engine
		- vanilla php templates designed from scratch (because why not)
		- thus, no overhead from templates "compilation"
		- all strings are transparently escaped unless explicitly specified not to
 - ultra fast on frontend:
	- written from scratch
	- simple readable ECMAScript 5.1 scripts
	- no modern web bullshit like webpack or babel
	- simple build system that just works
 - secure:
	- CSRF protection
	- automatic XSS protection in templates
	- see the "BUG BOUNTY" section below

## Requirements

- PHP >= 8.1, with following extensions:
	- mysqli
	- gd
- MariaDB server
- Composer
- Node.JS
- SCSS compiler, e.g. sassc

## Configuration

Should be done by copying config.php to config-local.php and modifying config-local.php.

## Installation

It uses https://github.com/sixlive/parsedown-highlight which you'll need to install using Composer, but since that
package's manifest is a bit outdated you have to pass `--ignore-platform-reqs` to composer.

TODO

## Logging

TODO

## Deploying

```
make deploy
```

## Bug bounty

I take security very seriously. If you found an exploitable vulnerability in _my_ code, please contact me by email.

I'm willing to pay $50 to $500 in crypto (depending on severity) for every discovered vulnerability.

## License

GPLv3