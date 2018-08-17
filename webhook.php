<?php

require_once('./LINEBotTiny.php');
/*アクセストークンを変数に代入*/
$channelAccessToken = 'r8gTdZabRuSoDZOENxZOhHvRla0KVlDMQaGKdgjmG7OYCpvay8gGzwjCZtj9Skg+qFQau5iqqdV8BuxUxNbost1fc+Yp+6uvemCccU1K9VIeurob6YnmyBAPqjnwiRbIK1l03MOsOH54IDJHaA5wIgdB04t89/1O/w1cDnyilFU=';
/*チャンネルシークレットを変数に代入*/
$channelSecret = '573ac68d004341041a11fabe515a4b80';
/*LINEBotTinyクラスをアクセストークンとチャンネルシークレットを引数にしてインスタンス化し、変数に代入する*/
$client = new LINEBotTiny($channelAccessToken, $channelSecret);
foreach ($client->parseEvents() as $event) {
    /*送られたものがどのようなものか調べている*/
    switch ($event['type']) {
        case 'message':
            $message = $event['message'];
            switch ($message['type']) {
                case 'text':

                        $client->replyMessage(array(
                            'replyToken' => $event['replyToken'],
                            'messages' => array(
                                array(
                                    'type' => 'text',
                                    'text' => $message['text'] . "です。"
                                ), array(
                                    'type' => 'text',
                                    'text' => $message['text'] . "ですよ！！！！？"
                                )
                            )
                        ));

                    break;
                default:
                    error_log("Unsupporeted message type: " . $message['type']);
                    break;
            }
            break;

        default:
            error_log("Unsupporeted event type: " . $event['type']);
            break;
    }
};
