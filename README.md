# Quicksave Plugin

The **Quicksave** Plugin is for [Grav CMS](http://github.com/getgrav/grav). Save your page content without needing a page refresh.

See [Official Documentation](https://www.twelvetone.tv/docs/developer-tools/grav-plugins/grav-quick-save-plugin).

## Installation

Installing the Quicksave plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install quicksave

This will install the Quicksave plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/quicksave`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `quicksave`. You can find these files on [GitHub](https://github.com/twelve-tone-llc/grav-plugin-quicksave) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/quicksave
	
> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav) and the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) to operate.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/quicksave/quicksave.yaml` to `user/config/plugins/quicksave.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
```
See [Official Documentation](https://www.twelvetone.tv/docs/developer-tools/grav-plugins/grav-quick-save-plugin) for more options.


## Usage

See [Official Documentation](https://www.twelvetone.tv/docs/developer-tools/grav-plugins/grav-quick-save-plugin)

## To Do

- [ ] i8n
- [ ] Hide button if user does not have permission
- [X] Keyboard shortcut
- [X] Create hooks instead of overriding pages.html.twig
- [X] Do not show button if new page has not been saved
- [X] Clear dirty state if only content changed


