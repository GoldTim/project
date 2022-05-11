@extends('public.table')
@section('tableList')
    <script type="text/html" id="condition-container-box">
        <div class="layui-form-item layui-row">
            <div class="layui-inline layui-col-sm3">
                <label class="layui-form-label">编号</label>
                <div class="layui-input-inline">
                    <input placeholder="请输入编号编号" type="text" class="layui-input" name="code" id="code"/>
                </div>
            </div>
        </div>
    </script>
    <script type="text/html" id="field">
        <div></div>
    </script>
    <script type="text/javascript">
        layui.define(['table'], function () {
            let table = layui.table;
            let options = {
                columns: [
                    {field: '', title: '', align: 'center'},
                    {field: '', formatter: ''}
                ],
                height: 'auto',
                single: 1,
                enable_condition: true,
                toolbar: false,
                table_url: '{{route('admin.order.list')}}',
                add_title: '新建',
                table_url_param: {
                    status: 1
                },
                other_click_active: {
                    view: () => {
                        table.reload('LAY-table-list');
                    }
                }
            };
            (layui.page_init = function () {
                if (!layui.init || layui.execute_page_init) {
                    return;
                }
                layui.execute_page_init = true;
                layui.init(options);

            })();
        });
    </script>
@endsection
