<?php
namespace QCloud_WeApp_SDK\Tunnel;

use \QCloud_WeApp_SDK\Conf as Conf;

class Signature {
    /**
     * 计算签名
     */
    public static function compute($input) {
        return sha1($input . config('qcloud.tunnel_signature_key'), FALSE);
    }

    /**
     * 校验签名
     */
    public static function check($input, $signature) {
        // 不需要校验签名
        if (!config('qcloud.tunnel_check_signature')) {
            return TRUE;
        }

        return self::compute($input) === $signature;
    }
}
