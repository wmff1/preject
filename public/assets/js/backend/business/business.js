
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 获取ids参数
            // var ids = Fast.api.query('ids') ? Fast.api.query('ids') : 0;

            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'business/business/index',
                    apply_url: `business/business/apply`,
                    share_url: `business/business/share`,
                    del_url: 'business/business/del',
                    table: 'business',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        { checkbox: true },
                        { field: 'id', title: __('Id') },
                        { field: 'nickname', title: __('Nickname'), operate: 'LIKE' },
                        { field: 'mobile_text', title: __('Mobile'), operate: 'LIKE' },
                        { field: 'avatar', title: __('Avatar'), operate: 'LIKE', events: Table.api.events.image, formatter: Table.api.formatter.image },
                        { field: 'gender', title: __('Gender'), searchList: { "0": __('保密'), "1": __('男'), "2": __('女') }, formatter: Table.api.formatter.normal },
                        { field: 'source.name', title: __('Sourceid') },
                        { field: 'province_text', title: __('Province'), operate: 'LIKE' },
                        { field: 'city_text', title: __('City'), operate: 'LIKE' },
                        { field: 'district_text', title: __('District'), operate: 'LIKE' },
                        { field: 'adminid', title: __('Adminid') },
                        { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            //增添自定义的按钮
                            buttons: [
                                // 申请
                                {
                                    name: 'apply',
                                    title: '申请',
                                    icon: 'fa fa-hand-paper-o',
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    url:$.fn.bootstrapTable.defaults.extend.apply_url,
                                    confirm: '是否申请客户',
                                    extend: "data-toggle='tooltip'",
                                    success: function (data) {
                                        //ajax成功会刷新一下table数据列表
                                        table.bootstrapTable('refresh');
                                    }
                                },
                                // 分配
                                {
                                    name: 'share',
                                    title: '分配',
                                    icon: 'fa fa-users',
                                    classname: 'btn btn-xs btn-success btn-magic btn-dialog',
                                    url: $.fn.bootstrapTable.defaults.extend.share_url,
                                    extend: "data-toggle='tooltip'",
                                    success: function (data) {
                                        //ajax成功会刷新一下table数据列表
                                        table.bootstrapTable('refresh');
                                    }
                                }
                            ]
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            $(document).on('click', '.btn-apply', function () {
                // 弹出确认对话框
                Layer.confirm(__('是否确认申请'), { icon: 3, title: __('Warning'), shadeClose: true }, function (index) {
                    //获取当前选中id
                    var ids = Table.api.selectedids(table)

                    //发送ajax请求
                    Backend.api.ajax(
                        //请求地址
                        { url: $.fn.bootstrapTable.defaults.extend.apply_url + `?ids=${ids}` },
                        //回调函数
                        function () {

                            // 关闭窗口
                            Layer.close(index)

                            //刷新数据表格
                            table.bootstrapTable('refresh')
                        }
                    )
                })
            })
            // 分配
            $(document).on("click", ".btn-share", function () {
                var ids = Table.api.selectedids(table);
                Fast.api.open($.fn.bootstrapTable.defaults.extend.share_url + `?ids=${ids}`, '分配', {})
            });
        },
        apply: function () {
            Controller.api.bindevent();
        },
        // 分配
        share: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
