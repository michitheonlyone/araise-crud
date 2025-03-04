# Getting Started

## Quick Start
```sh
symfony new my_project_directory
```
```sh
composer require araise/crud-bundle
```

The crud bundle is currently only translated in German. Be sure you have set the locale to `de` in your `config/packages/translation.yaml`
or create your own translations. If you do, we recommend opening a Pull Request for us on [GitHub](https://github.com/araise-dev/CrudBundle/pulls) so we can add them to the bundle.
```yaml
framework:
    default_locale: de
```

On a newly installed symfony project you can do all needed configurations with this command. If you are trying to implement
the crud bundle in an existing project you can skip this step. Read the full guide and only use the steps needed for your
project.

```sh
bin/console araise:crud:setup
```

To get the forms smoothly running you should add our form theme to your twig configuration.
Add a new `form_theme` to your `config/packages/twig.yaml` like following:

```yaml
twig:
    form_themes:
        - '@araiseCrud/form_layout.html.twig'
```

The crud bundle is currently only translated in German. Be sure to have set the locale to `de` in your `config/packages/framework.yaml`
or create your own translations. Push them to us, and we will add them to the bundle.

```yaml
framework:
    default_locale: de
```

You are now ready to create entities and definitions: 

```sh
bin/console make:entity
bin/console make:definition
``` 

Do not forge to update your database schema:
```sh
bin/console doctrine:schema:update --force
```

The bundles come with 2 default migrations. If you do not want to use them, you can mark them as executed like that:
```sh
bin/console doctrine:migrations:version --add araise\\SearchBundle\\Migrations\\Version20220602150539 --no-interaction
bin/console doctrine:migrations:version --add araise\\TableBundle\\Migrations\\Version20220622145409 --no-interaction
```

Now you that have set up the CRUD-Bundle, please follow these instructions to finish the installation of the needed dependencies:
- [SearchBundle](https://araise-dev.github.io/SearchBundle)

---

# Full Guide

## Requirements

This bundle has been tested on PHP >= 8.0 and Symfony >= 6.0.
We don't guarantee that it works on lower versions.  
It presumes a fresh symfony 6.x installation following the [symfony docs](https://symfony.com/doc/current/setup.html).  

## Templates

The views of this template are based on a [Tailwind CSS](https://tailwindcss.com/) layout.
You can overwrite them at any time.  
More info about that can be found in the [Templating](templating.md) section of this documentation.

## Installation
### Composer

```
composer require araise/crud-bundle
```

### Routing
Add our routes ```config/routes/wwd_crud.yaml```

```yaml
araise_crud_bundle:
    resource: "@araiseCrudBundle/Resources/config/routing.yml"
    prefix: /
```

Here you can define a prefix for the whole crud part.

We mostly use `/admin` as often times this bundle is used as a backend. 
You can however use and configure it to whatever suits your business case. 

### Tailwind and Webpack
Add a new `form_theme` to your `twig.yaml` like this:

```yaml
twig:
    form_themes:
        - '@araiseCrud/form_layout.html.twig'
```

To give you full access over the build and look-and-feel of the application, install these dependencies in your project locally.  
To get it up and running like whatwedo, install the following: 

```sh
yarn add @tailwindcss/forms
yarn add tailwindcss postcss-loader sass-loader sass autoprefixer --dev
```

#### Tailwind
Be sure to extend tailwinds default config. You need a `primary` color and an some accent colors like `error`, `warning` and `success` color.
Furthermore, you need to add our files to the `content` section. The `@tailwindcss/forms` plugin is a basic form style resetter. 
The config is located at `tailwind.config.js`.  

If you don't already have this file, generate it with `npx tailwind init`. Here is what a config could look like:

````js
const colors = require('tailwindcss/colors')

module.exports = {
    content: [
        './assets/**/*.js',
        './templates/**/*.{html,html.twig}',
        './vendor/araise/**/*.{html,html.twig,js}',
        './var/cache/twig/**/*.php',
        './src/Definition/*.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    200: '...',
                    300: '...',
                    400: '...',
                    500: '...',
                    600: '...',
                    700: '...',
                    800: '...',
                },
                neutral: colors.slate,
                error: colors.red,
                warning: colors.orange,
                success: color.green,
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}
````

#### Webpack
Create a `postcss.config.js` file in your root directory with this content:

```js
let tailwindcss = require('tailwindcss');

module.exports = {
    plugins: [
        tailwindcss('./tailwind.config.js'),
        require('autoprefixer'),
    ]
}
```

Enable sass and postcss support in the `webpack.config.js` as follows:

```js
Encore
    .enableSassLoader()
    .enablePostCssLoader()
;
```

Your main style, for instance `assets/styles/app.scss`, should be a `sass` file.
If your file is named `app.css` rename it to `app.scss`. Also change the import in the main entrypoint file, for instance `assets/admin.js`.

```js
import './styles/app.scss';
```

Import the following styles into the `app.scss`:

```scss
@tailwind base;
@tailwind components;
@tailwind utilities;

@import "~@araise/core-bundle/styles/_tailwind.scss";
@import "~@araise/table-bundle/styles/_tailwind.scss";
```

It is **important** that you include the @araise styles **after** the tailwind styles.

Run `yarn dev`, it should end with the message `webpack compiled successfully`. 

### Prepare UI

#### Base template
Our default views extend your `templates/base.html.twig` template. To get the defaults up and running, create the file as follows:

```twig
{% extends '@araiseCrud/base.html.twig' %}
```

If you create your own template without extending ours, be sure to use the same block names and stimulus controllers.

### Dependencies
Now you that have set up the CRUD-Bundle, please follow these instructions to finish the installation of the needed dependencies:
- [SearchBundle](https://araise-dev.github.io/SearchBundle)

Done! 🎉

The araiseCrudBundle is fully installed. You should see your dashboard at: http://localhost:8000/dashboard. Now start using it!

## Use Bundle

This Bundle uses translation files, currently only german is provided though. Feel free to open a PR with new translations!
To use it in german, set your applications `default_locale` to `de` as shown in the following example:

```yaml
framework:
    default_locale: de
```
(The config file can be found under `config/packages/translation.yaml`)

### Create an entity

First, you need to create a new entity for your data.
In our example we want to create a user management system.

Use your existing `User.php` entity or create a new one with `php bin/console make:entity`.

Every CRUD managed entity should have a `__toString` method. Don't forget to create a migration or update your database according to the new entities. 
The crud bundle itself will create two tables for you: `araise_search_index` and `araise_table_filter`. 

### Create a definition

In the definition file you explain and configure your entity.
It contains all information to create your CRUD view.
You can also generate a definition with our make command: `bin/console make:definition`

More about Definitions in the [next chapter](view-configuration.md).

### try it
That's all.

```http://localhost:8000/app_user```
