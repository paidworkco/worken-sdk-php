<?php
namespace Worken\Services;

use Web3\Web3;
use Web3\Contract;
use Web3\Utils;
use kornrunner\Keccak;

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
    public function getEstimatedGas($from, $to, int $amount) {
        $data = $this->encodeTransferData($to, $amount);
    
        $transaction = [
            'from' => $from,
            'to' => $this->contractAddress,
            'data' => $data
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

    //testing
    private function getERC20ABI() {
        return '[
            {
                "constant": true,
                "inputs": [{"name": "_owner", "type": "address"}],
                "name": "balanceOf",
                "outputs": [{"name": "balance", "type": "uint256"}],
                "type": "function"
            },
            {
                "constant": false,
                "inputs": [
                    {"name": "_to", "type": "address"},
                    {"name": "_value", "type": "uint256"}
                ],
                "name": "transfer",
                "outputs": [{"name": "", "type": "bool"}],
                "type": "function"
            },
            {
                "constant": false,
                "inputs": [
                    {"name": "_spender", "type": "address"},
                    {"name": "_value", "type": "uint256"}
                ],
                "name": "approve",
                "outputs": [{"name": "", "type": "bool"}],
                "type": "function"
            },
            {
                "constant": true,
                "inputs": [
                    {"name": "_owner", "type": "address"},
                    {"name": "_spender", "type": "address"}
                ],
                "name": "allowance",
                "outputs": [{"name": "remaining", "type": "uint256"}],
                "type": "function"
            }
        ]';
    }

    //testing
    function encodeTransferData($to, $amount) {
        $selector = substr(Keccak::hash('transfer(address,uint256)', 256), 0, 8);
        $addressPadded = str_pad(substr($to, 2), 64, '0', STR_PAD_LEFT);
        $amountPadded = str_pad(dechex($amount), 64, '0', STR_PAD_LEFT);
        return '0x' . $selector . $addressPadded . $amountPadded;
    }
}