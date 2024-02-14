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
            $return['error'] = $result['message'];
            return $return;
        }
    }

    // $amount string or int?? limit of int in PHP is 2147483647
    public function getEstimatedGas(string $from, string $to, string $amount) {
        $info = [];
        $result = [];
        $transaction = [
            'from' => $from,
            'to' => $to,
            'amount' => $amount
        ];
    
        $this->web3->eth->estimateGas($transaction, function ($err, $result) use (&$info){
            if ($err !== null) {
                $info['estimatedgas']['error'] = $err->getMessage();
            }
            $info['estimatedgas'] = $result;
        });
        $gasValue = $info['estimatedgas']; 
        $result['estimatedGas']['WEI'] = $gasValue->toString(); // in WEI
        $result['estimatedGas']['Ether'] = Converter::convertWEItoEther($result['estimatedGas']['WEI']); // Convert to Ether
        $result['estimatedGas']['Hex'] = "0x" . $gasValue->toHex(); // 0x... hex value
        return $result;
    }

    public function getNetworkStatus() {
        $status = [];
    
        // Get latest block number
        $this->web3->eth->blockNumber(function ($err, $block) use (&$status) {
            if ($err !== null) {
                $status['latestBlock']['error'] = $err->getMessage();
            }
            $status['latestBlock'] = intval($block->toString());
        });

        // Get hashrate of the network
        $this->web3->eth->hashrate(function ($err, $hashrate) use (&$status) {
            if ($err !== null) {
                $status['hashrate']['error'] = $err->getMessage();
            }
            $status['hashrate'] = $hashrate->toString();
        });
    
        // Get gas price
        $this->web3->eth->gasPrice(function ($err, $gasPrice) use (&$status) {
            if ($err !== null) {
                $status['gasPrice']['error'] = $err->getMessage();
            }
            $status['gasPrice']['WEI'] = $gasPrice->toString(); 
            $status['gasPrice']['Ether'] = Converter::convertWEItoEther($gasPrice->toString()); 
            $status['gasPrice']['Hex'] = "0x" . $gasPrice->toHex(); // 0x... hex value
        });

        // Get syncing status
        $this->web3->eth->syncing(function ($err, $syncing) use (&$status){
            if ($err !== null) {
                $status['syncStatus']['error'] = $err->getMessage();
            }
            $status['syncStatus'] = $syncing;
        });
        return $status;
    }

    public function getMonitorCongestion() {
        $status = [];
        
        $gasOracleUrl = "https://api.polygonscan.com/api?module=gastracker&action=gasoracle&apikey={$this->apiKey}";
        $gasData = file_get_contents($gasOracleUrl);
        if ($gasData !== false) {
            $gasData = json_decode($gasData, true);
            if ($gasData['status'] == '1' && isset($gasData['result'])) {
                $status['GasPrice']['Safe'] = (float)$gasData['result']['SafeGasPrice'];
                $status['GasPrice']['Propose'] = (float)$gasData['result']['ProposeGasPrice'];
                $status['GasPrice']['Fast'] = (float)$gasData['result']['FastGasPrice'];
            } else {
                $status['GasPrice']['error'] = "Could not retrieve gas price data";
            }
        } else {
            $status['GasPrice']['error'] = "Failed to connect to Polygonscan API";
        }
        return $status;
    }
}