<?php
/*アクセストークン代入*/
$accessToken = 'rRN3K3ic5K1yuAYk016fLCXZhQlJqJjhgGF+iv6fLQSrOai8lOMtfBpVdQfsr2Bgy3YzCKOh+hFnONOGZWvBAu7virAbtnU86IChplpzhWIUvmS6Uf3sBwwFQyuTEPqoZjjWg6PdYroQ3ejG9vWHWgdB04t89/1O/w1cDnyilFU=';
/*チャンネルシークレット代入*/
$channel_secret ='86d8fe81b925d4b23ca05332d9b04c2c';
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
$userId = $jsonObj->{"events"}[0]->{"source"}->{"userId"};
$addflag = false;
$editflag = false;
$listflag = false;
$deleteflag = false;
if ($message->{"text"} == "メニュー") {
    //Quick reply
    $messageData = [
        "type" => "text",
        "text" => "何を行うか選択してね",
        "quickReply" => [
            "items" => [
                [
                    "type" => "action",
                    "action" => [
                        "type" => "message",
                        "label" => "予定の追加",
                        "text" => "予定の追加"
                    ]
                ], [
                    "type" => "action",
                    "action" => [
                        "type" => "message",
                        "label" => "予定の編集",
                        "text" => "予定の編集"
                    ]
                ], [
                    "type" => "action",
                    "action" => [
                        "type" => "message",
                        "label" => "予定一覧",
                        "text" => "予定一覧"
                    ]
                ], [
                    "type" => "action",
                    "action" => [
                        "type" => "message",
                        "label" => "予定の削除",
                        "text" => "予定の削除"
                    ]
                ]
            ]
        ]
    ];
} elseif ($message->{"text"} == "予定の追加") {
    /*日付の入力を促すメッセージを出す
    日付を選択させる、または入力してもらう。どちらの場合でもユーザーから日付を受け取る
    日付を変数に保存しておく*/
    /*↑の処理*/
    $addflag = true;
} elseif ($message->{"text"} == "予定の編集") {
    /*日付の入力を促すメッセージを出す
    日付を選択させる、または入力してもらう。どちらの場合でもユーザーから日付を受け取る
    日付を変数に保存しておく*/
    /*↑の処理*/
    $editflag = true;
} elseif ($message->{"text"} == "予定一覧") {
    /*日付の入力を促すメッセージを出す
    日付を選択させる、または入力してもらう。どちらの場合でもユーザーから日付を受け取る
    日付を変数に保存しておく*/
    /*↑の処理*/
    $listflag = true;
} elseif ($message->{"text"} == "予定の削除") {
    /*日付の入力を促すメッセージを出す
    日付を選択させる、または入力してもらう。どちらの場合でもユーザーから日付を受け取る
    日付を変数に保存しておく*/
    /*↑の処理*/
    $deleteflag = true;
} else {
    // それ以外は送られてきたテキストをオウム返し
    $messageData = ['type' => 'text', 'text' => 'メニューと入力してみて'];
}
/*動いてない*/
if ($addflag == true) {
    /*通知の時間を保存しておく*/
    /*１時間前など通知しやすい時間帯とその他をクイックリプライで作り、その他の場合はカラカラ*/
    $messageData = [
        "type" => "text",
        "text" => "いつ通知しますか？",
        "quickReply" => [
            "items" => [
                [
                    "type" => "action",
                    "action" => [
                        "type" => "message",
                        "label" => mktime(date("h") - 1, 0, 0, date("n"), date("j"), date("Y")),
                        "text" => mktime(date("h") - 1, 0, 0, date("n"), date("j"), date("Y"))
                    ]
                ], [
                    "type" => "action",
                    "action" => [
                        "type" => "message",
                        "label" => mktime(date("h") - 2, 0, 0, date("n"), date("j"), date("Y")),
                        "text" => mktime(date("h") - 2, 0, 0, date("n"), date("j"), date("Y"))
                    ]
                ], [
                    "type" => "action",
                    "action" => [
                        "type" => "message",
                        "label" => mktime(date("h") - 3, 0, 0, date("n"), date("j"), date("Y")),
                        "text" => mktime(date("h") - 3, 0, 0, date("n"), date("j"), date("Y"))
                    ]
                ], [
                    "type" => "action",
                    "action" => [
                        "type" => "datetimepicker",
                        "label" => "時間選択",
                        "data" => "timedata",
                        "mode" => "datetime"
                    ]
                ]
            ]
        ]
    ];
    /*ユーザーIDと予定の日時、通知日時をデータベースに登録する*/
    /*INSERT文*/
    /*予定が追加されたことを表示するメッセージ*/
} elseif ($editflag == true) {
    /*データベース接続し、入力された日の予定を出す。複数の場合どれかをきく*/
    /*SELECT文*/
    $messageData = [
        "type" => "text",
        "text" => "どれを変更しますか",
        "quickReply" => [
            "items" => [
                [
                    "type" => "action",
                    "action" => [
                        "type" => "message",
                        "label" => "日時",
                        "text" => "日時"
                    ]
                ], [
                    "type" => "action",
                    "action" => [
                        "type" => "message",
                        "label" => "内容",
                        "text" => "内容"
                    ]
                ], [
                    "type" => "action",
                    "action" => [
                        "type" => "message",
                        "label" => "通知日時",
                        "text" => "通知日時"
                    ]
                ]
            ]
        ]
    ];
    /*UPDATE文*/
    /*更新されたことを報告するメッセージ*/
} elseif ($listflag == true) {
    /*データベース接続し、入力された日の予定を出す。*/
    /*if(予定件数　== 0){
        予定は特にないですと表示
    }
    */
    /*SELECT文*/
    $messageData = [
        /*SELECT文で出力したものを表示*/
    ];
} elseif ($deleteflag == true) {
    /*データベース接続し、入力された日の予定を出す。*/
    /*SELECT文*/
    $messageData = [
        "type" => "text",
        "text" => "どれを削除しますか",
        "quickReply" => [
            "items" => [
                /*入力された日にある予定を出す*/
            ]
        ]
    ];
    /*選択された日を削除する*/
    /*削除されたことを報告するメッセージ*/
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