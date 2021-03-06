<?php
namespace application;
/**
* The project's router
*/
class appRoutes extends \zinux\kernel\routing\routerBootstrap
{
    public function Fetch()
    {
        /**
         * Routes
         *      {/recovery/reset} to {/recovery_reset}
         * Note:
         *      This route needs to be before {$this->addRoute("^/(recovery)$2", "/auth/$1$2");}
         */
        $this->addRoute("^/(recovery)/(reset)$2", "/$1_$2$3");
        /**
         * Routes 
         *      {/signin} to {/auth/signin}
         *      {/signup} to {/auth/signup} 
         *      {/signout} to {/auth/signout}
         */
        $this->addRoute("^/(signin|signup|signout|recovery)$2", "/auth/$1$2");
        /**
         * Inorder to neutralize the next router on route {/default/index/acrhives} aka {/archives} we 
         * need to modify to original uri by explicitly addressing the {/:modules:/:controller:} in uri.
         * Roures
         *      {/archives} to {/default/index/archives}
         *      {/shared} to {/default/index/shared}
         *      {/trashes} to {/default/index/trashes}
         */
        $this->addRoute("^/(archives|shared|trashes|recent|d/)$2", "/frame/e/$1$2");
        /**
         * Routes
         *      {/(preview/)?@USERNAME(/posts|/about)} to {/profile/(preview/)?(posts|about)/USERNAME}
         * Note:
         *      This route needs to be before {$this->addRoute("^/(profile)$2", "/ops/$1$2");}
         */
        $this->addRoute("^/(preview/)?@([^/]+)/?(posts|about)?$4", "/profile/$1/$3/$2$4");
        /**
         * Routes
         *      {/send/message} to {/messages/send}
         * Note:
         *      This route needs to be before {$this->addRoute("^/(messages)$2", "/ops/$1$2");}
         */
        $this->addRoute("^/send/message$1", "/messages/send$1");
        /**
         * Routes 
         *      {/new} to {/ops/new}
         *      {/edit} to {/ops/edit} 
         *      {/view} to {/ops/view}
         *      {/delete} to {/ops/delete}
         *      {/archive} to {/ops/archive}
         *      {/share} to {/ops/share}
         *      {/profile} to {/ops/profile}
         *      {/notifications} to {/ops/notifications}
         *      {/subscribe} to {/ops/subscribe}
         *      {/unsubscribe} to {/ops/unsubscribe}
         *      {/goto} to {/ops/goto}
         *      {/messages} to {/ops/messages}
         *      {/comment} to {/ops/comment}
         *      {/fetch} to {/ops/fetch}
         *      {/vote} to {/ops/vote}
         */
        $this->addRoute("^/(new|edit|view|delete|archive|share|profile|notifications|follow|unfollow|goto|messages|comment|fetch|vote)$2", "/ops/$1$2");
        /**
         * Routes
         *      {/ops/profile/avatar/crop} to {/ops/profile/avatar_crop}
         *      {/ops/profile/avatar/view} to {/ops/profile/avatar_view}
         * Note:
         *      This route needs to be after {$this->addRoute("^/(profile)$2", "/ops/$1$2");}
         */
        $this->addRoute("^/(ops/profile/avatar)/(crop|view)$3", "$1_$2$3");
        /**
         * Routes
         *      {/ops/profile/cover/random} to {/ops/profile/randomcover}
         *      {/ops/profile/cover/remove} to {/ops/profile/removecover}
         * Note:
         *      This route needs to be after {$this->addRoute("^/(profile)$2", "/ops/$1$2");}
         */
        $this->addRoute("^/(ops/profile)/(cover)/(random|remove)$4", "$1/$3$2$4");
        /**
         * Routes
         *      {/tag/@TAG(/@WHATEVER)?/list(/@WHATEVER)?} to {/tag/list/@TAG/@WHATEVER}
         */
        $this->addRoute("^/tag/(.*[^/])/list$2", "/tag/list/$1$2");
        /**
         * Routes:
         *      What ever statments in configuration to its module
         */
        $route_pattern = "^/(";
        foreach(\zinux\kernel\application\config::GetConfig("toratan.statement") as $key => $value) { $route_pattern .= "$key|"; } $route_pattern = substr($route_pattern, 0, strlen($route_pattern) -1).")";
        $this->addRoute($route_pattern, "/statements/$1/$2");
    }
}