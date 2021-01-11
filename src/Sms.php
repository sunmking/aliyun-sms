<?php

namespace saviorlv\aliyun;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

/**
 * 阿里 短信SDK
 * User: clyde-cn
 * Date: 2021/1/11
 * Time: 上午11:54
 */
class Sms
{
    // 短信API产品名
    private    $product = "Dysmsapi";
    // 短信API产品域名
    private    $domain = "dysmsapi.aliyuncs.com";
    // 暂时不支持多Region
    private    $region = "cn-hangzhou";
    // 服务结点
    private    $endPointName = "cn-hangzhou";
    // accessKeyId
    public $accessKeyId;
    // accessKeySecret
    public $accessKeySecret;

    /**
     * Sms constructor.
     * @param $accessKeyId
     * @param $accessKeySecret
     */
    public function __construct($accessKeyId,$accessKeySecret)
    {
        if(!$accessKeyId){
            throw new \Exception('accessKeyId can not be blank.');
        }else{
            $this->accessKeyId = $accessKeyId;
        }
        if(!$accessKeySecret){
            throw new \Exception('accessKeySecret can not be blank.');
        }else{
            $this->accessKeySecret = $accessKeySecret;
        }

        AlibabaCloud::accessKeyClient($this->accessKeyId, $this->accessKeySecret)
                        ->regionId($this->region)
                        ->asDefaultClient();
    }

    /**
     * 发送短信范例
     *
     * @param string $signName <p>
     * 必填, 短信签名，应严格"签名名称"填写
     * </p>
     * @param string $templateCode <p>
     * 必填, 短信模板Code，应严格按"模板CODE"填写
     * (e.g. SMS_0001)
     * </p>
     * @param string $phoneNumbers 必填, 短信接收号码 (e.g. 12345678901)
     * @param array|null $templateParam <p>
     * 选填, 假如模板中存在变量需要替换则为必填项 (e.g. Array("code"=>"12345", "product"=>"阿里通信"))
     * </p>
     * @param string|null $outId [optional] 选填, 发送短信流水号 (e.g. 1234)
     * @return stdClass
     */
    public function sendSms($signName, $templateCode, $phoneNumbers, $templateParam = null, $outId = null) {

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = [
            'RegionId' =>  $this->region,
        ];

        // 必填，设置雉短信接收号码
        $request['PhoneNumbers'] = $phoneNumbers;

        // 必填，设置签名名称
        $request['SignName'] = $signName;

        // 必填，设置模板CODE
        $request['TemplateCode'] = $templateCode;

        // 可选，设置模板参数
        if($templateParam) {
            $request['TemplateParam'] = json_encode($templateParam);
        }

        // 可选，设置流水号
        if($outId) {
            $request['OutId'] = $outId;
        }

        try {
            $result = AlibabaCloud::rpc()
                    ->product('Dysmsapi')
                    // ->scheme('https') // https | http
                    ->version('2017-05-25')
                    ->action('SendSms')
                    ->method('POST')
                    ->host('dysmsapi.aliyuncs.com')
                    ->options([
                                'query' => $request
                            ])
                    ->request();

            $response  = $result->toArray();
        } catch (ClientException $e) {
            return json_encode([
                'code' => $e->getErrorCode(),
                'message' => $e->getErrorMessage()
            ]);
        } catch (ServerException $e) {
            return json_encode([
                'code' => $e->getErrorCode(),
                'message' => $e->getErrorMessage()
            ]);
        }
        

        if(array_key_exists('Message', $response) && $response['Code']=='OK'){
            return json_encode([
                'code' => 200,
                'message' => '验证码发送成功'
            ]);
        }
        return Utils::result($response);
    }

    /**
     * 批量发送短信
     * @param $signName
     * @param $templateCode
     * @param $phoneNumbers
     * @param null $templateParam
     * @return false|string
     */
    public function sendBatchSms($signName, $templateCode, $phoneNumbers, $templateParam = null) {

         // 初始化SendSmsRequest实例用于设置发送短信的参数
         $request = [
            'RegionId' =>  $this->region,
        ];

        // 必填，设置雉短信接收号码
        $request['PhoneNumberJson'] = json_encode($phoneNumbers, JSON_UNESCAPED_UNICODE);

        // 必填，设置签名名称
        $request['SignNameJson'] =json_encode($signName, JSON_UNESCAPED_UNICODE);

        // 必填，设置模板CODE
        $request['TemplateCode'] = $templateCode;

        // 可选，设置模板参数
        if($templateParam) {
            $request['TemplateParamJson'] = json_encode($templateParam, JSON_UNESCAPED_UNICODE);
        }

        try {
            $result = AlibabaCloud::rpc()
                    ->product('Dysmsapi')
                    // ->scheme('https') // https | http
                    ->version('2017-05-25')
                    ->action('SendBatchSms')
                    ->method('POST')
                    ->host('dysmsapi.aliyuncs.com')
                    ->options([
                                'query' => $request
                            ])
                    ->request();

            $response  = $result->toArray();
        } catch (ClientException $e) {
            return json_encode([
                'code' => $e->getErrorCode(),
                'message' => $e->getErrorMessage()
            ]);
        } catch (ServerException $e) {
            return json_encode([
                'code' => $e->getErrorCode(),
                'message' => $e->getErrorMessage()
            ]);
        }
        

        if(array_key_exists('Message', $response) && $response['Code']=='OK'){
            return json_encode([
                'code' => 200,
                'message' => '验证码发送成功'
            ]);
        }
        return Utils::result($response);
    }

    /**
     * 查询短信发送情况范例
     *
     * @param string $phoneNumbers 必填, 短信接收号码 (e.g. 12345678901)
     * @param string $sendDate 必填，短信发送日期，格式Ymd，支持近30天记录查询 (e.g. 20170710)
     * @param int $pageSize 必填，分页大小
     * @param int $currentPage 必填，当前页码
     * @param string $bizId 选填，短信发送流水号 (e.g. abc123)
     * @return stdClass
     */
    public function queryDetails($phoneNumbers, $sendDate, $pageSize = 10, $currentPage = 1, $bizId=null) {
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = [
            'RegionId' =>  $this->region,
        ];
    
             // 必填，短信接收号码
        $request['PhoneNumber'] = $phoneNumbers;

        // 选填，短信发送流水号
        $request['BizId'] = $bizId;

        // 必填，短信发送日期，支持近30天记录查询，格式Ymd
        $request['SendDate'] = $sendDate;

        // 必填，分页大小
        $request['PageSize'] = $pageSize;

        // 必填，当前页码
        $request['CurrentPage'] = $currentPage;
    
        try {
            $result = AlibabaCloud::rpc()
                    ->product('Dysmsapi')
                    // ->scheme('https') // https | http
                    ->version('2017-05-25')
                    ->action('QuerySendDetails')
                    ->method('POST')
                    ->host('dysmsapi.aliyuncs.com')
                    ->options([
                                'query' => $request
                            ])
                    ->request();

            $response  = $result->toArray();
        } catch (ClientException $e) {
            return json_encode([
                'code' => $e->getErrorCode(),
                'message' => $e->getErrorMessage()
            ]);
        } catch (ServerException $e) {
            return json_encode([
                'code' => $e->getErrorCode(),
                'message' => $e->getErrorMessage()
            ]);
        }
        

        if(array_key_exists('Message', $response) && $response['Code']=='OK'){
            return json_encode([
                'code' => 200,
                'message' => '验证码发送成功'
            ]);
        }
        return Utils::result($response);
    }

    /**
     * 对象转数组
     * @param $array
     * @return array
     */
    public static function object_array($array) {
        if(is_object($array)) {
            $array = (array)$array;
         } if(is_array($array)) {
            foreach($array as $key=>$value) {
                $array[$key] = self::object_array($value);
            }
        }
        return $array;
    }
}
