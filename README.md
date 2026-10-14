# [NgSurvey](https://ngideas.com) #

![Plugin Version](https://img.shields.io/wordpress/plugin/v/ngsurvey) ![GitHub](https://img.shields.io/github/license/ngideas/ngsurvey) ![Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/ngsurvey) ![Plugin: Tested WP Version](https://img.shields.io/wordpress/plugin/tested/ngsurvey) ![Plugin Rating](https://img.shields.io/wordpress/plugin/stars/ngsurvey)

### Welcome to our GitHub Repository

Getting feedback from your users is now made easier with this simple yet powerful survey plugin. NgSurvey is open source and full featured survey plugin for Wordpress. Create stunning surveys with built-in ajax powered form builder, conditional rules and more features out of the box. Need more? Extend the plugin with extensions and question types.

More information can be found at [ngideas.com](https://ngideas.com/).

## Installation ##

For detailed setup instructions, visit the official [Documentation](https://docs.ngideas.com/) page.

1. You can clone the GitHub repository: `https://github.com/ngideas/ngsurvey.git`
2. Or download it directly as a ZIP file: `https://github.com/ngideas/ngsurvey/archive/master.zip`

This will download the latest developer copy of NgSurvey to your computer.

## Compatibility checks

The compatibility fixes were tested with WordPress 7.1.3, PHP 8.3.33 and MariaDB 10.11, with both NgSurvey and NgSurvey Extensions active. Coverage includes translation/bootstrap timing, shortcode and synced-pattern assets, survey author permissions, Unicode conditional rules, and front-end survey creation, editing, publishing and responses. External service integrations and other PHP versions require separate testing.

Run the regression scripts only against a disposable WordPress installation containing the current source of both plugins. Set `NGSURVEY_WP_ROOT` to that installation's absolute path and use a PHP configuration with its database extensions enabled:

```sh
php tests/conditional-rules.test.php
php tests/bootstrap.test.php
wp eval-file tests/public-assets.php SURVEY_ID --path=/path/to/wordpress
wp eval-file tests/survey-author-ajax.php --path=/path/to/wordpress
wp eval-file tests/response-status.php --path=/path/to/wordpress
```

`SURVEY_ID` must identify an existing published survey with a page. Run `response-status.php` again with `NGSURVEY_STATUS_MODE=public` to test the public response model in a separate process. The author and response-status tests roll back their temporary database fixtures. Run the companion extension regression described in its README separately. Rules saved with already-corrupted text need to be corrected and saved again; the fix preserves UTF-8 for future saves and evaluations.

## Bugs ##
If you find an issue, let us know [here](https://github.com/ngideas/ngsurvey/issues?state=open)!

## Support ##
This is a developer's portal for NgIdeas and should _not_ be used for support. Please visit the [support page](https://ngideas.com/support) if you need to ask us a question. Discuss on topics related to NgSurvey on the [Support Page](https://wordpress.org/support/plugin/ngsurvey/)

## Contributions ##
Anyone is welcome to contribute to NgIdeas. Please read the [guidelines for contributing](https://github.com/ngideas/ngsurvey/blob/master/CONTRIBUTING.md) to this repository.

There are various ways you can contribute:

1. Raise an [Issue](https://github.com/ngideas/ngsurvey/issues) on GitHub
2. Send us a Pull Request with your bug fixes and/or new features
3. Translate NgSurvey into different languages
4. Provide feedback and suggestions
