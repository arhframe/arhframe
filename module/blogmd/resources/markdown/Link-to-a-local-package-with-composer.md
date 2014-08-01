%logo=https://cdn.tutsplus.com/net/uploads/2014/01/composer-retina-preview.png%
%title=Link to a local package with composer%
%tags=composer,dev,code%

Composer php is a great tool but you could some problems when you are developping software with it.
If you wanna link to an another local package that you are developping too and you don't want send it to github or other repositories cause it's not yet stable.

Composer doesn' give any solution to this but i found one.

Requirement
----
Before beginning you need this
 - git on your computer
 - composer

Example
----
Imagine you have two composer package, one you're actually developping and other one under development. But you want use the second one with the first one.

### Step 1
Add a new file in your second project named `.gitignore` (we need to ignore vendor folder and composer.lock)
and put this:

```text
/vendor
/composer.lock
/*.phar
```

### Step 2
In command line go to this second project and do this:
```bash
$ git init
$ git add ./
$ git commit -m "first commit"
```
now the second project can be use in the first one but you have to go to update your `composer.json` in your first project to set this new repository.

### Step 3
Add a repository in `composer.json` of first project like this:
```javascript
"repositories": [
        {
            "type": "git",
            "url": "../orange-php-full-rest"
        }
    ]
```
You will have also add in the `require` of your first project name with `dev-master` as version:
```javascript
"require": {
        "<vendorname second project>/<name second project>": "dev-master"
    }
```
### Step 4
Now simply run `php composer.phar update` in your first project and your second project will be include
And it's done.

### Tips
If you modify your second project you need to commit your changes with this `git add ./ & git commit -m "update"` and do a  `php composer.phar update` in your first project</name></vendorname>