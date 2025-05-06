<?php
/**
 * @Notes: 响应工具类
 * @Date: 2024/3/18
 * @Time: 10:56
 * @Interface Response
 * @return
 */


namespace App\Libraries;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Lang;

class Response
{

    /**
     * Notes: 操作成功
     * Date: 2024/3/18 11:06
     * @param array $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public static function success(array $data = [], string $message = '', int $code = 200): JsonResponse
    {
        if (empty($message)) $message = Lang::get('Errors.200000');
        return response()->json(self::formatData([
            'code' => $code,
            'msg'  => $message,
            'data' => $data
        ]), 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Notes: 操作失败 返回失败原因
     * Date: 2024/3/21 16:14
     * @param $message
     * @param array $data
     * @param int $code
     * @return JsonResponse
     */
    public static function fail($message, $data = [], $code = 500): JsonResponse
    {
        return response()->json(self::formatData([
            'code' => $code,
            'msg'  =>  $message,
            'data' => $data
        ]), 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Notes: 操作异常  //
     * Date: 2024/3/21 16:14
     * @param $e
     * @param string $message
     * @param array $data
     * @param int $code
     * @return JsonResponse
     */
    public static function error($e, $message = '操作异常', $data = [], $code = 500): JsonResponse
    {
        report($e);  // 记录异常全局日志
        if (empty($message)) $message = Lang::get('web.50001');
        return response()->json([
            'code' => $code,
            'msg'  =>  $message,
            'data' => $data
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Notes:  对象转数组
     * Date: 2024/4/1 19:31
     * @param $object
     * @return mixed
     */
    public  static function objToArr($object)
    {
        return json_decode(json_encode($object), true);
    }

    //分页响应
    public static function successPaginate($paginate,string $message = '', int $code = 200) {
        if (empty($message)) $message = Lang::get('Errors.200000');

        return response()->json([
            'code' => $code,
            'msg'  => $message,
            'data' => [
                'list'    => $paginate->items(),
                'total'   => $paginate->total(),
                'size'    => $paginate->perPage(),
                'current' => $paginate->currentPage(),
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    protected static function formatData($data){
        //获取请求头中的platform
        $platform = request()->header('Platform');
        if (!empty($platform) && strtolower($platform) == 'temu') {
            return self::TemuResponseData($data);
        }
        return $data;
    }

    //Temu平台响应数据 格式
    protected static function TemuResponseData($data){
        $success   = true;
        $errorCode = 0;
        $errorMsg  = '';
        if ($data['code'] != 200) {
            $success   = false;
            $errorCode = $data['code'];
            $errorMsg  = $data['msg'];
        }
        return [
            'success'    => $success,
            'errorCode'  => $errorCode,
            'errorMsg'   => $errorMsg,
            'serverTime' => time(),
            'result'     => $data['data'] ?? []
        ];
    }

    /**
     * 列表数据响应[数组]
     * @param int $code
     * @param array $data
     * @param string $msg
     * @return false|string
     */
    public static function paginateList($data = [], $code = 200, $msg = '操作成功！')
    {
        $return = [
            'code' => $code,
            'msg'  => $msg,
            'data' => [
                'list'    => $data['list'] ?? [],
                'total'   => $data['total'] ?? 0,
                'size'    => $data['size'] ?? 0,
                'current' => $data['current'] ?? 1
            ]
        ];
        return json_encode($return);
    }
}
