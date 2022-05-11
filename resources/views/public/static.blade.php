<!DOCTYPE html>
<html lang="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{config('app.name')}}</title>
    <style>
        input[readonly]:focus {
            border-color: #e6e6e6 !important;
        }

        input[readonly] {
            cursor: default !important;
        }
    </style>
    <script src="{{asset('js/jquery.min.js')}}"></script>
    <script src="{{asset('js/layui/layui.js?v2')}}"></script>
    <script src="{{asset('js/common.js')}}"></script>
</head>
<body>
@yield('content')
</body>
</html>
