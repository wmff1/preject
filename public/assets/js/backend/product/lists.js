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
      product: function () {
        Controller.api.bindevent();
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