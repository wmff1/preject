define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'toastr'], function ($, undefined, Backend, Table, Form, Toastr) {

    var Controller = {
        select: function () {
            function debounce(handle, delay) {
                let time = null;
                return function () {
                    let self = this,
                        arg = arguments;
                    clearTimeout(time);
                    time = setTimeout(function () {
                        handle.apply(self, arg);
                    }, delay)
                }
            }
            var goodsSelect = new Vue({
                el: "#goodsSelect",
                data() {
                    return {
                        categoryData: [],
                        searchWhere: '',
                        goodsList: [],
                        totalPage: 0,
                        offset: 0,
                        currentPage: 1,

                        multiple: new URLSearchParams(location.search).get('multiple'),
                        category_id: null,
                        selectedIds: []
                    }
                },
                mounted() {
                    if (new URLSearchParams(location.search).get('ids')) {
                        this.selectedIds = new URLSearchParams(location.search).get('ids').split(',')
                    }
                    this.getList();
                    this.categoryData = Config.category //左边导航栏
                },
                methods: {
                    // 商品选择左边导航栏
                    showLeft(p, c, a, s) {
                        if (p != null && a === null && c === null && s === null) {
                            this.categoryData[p].show = !this.categoryData[p].show;
                        }
                        if (p != null && c != null && a == null && s === null) {
                            this.categoryData[p].children[c].show = !this.categoryData[p].children[c].show;
                        }
                        if (p != null && c != null && a != null && s === null) {
                            this.categoryData[p].children[c].children[a].show = !this.categoryData[p].children[c].children[a].show;
                        }
                        this.$forceUpdate();
                    },
                    selectCategoryLeft(p, c, a, s) {
                        this.categoryData.forEach(i => {
                            i.selected = false;
                            if (i.children && i.children.length > 0) {
                                i.children.forEach(j => {
                                    j.selected = false;
                                    if (j.children && j.children.length > 0) {
                                        j.children.forEach(k => {
                                            k.selected = false;
                                            if (k.children && k.children.length > 0) {
                                                k.children.forEach(l => {
                                                    l.selected = false;
                                                })
                                            }
                                        })
                                    }
                                })
                            }
                        })
                        let category_id = null;
                        if (p != null && a === null && c === null && s === null) {
                            this.categoryData[p].selected = !this.categoryData[p].selected;
                            category_id = this.categoryData[p].id
                        }
                        if (p != null && c != null && a == null && s === null) {
                            this.categoryData[p].children[c].selected = !this.categoryData[p].children[c].selected;
                            category_id = this.categoryData[p].children[c].id
                        }
                        if (p != null && c != null && a != null && s === null) {
                            this.categoryData[p].children[c].children[a].selected = !this.categoryData[p].children[c].children[a].selected;
                            category_id = this.categoryData[p].children[c].children[a].id
                        }
                        if (p != null && c != null && a != null && s != null) {
                            this.categoryData[p].children[c].children[a].children[s].selected = !this.categoryData[p].children[c].children[a].children[s].selected;
                            category_id = this.categoryData[p].children[c].children[a].children[s].id
                        }
                        this.category_id = category_id;
                        this.offset = 0;
                        this.getList(category_id)
                        this.$forceUpdate();
                    },
                    getList(id) {
                        let that = this;
                        let url = "product/goods/select?status=up,hidden";
                        if (id) {
                            url = 'product/goods/select?status=up,hidden&category_id=' + id
                        }
                        Fast.api.ajax({
                            url: url,
                            data: {
                                limit: 10,
                                offset: that.offset,
                                search: that.searchWhere
                            },
                            type: 'GET'
                        }, function (ret, res) {
                            that.goodsList = res.data.rows;
                            that.goodsList.forEach(g => {
                                that.$set(g, 'checked', false)
                            })
                            let selectData = []
                            that.goodsList.forEach(g => {
                                if (that.selectedIds.includes(g.id + '')) {
                                    selectData.push(g)
                                }
                            })
                            that.$nextTick(() => {
                                selectData.forEach(row => {
                                    that.$refs.multipleTable.toggleRowSelection(row);
                                });
                            })
                            that.totalPage = res.data.total;
                            return false;
                        })
                    },
                    // 右边搜索功能
                    singleSelectionChange(row) {
                        this.selectedIds = []
                        this.selectedIds.push(row.id)
                        this.goodsList.forEach(g => {
                            if (g.id == row.id) {
                                this.$set(g, 'checked', true)
                            } else {
                                this.$set(g, 'checked', false)
                            }
                        })
                        this.$forceUpdate()
                    },
                    multipleSelectionChange(val) {
                        val.forEach(g => {
                            if (!this.selectedIds.includes(g.id + '')) {
                                this.selectedIds.push(g.id + '')
                            }
                        })
                    },
                    SelectionChange(selection, row) {
                        if (this.selectedIds.indexOf(row.id + '') != -1) {
                            this.selectedIds.splice(this.selectedIds.indexOf(row.id + ''), 1)
                        }
                    },
                    // 商品选择分页
                    changeClick(val) {
                        this.currentPage = val;
                        this.offset = 10 * (val - 1);
                        if (this.category_id == null) {
                            this.getList()
                        } else {
                            this.getList(this.category_id)
                        }
                    },
                    // 确认商品
                    operation() {
                        let that = this;
                        let domain = window.location.origin;
    
                        if (this.selectedIds.length == 0) {
                            return false
                        }
                        let ids = this.multiple == 'true' ? this.selectedIds.join(',') : this.selectedIds[this.selectedIds.length - 1]
                        Fast.api.ajax({
                            url: domain + '/erp.php/product/goods/lists?goods_ids=' + ids + "&per_page=999999999",
                            loading: false,
                        }, function (ret, res) {
                            Fast.api.close({
                                data: that.multiple == 'true' ? res.data.data : res.data.data[0],
                            });
                            return false;
                        }, function () {
                            return false;
                        })
                    },
                    debounceFilter: debounce(function () {
                        this.getList()
                    }, 1000)
                },
                watch: {
                    searchWhere(newVal, oldVal) {
                        if (newVal != oldVal) {
                            this.offset = 0;
                            this.currentPage = 1;
                            this.categoryData.forEach(i => {
                                i.selected = false;
                                if (i.children && i.children.length > 0) {
                                    i.children.forEach(j => {
                                        j.selected = false;
                                        if (j.children && j.children.length > 0) {
                                            j.children.forEach(k => {
                                                k.selected = false;
                                                if (k.children && k.children.length > 0) {
                                                    k.children.forEach(l => {
                                                        l.selected = false;
                                                    })
                                                }
                                            })
                                        }
                                    })
                                }
                            })
                            this.debounceFilter();
                        }
                    },
                },
            })
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        },
        initAddEdit: function (id, type, skuList, skuPrice) {
            Vue.directive('enterNumber', {
                inserted: function (el) {
                    let changeValue = (el, type) => {
                        const e = document.createEvent('HTMLEvents')
                        e.initEvent(type, true, true)
                        el.dispatchEvent(e)
                    }
                    el.addEventListener("keyup", function (e) {
                        let input = e.target;
                        let reg = new RegExp('^((?:(?:[1-9]{1}\\d*)|(?:[0]{1}))(?:\\.(?:\\d){0,2})?)(?:\\d*)?$');
                        let matchRes = input.value.match(reg);
                        if (matchRes === null) {
                            input.value = "";
                        } else {
                            if (matchRes[1] !== matchRes[0]) {
                                input.value = matchRes[1];
                            }
                        }
                        changeValue(input, 'input')
                    });
                }
            });
            Vue.directive('positiveInteger', {
                inserted: function (el) {
                    el.addEventListener("keypress", function (e) {
                        e = e || window.event;
                        let charcode = typeof e.charCode == 'number' ? e.charCode : e.keyCode;
                        let re = /\d/;
                        if (!re.test(String.fromCharCode(charcode)) && charcode > 9 && !e.ctrlKey) {
                            if (e.preventDefault) {
                                e.preventDefault();
                            } else {
                                e.returnValue = false;
                            }
                        }
                    });
                }
            });
            //vue Sku添加页 添加规格和价格数据
            var goodsDetail = new Vue({
                el: "#goodsDetail",
                data() {
                    return {
                        editId: id,
                        type: type,
                        stepActive: 1,
                        goodsDetail: {},
                        goodsDetailInit: {
                            category_ids: '',
                            content: '',
                            dispatch_ids: '',

                            express_ids: '', //单个数组
                            store_ids: '',
                            selfetch_ids: '',
                            autosend_ids: '',

                            dispatch_type: '',
                            expire_day: 0, //有效时间
                            image: '',
                            images: '',
                            is_sku: 0,
                            original_price: '',
                            params: '',
                            params_arr: [],
                            price: '',
                            service_ids: '',
                            show_sales: '',
                            status: 'up',
                            subtitle: '',
                            title: '',
                            type: 'normal',
                            views: '',
                            weigh: '',
                            weight: '',
                            stock: '',
                            stock_warning_switch: false,
                            stock_warning: null,
                            sn: '',
                            autosend_content: '',
                            brand_id: '',
                            vender_id: '',
                            category_id: '',
                            insurance_id:'',
                            label_id:'',
                            prescription_id:'',
                            sell_point:'',
                            nature_business_id:'',
                            is_medicine:'no',
                            is_recommend:'no',
                            is_new:'no',
                            is_prepare:'no',
                            is_soon_arrival:'no',
                            soon_arrival_date:'',
                            is_soon_rise:'no',
                            is_hot_sale:'no',
                            soon_rise_date:'',
                            goods_sign:'',
                            approval:'',
                            bar_code:'',
                            first_battalion:'',
                            batch_file:'',
                            mini_price:'',
                            manufacture_date:'',
                            guarantee_date:'',
                            specs:'',
                            factory_report:'',
                        },
                        timeData: {
                            images_arr: [],
                            dispatch_type_arr: [], //类型
                            dispatch_ids_arr: [], //id数组
                            service_ids_arr: [], //服务
                            label_ids_arr: [], //药物标签
                            prescription_ids_arr: [], //处方类型
                        },
                        rules: {
                            title: [{
                                required: true,
                                message: '请输入商品标题',
                                trigger: 'blur'
                            }],
                            subtitle: [{
                                required: true,
                                message: '请输入商品副标题',
                                trigger: 'blur'
                            }],
                            status: [{
                                required: true,
                                message: '请选择商品状态',
                                trigger: 'blur'
                            }],
                            image: [{
                                required: true,
                                message: '请上传商品主图',
                                trigger: 'change'
                            }],
                            images: [{
                                required: true,
                                message: '请上传商品轮播图',
                                trigger: 'change'
                            }],
                            category_ids: [{
                                required: true,
                                message: '请选择商品分类',
                                trigger: 'blur'
                            }],
                            dispatch_type: [{
                                required: true,
                                message: '请选择配送方式',
                                trigger: 'blur'
                            }],
                            dispatch_ids: [{
                                required: true,
                                message: '请选择运费模板',
                                trigger: 'blur'
                            }],
                            express_ids: [{
                                required: true,
                                message: '请选择运费模板',
                                trigger: 'blur'
                            }],
                            store_ids: [{
                                required: true,
                                message: '请选择配送模板',
                                trigger: 'blur'
                            }],
                            selfetch_ids: [{
                                required: true,
                                message: '请选择自提模板',
                                trigger: 'blur'
                            }],
                            autosend_ids: [{
                                required: true,
                                message: '请选择发货模板',
                                trigger: 'blur'
                            }],
                            is_sku: [{
                                required: true,
                                message: '请选择商品规格',
                                trigger: 'blur'
                            }],
                            price: [{
                                required: true,
                                message: '请输入价格',
                                trigger: 'blur'
                            }],
                            original_price: [{
                                required: true,
                                message: '请输入划线价格',
                                trigger: 'blur'
                            }],
                            weight: [{
                                required: true,
                                message: '请输入重量',
                                trigger: 'blur'
                            }],
                            stock: [{
                                required: true,
                                message: '请输入库存',
                                trigger: 'blur'
                            }],
                            service_ids: [{
                                required: true,
                                message: '请选择服务标签',
                                trigger: 'blur'
                            }],
                            show_sales: [{
                                required: true,
                                message: '请输入虚增数量',
                                trigger: 'blur'
                            }],
                            is_medicine: [{
                                required: true,
                                message: '请选择商品类型',
                                trigger: 'blur'
                            }],
                            is_recommend: [{
                                required: true,
                                message: '是否推荐商品',
                                trigger: 'blur'
                            }],
                            is_new: [{
                                required: true,
                                message: '是否新品',
                                trigger: 'blur'
                            }],
                            is_prepare: [{
                                required: true,
                                message: '是否预售',
                                trigger: 'blur'
                            }],
                            is_hot_sale: [{
                                required: true,
                                message: '是否特卖',
                                trigger: 'blur'
                            }],
                            is_soon_arrival: [{
                                required: true,
                                message: '是否即将到货',
                                trigger: 'blur'
                            }],
                            is_soon_rise: [{
                                required: true,
                                message: '是否即将上涨',
                                trigger: 'blur'
                            }],
                        },
                        mustDel: ['express_ids', 'store_ids', 'selfetch_ids', 'autosend_ids'],

                        //服务
                        serviceOptions: [],
                        dispatchType: [],
                        dispatchOptions: {},
                        brandList: [],
                        venderList: [],
                        categoryList: [],
                        insuranceList: [],
                        prescriptionList: [],
                        natureList: [],
                        labelOptions: [],
                        prescriptionOptions: [],





                        upload: Config.moduleurl,
                        editor: null,

                        //多规格
                        skuList: [],
                        skuPrice: [],
                        skuListData: '',
                        skuPriceData: '',
                        skuModal: '',
                        childrenModal: [],
                        countId: 1,
                        allEditSkuName: '',
                        isEditInit: false, // 编辑时候初始化是否完成
                        isResetSku: 0,
                        allEditPopover: {
                            price: false,
                            stock: false,
                            stock_warning: false,
                            weight: false,
                            mini_price: false,
                        },
                        allEditDatas: "",
                        allstock_warning_switch: false,

                        //选择分类
                        categoryOptions: [],
                        popperVisible: false,
                        tempTabsId: "",
                        tempCategory: {
                            idsArr: {},
                            label: {}
                        }
                    }
                },
                mounted() {
                    this.getServiceOptions();
                    this.getDispatchType();
                    this.getBrandList();
                    this.getVenderList();
                    this.getCategoryList();
                    this.getInsuranceList();
                    this.getPrescriptionList();
                    this.getNatureList();
                    this.getLabelList();







                    if (this.editId) {
                        this.goodsDetail = JSON.parse(JSON.stringify(this.goodsDetailInit));
                        this.getCategoryOptions(true);
                    } else {
                        this.getCategoryOptions();
                        this.goodsDetail = JSON.parse(JSON.stringify(this.goodsDetailInit));
                        this.getInit([], [])
                        this.$nextTick(() => {
                            Controller.api.bindevent();
                        });
                    }
                },
                methods: {
                    createTemplate(type) {
                        let that = this;
                        if (type == 'service') {
                            Fast.api.open("shopro/goods/service/add", "新建");
                        } else {
                            Fast.api.open("shopro/dispatch/" + type + "/add", "新建", {
                                callback(data) {
                                    if (data.data) {
                                        that.getDispatchTemplateData(type, 'create'); //TODO 判断type
                                    }
                                }
                            });
                        }
                    },
                    getInit(skuList, skuPrice) {
                        // 记录每个规格项真实 id，对应的临时 id
                        let tempIdArr = {};
                        for (let i in skuList) {
                            // 为每个 规格增加当前页面自增计数器，比较唯一用
                            skuList[i]['temp_id'] = this.countId++
                            for (let j in skuList[i]['children']) {
                                // 为每个 规格项增加当前页面自增计数器，比较唯一用
                                skuList[i]['children'][j]['temp_id'] = this.countId++

                                // 记录规格项真实 id 对应的 临时 id
                                tempIdArr[skuList[i]['children'][j]['id']] = skuList[i]['children'][j]['temp_id']
                            }
                        }
                        // for (let i in skuPrice) {
                        for (var i = 0; i < skuPrice.length; i++) {
                            let tempSkuPrice = skuPrice[i]
                            tempSkuPrice['temp_id'] = i + 1

                            // 将真实 id 数组，循环，找到对应的临时 id 组合成数组 
                            tempSkuPrice['goods_sku_temp_ids'] = [];
                            let goods_sku_id_arr = tempSkuPrice['goods_sku_ids'].split(',');
                            for (let ids of goods_sku_id_arr) {
                                tempSkuPrice['goods_sku_temp_ids'].push(tempIdArr[ids])
                            }

                            skuPrice[i] = tempSkuPrice
                        }
                        if (this.type == 'copy') {
                            for (let i in skuList) {
                                // 为每个 规格增加当前页面自增计数器，比较唯一用
                                skuList[i].id = 0;
                                for (let j in skuList[i]['children']) {
                                    skuList[i]['children'][j].id = 0;
                                }
                            }
                        }
                        if (skuPrice.length > 0) {
                            skuPrice.forEach(si => {
                                si.stock_warning_switch = false
                                if (si.stock_warning || si.stock_warning == 0) {
                                    si.stock_warning_switch = true
                                }
                            })
                        }
                        this.skuList = skuList;
                        this.skuPrice = skuPrice;

                        setTimeout(() => {
                            // 延迟触发更新下面列表
                            this.isEditInit = true;
                        }, 200)
                    },
                    getEditData() {
                        let that = this;
                        Fast.api.ajax({
                            url: 'shopro/goods/goods/detail/ids/' + that.editId,
                            loading: true,
                        }, function (ret, res) {
                            for (key in that.goodsDetail) {
                                if (res.data.detail[key]) {
                                    that.goodsDetail[key] = res.data.detail[key]
                                } else {
                                    that.goodsDetail.express_ids = res.data.detail.dispatch_group_ids_arr.express ? res.data.detail.dispatch_group_ids_arr.express : '';

                                    that.goodsDetail.store_ids = res.data.detail.dispatch_group_ids_arr.store ? res.data.detail.dispatch_group_ids_arr.store : '';

                                    that.goodsDetail.selfetch_ids = res.data.detail.dispatch_group_ids_arr.selfetch ? res.data.detail.dispatch_group_ids_arr.selfetch : '';

                                    that.goodsDetail.autosend_ids = res.data.detail.dispatch_group_ids_arr.autosend ? res.data.detail.dispatch_group_ids_arr.autosend : '';
                                }
                            }
                            for (key in that.timeData) {
                                if (res.data.detail[key]) {
                                    that.timeData[key] = res.data.detail[key]
                                }

                            }
                            that.handleCategoryIds(res.data.detail.category_ids_arr)

                            that.timeData.dispatch_type_arr.forEach(i => {
                                that.getDispatchTemplateData(i, 'edit');
                            })
                            that.getInit(res.data.skuList, res.data.skuPrice);

                            Controller.api.bindevent();
                            $('#c-content').html(res.data.detail.content)
                            // 库存预警
                            that.goodsDetail.stock_warning = res.data.detail.stock_warning
                            if (that.goodsDetail.stock_warning || that.goodsDetail.stock_warning == 0) {
                                that.goodsDetail.stock_warning_switch = true
                            }
                            return false;
                        })
                    },
                    // 处理 category_ids 显示 组合label数据
                    handleCategoryIds(ids_arr) {
                        if (ids_arr.length > 0) {
                            this.tempTabsId = ids_arr[0][0] + "";
                            ids_arr.forEach((cate) => {
                                if (!this.tempCategory.idsArr[cate[0]]) {
                                    this.tempCategory.idsArr[cate[0]] = [];
                                }
                                this.tempCategory.idsArr[cate[0]].push(cate[cate.length - 1]);
                            });
                        } else {
                            if (category.select.length) {
                                this.tempTabsId = category.select[0].id + "";
                            }
                        }
                        this.changeCategoryIds();
                    },
                    openCategory(type) {
                        if (type == 0) {
                            this.popperVisible = false
                        } else if (type == 1) {
                            this.popperVisible = true
                        } else {
                            this.popperVisible = !this.popperVisible
                        }
                    },
                    openPrescription(type) {
                        if (type == 0) {
                            this.popperVisible = false
                        } else if (type == 1) {
                            this.popperVisible = true
                        } else {
                            this.popperVisible = !this.popperVisible
                        }
                    },
                    handleCategoryIdsLabel(data, id) {
                        let that = this;
                        for (var i = 0; i < data.length; i++) {
                            if (data[i] && data[i].id == id) {
                                return [data[i].name];
                            }
                            if (data[i] && data[i].children && data[i].children.length > 0) {
                                var far = that.handleCategoryIdsLabel(data[i].children, id);
                                if (far) {
                                    return far.concat(data[i].name);
                                }
                            }
                        }
                    },
                    changeCategoryIds() {
                        this.$nextTick(() => {
                            this.tempCategory.idsArr = {};
                            this.tempCategory.label = {};
                            for (var key in this.$refs) {
                                if (key.includes('categoryRef')) {
                                    let keyArr = key.split("-");
                                    if (this.$refs[key].length > 0) {
                                        if (this.$refs[key][0].checkedNodePaths.length > 0) {
                                            this.$refs[key][0].checkedNodePaths.forEach((row) => {
                                                row.forEach(k => {
                                                    if (k.checked) {
                                                        if (!this.tempCategory.idsArr[keyArr[1]]) {
                                                            this.tempCategory.idsArr[keyArr[1]] = [];
                                                        }
                                                        this.tempCategory.idsArr[keyArr[1]].push(k.value);
                                                        this.tempCategory.label[k.value] =
                                                            keyArr[2] + "/" + k.pathLabels.join("/");
                                                    }
                                                })
                                            });
                                        }
                                    }
                                }
                            }
                        });
                    },
                    deleteCategoryIds(id) {
                        delete this.tempCategory.label[id];
                        for (var key in this.$refs) {
                            if (key.includes('categoryRef')) {
                                if (this.$refs[key].length > 0) {
                                    if (this.$refs[key][0].checkedNodePaths.length > 0) {
                                        this.$refs[key][0].checkedNodePaths.forEach((row) => {
                                            row.forEach(k => {
                                                if (k.data.id == id) {
                                                    k.checked = false;
                                                    this.$refs[key][0].calculateMultiCheckedValue()
                                                }
                                            })
                                        });
                                    }
                                }
                            }
                        }
                    },
                    getBrandList() {
                        let that = this;
                        Fast.api.ajax({
                            url: 'shopro/goods/goods/getBrand',
                            loading: false,
                        }, function (ret, res) {
                            that.brandList = res.data
                            return false;
                        })
                    },
                    getVenderList() {
                        let that = this;
                        Fast.api.ajax({
                            url: 'shopro/goods/goods/getVender',
                            loading: false,
                        }, function (ret, res) {
                            that.venderList = res.data
                            return false;
                        })
                    },
                    getCategoryList() {
                        let that = this;
                        Fast.api.ajax({
                            url: 'shopro/goods/goods/getCategory',
                            loading: false,
                        }, function (ret, res) {
                            that.categoryList = res.data
                            return false;
                        })
                    },
                    getInsuranceList() {
                        let that = this;
                        Fast.api.ajax({
                            url: 'shopro/goods/goods/getInsurance',
                            loading: false,
                        }, function (ret, res) {
                            that.insuranceList = res.data
                            return false;
                        })
                    },
                    getPrescriptionList() {
                        let that = this;
                        Fast.api.ajax({
                            url: 'shopro/goods/goods/getPrescription',
                            loading: false,
                        }, function (ret, res) {
                            that.prescriptionOptions = res.data
                            return false;
                        })
                    },
                    getNatureList() {
                        let that = this;
                        Fast.api.ajax({
                            url: 'shopro/goods/goods/getNature',
                            loading: false,
                        }, function (ret, res) {
                            that.natureList = res.data
                            return false;
                        })
                    },
                    getLabelList() {
                        let that = this;
                        Fast.api.ajax({
                            url: 'shopro/goods/goods/getLabel',
                            loading: false,
                        }, function (ret, res) {
                            that.labelOptions = res.data
                            return false;
                        })
                    },


                    getCategoryOptions(form) {
                        let that = this;
                        Fast.api.ajax({
                            url: 'shopro/category/index',
                            loading: false,
                        }, function (ret, res) {
                            that.categoryOptions = res.data;
                            // if (that.categoryOptions.length > 0 && !that.categoryTab) that.categoryTab = Number(that.categoryOptions[0].id);
                            if (form) {
                                that.getEditData()
                            }
                            return false;
                        })
                    },
                    createCategory() {
                        let that = this;
                        Fast.api.open("shopro/category/index", "新建", {
                            callback(data) {
                                that.getCategoryOptions();
                            }
                        });
                    },
                    submitForm(formName) {
                        this.$refs[formName].validate((valid) => {
                            if (valid) {
                                let that = this;
                                let arrForm = JSON.parse(JSON.stringify(that.goodsDetail));
                                let params_arrflg = true;
                                arrForm.params_arr.forEach(i => {
                                    for (key in i) {
                                        if (!i[key]) {
                                            params_arrflg = false;
                                        }
                                    }
                                })
                                if (!params_arrflg) {
                                    Toastr.error('参数详情未填写完整');
                                    return false;
                                }
                                arrForm.params = JSON.stringify(arrForm.params_arr);
                                arrForm.content = $("#c-content").val();

                                var arrids = []
                                // 发货模板id
                                if (arrForm.type == 'normal') {
                                    if (arrForm.dispatch_type.indexOf('express') != -1 && arrForm.express_ids != '') {
                                        arrids.push(arrForm.express_ids);
                                    }
                                    if (arrForm.dispatch_type.indexOf('store') != -1 && arrForm.store_ids != '') {
                                        arrids.push(arrForm.store_ids);
                                    }
                                    if (arrForm.dispatch_type.indexOf('selfetch') != -1 && arrForm.selfetch_ids != '') {
                                        arrids.push(arrForm.selfetch_ids);
                                    }
                                    arrForm.dispatch_ids = arrids.join(",")
                                } else {
                                    if (arrForm.dispatch_type == 'selfetch' && arrForm.selfetch_ids != '') {
                                        arrids = []
                                        arrids.push(arrForm.selfetch_ids);
                                    } else if (arrForm.dispatch_type == 'autosend' && arrForm.autosend_ids != '') {
                                        arrids = []
                                        arrids.push(arrForm.autosend_ids);
                                    }
                                }
                                arrForm.dispatch_ids = arrids.join(",")
                                that.mustDel.forEach(i => {
                                    delete arrForm[i]
                                })
                                let submitSkuList = []
                                let submitSkuPrice = []
                                if (arrForm.is_sku == 0) {
                                    // 库存预警
                                    if (!arrForm.stock_warning_switch) {
                                        arrForm.stock_warning = null;
                                    }
                                    delete arrForm.stock_warning_switch
                                } else {
                                    submitSkuList = JSON.parse(JSON.stringify(that.skuList))
                                    submitSkuPrice = JSON.parse(JSON.stringify(that.skuPrice))
                                    submitSkuPrice.forEach(s => {
                                        if (!s.stock_warning_switch) {
                                            s.stock_warning = null
                                        }
                                        delete s.stock_warning_switch
                                    })
                                }
                                let idsArr = [];
                                for (var key in this.tempCategory.idsArr) {
                                    this.tempCategory.idsArr[key].forEach((k) => {
                                        idsArr.push(Number(k));
                                    });
                                }
                                arrForm.category_ids = idsArr.join(",");
                                if (that.editId && that.type == 'edit') {
                                    Fast.api.ajax({
                                        url: 'shopro/goods/goods/edit/ids/' + that.editId,
                                        loading: true,
                                        data: {
                                            row: arrForm,
                                            sku: {
                                                listData: JSON.stringify(submitSkuList),
                                                priceData: JSON.stringify(submitSkuPrice)
                                            }
                                        }
                                    }, function (ret, res) {
                                        Fast.api.close();
                                    })
                                } else {
                                    if (this.type == 'copy') {
                                        delete arrForm.id
                                    }
                                    Fast.api.ajax({
                                        url: 'shopro/goods/goods/add',
                                        loading: true,
                                        data: {
                                            row: arrForm,
                                            sku: {
                                                listData: JSON.stringify(submitSkuList),
                                                priceData: JSON.stringify(submitSkuPrice)
                                            }
                                        }
                                    }, function (ret, res) {
                                        Fast.api.close();
                                    })
                                }

                            } else {
                                return false;
                            }
                        });
                    },
                    resetForm(formName) {
                        this.$refs[formName].resetFields();
                    },
                    addImg(type, index, multiple) {
                        let that = this;
                        parent.Fast.api.open("general/attachment/select?multiple=" + multiple, "选择图片", {
                            callback: function (data) {
                                switch (type) {
                                    case "image":
                                        that.goodsDetail.image = data.url;
                                        break;
                                    case "first_battalion":
                                        that.goodsDetail.first_battalion = data.url;
                                        break;
                                    case "batch_file":
                                        that.goodsDetail.batch_file = data.url;
                                        break;
                                    case "factory_report":
                                        that.goodsDetail.factory_report = data.url;
                                        break;
                                    case "images":
                                        that.goodsDetail.images = that.goodsDetail.images ? that.goodsDetail.images + ',' + data.url : data.url;
                                        let arrs = that.goodsDetail.images.split(',');
                                        if (arrs.length > 9) {
                                            that.timeData.images_arr = arrs.slice(-9)
                                        } else {
                                            that.timeData.images_arr = arrs
                                        }
                                        that.goodsDetail.images = that.timeData.images_arr.join(',');
                                        break;
                                    case "sku":
                                        that.skuPrice[index].image = data.url;
                                        break;
                                }
                            }
                        });
                        return false;
                    },
                    delImg(type, index) {
                        let that = this;
                        switch (type) {
                            case "image":
                                that.goodsDetail.image = '';
                                break;
                            case "first_battalion":
                                that.goodsDetail.first_battalion = '';
                                break;
                            case "batch_file":
                                that.goodsDetail.batch_file = '';
                                break;
                            case "factory_report":
                                that.goodsDetail.factory_report = '';
                                break;
                            case "images":
                                that.timeData.images_arr.splice(index, 1);
                                that.goodsDetail.images = that.timeData.images_arr.join(",");
                            break;
                            case "sku":
                                that.skuPrice[index].image = '';
                                break;

                        }
                    },
                    imagesDrag() {
                        this.goodsDetail.images = this.timeData.images_arr.join(",");
                    },
                    changeGoodsType(type) {
                        this.goodsDetail.type = type;
                        this.goodsDetail.dispatch_ids_arr = [];
                        this.goodsDetail.dispatch_ids = '';
                        this.goodsDetail.dispatch_type_arr = [];
                        this.goodsDetail.dispatch_type = '';
                        this.timeData.dispatch_type_arr = []
                        this.goodsDetail.express_ids = ''
                        this.goodsDetail.store_ids = ''
                        this.goodsDetail.selfetch_ids = ''
                    },
                    categoryChange(val) {
                        this.goodsDetail.category_ids = val.join(',');
                    },
                    serviceChange(val) {
                        this.goodsDetail.service_ids = val.join(',');
                    },
                    prescriptionChange(val) {
                        this.goodsDetail.prescription_id = val.join(',');
                    },
                    labelChange(val) {
                        this.goodsDetail.label_id = val.join(',');
                    },
                    dispatchTypeChange(val) {
                        this.goodsDetail.dispatch_type = val.join(',');
                    },
                    dispatchTypeChanger(val) {
                        this.goodsDetail.dispatch_type = val;
                        this.getDispatchTemplateData(val, 'virtual');
                    },
                    getDispatchTemplateData(type, fristEdit) {
                        let that = this;
                        if (this.goodsDetail.dispatch_type.indexOf(type) == -1 || fristEdit == 'edit' || fristEdit == 'virtual' || fristEdit == 'create') {
                            Fast.api.ajax({
                                url: 'shopro/dispatch/dispatch/select/type/' + type,
                                loading: false,
                                type: 'GET',
                            }, function (ret, res) {
                                that.$set(that.dispatchOptions, type, res.data)
                                return false;
                            })
                        }
                    },

                    getDispatchType() {
                        let that = this;
                        Fast.api.ajax({
                            url: 'shopro/dispatch/dispatch/typeList',
                            loading: false,
                        }, function (ret, res) {
                            let arr = []
                            for (key in res.data) {
                                arr.push({
                                    id: key,
                                    name: res.data[key]
                                })
                            }
                            that.dispatchType = arr;
                            return false;
                        })
                    },
                    getServiceOptions() {
                        let that = this;
                        Fast.api.ajax({
                            url: 'shopro/goods/service/all',
                            loading: false,
                        }, function (ret, res) {
                            that.serviceOptions = res.data
                            return false;
                        })
                    },
                    gotoback(formName) {
                        this.$refs[formName].validate((valid) => {
                            if (valid) {
                                this.stepActive++;
                            } else {
                                return false;
                            }
                        });
                    },
                    gonextback() {
                        this.stepActive--;
                    },
                    addParams() {
                        this.goodsDetail.params_arr.push({
                            title: '',
                            content: ''
                        })
                    },
                    delParams(index) {
                        this.goodsDetail.params_arr.splice(index, 1)
                    },

                    //添加主规格
                    addMainSku() {
                        // if (this.skuModal !== '') {
                        this.skuList.push({
                            id: 0,
                            temp_id: this.countId++,
                            name: this.skuModal,
                            pid: 0,
                            children: []
                        })
                        this.skuModal = '';
                        // this.skuPrice = []       // 新添加的主规格不清空 skuPrice,当添加主规格第一个子规格的时候清空
                        this.buildSkuPriceTable()
                        // }
                    },
                    //添加子规格
                    addChildrenSku(k) {
                        // if (this.childrenModal[k] !== '') {
                        // 检测当前子规格是否已经被添加过了
                        let isExist = false
                        this.skuList[k].children.forEach(e => {
                            if (e.name == this.childrenModal[k] && e.name != "") {
                                isExist = true
                            }
                        })

                        if (isExist) {
                            Toastr.error('子规格已存在');
                            return false;
                        }

                        this.skuList[k].children.push({
                            id: 0,
                            temp_id: this.countId++,
                            name: this.childrenModal[k],
                            pid: this.skuList[k].id,
                        })

                        this.childrenModal[k] = '';

                        // 如果是添加的第一个子规格，清空 skuPrice
                        if (this.skuList[k].children.length == 1) {
                            this.skuPrice = [] // 规格大变化，清空skuPrice
                            this.isResetSku = 1; // 重置规格
                        }

                        this.buildSkuPriceTable()
                        // }
                    },
                    //删除主规格
                    deleteMainSku(k) {
                        let data = this.skuList[k]

                        // 删除主规格
                        this.skuList.splice(k, 1)

                        // 如果当前删除的主规格存在子规格，则清空 skuPrice， 不存在子规格则不清空
                        if (data.children.length > 0) {
                            this.skuPrice = [] // 规格大变化，清空skuPrice
                            this.isResetSku = 1; // 重置规格
                        }

                        this.buildSkuPriceTable()
                    },
                    //删除子规格
                    deleteChildrenSku(k, i) {
                        let data = this.skuList[k].children[i]
                        this.skuList[k].children.splice(i, 1)

                        // 查询 skuPrice 中包含被删除的的子规格的项，然后移除
                        let deleteArr = []
                        this.skuPrice.forEach((item, index) => {
                            item.goods_sku_text.forEach((e, i) => {
                                if (e == data.name) {
                                    deleteArr.push(index)
                                }
                            })
                        })
                        deleteArr.sort(function (a, b) {
                            return b - a;
                        })
                        // 移除有相关子规格的项
                        deleteArr.forEach((i, e) => {
                            this.skuPrice.splice(i, 1)
                        })

                        // 当前规格项，所有子规格都被删除，清空 skuPrice
                        if (this.skuList[k].children.length <= 0) {
                            this.skuPrice = [] // 规格大变化，清空skuPrice
                            this.isResetSku = 1; // 重置规格
                        }
                        this.buildSkuPriceTable()
                    },
                    editStatus(i) {
                        if (this.skuPrice[i].status == 'up') {
                            this.skuPrice[i].status = 'down'
                        } else {
                            this.skuPrice[i].status = 'up'
                        }

                    },
                    //组合新的规格价格库存重量编码图片
                    buildSkuPriceTable() {
                        let arr = [];
                        //遍历sku子规格生成新数组，然后执行递归笛卡尔积
                        this.skuList.forEach((s1, k1) => {
                            let children = s1.children;
                            let childrenIdArray = [];
                            if (children.length > 0) {
                                children.forEach((s2, k2) => {
                                    childrenIdArray.push(s2.temp_id);
                                })

                                // 如果 children 子规格数量为 0,则不渲染当前规格, （相当于没有这个主规格）
                                arr.push(childrenIdArray);
                            }
                        })

                        this.recursionSku(arr, 0, []);
                    },
                    //递归找笛卡尔规格集合
                    recursionSku(arr, k, temp) {
                        if (k == arr.length && k != 0) {
                            let tempDetail = []
                            let tempDetailIds = []

                            temp.forEach((item, index) => {
                                for (let sku of this.skuList) {
                                    for (let child of sku.children) {
                                        if (item == child.temp_id) {
                                            tempDetail.push(child.name)
                                            tempDetailIds.push(child.temp_id)
                                        }
                                    }
                                }
                            })

                            let flag = false // 默认添加新的
                            for (let i = 0; i < this.skuPrice.length; i++) {
                                if (this.skuPrice[i].goods_sku_temp_ids.join(',') == tempDetailIds.join(',')) {
                                    flag = i
                                    break;
                                }
                            }

                            if (flag === false) {
                                this.skuPrice.push({
                                    id: 0,
                                    temp_id: this.skuPrice.length + 1,
                                    goods_sku_ids: '',
                                    goods_id: 0,
                                    weigh: 0,
                                    image: '',
                                    stock: 0,
                                    stock_warning: null,
                                    stock_warning_switch: false,
                                    price: 0,
                                    mini_price: '',
                                    weight: 0,
                                    status: 'up',
                                    goods_sku_text: tempDetail,
                                    goods_sku_temp_ids: tempDetailIds,
                                });
                            } else {
                                this.skuPrice[flag].goods_sku_text = tempDetail
                                this.skuPrice[flag].goods_sku_temp_ids = tempDetailIds
                            }
                            return;
                        }
                        if (arr.length) {
                            for (let i = 0; i < arr[k].length; i++) {
                                temp[k] = arr[k][i]
                                this.recursionSku(arr, k + 1, temp)
                            }
                        }
                    },
                    allEditData(type, opt) {
                        switch (opt) {
                            case 'define':
                                this.skuPrice.forEach(i => {
                                    if (type == 'stock_warning') {
                                        if (this.allstock_warning_switch) {
                                            i.stock_warning_switch = true
                                            if (this.allEditDatas) {
                                                i[type] = this.allEditDatas
                                            } else {
                                                i[type] = 0
                                            }
                                        } else {
                                            i.stock_warning_switch = false
                                            if (i.stock_warning_switch) {
                                                i[type] = this.allEditDatas
                                            } else {
                                                i[type] = null
                                            }
                                        }
                                    } else {
                                        i[type] = this.allEditDatas;
                                    }
                                })
                                this.allEditDatas = ''
                                this.allEditPopover[type] = false;
                                this.allstock_warning_switch = false;
                                break;
                            case 'cancel':
                                this.allEditDatas = ''
                                this.allEditPopover[type] = false;
                                this.allstock_warning_switch = false;
                                break;
                        }
                    },
                    changeStockWarningSwitch(type, index) {
                        // 0是单规格 1是多规格
                        if (type == 0) {
                            this.goodsDetail.stock_warning = this.goodsDetail.stock_warning_switch ? 0 : null
                        } else if (type == 1) {
                            this.skuPrice[index].stock_warning = this.skuPrice[index].stock_warning_switch ? 0 : null
                        }
                    }
                },
                watch: {
                    stepActive(newVal) {
                        this.editor = null;
                    },
                    skuList: {
                        handler(newName, oldName) {
                            if (this.isEditInit) { // 编辑初始化的时候会修改 skuList 但这时候不触发更新
                                this.buildSkuPriceTable();
                            }
                        },
                        deep: true
                    }
                },
            })
        }
    };
    return Controller;
});