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

use C;
use DB;
use Exception;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Vod\V20180717\Models\DescribeMediaInfosRequest;
use TencentCloud\Vod\V20180717\Models\ProcessMediaRequest;
use TencentCloud\Vod\V20180717\VodClient;
use Vod\VodUploadClient;
use Vod\Model\VodUploadRequest;

defined('TENCENT_DISCUZX_VOD_PLUGIN_NAME') || define('TENCENT_DISCUZX_VOD_PLUGIN_NAME', 'tencentcloud_vod');

class VODActions
{
    const PLUGIN_TYPE = 'vod';

    /**
     * post参数过滤
     * @param $key
     * @param string $default
     * @return string|void
     */
    public function filterPostParam($key, $default = '')
    {
        return isset($_POST[$key]) ? dhtmlspecialchars($_POST[$key]) : $default;
    }

    /**
     * get参数过滤
     * @param $key
     * @param string $default
     * @return string|void
     */
    public function filterGetParam($key, $default = '')
    {
        return isset($_GET[$key]) ? dhtmlspecialchars($_GET[$key]) : $default;
    }

    /**
     * 获取媒体信息
     * @param $fileID
     * @return string
     */
    public function medianInfo($fileID)
    {
        try {
            $VODOptions = self::getVODOptionsObject();
            $cred = new Credential($VODOptions->getSecretID(), $VODOptions->getSecretKey());
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("vod.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new VodClient($cred, "", $clientProfile);
            $req = new DescribeMediaInfosRequest();

            $params = array(
                "FileIds" => array(
                    $fileID
                )
            );
            $req->fromJsonString(json_encode($params));
            $resp = $client->DescribeMediaInfos($req);
            if ( empty($resp->MediaInfoSet) ) {
                return '';
            }
            return $resp->MediaInfoSet[0]->BasicInfo->MediaUrl;
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * 解析帖子内容中的vod播放器
     * @param $content
     * @param $playerID
     * @return string|string[]|null
     * @throws Exception
     */
    public function parseContentPlayer($content, $playerID)
    {
        $pattern = '/\[tcplayer\](\d+)\[\/tcplayer\]/';
        $matches = array();
        preg_match($pattern, $content, $matches);
        if ( empty($matches) || !is_numeric($matches[1]) ) {
            return $content;
        }
        $VODOptions = self::getVODOptionsObject();
        $fileID = $matches[1];
        if ( $VODOptions->getTranscode() === $VODOptions::DO_NOT_TRANSCODE ) {
            $url = $this->medianInfo($fileID);
            include template('tencentcloud_vod:player');
            $player = str_replace("\n", '', $player);
            return preg_replace($pattern, $player, $content);
        }
        $appID = $VODOptions->getAppID();
        $playerID = 'player-container-id-' . $playerID;
        include template('tencentcloud_vod:tcplayer');
        $tcplayer = str_replace("\n", '', $tcplayer);
        return preg_replace($pattern, $tcplayer, $content);
    }


    /**
     * 获取配置对象
     * @return VODOptions
     * @throws Exception
     */
    public static function getVODOptionsObject()
    {
        global $_G;
        $VODOptions = new VODOptions();
        $options = $_G['setting'][TENCENT_DISCUZX_VOD_PLUGIN_NAME];
        if ( empty($options) ) {
            $options = C::t('common_setting')->fetch(TENCENT_DISCUZX_VOD_PLUGIN_NAME);
        }
        if ( empty($options) ) {
            return $VODOptions;
        }
        $options = unserialize($options);
        $VODOptions->setCustomKey($options['customKey']);
        $VODOptions->setSecretID($options['secretId']);
        $VODOptions->setSecretKey($options['secretKey']);
        $VODOptions->setAppID($options['appID']);
        $VODOptions->setTranscode($options['transcode']);
        return $VODOptions;
    }

    public static function uploadDzxStatisticsData($action)
    {
        try {
            $file = DISCUZ_ROOT . './source/plugin/tencentcloud_center/lib/tencentcloud_helper.class.php';
            if ( !is_file($file) ) {
                return;
            }
            require_once $file;
            $data['action'] = $action;
            $data['plugin_type'] = self::PLUGIN_TYPE;
            $data['data']['site_url'] = \TencentCloudHelper::siteUrl();
            $data['data']['site_app'] = \TencentCloudHelper::getDiscuzSiteApp();
            $data['data']['site_id'] = \TencentCloudHelper::getDiscuzSiteID();
            $options = self::getVODOptionsObject();
            $data['data']['uin'] = \TencentCloudHelper::getUserUinBySecret(
                $options->getSecretID(),
                $options->getSecretKey()
            );
            $data['data']['cust_sec_on'] = $options->getCustomKey() === $options::CUSTOM_KEY ? 1 : 2;

            \TencentCloudHelper::sendUserExperienceInfo($data);
        } catch (\Exception $exception) {
            return;
        }

    }
}
