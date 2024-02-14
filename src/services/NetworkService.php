<?php
namespace Worken\Services;

use Web3\Web3;
use Worken\Utils\Converter;

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
            return intval($result['result']);
        } else {
            return $result['message'];
        }
    }

    // testing
    public function getEstimatedGas($from, $to, $amount) {
        $info = [];
        $result = [];
        $transaction = [
            'from' => $from,
            'to' => $to,
            'amount' => $amount
        ];
    
        $this->web3->eth->estimateGas($transaction, function ($err, $result) use (&$info){
            if ($err !== null) {
                echo 'Error: ' . $err->getMessage();
                return;
            }
            $info['estimatedgas'] = $result;
        });
        $gasValue = $info['estimatedgas']; 
        $result['gasDecimalWEI'] = $gasValue->toString(); // in WEI
        $result['gasDecimalEther'] = Converter::convertWEItoEther($result['gasDecimalWEI']); // Convert to Ether
        $result['gasHex'] = "0x" . $gasValue->toHex(); // 0x... hex value
        return $result;
    }

    public function getNetworkStatus() {
        $status = [];
    
        // Get latest block number
        $this->web3->eth->blockNumber(function ($err, $block) use (&$status) {
            if ($err !== null) {
                $status['blockNumberError'] = 'Error: ' . $err->getMessage();
                return;
            }
            $status['latestBlock'] = intval($block->toString());
        });

        // Get hashrate of the network
        $this->web3->eth->hashrate(function ($err, $hashrate) use (&$status) {
            if ($err !== null) {
                $status['hashrateError'] = 'Error: ' . $err->getMessage();
                return;
            }
            $status['hashrate'] = $hashrate->toString();
        });
    
        // Get gas price
        $this->web3->eth->gasPrice(function ($err, $gasPrice) use (&$status) {
            if ($err !== null) {
                $status['gasPriceError'] = 'Error: ' . $err->getMessage();
                return;
            }
            $status['gasPriceWEI'] = $gasPrice->toString(); 
            $status['gasPriceEther'] = Converter::convertWEItoEther($gasPrice->toString()); 
            $status['gasPriceHex'] = "0x" . $gasPrice->toHex(); // 0x... hex value
        });

        // Get syncing status
        $this->web3->eth->syncing(function ($err, $syncing) use (&$status){
            if ($err !== null) {
                return;
            }
            $status['syncStatus'] = $syncing;
        });
        return $status;
    }

    public function getMonitorCongestion() {

    }
}