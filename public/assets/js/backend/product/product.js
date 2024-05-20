define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'product/product/index' + location.search,
                    add_url: 'product/product/add',
                    edit_url: 'product/product/edit',
                    del_url: 'product/product/del',
                    multi_url: 'product/product/multi',
                    import_url: 'product/product/import',
                    recyclebin_url: 'product/product/recyclebin',
                    info_url: 'product/lists/info',
                    table: 'product',
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
                        {checkbox: true},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'type.name', title: __('Typeid'), operate: 'LIKE' },
                        {field: 'stock', title: __('Stock')},
                        {field: 'minstock', title: __('Minstock')},
                        {field: 'flag', title: __('Flag'), searchList: {"0":__('下架'),"1":__('上架')}, formatter: Table.api.formatter.flag},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'rent', title: __('Rent'), operate:'BETWEEN'},
                        {field: 'rent_price', title: __('Rent_price'), operate:'BETWEEN'},
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
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
                                    icon: 'fa fa-info',
                                    classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-info',
                                    url: $.fn.bootstrapTable.defaults.extend.info_url,
                                    extend: 'data-toggle=\'tooltip\' data-area= \'["100%", "100%"]\'', 
                                }
                            ]
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            //修改回收站按钮的窗口大小
            $(".btn-recyclebin").data('area', ['100%', '100%'])
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },

        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    //回收站
                    recyclebin_url: 'product/product/recyclebin',
                    //还原
                    restore_url: 'product/product/restore',
                    //真删除
                    del_url: 'product/product/destroy',

                    table: 'product',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.recyclebin_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'type.name', title: __('Typeid'), operate: 'LIKE' },
                        {field: 'stock', title: __('Stock')},
                        {field: 'minstock', title: __('Minstock')},
                        {field: 'flag', title: __('Flag'), searchList: {"0":__('下架'),"1":__('上架')}, formatter: Table.api.formatter.flag},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'rent', title: __('Rent'), operate:'BETWEEN'},
                        {field: 'rent_price', title: __('Rent_price'), operate:'BETWEEN'},
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        {field: 'deletetime', title: __('Deletetime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'restore',
                                    title: '还原',
                                    icon: 'fa fa-reply',
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    url: $.fn.bootstrapTable.defaults.extend.restore_url,
                                    confirm: '是否还原商品',
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
            // 给表格的按钮绑定点击事件
            $(document).on('click', '.btn-restore', function () {
                // 弹出确认对话框
                Layer.confirm(__('是否确认还原商品'), { icon: 3, title: __('Warning'), shadeClose: true }, function (index) {
                    //获取当前选中id
                    var ids = Table.api.selectedids(table)

                    //发送ajax请求
                    Backend.api.ajax(
                        //请求地址
                        { url: $.fn.bootstrapTable.defaults.extend.restore_url + `?ids=${ids}` },
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
