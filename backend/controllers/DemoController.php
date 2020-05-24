<?php

namespace backend\controllers;

use backend\models\User;

/**
 * Class UserController 用户信息
 * @package backend\controllers
 */
class DemoController extends Controller
{
    /**
     * @var string 定义使用的model
     */
    public $modelClass = 'backend\models\User';

    /**
     * 定义查询字段对应处理表达式
     *
     * @return array
     */
    public function where()
    {
        return [
            [['email', 'username'], 'like'],
        ];
    }

    /**
     * 首页显示
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'status'      => User::getArrayStatus(),
            'statusColor' => User::getStatusColor(),
        ]);
    }
}

