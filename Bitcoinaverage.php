<?php

class Bitcoinaverage
{
    const BASE_URL = 'https://apiv2.bitcoinaverage.com';

    private $publicKey;

    private $secretKey;

    private $apiUrls = array(
        'symbols'           => '/constants/symbols',
        'symbolsMarket'     => '/constants/symbols/{market}',
        'ticker'            => '/indices/{market}/ticker/all?crypto={crypto}&fiat={fiat}',
        'tickerSymbol'      => '/indices/{market}/ticker/{symbol}',
        'convert'           => '/convert/{market}?from={source_cur}&to={target_cur}&amount={amount}',
    );

    public function __construct($publicKey = null, $secretKey = null)
    {
        $this->publicKey = $publicKey;
        $this->secretKey = $secretKey;

        $this->auth = $this->authenticate();

        foreach ($this->apiUrls as $key => $value) {
            $this->apiUrls[$key] = self::BASE_URL . $value;
        }
    }

    public function __destruct()
    {
        $this->auth = null;
    }

    private function request($url)
    {
        $content = file_get_contents($url, false, $this->auth);

        if ($content === false) {
            throw new Exception("Error Processing Request", 1);
        }
        if ($content === '{}') {
            throw new Exception("Error Processing Request, Empty result", 1);
        }

        return $content;
    }

    private function authenticate()
    {
        $payload = time() . '.' . $this->publicKey;
        $hash = hash_hmac('sha256', $payload, $this->secretKey, true);
        $keys = unpack('H*', $hash);
        $hexHash = array_shift($keys);
        $signature = $payload . '.' . $hexHash;

        $aHTTP = array('http' => array(
            'method'  => 'GET',
            'header'  => "X-Signature: " . $signature
        ));
        $context = stream_context_create($aHTTP);

        return $context;
    }

    private function prepUrl($search, $replace, $url)
    {
        $url = str_replace('{' . $search . '}', $replace, $url);
        return $url;
    }

    private function prepAllUrl($arr, $url)
    {
        foreach ($arr as $key => $value) {
            $url = $this->prepUrl($key, $value, $url);
        }
        return $url;
    }

    private function output($str, $assoc = false)
    {
        return json_decode($str, $assoc);
    }

    public function formatPrice($amount = 1, $currency = 'BTC')
    {
        $decimals = $currency == 'BTC' ? 8 : 2;

        return number_format($amount, $decimals, ',', '.');
    }

    public function ticker($fiat = 'EUR', $crypto = 'BTC', $market = 'global')
    {
        $url = $this->prepAllUrl( array(
            'fiat' => $fiat,
            'crypto' => $crypto,
            'market' => $market
        ),  $this->apiUrls['ticker']);

        return $this->output($this->request($url));
    }

    public function tickerShort()
    {

    }

    public function convert($from = 'BTC', $to = 'EUR', $amount = 1, $market = 'global')
    {
        $url = $this->prepAllUrl( array(
            'source_cur' => $from,
            'target_cur' => $to,
            'amount' => $amount,
            'market' => $market,
        ),  $this->apiUrls['convert']);

        return $this->output($this->request($url));
    }

}

?>
