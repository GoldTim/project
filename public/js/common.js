let layer = layui.layer;
let host = '';

/**
 * 公共ajax请求函数
 * @param $url string 请求url
 * @param $method string 请求方式 get、post、put...
 * @param $data object 提交数据
 * @param $callBack_success callback 返回成功回调
 * @param $callBack_fail callback 返回失败回调
 * @param $callBack_before callback ajax请求之前回调函数
 * @param $callBack_complete callback ajax请求完成回调函数
 * @param $callBack_error callback ajax请求异常回调函数
 * @param $silent_request callback 静默请求
 */
function ajaxRequest($url, $method, $data, $callBack_success = null, $callBack_fail = null, $callBack_before = null, $callBack_complete = null, $callBack_error = null, $silent_request = 0) {
    let loadIndex = -1;
    if ($silent_request === 0) {
        loadIndex = loading();
    }
    $.ajax({
        url: $url,
        method: $method,
        data: $data,
        dataType: 'json',
        beforeSend: function (xhr) {
            if ($callBack_before != null) $callBack_before(xhr);
        },
        success: function (res) {
            $callBack_success == null ? showMsg(res.message, 1, 1000) : $callBack_success(res);
        },
        error: function (err) {
            let res = err.getResponseHeader('res');
            if (!res) {
                res = err.responseJSON;
                if (!res) {
                    res = {'message': '请求接口错误', 'code': 500};
                }
            } else {
                res = JSON.parse(res);
            }
            // if (res.code === '21') {
            //     switch (res.module) {
            //         case "home":
            //             setTimeout(function () {
            //                 top.location.href = "/index.html/home/Login/login";
            //             }, 1500)
            //             break;
            //         default :
            //             top.location.href = "/admin/index/login";
            //             break;
            //     }
            // }
            $callBack_fail == null ? showMsg(res.message, 2, 2000) : $callBack_fail(res);
        },
        complete: function () {
            if ($silent_request === 0) {
                layer.close(loadIndex);
            }
            if ($callBack_complete != null) $callBack_before(xhr);
        }
    });
}

//加载层
function loading($type = 0, $time = null, $shade = 0.25) {
    return layer.load($type, {time: $time, shade: $shade});
}

//刷新页面
function reload() {
    window.location.reload();
}

/**
 * 弹窗确认
 * @param $msg
 * @param $handle_callback
 * @param $icon
 * @param $shade
 */
function layerConfirm($msg, $handle_callback = null, $icon = 7, $shade = 0.25) {
    layer.confirm($msg, {icon: $icon, shade: $shade}, function (index) {
        layer.close(index);
        $handle_callback();
    });
}

/**
 * 弹窗消息
 * @param $msg
 * @param $icon
 * @param $time
 * @param $callback
 * @param $shade
 * @returns {*}
 */
function showMsg($msg, $icon = 1, $time = 1500, $callback = null, $shade = 0.2) {
    return layer.msg($msg, {
        icon: $icon,
        time: $time,
        shade: $shade
    }, $callback);
}

/**
 * ifrmae层(仅用于打开页面,无按钮情况下)
 * @param $url 要打开的url
 * @param $title iframe层 标题
 * @param $callback_ok
 * @param $callback_cancel 右上角关闭按钮回调函数
 * @param $width 宽
 * @param $height 高
 * @param $btn
 */
function openOperateIframe($url, $title, $callback_ok = null, $callback_cancel = null, $width = '95%', $height = '95%', $btn = ['确定', '取消']) {
    layer.open({
        type: 2,
        anim: 3, //动画
        title: $title,
        content: $url,
        area: [$width, $height],
        btn: $btn,
        yes: function (index, layer) {
            if ($callback_ok != null) {
                $callback_ok(index, layer);
            }
        },
        cancel: function (index, layer) {
            if ($callback_cancel != null) {
                $callback_cancel(index, layer);
            }
        }
    });
}

/**
 * 浮点数相减
 * @param num1
 * @param num2
 * @returns {string}
 */
function accSub(num1, num2) {
    let r1 = AssFloat(num1), r2 = AssFloat(num2);
    let m = Math.pow(10, Math.max(r1, r2)), n = (r1 >= r2) ? r1 : r2;
    return (Math.round(num1 * m - num2 * m) / m).toFixed(n);
}

/**
 * 浮点数相除
 * @param arg1
 * @param arg2
 * @returns {number}
 */
function accDiv(arg1, arg2) {
    let t1 = AssFloat(arg1), t2 = AssFloat(arg2);
    with (Math) {
        let r1 = Number(arg1.toString().replace('.', "")),
            r2 = Number(arg2.toString().replace(".", ""));
        return (r1 / r2) * Math.pow(10, t2 - t1);
    }
}

/**
 * 浮点数处理
 * @param num
 * @returns {number}
 * @constructor
 */
function AssFloat(num) {
    let r;
    try {
        r = num.toString().split('.')[1].length;
    } catch (e) {
        r = 0;
    }
    return r;
}

/**
 * 获取url参数
 * @param variable
 * @returns {string|null}
 */
function urlData(variable) {
    var reg = new RegExp("(^|&)" + variable + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return null;
}

/**
 * 根据表格宽度自动显示右侧浮动栏,并修正浮动栏高度
 * @param tableElem 绑定元素的dom
 */
function autoTableFixedHeight(tableElem) {
    window.setTimeout(function () {
        // 获取表格div
        var $tableView = $(tableElem).next(".layui-table-view");
        // 获取两侧浮动栏
        var $fixed = $tableView.find(".layui-table-fixed");
        var dataIndex;
        var trHeight;
        // 遍历tr 修正浮动栏行高 - 修改浮动表格内容高度
        $tableView.find(".layui-table-main").find("tr").each(function () {
            dataIndex = $(this).attr("data-index");
            trHeight = $(this).css("height");
            $fixed.find("tr[data-index=" + dataIndex + "]").css("height", trHeight);
        });
        // 遍历tr 修正浮动栏行高 - 修改浮动表格内容高度
        $tableView.find(".layui-table-header").find("tr").each(function () {
            trHeight = $(this).css("height");
            $fixed.find(".layui-table-header tr").css("height", trHeight);
        });

        // 判断div宽度是否小于table宽度
        // if ($tableView.width() < $tableView.find("table").width()) {
        //     // 显示右侧浮动栏
        //     $fixed.removeClass("layui-hide");
        // }
    }, 50);
}

/**
 * 图片点击弹窗展示
 */
$("img").click(function () {
    let url = $(this).attr('src');
    var FileExt = url.replace(/.+\./, "");
    if (FileExt.indexOf('http') === -1) {
        url = host + url;
    }
    if (url.length === 0) {
        showMsg('无图片', 7, 1200);
        return false;
    }
    layer.open({
        type: 1, title: false, anim: 1, area: ["400px", "400px"],
        shade: 0.35,
        btns: false,
        shadeClose: true, resize: true,
        content: '<img src="' + url + '" style="width:100%; height: 100%;object-fit: contain;" alt="">',
    });
});

$("input[type='number']").on('keypress', () => {
    return (/[0-9\.]/.test(String.fromCharCode(event.keyCode)));
});
