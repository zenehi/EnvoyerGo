<?php


namespace App\Services;


class Discord
{
    protected $webhookUrl;

    public function __construct($webhookUrl)
    {
        $this->webhookUrl = $webhookUrl;
    }

    public function sendMessage($msg, $params = [])
    {
        $jsonData = json_encode([
            // Message
            "content" => $msg,

            // Username
//            "username" => "krasin.space",

            // Avatar URL.
            // Uncoment to replace image set in webhook
            //"avatar_url" => "https://ru.gravatar.com/userimage/28503754/1168e2bddca84fec2a63addb348c571d.jpg?size=512",

            // Text-to-speech
            "tts" => false,

            // File upload
            // "file" => "",

            // Embeds Array
//            "embeds" => [
//                [
//                    // Embed Title
//                    "title" => "PHP - Send message to Discord (embeds) via Webhook",
//
//                    // Embed Type
//                    "type" => "rich",
//
//                    // Embed Description
//                    "description" => "Description will be here, someday, you can mention users here also by calling userID <@12341234123412341>",
//
//                    // URL of title link
//                    "url" => "https://gist.github.com/Mo45/cb0813cb8a6ebcd6524f6a36d4f8862c",
//
//                    // Timestamp of embed must be formatted as ISO8601
//                    "timestamp" => $timestamp,
//
//                    // Embed left border color in HEX
//                    "color" => hexdec( "3366ff" ),
//
//                    // Footer
//                    "footer" => [
//                        "text" => "GitHub.com/Mo45",
//                        "icon_url" => "https://ru.gravatar.com/userimage/28503754/1168e2bddca84fec2a63addb348c571d.jpg?size=375"
//                    ],
//
//                    // Image to send
//                    "image" => [
//                        "url" => "https://ru.gravatar.com/userimage/28503754/1168e2bddca84fec2a63addb348c571d.jpg?size=600"
//                    ],
//
//                    // Thumbnail
//                    //"thumbnail" => [
//                    //    "url" => "https://ru.gravatar.com/userimage/28503754/1168e2bddca84fec2a63addb348c571d.jpg?size=400"
//                    //],
//
//                    // Author
//                    "author" => [
//                        "name" => "krasin.space",
//                        "url" => "https://krasin.space/"
//                    ],
//
//                    // Additional Fields array
//                    "fields" => [
//                        // Field 1
//                        [
//                            "name" => "Field #1 Name",
//                            "value" => "Field #1 Value",
//                            "inline" => false
//                        ],
//                        // Field 2
//                        [
//                            "name" => "Field #2 Name",
//                            "value" => "Field #2 Value",
//                            "inline" => true
//                        ]
//                        // Etc..
//                    ]
//                ]
//            ]

        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

        $ch = curl_init($this->webhookUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

}
