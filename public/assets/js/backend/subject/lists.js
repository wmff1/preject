define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            //获取ids参数
            var ids = Fast.api.query('ids') ? Fast.api.query('ids') : 0;

            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: `subject/lists/index?ids=${ids}`,
                    add_url: `subject/lists/add?subid=${ids}`,
                    edit_url: 'subject/lists/edit',
                    del_url: 'subject/lists/del',
                    multi_url: 'subject/lists/multi',
                    import_url: 'subject/lists/import',
                    table: 'subject_lists',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'asc',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'url', title: __('Url'), operate: false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
