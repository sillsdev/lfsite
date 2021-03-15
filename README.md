# web-languageforge #

[Language Forge](https://languageforge.org) is an online service to collaborate on building a dictionary.

## Users ##

To use **Language Forge** go to [languageforge.org](https://languageforge.org).

### User Problems ###

To report an issue using the **Language Forge** service, email "languageforgeissues @ sil dot org".

## Special Thanks To ##

For end-to-end test automation:

![BrowserStack Logo](readme_images/browserstack-logo.png "BrowserStack")

For error reporting:

[![Bugsnag logo](readme_images/bugsnag-logo.png "Bugsnag")](https://bugsnag.com/blog/bugsnag-loves-open-source)

## Developers ##

We use [Gitflow](http://nvie.com/posts/a-successful-git-branching-model/) with two modifications:

- The Gitflow **master** branch is our **live** branch.
- The Gitflow **develop** branch is our **master** branch. All pull requests go against **master**.

We merge from **master** to testing (**qa** branch) then ship from **qa** to **live**.

| Site            | Master Branch | QA Branch | Live Branch |
| --------------- | ------------- | --------- | ----------- |
| Language Forge  | `master` | `lf-qa` | `lf-live` |

### Builds ###

Status of builds from our continuous integration (CI) [server](https://build.palaso.org):

| Site            | Master Unit | Master E2E | QA | Live |
| --------------- | ----------- | ---------- | -- | ---- |
| Language Forge  | ![Build Status](https://build.palaso.org/app/rest/builds/buildType:(id:bt372)/statusIcon) | in transition | ![Build Status](https://build.palaso.org/app/rest/builds/buildType:(id:LanguageForge_LanguageForgeQa)/statusIcon) | ![Build Status](https://build.palaso.org/app/rest/builds/buildType:(id:LanguageForge_LanguageForgeLive)/statusIcon) |

Successful builds from our CI server deploy to:

| Site            | QA | Live |
| --------------- | -- | ---- |
| Language Forge  | [qa.languageforge.org](https://qa.languageforge.org) | [languageforge.org](https://languageforge.org) |

## Style Guides ##

PHP code conforms to [PSR-2](http://www.php-fig.org/psr/psr-2/).

- Add `php-cs-fixer` globally installed with *composer* (http://cs.sensiolabs.org/). Here is how to add it to **PhpStorm** (https://hackernoon.com/how-to-configure-phpstorm-to-use-php-cs-fixer-1844991e521f). Use it with the parameters `fix --verbose "$FileDir$/$FileName$"`.

JavaScript code conforms to [AirBNB JS style guide](https://github.com/airbnb/javascript).

- Using PhpStorm with JSCS helps a lot with automating this (see the section below on PhpStorm [Coding Standard and Style](#coding-standard-and-style)).
- We plan to use [Prettier](https://prettier.io/) with pre-commit hook after re-writing the whole repo with Prettier first.

### TypeScript Style Guide ###

Our code base has moved from JavaScript to [**TypeScript**](https://www.typescriptlang.org).

> Note: this repo is currently AngularJS (1.6) not Angular (2+).

Our TypeScript follows the [Angular Style Guide](https://angular.io/guide/styleguide). This is opinionated not only about things like file name conventions but also file and folder structure. This is an appropriate time to change structure and file names since most file contents will be changed anyway. The reason for following this is to make it easier, not only for new developers to the project (like the FLEx team and hired developers) but also to change to Angular (2+) later.

To this end you'll also want to be familiar with [Upgrading from AngularJS](https://angular.io/guide/upgrade) particularly the [Preparation](https://angular.io/guide/upgrade#preparation) section.

We are expecting that TypeScript will help us get things right from the beginning (catching things even as you type) as well as maintenance. We are expecting that it will be an easier transition for the FLEx team and that they will be able to help us with good typing, interfaces and class design.

Other useful resources:

- [x] [angularjs-styleguide/typescript at master · toddmotto/angularjs-styleguide](https://github.com/toddmotto/angularjs-styleguide/tree/master/typescript#stateless-components)
- [x] [AngularJS 1.x with TypeScript (or ES6) Best Practices by Martin McWhorter on CodePen](https://codepen.io/martinmcwhorter/post/angularjs-1-x-with-typescript-or-es6-best-practices)
- [x] [What is best practice to create an AngularJS 1.5 component in Typescript? - Stack Overflow](https://stackoverflow.com/questions/35451652/what-is-best-practice-to-create-an-angularjs-1-5-component-in-typescript)
- [x] [Don't Panic: Using ui-router as a Component Router](http://dontpanic.42.nl/2016/07/using-ui-router-as-component-router.html)
- [x] [Lifecycle hooks in Angular 1.5](https://toddmotto.com/angular-1-5-lifecycle-hooks#onchanges)

## Recommended Development Environment ##

### Docker ###

1. Install [Docker](https://www.docker.com/get-started) (Linux users will need some additional steps, please visit https://docs.docker.com/compose/install for info on install the engine and compose)
1. Install [Make](https://www.gnu.org/software/make/).  This is actually optional but simplifies things a bit.
1. Clone the repo:  `git clone https://github.com/sillsdev/web-languageforge`
1. `cd web-languageforge/docker`
1. First time only (this will establish a default user): `make db-reset`
1. `make`

#### Testing the Installation

1. Within any browser, navigate to https://localhost
1. Continue through any certificate warnings
1. You should see a landing page, click "Login"
1. Use `admin` and `password` to get in (if that user is not recognized, you'll need to `make db-reset` and then try again)

#### If you would like to run the E2E tests

1. `make build e2e`
1. Individual test results will appear in your terminal but if you'd like to watch them in real-time, simply VNC into the running tests via `localhost:5900`, e.g., Mac OSX users simply `open vnc://localhost:5900` and use `secret` as the password.  Other operating systems may require installing a separate VNC Viewer tool.

#### If you would like to run the unit tests

1. `make tests`
1. Test results will appear in your terminal

#### Viewing logs

1. `make logs` will show logs from any runnign contianers, results can be filtered by simply grepping, e.g., `make logs | grep lf-ui`

#### Cleanup

1. `make clean` is the most common, it shuts down and cleans up running containers
1. less commonly, if you need to blow away shared artifacts from previous runs, simply `make clean-volumes`
1. rarely needed but for a "start from scratch" environment, `make clean-powerwash`.

#### Language Forge Configuration File ####

If you are working with FLEx Send and Receive feature, manually edit the Language Forge config file

``` bash
sudo gedit /etc/languageforge/conf/sendreceive.conf
```

and modify PhpSourcePath to

``` bash
PhpSourcePath = /var/www/languageforge.org/htdocs
```

-------------------------------

## Installing IDEs and Debugger ##

### Eclipse ###

Install Oracle Java JDK 8

``` bash
sudo add-apt-repository ppa:webupd8team/java
sudo apt-get update
sudo apt-get install oracle-java8-installer oracle-java8-set-default
```

Download the [Eclipse](http://www.eclipse.org/downloads/) installer, extract the tar folder and run the installer.

``` bash
tar xvf eclipse-inst-linux64.tar.gz
cd eclipse-installer
./eclipse-inst
```

From the installer, select **Eclipse IDE for PHP Developers**

Create a launcher shortcut from your *home* directory

``` bash
gedit .local/share/applications/eclipse.desktop
```

Replacing your *USERNAME*, paste the content below and save

``` bash
[Desktop Entry]
Name=Eclipse
Type=Application
Exec=/home/USERNAME/eclipse/php-neon/eclipse/eclipse
Terminal=false
Icon=/home/USERNAME/eclipse/php-neon/eclipse/icon.xpm
Comment=Integrated Development Environment
NoDisplay=false
Categories=Development;IDE;
Name[en]=Eclipse
```

Even though we no longer use Eclipse for web development, we [install](https://marketplace.eclipse.org/content/monjadb) the MonjaDB plugin for browsing and updating MongoDB.

Once the MonjaDB plugin is installed, access `MonjaDB` from the Eclipse menu and select `Connect`. Set the database name to `scriptureforge` (named this for legacy reasons). Keep the other default settings and click `OK` and you should see the contents of the database.

### PhpStorm ###

Download [PhpStorm](https://www.jetbrains.com/phpstorm/download/#section=linux-version), extract the tar file and install.  You may need to modify newer version numbers accordingly...

``` bash
tar xvf PhpStorm-2016.2.1.tar.gz
sudo mv PhpStorm-162.1889.1/  /opt/phpstorm
sudo ln -s /opt/phpstorm/bin/phpstorm.sh /usr/local/bin/phpstorm
# launch
phpstorm
```

LSDev members can contact their team lead to get the SIL license information.  PhpStorm also has an option *Evaluate for free for 30 days*.

#### Coding Standard and Style ####

[Download](https://plugins.jetbrains.com/plugin/7294) the PhpStorm plugin for EditorConfig and then install:

**File** --> **Settings** --> **Plugins** --> **Install plugin from disk**

This uses the `.editorconfig` file at the root folder to enforce coding standards.

Also enable JSCS:

**File** -> **Settings** -> **Languages & Frameworks** -> **JavaScript** --> **Code Quality Tools**  --> **JSCS**

Set the *Enable* checkbox
Set *Node interpreter* to `usr/local/bin/node`
Set *JSCS package* to `/usr/local/bin/jscs`
Set *Search for config(s)* radio button to `.jscsrc or .jscs.json`
Set the *Code style preset* dropdown to `Airbnb`

Modify `/usr/local/lib/node_modules/jscs/presets/airbnb.json` and change `"requireTrailingComma": { "ignoreSingleLine": true }` to
`"disallowTrailingComma": true,`

#### Creating the PhpStorm Project ####

Launch PhpStorm.

Click **Create New Project from Existing Files**. Leave the default option (Web server is installed locally, source files are located under its document root) and click **Next**.

 From the **Create New Project: Choose Project Directory** dialog,  browse to the `web-languageforge` directory, then mark it as **Project Root** (using the `Project Root` button in the toolbar) and click **Next**.

From the **Add Local Server** dialog set
Name: `localhost`
Web server root URL: `http://localhost`
--> **Next** --> **Finish**

### Xdebug ###

The Ansible script should automatically install and configure php-xdebug for you. If using VS Code, a debug extension is included in the recommended extensions to install for this project.

#### Xdebug helper Chrome extension ####

Install the [Xdebug helper](https://chrome.google.com/webstore/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc) extension which adds a bug icon to the top right area of Chrome extensions.

Right-click to select **Options** and set **IDE key**

- PhpStorm PHPSTORM

When it's time to Debug, check that the bug icon is green for **Debug**.

Then, from PhpStorm, click the telephone icon near the top right for *Start Listening for PHP Connections*.

Reference for [Integrating Xdebug with PhpStorm](https://www.jetbrains.com/help/phpstorm/2016.2/configuring-xdebug.html#integrationWithProduct).

### LiveReload ###

#### LiveReload Chrome extension ####

Install the [LiveReload](https://chrome.google.com/webstore/detail/livereload/jnihajbhpnppcggbcgedagnkighmdlei?hl=en-US) extension.

Then from PhpStorm, click
**View**->**Tool Windows** -> **Gulp**

When you want LiveReload running, double-click the **reload** or **build-webpack:watch** Gulp task.
Then in the LiveReload chrome extension, click to enable it.  A solid dot in the circle means the plugin is connected. Now when an applicable source file is changed and saved, it should trigger an automate page reload in the browser.

### Visual Studio Code ###

Visual Studio Code is a simple, free, cross-platform code editor. You can download VS Code from [here](https://code.visualstudio.com/).

The first time you open VS Code in the `web-languageforge` directory, it will recommend a list of extensions that are useful for developing **xForge** apps.

Build tasks have been setup to work on both and Linux. Tasks are available for performing webpack build, sass build, and npm install. Tasks are defined in the `.vscode/tasks.json` file.

Chrome debugging has also been configured. Launch configurations are defined in the `.vscode/launch.json` file.

## Testing ##

### PHP Unit Tests ###

Unit testing currently uses [PHPUnit](https://phpunit.de/) which was already installed by composer. To run them use `gulp test-php`.

### TypeScript Unit Tests ###

`gulp test-ts-lf` or `gulp test-ts-sf`

If you are doing a lot of testing use

`gulp test-ts-lf:watch` or `gulp test-ts-sf:watch`

TypeScript unit test spec files live in the `src` folder next to the relevant source file and have the file extension `.spec.ts`. Only E2E test specs will be in the `test` folder.

### End-to-End (E2E) Tests ###

E2E tests live in the `test` folder and have the file extension `.e2e-spec.ts`.

#### Install/Update Webdriver ####

From the `web-languageforge` directory

``` bash
npm install
gulp test-e2e-webdriver_update
```

#### E2E Test Run ####

From the *web-languageforge* directory, start **webdriver** in one terminal:

```` bash
gulp test-e2e-webdriver_standalone
````

Then to run **languageforge** tests in another terminal:

```` bash
./rune2e.sh lf
````

(If you get an error messages like `Error: ECONNREFUSED connect ECONNREFUSED 127.0.0.1:4444` you probably forgot to start the **webdriver**, see above)

To test a certain test spec, add a parameter `--specs [spec name]`.  For example,

``` bash
./rune2e.sh lf --specs lexicon-new-project
```

will run the the *lexicon-new-project.e2e-spec.ts* tests on **languageforge**.

To debug the tests:
- Do at least one of the following:
  * If you are going to debug in VSCode, place breakpoints in the tests.
  * Place breakpoints in your code (`debugger;` statements).
  * To pause the debugger on the first test failure, go to `test/app/protractorConf.js` and uncomment the line that adds the `pauseOnFailure` reporter.
- Start the tests with `./rune2e.sh`. Wait for the tests to actually start running before moving to the next steps.
- To debug in Chrome, go to `chrome://inspect/#devices`. Under "Remote Target" click to inspect the Node.js process.
- To debug in VSCode, select the "Node debugger" debug configuration and run it.

Unfortunately, debugging the e2e tests does not currently work very well because of the way WebDriver handles control flow.

## Resetting MongoDB ##

If you want to _start over_ with your mongo database, you can use the factory reset script like so (this will delete all data in the mongodb):

```` bash
cd scripts/tools
./FactoryReset.php run
````
After a fresh factory reset, there is one user.  username: admin password: password

## Libraries used ##

[lamejs](https://github.com/zhuker/lamejs) is used for encoding recorded audio and is based on [LAME](http://lame.sourceforge.net/), which is licensed under the terms of [the LGPL](https://www.gnu.org/licenses/old-licenses/lgpl-2.0.html).
