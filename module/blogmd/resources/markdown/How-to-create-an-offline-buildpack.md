%logo=http://arthurh.fr/cache/image/blogmd/md/58e07a24f7385d7751697644d64fd8dd.png%
%title=How to create an offline buildpack%
%tags=dev,cloudfoundry,buildpack%

One of our problems is to have custom buildpack which doesn't need internet to find his dependencies.
To deal with this issue cloudfoundry create 2 modules:
 - [buildpack packager](https://github.com/cf-buildpacks/buildpack-packager) wich download all your dependencies you set and make a zip file of your offline buildpack.
 - [compile extension](https://github.com/cf-buildpacks/compile-extensions) is a module wich override curl command to find in cache if there is the dependencies and fallback to internet in other case
 
## Prerequirements
  - GIT
  - Cygwin (if on windows)
  - CNTLM (if under proxy ntlm)
  
### If under proxy do this:

for git and cntlm run in terminal:

```bash
git config --global http.proxy http://localhost:3128
git config --global https.proxy http://localhost:3128
```
for curl and cntlm run in terminal

```bash
echo "proxy=http://localhost:3128" > ~/.curlrc
```
### Tips for windows user:
you can have problem with End Of Line, you MUST HAVE unix EOL in your script file but git when you pull or clone with windows he will all EOL in windows format to bypass this do that in your terminal:

```bash
git config --system core.autocrlf false
```

-------------------------------

## Step 1
run your favorite `terminal` (if on windows prefer use cygwin)
go to your buildpack source file with your terminal
we will clone the both module from cloudfoundry
in terminal:

```bash
git clone https://github.com/cf-buildpacks/buildpack-packager
git clone https://github.com/cf-buildpacks/compile-extensions
```


## Step 2
create a new file insinde your `bin` directory called `package` and follow this content template:
```bash
#!/usr/bin/env bash
language='&lt;your buildpack language&gt;'
#to get url dependencies a little reverse engineering is needed, just look at pulled url inside your `bin/compile` file
dependencies=(
  'url of your first dependencies'
  'url of your second dependencies'
  '...'
)
excluded_files=(
  '.git/'
  '.gitignore'
  '.gitmodules'
  'repos/'
  'cf_spec/'
  'log/'
  'cf.Gemfile'
  'cf.Gemfile.lock'
  'bin/package'
  'buildpack-packager/'
)
BIN="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
source $BIN/../buildpack-packager/lib/packager
```


## Step 3
you also need to modify your `bin/compile` put this in the beginning of your file:
```bash
# CF Common
BUILDPACK_PATH=$(cd $(dirname $0); cd ..; pwd)
export BUILDPACK_PATH
source $BUILDPACK_PATH/compile-extensions/lib/common
# END CF Common
```


## Step 4
if you are under windows do this before `dos2unix bin/*` in your terminal
Compile your offline package by doing this `bin/package offline` in your terminal
At the end you will have a `zip` file with suffix offline you can create buildpack with this zip file

## Step 5
Create a buildpack in cf with this command: 

```
cf create-buildpack <name of your buildpack>-offline <local path to your new zip file> 1
```

## Step 6
Take a coffee, you've done :)