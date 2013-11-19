<?php
namespace modules\opsModule;
/**
* The opsModule's Bootstrapper
*/
class opsBootstrap
{
    /**
     * Checks session signin sig. and make exceptions for some routes to pass through without signing in.
     * @param \zinux\kernel\routing\request $request
     */
    public function pre_signin_check(\zinux\kernel\routing\request $request)
    {
        # if user already signed in?
        if(\core\db\models\user::IsSignedin())
            # no need for check
            return;
        # a list of exception {controller => action} which does not need signin sig.
        $signin_free_ops = array(
           "index" => "view" 
        );
        # the normalized currently requested {conttroller => action} 
        $current_request = array(
            # the pure controller name without the suffix
            strtolower($request->controller->name), 
            # the pure action name without the suffix 
            strtolower($request->action->name)
        );
        # if current request's matches with an index in the signin free list
        if(array_key_exists($current_request[0], $signin_free_ops))
        {
            # proceed to checking actions
            if($signin_free_ops[$current_request[0]] == $current_request[1])
                # if it matches, we are OK
                # no need to signin
                return;
        }
        # if the PC didn't changed in previous lines
        # we need to signin 
        # we will also pass the continue spot to auth module
        header("location: /auth/signin?continue={$request->GetURI()}");
        # halt the PHP
        exit;
    }
}