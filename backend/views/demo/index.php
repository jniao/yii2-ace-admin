<?php

use jinxing\admin\helpers\Helper;
use jinxing\admin\widgets\MeTable;

// 定义标题和面包屑信息
$this->title = '示例页面';

$url     = Helper::getAssetUrl();
$depends = ['depends' => 'jinxing\admin\web\AdminAsset'];
$this->registerJsFile('/ueditor/ueditor.config.js', $depends);
$this->registerJsFile('/ueditor/ueditor.all.min.js', $depends);
$this->registerJsFile('/ueditor/lang/zh-cn/zh-cn.js', $depends);
$this->registerCss('
.edui-default {
    z-index: 1051 !important;
}
');

$this->registerCssFile($url . '/css/daterangepicker.css', $depends);
$this->registerCssFile($url . '/css/bootstrap-datetimepicker.css', $depends);
$this->registerJsFile($url . '/js/date-time/moment.min.js', $depends);
$this->registerJsFile($url . '/js/date-time/bootstrap-datetimepicker.min.js', $depends);
$this->registerJsFile($url . '/js/date-time/daterangepicker.min.js', $depends);
?>
<?= MeTable::widget([
    'buttonsTemplate' => '<p {options}></p>
    <div class="well">
        <div class="row">
            <form id="search-form"></form>
        </div>
    </div>',
]) ?>
<?php $this->beginBlock('javascript') ?>
    <script type="text/javascript">
        $.extend(MeTables, {
            /**
             * 定义编辑表单(函数后缀名Create)
             * 使用配置 edit: {"type": "email", "id": "user-email"}
             * edit 里面配置的信息都通过 params 传递给函数
             */
            uEditorCreate: function (params) {
                delete params.class;
                return '<div><textarea ' + this.handleParams(params) + '></textarea></div>';
            },
        });

        var m = meTables({
            title: "用户信息",
            number: false,
            buttons: {
                // 不需要导出按钮
                export: null,

                // 自定义按钮配置
                customize: {
                    icon: "ace-icon fa fa-plus-circle yellow",
                    className: "btn btn-white btn-primary btn-bold",
                    text: "新页面创建",          // 按钮文字
                    "data-func": "customize"  // 指定调用自己的那个函数
                }
            },
            searchType: "top",
            operations: {
                width: "auto",
                buttons: {
                    update: {icon: "", "button-title": "编辑"},

                    // 添加操作项，自定义按钮
                    other: {
                        title: "编辑新页面",
                        "button-title": "编辑新页面",
                        className: "btn-warning",
                        cClass: "my-edit",
                        icon: "fa-pencil-square-o",
                        sClass: "yellow"
                    }
                }
            },
            editFormParams: {
                modalClass: 'bs-example-modal-lg',
                modalDialogClass: 'modal-lg',
            },
            table: {
                columns: [
                    {
                        title: "id",
                        data: "id",
                        edit: {type: "hidden"},
                        sortable: false
                    },
                    {
                        title: "用户名",
                        data: "username",
                        search: {name: "username"},
                        edit: {type: "text", required: true, rangeLength: "[2, 255]", autocomplete: "off"},
                        sortable: false
                    },
                    {
                        title: "时间",
                        data: "time",
                        hide: true,
                        defaultContent: "",
                        edit: {type: "dateTime", class: "datetime-picker", required: true},
                        sortable: false,
                        search: {
                            class: "datetime-picker",
                            placeholder: "请选择时间"
                        }
                    },
                    {
                        title: "日期",
                        data: "date-time",
                        hide: true,
                        defaultContent: "",
                        edit: {type: "dateRange", required: true, class: "date-range-picker"},
                        sortable: false,
                        search: {
                            class: "date-range-picker",
                            style: "min-width: 200px",
                            placeholder: "请选择日期时间段"
                        }
                    },
                    {
                        title: "时间段",
                        data: "time-range",
                        hide: true,
                        defaultContent: "",
                        edit: {type: "timeRange", required: true, number: true, default: 10},
                        sortable: false,
                    },
                    {
                        title: "内容",
                        data: "content",
                        defaultContent: "",
                        edit: {
                            type: "uEditor",
                            required: true,
                            minLength: 1,
                            id: "editor-content"
                        },
                        sortable: false
                    },
                    {
                        title: "状态",
                        data: "status",
                        value: [
                            {label: "启用", value: 10},
                            {label: "停用", value: 0}
                        ],
                        edit: {type: "radio", required: true, number: true, default: 10},
                        search: {type: "select"},
                        sortable: false
                    },
                    {
                        title: "创建时间",
                        data: "created_at",
                        defaultOrder: "desc",
                        createdCell: MeTables.dateTimeString
                    },
                    {
                        title: "修改时间",
                        data: "updated_at",
                        createdCell: MeTables.dateTimeString
                    }
                ]
            }
        });

        var editor = null;
        $.extend(m, {
            // 自定义操作
            customize: function () {
                layer.open({
                    type: 2,
                    area: ["90%", "90%"],
                    title: "创建数据",
                    content: "/demo/index", // 页面url
                    anim: 2,
                    maxmin: true,
                });
            },
            // 显示的前置和后置操作
            afterShow: function (data, child) {
                // 需要手动清除值
                editor && editor.setContent("");
                if (this.action === "update" && data["content"]) {
                    // 这里需要设置实际的值
                    editor && editor.setContent(data["content"])
                }

                $("#edit-form input[name=date-time]").val("");
                $("#edit-form input[name=time]").val("");
            }
        });

        $(function () {
            m.init();

            // 初始化富文本输入框
            editor = UE.getEditor('editor-content');

            // 时间选项
            $('.datetime-picker').datetimepicker({format: 'YYYY-MM-DD HH:mm:ss'});

            // 日期时间段选择
            $("input[name=date-time]").daterangepicker({format: 'YYYY-MM-DD',}, function (start, end) {
                $("input[name=start]").val(moment(start).format("YYYY-MM-DD"))
                $("input[name=end]").val(moment(end).format("YYYY-MM-DD"))
                $("input[name=date-time]").val(moment(start).format("YYYY-MM-DD") + " - " + moment(end).format("YYYY-MM-DD"))
            });

            // 需要自己定义点击按钮配置项 监听class 规则 cClass + sTable
            $(document).on('click', '.my-edit-show-table', function () {

                // 获取到点击的这一行的数据
                var data = m.table.data()[$(this).data('row')];

                // 自定义处理方式
                if (data) {
                    layer.open({
                        type: 2,
                        area: ["90%", "90%"],
                        title: "编辑数据",
                        content: "/demo/index?id=" + data["id"], // 页面url
                        anim: 2,
                        maxmin: true,
                    })
                }
            });
        });
    </script>
<?php $this->endBlock(); ?>