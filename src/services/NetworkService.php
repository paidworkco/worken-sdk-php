<?php
namespace Worken\Services;

use Web3\Web3;

class NetworkService {
    private $web3;
    private $contractAddress;
    private $apiKey;

    public function __construct(Web3 $web3, string $contractAddress, string $apiKey) {
        $this->web3 = $web3;
        $this->apiKey = $apiKey;
        $this->contractAddress = $contractAddress;
    }

    public function getBlockInformation(int $blockNumber) { 
        $url = "https://api.polygonscan.com/api?module=account&action=tokentx&contractaddress={$this->contractAddress}&startblock={$blockNumber}&endblock={$blockNumber}&sort=asc&apikey={$this->apiKey}";
    
        $response = file_get_contents($url);
        $result = json_decode($response, true);
    
        if ($result['status'] == '1' && $result['message'] == 'OK') {
            return $result['result'];
        } else {
            return $result['message'];
        }
    }

    // testing
    public function getEstimatedGas($from, $to, $amount) {
        $transaction = [
            'from' => $from,
            'to' => $to,
            'amount' => $amount
        ];
    
        $this->web3->eth->estimateGas($transaction, function ($err, $gas) {
            if ($err !== null) {
                echo 'Error: ' . $err->getMessage();
                return;
            }
            echo "Estimated Gas: " . $gas;
        });
    }

    public function getNetworkStatus() {

    }

    public function getMonitorCongestion() {

    }
}