define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'subject/subject/index',
                    add_url: 'subject/subject/add',
                    edit_url: 'subject/subject/edit',
                    del_url: 'subject/subject/del',
                    multi_url: 'subject/subject/multi',
                    // 回收站按钮链接
                    recyclebin_url: 'subject/subject/recyclebin',
                    import_url: 'subject/subject/import',
                    lists_url: 'subject/lists/index',
                    table: 'subject',
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
                        //课程名称
                        { field: 'title', title: __('Title'), operate: 'LIKE' },
                        // 课程分类
                        { field: 'category.name', title: __('Category.name'), operate: 'LIKE' },
                        //课程价格
                        { field: 'price', title: __('Price'), operate: 'BETWEEN' },
                        //点赞
                        { field: 'likes_text', title: __('Likes'), operate: false },
                        //课程创建时间
                        { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        //课程操作
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            //自定义按钮封装
                            buttons: [
                                {
                                    name: 'lists',
                                    title: function (data) {
                                        return `${data.title}-章节列表`
                                    },
                                    icon: 'fa fa-bars',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: $.fn.bootstrapTable.defaults.extend.lists_url,
                                    extend: 'data-toggle=\'tooltip\' data-area= \'["100%", "100%"]\'', //重点是这一句
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
                    recyclebin_url: 'subject/subject/recyclebin',
                    //真删除
                    del_url: 'subject/subject/destroy',
                    //还原
                    restore_url: 'subject/subject/restore',
                    table: 'subject',
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
                        { checkbox: true },
                        //课程名称
                        { field: 'title', title: __('Title'), operate: 'LIKE' },
                        // 课程分类
                        { field: 'category.name', title: __('Category.name'), operate: 'LIKE' },
                        //课程价格
                        { field: 'price', title: __('Price'), operate: 'BETWEEN' },
                        //点赞
                        { field: 'likes_text', title: __('Likes'), operate: false },
                        //课程创建时间
                        { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        //课程删除时间
                        { field: 'deletetime', title: __('Deletetime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        //课程操作
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            //增添自定义的按钮
                            buttons: [
                                {
                                    name: 'restore',
                                    title: '还原',
                                    icon: 'fa fa-reply',
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    url: $.fn.bootstrapTable.defaults.extend.restore_url,
                                    confirm: '是否还原课程',
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
            //给表格的按钮绑定点击事件
            $(document).on('click', '.btn-restore', function () {
                // 弹出确认对话框
                Layer.confirm(__('是否确认还原数据'), { icon: 3, title: __('Warning'), shadeClose: true }, function (index) {
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
