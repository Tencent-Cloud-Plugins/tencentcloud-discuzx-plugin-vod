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
if (!defined('IN_DISCUZ')){
    exit('Access Denied');
}
defined('TENCENT_DISCUZX_VOD_DIR')||define( 'TENCENT_DISCUZX_VOD_DIR', __DIR__.DIRECTORY_SEPARATOR);
defined('TENCENT_DISCUZX_VOD_PLUGIN_NAME')||define( 'TENCENT_DISCUZX_VOD_PLUGIN_NAME', 'tencentcloud_vod');
if (!is_file(TENCENT_DISCUZX_VOD_DIR.'vendor/autoload.php')) {
    exit(lang('plugin/tencentcloud_vod','require_sdk'));
}
require_once 'vendor/autoload.php';
use TencentDiscuzVOD\VODActions;
class plugin_tencentcloud_vod
{
    /**
     * 先执行的全局钩子，保留这个空方法其他inc.php不需要再require_once 'vendor/autoload.php'
     */
    public function common()
    {

    }

    public function discuzcode($val)
    {
        if ($val['caller'] != 'discuzcode'){
            return;
        }
        global  $_G;
        $dzxVOD = new VODActions();
        $_G['discuzcodemessage'] = $dzxVOD->parseContentPlayer($_G['discuzcodemessage'],$_G['tid'].'-'.$_G['forum_numpost']);
    }

}

class plugin_tencentcloud_vod_forum extends plugin_tencentcloud_vod
{
    public function post_editorctrl_left()
    {
        $VODOptions = VODActions::getVODOptionsObject();
        $transcode = $VODOptions->getTranscode();
        include template('tencentcloud_vod:editor_icon');
        return $editor_icon;
    }
}
