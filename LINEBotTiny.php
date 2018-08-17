<?php

/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

/*
 * This polyfill of hash_equals() is a modified edition of https://github.com/indigophp/hash-compat/tree/43a19f42093a0cd2d11874dff9d891027fc42214
 *
 * Copyright (c) 2015 Indigo Development Team
 * Released under the MIT license
 * https://github.com/indigophp/hash-compat/blob/43a19f42093a0cd2d11874dff9d891027fc42214/LICENSE
 */
/*指定した関数が定義されているかどうか*/
if (!function_exists('hash_equals')) {
    /*指定した定数が定義されているかどうか*/
    defined('USE_MB_STRING') or define('USE_MB_STRING', function_exists('mb_strlen'));

    function hash_equals($knownString, $userString)
    {
        $strlen = function ($string) {
            if (USE_MB_STRING) {
                /*文字数取得*/
                return mb_strlen($string, '8bit');
            }

            return strlen($string);
        };

        // Compare string lengths
        /*署名比較*/
        if (($length = $strlen($knownString)) !== $strlen($userString)) {
            return false;
        }

        $diff = 0;

        // Calculate differences
        for ($i = 0; $i < $length; $i++) {
            /*文字のASCII値を比較*/
            /*^排他的論理和*/
            /*|ビット和*/
            $diff |= ord($knownString[$i]) ^ ord($userString[$i]);
        }
        return $diff === 0;
    }
}

class LINEBotTiny
{
    /*コンストラクタ*/
    public function __construct($channelAccessToken, $channelSecret)
    {
        $this->channelAccessToken = $channelAccessToken;
        $this->channelSecret = $channelSecret;
    }

    public function parseEvents()
    {
        /*ブラウザからのリクエストが、POSTメソッドなのかGETメソッドなのか、スクリプト側で判別した*/
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            error_log("Method not allowed");
            /*プログラム終了*/
            exit();
        }
        /*php://input は読み込み専用のストリームで、 リクエストの body 部から生のデータを読み込むことができる*/
        $entityBody = file_get_contents('php://input');
        /*文字列の長さを調べる*/
        if (strlen($entityBody) === 0) {
            http_response_code(400);
            error_log("Missing request body");
            /*プログラム終了*/
            exit();
        }
        /*二つの文字列が等しいかどうか調べる*/
        /*LINEプラットフォームから送信されたかどうか*/
        if (!hash_equals($this->sign($entityBody), $_SERVER['HTTP_X_LINE_SIGNATURE'])) {
            http_response_code(400);
            error_log("Invalid signature value");
            /*プログラム終了*/
            exit();
        }
        /*jsonでエンコードされたデータを、適切なphpの型として返す。TRUE の場合、返されるオブジェクトは連想配列形式になります。*/
        $data = json_decode($entityBody, true);
        /*データがあるかどうか*/
        if (!isset($data['events'])) {
            http_response_code(400);
            error_log("Invalid request body: missing events property");
            /*プログラム終了*/
            exit();
        }
        return $data['events'];
    }

    public function replyMessage($message)
    {
        $header = array(
            "Content-Type: application/json",
            'Authorization: Bearer ' . $this->channelAccessToken,
        );

        $context = stream_context_create(array(
            "http" => array(
                "method" => "POST",
                /*文字列の連結*/
                /*エスケープシーケンス/n改行/rキャリッジリターン*/
                "header" => implode("\r\n", $header),
                /*メッセージのタイプ*/
                "content" => json_encode($message),
            ),
        ));

        $response = file_get_contents('https://api.line.me/v2/bot/message/reply', false, $context);
        /*通信が成功している位置を探している。見つからない場合false*/
        if (strpos($http_response_header[0], '200') === false) {
            http_response_code(500);
            error_log("Request failed: " . $response);
        }
    }
    /*署名作成*/
    private function sign($body)
    {
        $hash = hash_hmac('sha256', $body, $this->channelSecret, true);
        $signature = base64_encode($hash);
        return $signature;
    }
}
