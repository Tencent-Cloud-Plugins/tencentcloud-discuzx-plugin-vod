<?php
/*
 * Copyright (C) 2020 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace TencentDiscuzVOD;
class VODOptions
{
    //使用全局密钥
    const GLOBAL_KEY = 0;
    //使用自定义密钥
    const CUSTOM_KEY = 1;
    //转自适应码流
    const HLS_TRANSCODE = 1;
    const DO_NOT_TRANSCODE = 0;

    private $commonOptions;
    private $secretID;
    private $secretKey;
    private $appID;
    private $customKey;
    private $transcode;
    public function __construct($customKey = self::GLOBAL_KEY, $secretID = '', $secretKey = '',$appID = '',$transcode = self::HLS_TRANSCODE)
    {
        $this->customKey = intval($customKey);
        $this->secretID = $secretID;
        $this->secretKey = $secretKey;
        $this->appID = $appID;
        $this->transcode = $transcode;
        global $_G;
        if (isset($_G['setting']['tencentcloud_center'])) {
            $this->commonOptions = unserialize($_G['setting']['tencentcloud_center']);
        }
    }
    /**
     * 获取全局的配置项
     */
    public function getCommonOptions()
    {
        return $this->commonOptions;
    }

    public function setSecretID($secretID)
    {
        $this->secretID = $secretID;
    }

    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function setAppID($appID)
    {
        $this->appID = $appID;
    }
    public function setCustomKey($customKey)
    {
        if ( !in_array($customKey, array(self::GLOBAL_KEY, self::CUSTOM_KEY)) ) {
            throw new \Exception('自定义密钥传参错误');
        }
        $this->customKey = intval($customKey);
    }

    public function setTranscode($transcode)
    {
        $this->transcode = intval($transcode);
    }

    public function getSecretID()
    {
        if ( $this->customKey === self::GLOBAL_KEY && isset($this->commonOptions['secretId']) ) {
            $this->secretID = $this->commonOptions['secretId'] ?: '';
        }
        return $this->secretID;
    }
    public function getSecretKey()
    {
        if ( $this->customKey === self::GLOBAL_KEY && isset($this->commonOptions['secretKey']) ) {
            $this->secretKey = $this->commonOptions['secretKey'] ?: '';
        }
        return $this->secretKey;
    }
    public function getCustomKey()
    {
        return $this->customKey;
    }
    public function getAppID()
    {
        return $this->appID;
    }

    public function getTranscode()
    {
       return $this->transcode;
    }

    public function toArray()
    {
        return array(
            'secretId'=>$this->secretID,
            'customKey'=>$this->customKey,
            'secretKey'=>$this->secretKey,
            'appID'=>$this->appID,
            'transcode'=>$this->transcode,
        );
    }
}
