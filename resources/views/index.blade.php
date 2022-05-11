@extends('public.table')
@section('tableList')
    <script type="text/javascript">
        layui.define(['table'], function () {
            let table = layui.table;
            let options = {
                columns: [
                    {field: '', title: '标题', align: 'center'}
                ],
                height: 'auto',
                single: 1,
                enable_condition: true,
                toolbar: false,
                method: 'post',
                table_url: '{{route('admin.order.list')}}',
                add_title: '新建',
                table_url_param: {},
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
