define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/order/index' + location.search,
                    add_url: 'order/order/add',
                    edit_url: 'order/order/edit',
                    del_url: 'order/order/del',
                    recyclebin_url: 'order/order/recyclebin',
                    delivery_url: 'order/order/delivery',
                    receipt_url: 'order/order/receipt',
                    multi_url: 'order/order/multi',
                    import_url: 'order/order/import',
                    table: 'order',
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
                        { field: 'id', title: __('ID') },
                        { field: 'code', title: __('Code'), operate: 'LIKE' },
                        { field: 'amount', title: __('Amount'), operate: 'BETWEEN' },
                        { field: 'business.nickname', title: __('Busid') },
                        { field: 'address_text', title: __('Address') },
                        { field: 'addressinfo_text', title: __('Addressinfo') },
                        { field: 'express_text', title: __('Expressid') },
                        { field: 'expresscode', title: __('Expresscode'), operate: 'LIKE' },
                        { field: 'status', title: __('Status'), searchList: {"0":__('未支付'),"1":__('已支付'),"2":__('已发货'),"3":__('已收货'),"4":__('已完成'),'5':__("已退货")}, formatter: Table.api.formatter.status},
                        { field: 'sell_text', title: __('Adminid') },
                        { field: 'check_text', title: __('Checkmanid') },
                        { field: 'deliver_text', title: __('Shipmanid') },
                        { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'delivery',
                                    title: '发货',
                                    icon: 'fa fa-truck',
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    url: $.fn.bootstrapTable.defaults.extend.delivery_url,
                                    visible: function (row) {
                                        if( row.status >= 1 && row.status < 2) {
                                            return true
                                        }
                                    },
                                    confirm: '是否确认发货',
                                    extend: "data-toggle='tooltip'",
                                    success: function (data) {
                                        table.bootstrapTable('refresh');
                                    }
                                },
                                {
                                    name: 'ConfirmReceipt',
                                    title: '收货',
                                    icon: 'fa fa-archive',
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    url: $.fn.bootstrapTable.defaults.extend.receipt_url,
                                    visible: function (row) {
                                        if( row.status >= 2 && row.status < 3) {
                                            return true
                                        }
                                    },
                                    confirm: '是否确认收货',
                                    extend: "data-toggle='tooltip'",
                                    success: function (data) {
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

            //修改回收站按钮的窗口大小
            $(".btn-recyclebin").data('area', ['100%', '100%'])
        },

        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': 'order/order/destroy'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'order/order/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        { checkbox: true },
                        { field: 'code', title: __('Code'), operate: 'LIKE' },
                        { field: 'amount', title: __('Amount'), operate: 'BETWEEN' },
                        { field: 'business.nickname', title: __('Busid') },
                        { field: 'address_text', title: __('Address') },
                        { field: 'addressinfo_text', title: __('Addressinfo') },
                        { field: 'express_text', title: __('Expressid') },
                        { field: 'expresscode', title: __('Expresscode'), operate: 'LIKE' },
                        { field: 'status_text', title: __('Status'), searchList: { "0": __('Status 0'), "1": __('Status 1'), "2": __('Status 2'), "3": __('Status 3'), "4": __('Status 4'), "5": __('Status 5'), "6": __('Status 6') }, formatter: Table.api.formatter.status },
                        { field: 'sell_text', title: __('Adminid') },
                        { field: 'check_text', title: __('Checkmanid') },
                        { field: 'deliver_text', title: __('Shipmanid') },
                        { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '130px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'order/order/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'order/order/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },

        delivery: function () {
            Controller.api.bindevent();
        },

        ConfirmReceipt: function () {
            Controller.api.bindevent();
        },

        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
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
