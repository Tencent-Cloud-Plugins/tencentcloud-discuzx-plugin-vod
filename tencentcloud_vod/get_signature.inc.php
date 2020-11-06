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
if ( !defined('IN_DISCUZ') ) {
    exit('Access Denied');
}

use TencentDiscuzVOD\VODActions;

try {
    $VODOptions = VODActions::getVODOptionsObject();
    $secretId = $VODOptions->getSecretID();
    $secretKey = $VODOptions->getSecretKey();
    $appId = $VODOptions->getAppID();
    if ( empty($secretId) || empty($secretKey) || empty($appId)) {
        echo json_encode(array('code' => 1, 'msg' => lang('plugin/tencentcloud_vod', 'secret_id_error') . ' ,请联系管理员解决'));
        exit();
    }

    $current = time();
    $expired = $current + 28800;  // 签名有效期：8小时
    // 向参数列表填入参数
    $argList = array(
        'secretId' => $secretId,
        'currentTimeStamp' => $current,
        'expireTime' => $expired,
        'random' => mt_rand(),
        'vodSubAppId'=>$appId,
    );
    if ($VODOptions->getTranscode() === $VODOptions::HLS_TRANSCODE) {
        //预设任务转自适应码流
        $argList['procedure'] = 'LongVideoPreset';
    }
    // 计算签名
    $original = http_build_query($argList);
    $signature = base64_encode(hash_hmac('SHA1', $original, $secretKey, true) . $original);
    echo json_encode(array('code' => 0, 'msg' => '', 'signature' => $signature));
    exit();
} catch (\Exception $exception) {
    echo json_encode(array('code' => 1, 'msg' => $exception->getMessage()));
    exit();
}

