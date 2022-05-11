<?php
/**
 * 失败业务返回
 * @param $message
 * @param int $code
 * @return array
 */
function error($message, int $code = 500): array
{
    return ['succeed' => false, 'message' => $message, 'code' => $code];
}

/**
 * 成功返回
 * @param  $data
 * @return array
 */
function succeed($data = null): array
{
    return ['succeed' => true, 'data' => $data];
}

/**
 * 生成单号
 */
if (!function_exists('getSn')) {
    /**
     * 生成编码
     * @param $prefix
     * @param $table
     * @param $field
     * @return string
     */
    function getSn($prefix, $table, $field): string
    {
        $sn = $prefix . date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        $count = \Illuminate\Support\Facades\DB::table($table)->where($field, $sn)->count();
        if ($count > 0) {
            $sn = getSn($prefix, $table, $field);
        }
        return $sn;
    }
}

/**
 * 获取提交的数据
 * @return mixed
 */
if (!function_exists('getPostData')) {
    function getPostData()
    {
        return @json_decode(file_get_contents("php://input"), true);
    }
}

/**
 * 接口返回信息
 */
if (!function_exists('ajaxDataHandle')) {
    function ajaxDataHandle($data): \Illuminate\Http\JsonResponse
    {
        if (empty($data) || !isset($data['succeed']) || $data['succeed'] === false) {
            return ajaxError('异常请求');
        }
        return ajaxSucceed($data);
    }
}

/**
 * 接口返回
 */
if (!function_exists('ajaxSucceed')) {
    function ajaxSucceed($data = [], $message = 'Success', $code = 0): \Illuminate\Http\JsonResponse
    {
        return response()->json(['data' => $data, 'message' => $message, 'error_code' => 0]);
    }
}

/**
 * 接口错误返回
 */
if (!function_exists('ajaxError')) {
    function ajaxError($message, $code = 0): \Illuminate\Http\JsonResponse
    {
        return response()->json(['message' => $message, 'error_code' => $code], 400);
    }
}

