<?php

namespace backend\controllers;

use backend\models\Menu;
use common\helpers\Helper;
use yii\helpers\Json;

/**
 * Class    MenuController
 * @package backend\controllers
 * Desc     导航栏目管理
 * Date     2016-4-18
 */
class MenuController extends Controller
{
    public $modelClass = 'backend\models\Menu';
    /**
     * where() 查询参数配置
     * @param array $params
     * @return array
     */
    public function where($params)
    {
        return [
            'id' => '=',
            'menu_name' => 'like',
            'url' => '=',
            'action_name' => 'like',
            'status' => '=',
        ];
    }

    /**
     * actionIndex() 首页显示
     * @return string
     */
    public function actionIndex()
    {
        // 查询父级分类信息
        $parents = Menu::find()->select(['id', 'menu_name'])->where([
            'status' => 1,
            'pid' => 0
        ])->indexBy('id')->all();

        return $this->render('index', [
            'parents' => Json::encode(Helper::map($parents, 'id', 'menu_name', ['顶级分类']))
        ]);
    }

    /**
     * 处理导出显示数据
     * @param array $array
     */
    public function handleExport(&$array)
    {
        $array['created_at'] = date('Y-m-d H:i:s', $array['created_at']);
        $array['updated_at'] = date('Y-m-d H:i:s', $array['updated_at']);
    }
}
