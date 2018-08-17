<?php
/*アクセストークン代入*/
$accessToken = getenv('ACCESS_TOKEN');
/*チャンネルシークレット代入*/
$channel_secret = getenv('CHANNEL_SECRET');
/*データ取り出し*/
$jsonString = file_get_contents('php://input');
/*エラーわかりやすく*/
error_log($jsonString);
/*JSON形式のデータを文字列に変換*/
$jsonObj = json_decode($jsonString);
/*メッセージ取り出し*/
$message = $jsonObj->{"events"}[0]->{"message"};
/*返信用変数作成*/
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};
// 送られてきたメッセージの中身からレスポンスのタイプを選択する
if ($message->{"text"} == '確認') {
    // 確認ダイアログタイプ
    $messageData = [
        'type' => 'template',
        'altText' => '確認ダイアログ',
        'template' => ['type' => 'confirm', 'text' => '元気ですかー？',
            'actions' => [
                ['type' => 'message', 'label' => '元気です', 'text' => '元気やで'],
                ['type' => 'message', 'label' => 'まあまあです', 'text' => 'まあまあです'],
            ]
        ]
    ];
} elseif ($message->{"text"} == 'ボタン') {
    // ボタンタイプ
    $messageData = [
        'type' => 'template',
        'altText' => 'ボタン',
        'template' => [
            'type' => 'buttons',
            'title' => 'タイトルです',
            'text' => '選択してね',
            'actions' => [
                [
                    'type' => 'postback',
                    'label' => 'webhookにpost送信',
                    'data' => 'value'
                ],
                [
                    'type' => 'uri',
                    'label' => 'googleへ移動',
                    'uri' => 'https://google.com'
                ]
            ]
        ]
    ];
} elseif ($message->{"text"} == 'カルーセル') {
    // カルーセルタイプ
    $messageData = [
        'type' => 'template',
        'altText' => 'カルーセル',
        'template' => [
            'type' => 'carousel',
            'columns' => [
                [
                    'title' => 'カルーセル1',
                    'text' => 'カルーセル1です',
                    'actions' => [
                        [
                            'type' => 'postback',
                            'label' => 'webhookにpost送信',
                            'data' => 'value'
                        ],
                        [
                            'type' => 'uri',
                            'label' => '一番軽いと有名なサイトに移動',
                            'uri' => 'http://dev.to'
                        ]
                    ]
                ],
                [
                    'title' => 'カルーセル2',
                    'text' => 'カルーセル2です',
                    'actions' => [
                        [
                            'type' => 'postback',
                            'label' => 'webhookにpost送信',
                            'data' => 'value'
                        ],
                        [
                            'type' => 'uri',
                            'label' => '二番目に軽いと言われるサイトに移動',
                            'uri' => 'http://abehiroshi.la.coocan.jp/'
                        ]
                    ]
                ],
            ]
        ]
    ];
} elseif ($message->{"text"} == "クイックリプライ") {
    //Quick reply
    $messageData = [
        "type" => "text",
        "text" => "クイックリプライ！好きなやつを選んでね",
        "quickReply" => [
            "items" => [
                [
                    "type" => "action",
                    "imageUrl" => "https://cdn.macaro-ni.jp/assets/img/shutterstock/shutterstock_236342974.jpg",
                    "action" => [
                        "type" => "message",
                        "label" => "sushi",
                        "text" => "love sushi"
                    ]
                ], [
                    "type" => "action",
                    "imageUrl" => "https://stat.ameba.jp/user_images/20131010/13/nyanwan-mamma/8b/56/j/o0400031612711441531.jpg?caw=800",
                    "action" => [
                        "type" => "message",
                        "label" => "cat",
                        "text" => "i am cat"
                    ]
                ], [
                    "type" => "action",
                    "imageUrl" => "https://pbs.twimg.com/profile_images/989343011619270656/INSDpgpx_400x400.jpg",
                    "action" => [
                        "type" => "message",
                        "label" => "校長先生",
                        "text" => "校長"
                    ]
                ], [
                    "type" => "action",
                    "imageUrl" => "https://lohas.nicoseiga.jp/thumb/8103017i?1525435630",
                    "action" => [
                        "type" => "message",
                        "label" => "ナナチ",
                        "text" => "んなぁ"
                    ]
                ], [
                    "type" => "action",
                    "imageUrl" => "https://askul.c.yimg.jp/img/product/3L1/9396850_3L1.jpg",
                    "action" => [
                        "type" => "datetimepicker",
                        "label" => "時間選択",
                        "data" => "storeId=12345",
                        "mode"=>"datetime",
                    ]
                ]
            ]
        ]
    ];
} else {
    // それ以外は送られてきたテキストをオウム返し
    $messageData = ['type' => 'text', 'text' => $message->{"text"}];
}
$response = ['replyToken' => $replyToken, 'messages' => [$messageData]];
error_log(json_encode($response));
$ch = curl_init('https://api.line.me/v2/bot/message/reply');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charser=UTF-8', 'Authorization: Bearer ' . $accessToken));
$result = curl_exec($ch);
error_log($result);
curl_close($ch);