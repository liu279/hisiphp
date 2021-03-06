<div class="layui-collapse page-tips">
  <div class="layui-colla-item">
    <h2 class="layui-colla-title">重要提示</h2>
    <div class="layui-colla-content layui-show">
      <p class="red">为了防止升级过程中出现数据丢失等问题，建议您在升级前先备份数据库！！！<a href="{:url('admin/database/index')}">【点此备份数据库】</a></p>
    </div>
  </div>
</div>
<div class="layui-form" id="view">
    <table class="layui-table mt10" lay-even="" lay-skin="row">
        <tbody>
            <tr>
                <td align="center" style="padding:50px 0" class="mcolor font18" id="loading">请稍等，正在检查最新版......</td>
            </tr>
        </tbody>
    </table>
</div>
<script type="text/html" id="template">
    <table class="layui-table mt10" lay-even="" lay-skin="row">
        <thead>
            <tr>
                <th>新版本</th>
                <th>更新日志</th>
                <th>操作</th>
            </tr> 
        </thead>
        <tbody>
            {{#  layui.each(d, function(index, item){ }}
            <tr>
                <td>{{ item.version }}</td>
                <td>
                    {{#  layui.each(item.log, function(index, item2){ }}
                        {{#  if(index < 3 ){ }}
                            - {{ item2 }}<br>
                        {{#  } }}
                    {{#  }); }}
                    {{#  if(item.log.length > 3 ){ }}
                        <a href="javascript:;" class="mcolor2 j-show-log">点此查看更多...</a>
                    {{#  } }}
                    <div style="display:none">
                        {{#  layui.each(item.log, function(index2, item2){ }}
                            - {{ item2 }}<br>
                        {{#  }); }}
                    </div>
                </td>
                <td>
                    <a href="javascript:;" data-version="{{ item.version }}" class="layui-btn layui-btn-sm j-ajax-upgrade">更新至此版本</a>
                </td>
            </tr>
            {{#  }); }}
        </tbody>
    </table>
</script>
{include file="block/layui" /}
<script>
layui.use(['jquery', 'layer', 'laytpl'], function() {
    var $ = layui.jquery, layer = layui.layer;
    $(document).on('click', '.j-show-log', function(){
        var that = $(this);
        layer.open({title:'更新日志', content:that.siblings('div').html(), area: ['500px', '400px'], btn:['关闭']});
    });
    getVersion();
    // 执行升级，
    $(document).on('click', '.j-ajax-upgrade', function(){
        var that = $(this);
        layer.msg('正在获取 '+that.attr('data-version')+' 升级包....',{time:500000});
        $.ajax({
            type: "POST",
            url: '{:url("download")}',
            data: 'identifier={$identifier}&app_type={$app_type}&app_version={$app_version}&version='+that.attr('data-version'),
            success: function(res) {
                if (res.code == 1) {
                    layer.msg('升级包获取成功，正在安装 '+that.attr('data-version')+' ...', {time:50000});
                    $.ajax({
                        type: "POST",
                        url: '{:url("install")}',
                        data: 'identifier={$identifier}&app_type={$app_type}&app_version={$app_version}&file='+res.msg+'&version='+that.attr('data-version'),
                        success: function(res) {
                            layer.msg(res.msg, {}, function() {
                                location.href= res.url;
                            });
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            layer.msg('安装失败，请查看系统日志了解错误详情<br>[/runtime/log/{:date("Ym")}/{:date("d")}.log]', {time:10000});
                        }
                    });
                } else {
                    layer.msg(res.msg, {}, function(){
                        location.href= res.url;
                    });
                }
            }
        });
        return false;
    });
});

// 获取可升级版本
function getVersion() {
    var $ = layui.jquery,laytpl = layui.laytpl;
    $('#loading').html('请稍等，正在检查最新版......');
    $.ajax({
        type: "POST",
        url: '{:url("lists")}',
        data: 'identifier={$identifier}&app_type={$app_type}&app_version={$app_version}',
        success: function(res) {
            if (res.code == 1) {
                var getTpl = template.innerHTML;
                if (res.data == '') {
                    $('#loading').html('您当前的版本号已经是最新了哦！');
                    return false;
                }
                laytpl(getTpl).render(res.data, function(html) {
                  view.innerHTML = html;
                });
            } else {
                $('#loading').html('<span class="red">'+res.msg+'</span> <a href="javascript:;" onclick="getVersion()" class="mcolor2">点此刷新重试</a>');
            }
        }
    });
}
</script>