<?php

// outputJson
if (!function_exists('outputJson')) {
    function outputJson($ret, $msg = '', $data = []) {
        return json_encode(
            [
                'ret' => (string)$ret,
                'msg' => $msg ? $msg : ($ret == 1 ? 'success' : 'fail'),
                'data' => $data
            ]
        );
    }
}

// urlDecode
if (!function_exists('urlDecode')) {
    function urlDecode($data) {
        if (is_array($data)) {
            $ret = [];
            foreach ($data as $k => $v) {
                $ret[$k] = urldecode($v);
            }
            return $ret;
        } else {
            return urldecode($data);
        }
    }
}

// checkParams
if (!function_exists('checkParams')) {
    function checkParams($parmas, $type = 'post', $mustParams = [], $urlDecode = false) {
        $parmas = is_array($parmas) ? $parmas : array($parmas);
        //获取的数据类型
        switch ($type) {
            case 'post':
                $data = \SilangPHP\SilangPHP::$request->posts;
                break;
            case 'get':
                $data = \SilangPHP\SilangPHP::$request->gets;
                break;
            case 'request':
                $data = \SilangPHP\SilangPHP::$request->request;
                break;
            case 'json':
                $data = json_decode(\SilangPHP\SilangPHP::$request->getRaw(), true);
                break;
            case 'xml':
                $data = json_decode(json_encode(simplexml_load_string(\SilangPHP\SilangPHP::$request->getRaw())), true);
                break;
            default:
                $data = \SilangPHP\SilangPHP::$request->posts;
        }
        $ret = [];

        //需要urldecode的字段
        $urlDecodeAry = $urlDecode && is_array($urlDecode) ? $urlDecode
            : ($urlDecode && is_string($urlDecode) ? array($urlDecode) : []);
        //过滤逻辑
        foreach ($parmas as $parma) {
            if (!isset($data[$parma]) || $data[$parma] === '') {
                if (in_array($parma, $mustParams)) {
                    $uri = $_SERVER["REQUEST_URI"];
                    $query = $_SERVER["QUERY_STRING"];
                    throw new \Exception('参数错误:' . $parma . "参数不能为空");
                } else {
                    if(!isset($data[$parma])) {
                        continue;
                    }
                }
            }
            if (!in_array($parma, $urlDecodeAry)) {
                $ret[$parma] = $data[$parma];
            } else {
                $ret[$parma] = urlDecode($data[$parma]);
            }
        }
        return $ret;
    }
}

if (!function_exists('randomCode')) {
    function randomCode($length = 6, $onlyNum = false) {
        mt_srand((double)microtime() * 1000000);
        $hash = '';
        $chars = 'ALLENABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        if ($onlyNum) {
            $chars = '01234567890123456789';
        }
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }
}

if (!function_exists('xmlCurl')) {
    function xmlCurl($url, $arr) {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        $ch = curl_init();

        //https的设置
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            throw new \Exception("curlerror, {$error}", $errno);
        }
        curl_close($ch);
        return $result;
    }
}

