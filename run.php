<?php
echo "Masukan URL: ";
$url = trim(fgets(STDIN));

$u = "https://api.hivera.org/engine/info?auth_data=user=%7B%22id%22:7097743626,%22first_name%22:%22I%22,%22last_name%22:%22%22,%22username%22:%22Gguggffffffffghh%22,%22language_code%22:%22en%22,%22allows_write_to_pm%22:true,%22photo_url%22:%22https:%5C%2F%5C%2Ft.me%5C%2Fi%5C%2Fuserpic%5C%2F320%5C%2FjsJ8unqqjDDR12D2BOvWJfQcqe72G6JdT3GOQ_3XPhxMtMAvCK_ky8owJtJJlFGv.svg%22%7D%26chat_instance=4506362560145935621%26chat_type=sender%26auth_date=1734648861%26signature=rKBxv_Cc8XeEKMYyg3H_ntaqAvhtaHg8DZx66tzSIYCmROCtCzXydyKChEaXariik__gf3p1BH1DSSCEMPJGCQ%26hash=0b94ce2f15c83437803f237f5b4af1dc8c1d81376da77c8652d703ba1a3a756f";
$urlInfo = $url;
$parsed_url = parse_url($url);
$query_string = $parsed_url['query'];

$contribute = "https://api.hivera.org/engine/contribute";
$urlContribute = $contribute . "?" . $query_string;

function generateServerTiming() {
    $speed = 125000000;

    $sent_bytes = rand(100000000, 150000000);
    $recv_bytes = rand(100000000, 150000000);

    $rtt = rand(10000, 50000);
    $min_rtt = rand(5000, 10000);
    $rtt_var = $rtt - $min_rtt;
    $cwnd = rand(512, 2048);
    $cid = bin2hex(random_bytes(8));
    $ts = time();

    return sprintf(
        'cfL4;desc="?proto=TCP&rtt=%d&min_rtt=%d&rtt_var=%d&sent=%d&recv=%d&lost=0&retrans=0&sent_bytes=%d&recv_bytes=%d&delivery_rate=%d&cwnd=%d&unsent_bytes=0&cid=%s&ts=%d&x=0"',
        $rtt,
        $min_rtt,
        $rtt_var,
        rand(1000, 2000),
        rand(1000, 2000),
        $sent_bytes,
        $recv_bytes,
        $speed,
        $cwnd,
        $cid,
        $ts
    );
}
while (true) {


    $info = file_get_contents($urlInfo);
    $infoJson = json_decode($info, true);
    if($infoJson['result']['profile']['POWER'] < $infoJson['result']['profile']['POWER_CAPACITY']){
        echo "[+] Power kamu belum mencukupi".PHP_EOL;
        echo "    Saldo HIVERA: {$infoJson['result']['profile']['HIVERA']}".PHP_EOL;
        echo "    Power kamu saat ini : {$infoJson['result']['profile']['POWER']}".PHP_EOL;
        echo "    Maksimal Power : {$infoJson['result']['profile']['POWER_CAPACITY']}".PHP_EOL;
        echo "[-] Menunggu power kembali terisi...".PHP_EOL;
        sleep(300);
    }
    $url = $urlContribute;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "OPTIONS");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    $response = curl_exec($ch);

    $headers = [];
    foreach (explode("\r\n", $response) as $line) {
        if (strpos($line, ": ") !== false) {
            list($key, $value) = explode(": ", $line, 2);
            $headers[trim($key)] = trim($value);
        }
    }
    curl_close($ch);

    if (isset($headers['server-timing'])) {
        $original_server_timing = $headers['server-timing'];

        $manipulated_server_timing = generateServerTiming();

        $headers['server-timing'] = $manipulated_server_timing;
    }

    $post_data = [
        "from_date" => 1734615885252,
        "quality_connection" => 100,
    ];

    $request_headers = [];
    foreach ($headers as $key => $value) {
        if (!in_array(strtolower($key), ["content-length", "transfer-encoding", "date", "connection"])) {
            $request_headers[] = "$key: $value";
        }
    }

    $request_headers[] = "Content-Type: application/json";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Status Code: $http_code\n";
    echo "Response: $response\n";
    sleep(120);
}
