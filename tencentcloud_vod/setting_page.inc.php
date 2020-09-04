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
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
defined('TENCENT_DISCUZX_VOD_DIR')||define( 'TENCENT_DISCUZX_VOD_DIR', __DIR__.DIRECTORY_SEPARATOR);
if (!is_file(TENCENT_DISCUZX_VOD_DIR.'vendor/autoload.php')) {
    exit(lang('plugin/tencentcloud_vod','require_sdk'));
}
require_once 'vendor/autoload.php';
use TencentDiscuzVOD\VODActions;
use TencentDiscuzVOD\VODOptions;

try {
    //不是ajax请求直接返回html页面
    if( $_SERVER['REQUEST_METHOD'] !== 'POST') {
        $options = VODActions::getVODOptionsObject();
        $secretId = $options->getSecretID();
        $secretKey = $options->getSecretKey();
        $appID = $options->getAppID();
        $customKey = $options->getCustomKey();
        $transcode = $options->getTranscode();
        include template('tencentcloud_vod:setting_page');
        exit;
    }
    $dzxVOD = new VODActions();
    $customKey = intval($dzxVOD->filterPostParam('customKey',VODOptions::GLOBAL_KEY));
    $secretId = $dzxVOD->filterPostParam('secretId');
    $secretKey = $dzxVOD->filterPostParam('secretKey');
    $appID = $dzxVOD->filterPostParam('appID');
    $transcode = intval($dzxVOD->filterPostParam('transcode'));
    if ($customKey !== VODOptions::GLOBAL_KEY) {
        if (empty($secretId)) {
            cpmsg('tencentcloud_vod:secret_id_error', '', 'error');
        }
        if (empty($secretKey)) {
            cpmsg('tencentcloud_vod:secret_key_error', '', 'error');
        }
    }

    if (empty($appID)) {
        cpmsg('tencentcloud_vod:app_id_error', '', 'error');
    }
    $options = VODActions::getVODOptionsObject();
    $options->setCustomKey($customKey);
    $options->setSecretID($secretId);
    $options->setSecretKey($secretKey);
    $options->setAppID($appID);
    $options->setTranscode($transcode);

    C::t('common_setting')->update_batch(array("tencentcloud_vod" => $options->toArray()));
    updatecache('setting');
    VODActions::uploadDzxStatisticsData('save_config');

    $url = 'action=plugins&operation=config&do='.$pluginid.'&identifier=tencentcloud_vod&pmod=setting_page';
    cpmsg('plugins_edit_succeed', $url, 'succeed');
}catch (\Exception $exception) {
    cpmsg($exception->getMessage(), '', 'error');
}
