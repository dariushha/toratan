<?php
namespace modules\opsModule\controllers;
/**
 * The modules\opsModule\controllers\commentController
 * @by Zinux Generator <b.g.dariush@gmail.com>
 */
class commentController extends \zinux\kernel\controller\baseController
{
    /**
    * The modules\opsModule\controllers\commentController::IndexAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function IndexAction() { throw new \zinux\kernel\exceptions\notFoundException; }
    /**
    * The \modules\opsModule\controllers\commentController::newAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function newAction()
    {
    }
    /**
    * The \modules\opsModule\controllers\commentController::editAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function editAction()
    {
    }
    /**
    * The \modules\opsModule\controllers\commentController::voteAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function voteAction()
    {
        \zinux\kernel\utilities\debug::_var($this->request->params, 1);
    }
    /**
    * The \modules\opsModule\controllers\commentController::markAction()
    * @by Zinux Generator <b.g.dariush@gmail.com>
    */
    public function markAction()
    {
    }
}