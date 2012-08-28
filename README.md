# WordPress Functionality Plugins

This project began as a community development project for the Boston WordPress meetup.

All functionality plugins must meet the following criteria:

* Serves one specifc purpose
* Follows WordPress development guidelines
* Uses the Settings API to add settings to existing settings pages (avoid cluttering admin)
* Provides WordPress filters and actions so other developers can easily modify functionality

## How to submit a plugin

*Github beginners: [Here's a helpful article explaining pull requests](https://help.github.com/articles/using-pull-requests). This project uses the Fork & Pull collaborative development model*

1. [Fork the repository](https://help.github.com/articles/fork-a-repo)
2. Add your plugin to the repository within a new folder, such as `[repository]/my-awesome-plugin/my-awesome-plugin.php`
3. Make sure you have filled out the [Standard Plugin Information](http://codex.wordpress.org/Writing_a_Plugin#Standard_Plugin_Information)
4. [Submit a pull request](https://help.github.com/articles/creating-a-pull-request) explianing what your plugin does

The community and administrators will have an opportunity to review and comment on your plugin, and then it will become a part of the BostonWP Functionality Plugins repository!

## Why Functionality Plugins?

> Write programs that do one thing and do it well. Write programs to work together.
> *Doug McIlroy, describing the "Unix philosophy"*

As web developers we have learned that while every website has its own specific concerns and challenges, there are some problems that we confront over and over again on each new project. Functionality plugins are intended to solve those recurring problems: A functionality plugin does one thing only, and does it well.

Other kinds of plugins are very useful (such as media gallery managers, robust caching and performance tools, SEO or social media utilities), but they are designed to solve a specific problem. You may not be able to use the same type of image gallery on more than one project. The goal of this repository is to contain the smaller plugins that can be applied almost anywhere&mdash;a useful shortcode, perhaps, or a small function for custom excerpt text. You should be able to pick and choose individual plugins from this repo in order to get a head-start on your own theme or site. As long as you don't select two plugins that do the same thing, you shouldn't have any problem getting these plugins to work together within the same site.