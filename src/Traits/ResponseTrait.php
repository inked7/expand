<?php

namespace Inked7\Expand\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Inked7\Expand\Utils\Response;

trait ResponseTrait
{
    public static $responseCodeKey = 3; // 1:code msg、2:code message、3:err_code err_msg、errcode errmsg

    public static function setResponseCodeKey(int $responseCodeKey = 1)
    {
        ResponseTrait::$responseCodeKey = $responseCodeKey;
    }

    public static function string2utf8($string = '')
    {
        if (empty($string)) {
            return $string;
        }

        $encoding_list = [
            "ASCII",'UTF-8',"GB2312","GBK",'BIG5'
        ];

        $encode = mb_detect_encoding($string, $encoding_list);

        $string = mb_convert_encoding($string, 'UTF-8', $encode);

        return $string;
    }

    public function customPaginate($items, $paginatorOrTotal, ?int $pageSize = 15, array $meta = [])
    {
        $items = (array) $items;

        $total = $paginatorOrTotal;
        if ($paginatorOrTotal instanceof LengthAwarePaginator) {
            $paginator = $paginatorOrTotal;
            $pageSize = $paginator->perPage();
            $total = $paginator->total();
        } else {
            if ($items instanceof Collection) {
                $total = $items->count();
            } else {
                $total = count($items);
            }
        }

        $pageSize = $pageSize ?? 15;

        $paginate = new LengthAwarePaginator(
            $items,
            $total,
            $pageSize,
            \request('page')
        );

        $paginate
            ->withPath('/'.\request()->path())
            ->withQueryString();

        return $this->paginate($paginate, null, $meta);
    }

    public function paginate($data, ?callable $callable = null, array $meta = [])
    {
        // 处理集合数据
        if ($data instanceof \Illuminate\Database\Eloquent\Collection) {
            return $this->success(array_map(function ($item) use ($callable) {
                if ($callable) {
                    return $callable($item) ?? $item;
                }

                return $item;
            }, $data->all()));
        }

        // 处理非分页数据
        if (! $data instanceof LengthAwarePaginator) {
            return $this->success($data);
        }

        // 处理分页数据
        $paginate = $data;
        return $this->success([
            'meta' => array_merge([
                'total' => $paginate->total(),
                'current_page' => $paginate->currentPage(),
                'page_size' => $paginate->perPage(),
                'last_page' => $paginate->lastPage(),
            ], $meta),
            'data' => array_map(function ($item) use ($callable) {
                if ($callable) {
                    return $callable($item) ?? $item;
                }

                return $item;
            }, $paginate ? $paginate->items() : []),
        ]);
    }

    public function success($data = [], $err_msg = 'success', $err_code = 200, $headers = [])
    {
        if (is_string($data)) {
            $err_code = is_string($err_msg) ? $err_code : $err_msg;
            $err_msg = $data;
            $data = [];
        }

        // 处理 meta 数据
        $meta = [];
        if (isset($data['data']) && isset($data['meta'])) {
            extract($data);
        }

        $err_msg = static::string2utf8($err_msg);

        if ($err_code === 200 && ($config_err_code = config('laravel-init-template.response.err_code', 200)) !== $err_code) {
            $err_code = $config_err_code;
        }

        $data = $data ?: null;

        switch (ResponseTrait::$responseCodeKey) {
            case 1:
                $res = ['err_code' => $err_code, 'err_msg' => $err_msg, 'data' => $data];
                break;
            case 2:
                $res = ['code' => $err_code, 'message' => $err_msg, 'data' => $data,];
                break;
            case 3:
                $res = ['code' => $err_code, 'msg' => $err_msg, 'data' => $data,];
                break;
            case 4:
                $res = ['errcode' => $err_code, 'errmsg' => $err_msg, 'data' => $data,];
                break;
            default:
                $res = ['code' => $err_code, 'msg' => $err_msg, 'data' => $data,];
                break;
        }

        $res = $res + array_filter(compact('meta'));

        return \response(
            \json_encode($res, \JSON_UNESCAPED_SLASHES|\JSON_PRETTY_PRINT),
            Response::HTTP_OK,
            array_merge([
                'Content-Type' => 'application/json',
            ], $headers)
        );
    }

    public function fail($err_msg = 'unknown error', $err_code = 400, $data = [], $headers = [])
    {
        switch (ResponseTrait::$responseCodeKey) {
            case 1:
                $res = ['err_code' => $err_code, 'err_msg' => $err_msg, 'data' => $data];
                break;
            case 2:
                $res = ['code' => $err_code, 'message' => $err_msg, 'data' => $data,];
                break;
            case 3:
                $res = ['code' => $err_code, 'msg' => $err_msg, 'data' => $data,];
                break;
            case 4:
                $res = ['errcode' => $err_code, 'errmsg' => $err_msg, 'data' => $data,];
                break;
            default:
                $res = ['code' => $err_code, 'msg' => $err_msg, 'data' => $data,];
                break;
        }

        if (! \request()->wantsJson()) {
            $err_msg = \json_encode($res, \JSON_UNESCAPED_SLASHES|\JSON_PRETTY_PRINT);
            if (!array_key_exists($err_code, Response::$statusTexts)) {
                $err_code = 500;
            }

            return \response(
                $err_msg,
                $err_code,
                array_merge([
                    'Content-Type' => 'application/json',
                ], $headers)
            );
        }

        return $this->success($data, $err_msg ?: 'unknown error', $err_code ?: 500, $headers);
    }

    public function reportableHandle()
    {
        return function (\Throwable $e) {
            //
        };
    }

    public function renderableHandle()
    {
        return function (\Throwable $e) {
            if (! \request()->wantsJson()) {
                return;
            }

            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return $this->fail('未登录', $e->getCode() ?: config('laravel-init-template.auth.unauthorize_code', 401));
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException) {
                if (\request()->wantsJson()) {
                    return $this->fail('未授权', $e->getStatusCode());
                }

                return \response()->noContent($e->getStatusCode(), $e->getHeaders());
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $message = '请求失败';
                if ($e->getStatusCode() == 403) {
                    $message = '拒绝访问';
                }

                return $this->fail($message, $e->getStatusCode());
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return $this->fail($e->validator->errors()->first(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->fail('404 Data Not Found.', Response::HTTP_NOT_FOUND);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return $this->fail('404 Url Not Found.', Response::HTTP_NOT_FOUND);
            }

            $code = $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR;
            if (method_exists($e, 'getStatusCode')) {
                $code = $e->getStatusCode();
            }

            \info('error', [
                'class' => get_class($e),
                'code' => $code,
                'message' => $e->getMessage(),
                'file_line' => sprintf('%s:%s', $e->getFile(), $e->getLine()),
            ]);

            return $this->fail($e->getMessage(), $code);
        };
    }
}
