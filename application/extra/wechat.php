<?php
return[
    // 必要配置
    'app_id'             => 'wx69c7b8c4501fa3f4',
    'mch_id'             => '1518358141',
    'key'                => 'jkir85oelfitkDrergEo34k3g34doD33',   // API 密钥
    // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
    'cert_path'          => '/www/wwwroot/project/addons/epay/certs/apiclient_cert.pem', // XXX: 绝对路径！！！！
    'key_path'           => '/www/wwwroot/project/addons/epay/certs/apiclient_key.pem', // XXX: 绝对路径！！！！
    'notify_url'         => 'http://www.project.com/wmfproject/public/index.php/shop/pay/notify',     // 你也可以在下单时单独设置来想覆盖它
];