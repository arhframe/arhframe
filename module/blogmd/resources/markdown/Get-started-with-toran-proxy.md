%logo=http://arthurh.fr/cache/image/blogmd/md/1df8bcb38601f36791c85a0cb33359db.png%
%title=Get started with toran proxy%
%tags=dev,composer,toran proxy,php%

Toran proxy is a Composer proxy for speed and reliability made by [Composer](https://getcomposer.org)'s creator himself (take a look to his [article](http://seld.be/notes/toran-proxy-and-the-future-of-composer). It is destined to replace satis and be stronger than this one. But it will also make the composer's creator earn money for his job (And if you want use it, please pay him).

So i will give you few instructions to make this proxy work in a webserver and have a better security to access privately (thanks to [Symfony2](http://symfony.com/) ).

-------------------------------------

Installing Toran proxy
---------------------------
First you need to download toran proxy, choose personal use only if you really will use it personally (Yeah i want him to have his money to finish the job), for my part i'm really use it for my personal projects and i never earn money for this kind of project so...
Ok let's go on his website: [https://toranproxy.com](https://toranproxy.com/) choose your license and download it.
Now you can move this project inside your webserver folder. 
Let's go on in his `web/app.php`, toran proxy will be installed by his own just follow his few instructions.
Now you will have this kind of page:

![Toran.arhurh.fr](http://arthurh.fr/cache/image/blogmd/md/d55da1f6b70bfdbded8d5e0287bc4894.png)

You can now go to `Private Repositories` tab and add a repositories you want.

Note about `artifact` repo type, it's seem very useful but he is not well documented (I had to look at composer source to know how it's work). For this kind of repo you have to give an absolute directory path and toran will search all `.zip` and `.tar` files inside (he will look at all subdirectory inside too) and look if they have a `composer.json`, if they have he will report in his index. I tried it and it's really works well. Coupled to a webdav can be really interesting.

But your toran proxy isn't secure and everyone can access to this website.

Secure your toran proxy
----------
I choose to secure my toran proxy with `basic auth` and create two accounts:
 - one for administrate my toran proxy
 - An another one to pull private package

To do this simply go in you project folder and modify this file `app/config/security.yml` and change is like this:
```yaml
security:
    encoders:
        Symfony\Component\Security\Core\User\User: plaintext

    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: [ROLE_USER, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]

    providers:
        in_memory:
            memory:
                users:
                    #the two users
                    user:  { password: changePassword2, roles: [ 'ROLE_USER' ] }
                    admin: { password: changePassword, roles: [ 'ROLE_ADMIN' ] }

    firewalls:
        dev:
            pattern:  ^/(_(profiler|wdt)|css|images|js)/
            security: false

        login:
            pattern:  ^/demo/secured/login$
            security: false
        secured_area:
            pattern:    ^/
            anonymous: ~ #anonymous user can't access it
            http_basic:
                realm: "Toran proxy access"

    access_control:
        - { path: ^/, roles: ROLE_ADMIN } #only user with Role admin can access to all the website
        - { path: ^/repo, roles: ROLE_USER } #every user can access to repo to pull package
```

For better security i recommend to use `ssl` on your webserver

This security will change how to get packages from your private server, simply add a repository in your `composer.json` like this for example:

```json
{
    "repositories": [
        {"type": "composer", "url": "http://user:changePassword2@toran.arthurh.fr/repo/private/"}
    ]
}
```
And you're done, i hope it will help some people to get start with toran.