define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'product/product_type/index' + location.search,
                    add_url: 'product/product_type/add',
                    edit_url: 'product/product_type/edit',
                    del_url: 'product/product_type/del',
                    multi_url: 'product/product_type/multi',
                    import_url: 'product/product_type/import',
                    table: 'product_type',
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
                        {checkbox: true},
                        {field: 'weight', title: __('Weight')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        { field: 'thumb', title: __('Thumb'), operate: 'LIKE', events: Table.api.events.image, formatter: Table.api.formatter.image },
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
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
