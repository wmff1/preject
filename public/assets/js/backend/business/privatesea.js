define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'business/privatesea/index' + location.search,
                    add_url: 'business/privatesea/add',
                    del_url: 'business/privatesea/del',
                    info_url: 'business/lists/info',
                    recovery_url: 'business/privatesea/recovery',
                    table: 'business',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        { checkbox: true },
                        { field: 'id', title: __('Id') },
                        { field: 'mobile', title: __('Mobile'), operate: 'LIKE' },
                        { field: 'nickname', title: __('Nickname'), operate: 'LIKE' },
                        { field: 'gender', title: __('Gender'), searchList: { "0": __('保密'), "1": __('男'), "2": __('女') }, formatter: Table.api.formatter.normal },
                        { field: 'admin_name.nickname', title: __('Adminid') },
                        { field: 'province_text', title: __('Province'), operate: 'LIKE' },
                        { field: 'city_text', title: __('City'), operate: 'LIKE' },
                        { field: 'district_text', title: __('District'), operate: 'LIKE' },
                        { field: 'email', title: __('Email'), operate: 'LIKE' },
                        { field: 'status', title: __('Status'), searchList: { "0": __('未验证'), "1": __('已验证') }, formatter: Table.api.formatter.status },
                        { field: 'password', title: __('Password'), operate: 'LIKE' },
                        { field: 'salt', title: __('Salt'), operate: 'LIKE' },
                        { field: 'avatar', title: __('Avatar'), operate: 'LIKE', events: Table.api.events.image, formatter: Table.api.formatter.image },
                        { field: 'money', title: __('Money'), operate: 'BETWEEN' },
                        { field: 'source.name', title: __('Sourceid'), searchList: { "1": __('云课堂'), "2": __('云商城') }, formatter: Table.api.formatter.normal },
                        { field: 'deal', title: __('Deal'), searchList: { "0": __('Deal 0'), "1": __('Deal 1') }, formatter: Table.api.formatter.normal },
                        { field: 'openid', title: __('Openid'), operate: 'LIKE' },
                        { field: 'invitecode', title: __('Invitecode'), operate: 'LIKE' },
                        { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'info',
                                    title: '查看',
                                    icon: 'fa fa-street-view',
                                    classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-info',
                                    url: $.fn.bootstrapTable.defaults.extend.info_url,
                                    extend: 'data-toggle=\'tooltip\' data-area= \'["100%", "100%"]\'', 
                                },
                                {
                                    name: 'recovery',
                                    title: '回收',
                                    icon: 'fa fa-recycle',
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    url: $.fn.bootstrapTable.defaults.extend.recovery_url,
                                    confirm: '是否回收用户数据',
                                    extend: "data-toggle='tooltip'",
                                    success: function (data) {
                                        table.bootstrapTable('refresh');
                                    }
                                },
                            ]
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            // 根据class名称，来修改按钮的窗口大小
            $(".btn-recyclebin").data('area', ['100%', '100%'])

            $(document).on('click', '.btn-recovery', function () {
                // 弹出确认对话框
                Layer.confirm(__('是否放回公海'), { icon: 3, title: __('Warning'), shadeClose: true }, function (index) {
                    //获取当前选中id
                    var ids = Table.api.selectedids(table)

                    //发送ajax请求
                    Backend.api.ajax(
                        //请求地址
                        { url: $.fn.bootstrapTable.defaults.extend.recovery_url + `?ids=${ids}` },
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
        },

        add: function () {
            Controller.api.bindevent();
        },
        info: function() {
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
