Date.prototype.fromUnixTimestamp = function (value) {
    return new Date(parseFloat(value) * 1000);
};
Date.prototype.format = function (format) {
    var o = {
        "M+": this.getMonth() + 1, //month 
        "d+": this.getDate(), //day 
        "h+": this.getHours(), //hour 
        "m+": this.getMinutes(), //minute 
        "s+": this.getSeconds(), //second 
        "q+": Math.floor((this.getMonth() + 3) / 3), //quarter 
        "S": this.getMilliseconds() //millisecond 
    };
    if (/(y+)/.test(format)) {
        format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    }
    for (var k in o) {
        if (new RegExp("(" + k + ")").test(format)) {
            format = format.replace(RegExp.$1, RegExp.$1.length === 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
        }
    }
    return format;
};
Number.prototype.toFixed = function (d) {
    var s = this + "";
    if (!d) {
        d = 0;
    }
    if (s.indexOf(".") == -1) {
        s += ".";
    }
    s += new Array(d + 1).join("0");
    if (new RegExp("^(-|\\+)?(\\d+(\\.\\d{0," + (d + 1) + "})?)\\d*$").test(s)) {
        var s = "0" + RegExp.$2, pm = RegExp.$1, a = RegExp.$3.length, b = true;
        if (a == d + 2) {
            a = s.match(/\d/g);
            if (parseInt(a[a.length - 1]) > 4) {
                for (var i = a.length - 2; i >= 0; i--) {
                    a[i] = parseInt(a[i]) + 1;
                    if (a[i] == 10) {
                        a[i] = 0;
                        b = i != 1;
                    } else {
                        break;
                    }
                }
            }
            s = a.join("").replace(new RegExp("(\\d+)(\\d{" + d + "})\\d$"), "$1.$2");
        }
        if (b) {
            s = s.substr(1);
        }
        return (pm + s).replace(/\.$/, "");
    }
    return this + "";
};
/**
 * Lock UI
 */
(function ($) {
    $.fn.lock = function () {
        this.unlock();
        $('body').append('<div id="widget-lock-ui" class="lock-ui" style="position:absolute;width:100%;height:100%;top:0;left:0;z-index:1000;background-color:#000;cursor:wait;opacity:.7;filter: alpha(opacity=70);"><div>');
    };
    $.fn.unlock = function () {
        $('#widget-lock-ui').remove();
    };
})(jQuery);

$(function () {
    $(document).on('pjax:error', function (xhr, textStatus, error, options) {
        console.log(xhr);
        console.log(textStatus);
        console.log(error);
        console.log(options);
        layer.alert(textStatus.responseText);
    });
    $('.ajax').on('click', function () {
        var $this = $(this);
        $.ajax({
            type: 'POST',
            url: $this.attr('href'),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $this.remove();
                } else {
                    layer.alert(response.error.message);
                }
            }, error: function (XMLHttpRequest, textStatus, errorThrown) {
                layer.alert('[ ' + XMLHttpRequest.status + ' ] ' + XMLHttpRequest.responseText);
            }
        });
        
        return false;
    });
    
    $('#logout').click(function () {
        var $t = $(this);
        layer.confirm('?????????????????????????????????', function (index) {
            layer.close(index);
            window.location.href = $t.attr('href');
        });
        return false;
    });
});

function clone(myObj) {
    if (typeof (myObj) != 'object' || myObj == null)
        return myObj;
    var newObj = new Object();
    for (var i in myObj) {
        newObj[i] = clone(myObj[i]);
    }
    
    return newObj;
}

var yadjet = yadjet || {};
yadjet.icons = yadjet.icon || {};
yadjet.icons.boolean = [
    '/images/no.png',
    '/images/yes.png'
];
yadjet.utils = yadjet.utils || {
    getCsrfParam: function () {
        return $('meta[name=csrf-param]').attr('content');
    },
    getCsrfToken: function () {
        return $('meta[name=csrf-token]').attr('content');
    }
};
yadjet.actions = yadjet.actions || {
    toggle: function (selector, url) {
        var dataExt = arguments[2] ? arguments[2] : {},
            trData = arguments[3] ? arguments[3] : [],
            callback = arguments[4] ? arguments[4] : undefined;
        $(document).off(selector).on('click', selector, function (event) {
            event.stopImmediatePropagation();
            event.stopPropagation();
            var $this = $(this);
            var $tr = $this.parent().parent();
            var data = {
                id: $tr.attr('data-key'),
                _csrf: yadjet.utils.getCsrfToken()
            };
            for (var key in dataExt) {
                data[key] = dataExt[key];
            }
            console.info(trData);
            for (var key in trData) {
                // `data-key` To `dataKey`
                var t = trData[key].toLowerCase();
                t = t.replace(/\b\w+\b/g, function (word) {
                    return word.substring(0, 1).toUpperCase() + word.substring(1);
                });
                t = t.replace('-', '');
                t = t.substring(0, 1).toLowerCase() + t.substring(1);
                data[t] = $tr.attr('data-' + trData[key]);
            }
            console.info(data);
            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                dataType: 'json',
                beforeSend: function (xhr) {
                    $this.hide().parent().addClass('running-c-c');
                }, success: function (response) {
                    if (response.success) {
                        var data = response.data;
                        $this.attr('src', yadjet.icons.boolean[data.value ? 1 : 0]);
                        if (data.updatedAt) {
                            $tr.find('td.rb-updated-at').html(data.updatedAt);
                        }
                        if (data.updatedBy) {
                            $tr.find('td.rb-updated-by').html(data.updatedBy);
                        }
                        if (data.onOffDatetime) {
                            $tr.find('td.rb-on-off-datetime').html(data.onOffDatetime);
                        }
                        if (callback) {
                            callback(response);
                        }
                    } else {
                        layer.alert(response.error.message);
                    }
                    $this.show().parent().removeClass('running-c-c');
                }, error: function (XMLHttpRequest, textStatus, errorThrown) {
                    layer.alert('[ ' + XMLHttpRequest.status + ' ] ' + XMLHttpRequest.responseText);
                    $this.show().parent().removeClass('running-c-c');
                }
            });
            
            return false;
        });
    },
    gridColumnConfig: function () {
        jQuery(document).on('click', '#menu-buttons li a.grid-column-config', function () {
            var $this = $(this),
                gridId = $this.attr('data-grid-id');
            $.ajax({
                type: 'POST',
                url: $this.attr('href'),
                data: {models: $('#' + gridId + ' > table').attr('data-models')},
                beforeSend: function (xhr) {
                    $.fn.lock();
                }, success: function (response) {
                    layer.open({
                        title: '??????????????????',
                        content: '<div id="yad-grid-columns-setting-render">' + response + '</div>',
                        skin: 'layer-fix',
                        yes: function () {
                            $.pjax.reload({container: '#' + gridId});
                        }
                    });
                    $.fn.unlock();
                }, error: function (XMLHttpRequest, textStatus, errorThrown) {
                    layer.alert('[ ' + XMLHttpRequest.status + ' ] ' + XMLHttpRequest.responseText);
                    $.fn.unlock();
                }
            });
            
            return false;
        });
    }
};

yadjet.actions.gridColumnConfig();

$(function () {
    $('.tabs-common li a').on('click', function () {
        var $t = $(this),
            $widget = $t.parent().parent().parent();
        $t.parent().siblings().removeClass('active');
        $t.parent().addClass('active');
        $widget.find('.tab-panel').hide();
        $widget.find('#' + $t.attr('data-toggle')).show();
        return false;
    });
});
$(document).on('click', '.search-button a', function () {
    var $t = $(this);
    if ($t.attr('data-toggle') === 'show') {
        $t.attr('data-toggle', 'hide');
        $('.form-search').hide();
    } else {
        $t.attr('data-toggle', 'show');
        $('.form-search').show();
    }
    
    return false;
});
if ($('#mts-app').length) {
    var vm = new Vue({
        el: '#mts-app',
        data: {
            // ????????????????????????
            validators: {},
            meta: {
                // ??????????????????????????????
                validators: {}
            },
            // ????????? meta ??????
            metaObjects: {}
        },
        methods: {
            // ????????????????????????
            isEmptyObject: function (e) {
                var t;
                for (t in e)
                    return !1;
                return !0;
            }
        },
        computed: {
            // ?????? Meta ??????????????????????????????????????????????????????
            metaValidators: function () {
                var validators = [], validator;
                for (var validatorName in this.validators) {
                    validator = clone(this.validators[validatorName]);
                    validator.name = validatorName;
                    validator.active = false;
                    for (var j in this.meta.validators) {
                        if (this.meta.validators[j].name === validatorName) {
                            validator.active = true;
                            validator.options = this.meta.validators[j].options;
                            break;
                        }
                    }
                    validators.push(validator);
                }
                
                return validators;
            }
        }
    });
}
/**
 * ????????????
 * @type {Clipboard}
 */
if (document.getElementsByClassName('btn-copy').length) {
    var clipboard = new Clipboard('.btn-copy');
    clipboard.on('success', function (e) {
        var $o = $(e.trigger).attr('data-clipboard-target');
        layer.tips('????????????', $(e.trigger).attr('data-clipboard-target'), {tips: [2, '#000000']});
        e.clearSelection();
    });
    clipboard.on('error', function (e) {
        layer.tips('??????????????????????????????', $(e.trigger).attr('data-clipboard-target'), {tips: 4});
    });
}

