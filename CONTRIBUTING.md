# Contribute To Quiz And Survey Master

Community made feature requests, patches, localisations, bug reports and contributions are always welcome and are crucial to ensure Quiz and Survey Master continues to grow to become the #1 quiz and survey platform for on WordPress.

When contributing please ensure you follow the guidelines below so that we can keep on top of things.

__Please Note:__ GitHub is not intended for support based questions. For those, please use [Our Contact Form](http://quizandsurveymaster.com/contact-us/)

## Getting Started
* Make sure you have a free [GitHub Account](https://github.com/signup/free)

## Creating Issues
* If you have a bug report or feature request, please [create an issue](https://github.com/QuizandSurveyMaster/quiz_master_next/issues).
* For bug reports:
	* Please follow the debug guidlines below and report findings
	* Include what version of WordPress and what version of Quiz And Survey Master you are using
	* Include what operating system and browser you are using if applicable
	* If possible include link to quiz url or screenshot of issue
	* Clearly describe the bug/issue and include steps on how to reproduce it
* For feature requests:
	* Please clearly describe what you would like and how it would be used
	* If possible, include a link or screenshot of an example

## Debug Guidelines

If you are reporting a bug, please follow these guidlines first to see if any of these resolves the issue.
* Switch to the default theme such as Twenty Fifteen, does your problem still persist?
* De-activate all other plugins, does your problem still persist?
	* If that solves the problem, re-activate your plugins one at a time and test if the problem re-appears. If so, let us know which plugin caused it.
* Try switching to another browser. Does your problem still persist?

## Making Changes

* Fork the repository on GitHub
* Make the changes to your forked repository
* Ensure you stick to the [WordPress Coding Standards](https://codex.wordpress.org/WordPress_Coding_Standards)
* When committing, reference your issue (if present) and include a note about the fix
* Push the changes to your fork and submit a pull request to the 'master' branch of the QSM repository

## Code Documentation

* We are trying to ensure that every QSM function is documented well and follows the standards set by phpDoc going forward
* An example function can be found [here](https://gist.github.com/sunnyratilal/5308969)
* Please make sure that every function is documented so that when we update our API Documentation things don't go awry!
* If you're adding/editing a function in a class, make sure to add `@access {private|public|protected}`
* Finally, please use tabs and not spaces. The tab indent size should be 4 for all QSM code.

At this point you're waiting on us to merge your pull request. We'll review all pull requests, and make suggestions and changes if necessary.

# Additional Resources
* [General GitHub Documentation](http://help.github.com/)
* [GitHub Pull Request documentation](http://help.github.com/send-pull-requests/)
