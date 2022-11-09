# Blackfire.io: Revealing Performance Secrets with Profiling

Well hi there! This repository holds the code and script
for the [Blackfire.io: Revealing Performance Secrets with Profiling](https://symfonycasts.com/screencast/blackfire) course on SymfonyCasts.

## Setup

If you've just downloaded the code, congratulations!!

Otherwise, use 

```shell
git clone git@github.com:platformsh-templates/sfcon2022-symfony-bigfoot-workshop.git
```

To get it working, pour some coffee or tea, and
follow these steps:

**Use Composer**

Make sure you have [Composer installed](https://getcomposer.org/download/)


<details>
<summary>Using Symfony server</summary>
<!-- <blockquote>
<br/> -->

**Download Composer dependencies**

```
composer install
```

You may alternatively need to run `php composer.phar install`, depending
on how you installed Composer.


**Configure the .env (or .env.local) File**

Open the `.env` file and make any adjustments you need - specifically
`DATABASE_URL`. Or, if you want, you can create a `.env.local` file
and *override* any configuration you need there (instead of changing
`.env` directly).

> **Note:**
>
> if you don't have PostgreSQL installed locally, you can use provided PostgreSQL container
> by running command 
> ```
> docker-compose up -d 
> ```
> then configure your .env `DATABASE_URL` with 
> ```
> DATABASE_HOST=127.0.0.1
> DATABASE_PORT=5432
> DATABASE_NAME=app
> DATABASE_USER=symfony
> DATABASE_PASSWORD=ChangeMe
> DATABASE_URL="postgresql://${DATABASE_USER}:${DATABASE_PASSWORD}@${DATABASE_HOST}:${DATABASE_PORT}/${DATABASE_NAME}?serverVersion=13&charset=utf8"
> ```

**Set up the Database**

Again, make sure `.env` is set up for your computer. Then, create
the database & tables!

```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

If you get an error that the database exists, that should
be ok. But if you have problems, completely drop the
database (`doctrine:database:drop --force`) and try again.

**Start the development web server**

You can use Nginx or Apache, but Symfony's local web server
works even better.

To install the Symfony local web server, follow
"Downloading the Symfony client" instructions found
here: https://symfony.com/download - you only need to do this
once on your system.

Then, to start the web server, open a terminal, move into the
project, and run:

```
symfony serve
```

(If this is your first time using this command, you may see an
error that you need to run `symfony server:ca:install` first).

Now check out the site at `https://localhost:8000`
</details>

<details>
<summary>Using DDev from scratch</summary>
Ddev provides an integration with Platform.sh that makes it simple to develop Symfony locally. 
Check the [providers documentation](https://ddev.readthedocs.io/en/latest/users/providers/platform/) for the most up-to-date information.

Steps are as follows:

1. run `git clone git@github.com:platformsh-templates/sfcon2022-symfony-bigfoot-workshop.git sfcon-bigfoot-workshop`
1. run `symfony composer install`
1. run `symfony project:init`
1. run `git add .platform.app.yaml .platform/services.yaml .platform/routes.yaml && git commit -m "Add Platform.sh configuration"`
1. run `symfony cloud:create`
   1. _Login via browser: yes_
   1. _Choose your organization_
   1. _Choose project title: SFCon2022 - Symfony Bigfoot workshop_
   1. _Choose your region: [fr-3.platform.sh] Gravelines, France (OVH) [58 gC02eq/kWh]_
   1. _Choose plan : 0 (Developpement)_
   1. _Choose number of (active) environments (default 3)_
   1. _Choose storage (default 5GiB)_
   1. _Choose default branch (default “main”) : main_
   1. _Set the new project "SFCon2022 - Symfony Bigfoot workshop" as the remote for this repository?: y_
   1. _Given price is an estimation after the free trial period: you can continue_
1. run `symfony deploy`
1. Initialize data on Platform.sh project
   1. run `symfony ssh`
   1. [option] run `$ php bin/console doctrine:schema:update --dump-sql`
   1. run `php bin/console doctrine:schema:update --force`
   1. run `php bin/console doctrine:fixtures:load -e dev`   
   1. `exit` from Platform.sh container
1. [Install ddev](https://ddev.readthedocs.io/en/stable/#installation).
1. run `ddev config`
    1. _Project name (sfcon-bigfoot-workshop): \<enter\>_
    1. _Docroot Location (\_www): public_
    1. _Project Type [backdrop, craftcms, drupal10, drupal6, drupal7, drupal8, drupal9, laravel, magento, magento2, php, shopware6, typo3, wordpress] (php): \<enter\>_
1. Check that library `jq` is installed locally
    1. Mac: `brew list | grep jq`  → jq
    1. Windows: `winget list -q jq`
    1. If not, install it
        1. Mac : `brew install jq`
        1. Windows: `chocolatey install jq`
1. Create a <a href="https://docs.platform.sh/administration/cli/api-tokens.html#get-a-token" target="_blank">Platform.sh API Token</a> and keep it
1. run `ddev get platformsh/ddev-platformsh` _(this will get copy production configs to setup Ddev container)_
    1. _Please enter your platform.sh token: \<Platform.sh APIToken\>_
    1. _Please enter your platform.sh project ID (like '6k4ypl5iendqd'): \<projectID\>_
    1. _Please enter your platform.sh project environment (like 'main'): main_
1. run `ddev pull platform` _(this will pull data from Platform.sh project)_
    1. _https://ddev.readthedocs.io/en/stable/users/details/opting-in
       Permission to beam up? [Y/n] (yes): \<enter\>_
1. Go on <a href="https://sfcon-bigfoot1-workshop.ddev.site/" target="_blank">https://sfcon-bigfoot1-workshop.ddev.site/</a>
1. When you have finished with your work, run `ddev stop` and `ddev poweroff`.

> **Note:**
>
> PHP 8.1 is needed when using latest 6.x version of this project.<br>
> So please change/check ddev .ddev/config.platformsh.yaml file and use PHP version 8.1 or higher <br>
> ```
> // .ddev/config.platformsh.yaml
> php_version: "8.1"
> ```
> Then use `ddev restart`
</details>



<details>
<summary>Using DDEV with an existing Bigfoot project deployed on Platform.sh</summary>
Ddev provides an integration with Platform.sh that makes it simple to develop Symfony locally. 
Check the [providers documentation](https://ddev.readthedocs.io/en/latest/users/providers/platform/) for the most up-to-date information.

Steps are as follows:

1. run `symfony get <projectID>`
1. run `symfony composer install`
1. [Install ddev](https://ddev.readthedocs.io/en/stable/#installation).
1. run `ddev config`
   1. _Project name (sfcon-bigfoot1-workshop): \<enter\>_
   1. _Docroot Location (\_www): public_ 
   1. _Project Type [backdrop, craftcms, drupal10, drupal6, drupal7, drupal8, drupal9, laravel, magento, magento2, php, shopware6, typo3, wordpress] (php): \<enter\>_
1. Check that library `jq` is installed locally
   1. Mac: `brew list | grep jq`  → jq
   1. Windows: `winget list -q jq`
   1. If not, install it
        1. Mac : `brew install jq`
        1. Windows: `chocolatey install jq`
1. Create a <a href="https://docs.platform.sh/administration/cli/api-tokens.html#get-a-token" target="_blank">Platform.sh API Token</a> and keep it
1. run `ddev get platformsh/ddev-platformsh` _(this will get copy production configs to setup Ddev container)_
    1. _Please enter your platform.sh token: \<Platform.sh APIToken\>_
    1. _Please enter your platform.sh project ID (like '6k4ypl5iendqd'): \<projectID\>_
    1. _Please enter your platform.sh project environment (like 'main'): main_
1. run `ddev pull platform` _(this will pull data from Platform.sh project)_
   1. _https://ddev.readthedocs.io/en/stable/users/details/opting-in
       Permission to beam up? [Y/n] (yes): \<enter\>_
1. Go on <a href="https://sfcon-bigfoot1-workshop.ddev.site/" target="_blank">https://sfcon-bigfoot1-workshop.ddev.site/</a>
1. When you have finished with your work, run `ddev stop` and `ddev poweroff`.

> **Note:**
>
> PHP 8.1 is needed when using latest 6.x version of this project.<br>
> So please change/check ddev .ddev/config.platformsh.yaml file and use PHP version 8.1 or higher <br>
> ```
> // .ddev/config.platformsh.yaml
> php_version: "8.1"
> ```
> Then use `ddev restart`
</details>

Have fun!

## Have Ideas, Feedback or an Issue?

If you have suggestions or questions, please feel free to
open an issue on this repository or comment on the course
itself. We're watching both :).

## Thanks!

And as always, thanks so much for your support and letting
us do what we love!

<3 Your friends at SymfonyCasts
