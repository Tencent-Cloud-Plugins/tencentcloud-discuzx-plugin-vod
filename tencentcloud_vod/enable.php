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

$pluginInfo=C::t('common_plugin')->fetch_by_identifier('tencentcloud_center');
if ($pluginInfo['available'] != '1'){
    C::t('common_plugin')->update($pluginInfo['pluginid'], array('available' => '1'));
}
runquery("UPDATE cdb_tencentcloud_pluginInfo SET status = 'true' WHERE plugin_name='tencentcloud_vod'");
VODActions::uploadDzxStatisticsData('activate');
