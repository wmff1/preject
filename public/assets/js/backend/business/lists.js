define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
  var Controller = {
    //详细
    info: function () {
      //绑定事件
      $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var panel = $($(this).attr("href"));
        if (panel.size() > 0) {
          Controller.table[panel.attr("id")].call(this);
          $(this).on('click', function (e) {
            $($(this).attr("href")).find(".btn-refresh").trigger("click");
          });
        }
      });

      //必须默认触发shown.bs.tab事件
      $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");

      Controller.api.bindevent();
    },

    table: {
      //客户资料方法
      base: function () {
        Controller.api.bindevent();
      },

      // 回访列表
      visit: function () {

        // 获取ids参数
        var ids = Fast.api.query('ids') ? Fast.api.query('ids') : 0;

        // 初始化表格参数配置
        Table.api.init({
          extend: {
            visit_url: `business/lists/visit?ids=${ids}`,
            add_url: `business/lists/add?ids=${ids}`,
            edit_url: `business/lists/edit?ids=${ids}`,
            del_url: 'business/lists/del',
            table: 'business_visit',
          }
        });

        var visitTable = $("#ViTable");

        // 初始化表格
        visitTable.bootstrapTable({
          url: $.fn.bootstrapTable.defaults.extend.visit_url,
          pk: 'id',
          sortName: 'id',
          toolbar: '#visitTable',
          // fixedColumns: true,
          // fixedRightNumber: 1,
          dblClickToEdit: false,
          columns: [
            [
              { checkbox: true },
              { field: 'id', title: __('Id') },
              { field: 'business.nickname', title: __('Busid'), operate: 'LIKE' },
              { field: 'admin.nickname', title: __('Admin') },
              { field: 'content', title: __('Content') },
              { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
              {
                field: 'operate',
                title: __('Operate'),
                table: visitTable,
                events: Table.api.events.operate,
                formatter: Table.api.formatter.operate
              }]
          ]
        });

        // 为表格绑定事件
        Table.api.bindevent(visitTable);
      },

      // 申请记录
      receive: function () {
        // 获取ids参数
        var ids = Fast.api.query('ids') ? Fast.api.query('ids') : 0;

        // 初始化表格参数配置
        Table.api.init({
          extend: {
            receive_url: `business/lists/receive?ids=${ids}`,
            del_url: 'business/lists/dels',
            table: 'business_receive',
          }
        });

        var receiveTable = $("#reTable");

        // 初始化表格
        receiveTable.bootstrapTable({
          url: $.fn.bootstrapTable.defaults.extend.receive_url,
          pk: 'id',
          sortName: 'id',
          toolbar: '#receiveTable',
          fixedColumns: true,
          fixedRightNumber: 1,
          columns: [
            [
              { checkbox: true },
              { field: 'id', title: __('Id') },
              { field: 'business.nickname', title: __('Busid') },
              { field: 'status', title: __('Status') },
              { field: 'apply.nickname', title: __('Applyid') },
              { field: 'applytime', title: __('Applytime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
              {
                field: 'operate',
                title: __('Operate'),
                table: receiveTable,
                events: Table.api.events.operate,
                formatter: Table.api.formatter.operate,
              }]
          ]
        });

        // 为表格绑定事件
        Table.api.bindevent(receiveTable);
      },
    },

    add: function () {
      Controller.api.bindevent();
    },

    edit: function () {
      Controller.api.bindevent();
    },

    api: {
      bindevent: function () {
        Form.api.bindevent($("form[role=form]"))
      },
    },
  };
  return Controller;
});