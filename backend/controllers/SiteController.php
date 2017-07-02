<?php

namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UnauthorizedHttpException;
use common\models\AdminForm;
use common\helpers\Helper;
use backend\models\Menu;
use backend\models\Admin;

/**
 * Class SiteController 后台首页处理
 * @package backend\controllers
 */
class SiteController extends \yii\web\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow'   => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'system', 'grid', 'get-data', 'update', 'create'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => ['class' => 'yii\web\ErrorAction',],
        ];
    }



    /**
     * actionIndex() 管理员登录欢迎页
     * @return string
     * @throws UnauthorizedHttpException
     */
    public function actionIndex()
    {
        $this->layout = false;

        // 获取用户导航栏信息
        $menus = Menu::getUserMenus(Yii::$app->user->id);
        if ($menus) {
            Yii::$app->view->params['user']  = Yii::$app->getUser()->identity;
            Yii::$app->view->params['menus'] = $menus;
            // 加载视图
            return $this->render('index');
        } else {
            throw new UnauthorizedHttpException('对不起，您还没获得显示导航栏目权限!');
        }
    }

    /**
     * 显示首页系统信息
     * @return string
     */
    public function actionSystem()
    {
        // 用户信息
        Yii::$app->view->params['user']  = Yii::$app->getUser()->identity;

        // 系统信息
        $system = explode(' ', php_uname());
        $system = $system[0] .'&nbsp;' . ('/' == DIRECTORY_SEPARATOR ? $system[2] : $system[1]);

        // MySql版本
        $version = Yii::$app->db->createCommand('SELECT VERSION() AS `version`')->queryOne();

        return $this->render('system', [
            'system' => $system,                                        // 系统信息
            'yii'    => 'Yii '. Yii::getVersion(),                      // Yii 版本
            'php'    => 'PHP '. PHP_VERSION,                            // PHP 版本
            'server' => $_SERVER['SERVER_SOFTWARE'],                    // 服务器信息
            'mysql'  => 'MySQL '.($version ? $version['version'] : ''), // Mysql版本
            'upload' => ini_get('upload_max_filesize'),                 // 上传文件大小
        ]);
    }

    /**
     * 后台管理员登录
     * @return string|\yii\web\Response
     */
    public function actionLogin()
    {
        $this->layout = 'login.php';
        if ( ! Yii::$app->user->isGuest) {return $this->goHome();}
        $model = new AdminForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // 生成缓存导航栏文件
            Menu::setNavigation(Yii::$app->user->id);
            return $this->goBack(); // 到首页去
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * actionLogout() 后台管理员退出
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        // 用户退出修改登录时间
        $admin = Admin::findOne(Yii::$app->user->id);
        if ($admin) {
            $admin->last_time = time();
            $admin->last_ip   = Helper::getIpAddress();
            $admin->save();
        }

        Yii::$app->user->logout();
        return $this->goHome();
    }
}
