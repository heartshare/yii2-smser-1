<?php

namespace daixianceng\smser;

use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;

/**
 * 凌凯短信平台
 * 
 * @author Cosmo <daixianceng@gmail.com>
 * @property string $password write-only password
 * @property string $state read-only state
 * @property string $message read-only message
 */
class LkSmser extends Smser
{
    /**
     * @inheritdoc
     */
    public $url = 'http://mb345.com:999/ws/LinkWS.asmx?wsdl';
    
    /**
     * @inheritdoc
     */
    public function send($mobile, $content)
    {
        if (parent::send($mobile, $content)) {
            return true;
        }
        
        $data = [
            'CorpID' => $this->username,
            'Pwd' => $this->password,
            'Mobile' => $mobile,
            'Content' => self::charsetFormat(['con' => $content],'UTF-8'),
            'Cell'=>'',
            'SendTime'=>''
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        $resultArr = [];
        parse_str($result, $resultArr);
        
        $this->state = isset($resultArr['stat']) ? (string) $resultArr['stat'] : null;
        $this->message = isset($resultArr['message']) ? (string) $resultArr['message'] : null;
        
        return $this->state === '100';
    }

    /**
     * 通用转码处理
     *
     * @param array $arr 要转码的数组
     * @param string $out_charset 输出的字符集
     * @return array
     */
    public static function charsetFormat(array $arr,$out_charset){
        $in_charset = mb_detect_encoding(var_export($arr, 1), ["ASCII","UTF-8","GB2312","GBK","BIG5"]);
        return eval('return ' . mb_convert_encoding(var_export($arr, 1), $out_charset,$in_charset) . ';');
    }
}