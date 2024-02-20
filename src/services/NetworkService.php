<?php
namespace Worken\Services;

use Web3\Web3;
use Web3\Contract;
use Web3\Utils;
use Worken\Utils\Converter;
use Worken\Utils\ABI;

class NetworkService {
    private $web3;
    private $contractAddress;
    private $apiKey;
    private $contract;

    public function __construct(Web3 $web3, string $contractAddress, string $apiKey) {
        $this->web3 = $web3;
        $this->apiKey = $apiKey;
        $this->contractAddress = $contractAddress;
        $this->contract = new Contract($this->web3->provider, ABI::ERC20());
        $this->contract->at($this->contractAddress);
    }

    /**
     * Get block information
     * 
     * @param string $blockNumber block number in Hex
     * @return array
     */
    public function getBlockInformation(string $blockNumber) { 
        // mainnet 
        //$url = "https://api.polygonscan.com/api?module=account&action=tokentx&contractaddress={$this->contractAddress}&startblock={$blockNumber}&endblock={$blockNumber}&sort=asc&apikey={$this->apiKey}";
        // testnet
        $url = "https://api-testnet.polygonscan.com/api?module=account&action=tokentx&contractaddress={$this->contractAddress}&startblock={$blockNumber}&endblock={$blockNumber}&sort=asc&apikey={$this->apiKey}";
        $response = file_get_contents($url);
        $result = json_decode($response, true);
    
        if ($result['status'] == '1' && $result['message'] == 'OK') {
            return $result['result'];
        } else {
            $return['error'] = $result['message'];
            return $return;
        }
    }

    /**
     * Get estimated gas for transaction (in WEI, Ether and Hex value)
     * 
     * @param string $from Sender address in Hex
     * @param string $to Receiver address in Hex
     * @param string $amount Amount to send in WEI
     * @return array
     */
    public function getEstimatedGas(string $from, string $to, string $amount) {
        $info = [];
        $result = [];
        $data = '0x' . $this->contract->getData('transfer', $to, $amount);

        $transaction = [
            'from' => $from,
            'to' => $this->contractAddress,
            'data' => $data
        ];

        $this->web3->eth->estimateGas($transaction, function ($err, $gas) use (&$info) {
            if ($err !== null) {
                $info['error'] = $err->getMessage();
            } else {
                $info['estimatedGas'] = $gas; 
            }
        });
        $gasValue = $info['estimatedGas']; 
        $result['WEI'] = $gasValue->toString(); // in WEI
        $result['Ether'] = Converter::convertWEItoEther($result['WEI']); // Convert to Ether
        $result['Hex'] = "0x" . $gasValue->toHex(); // 0x... hex value
        return $result;
    }

    /**
     * Get network status information (latest block, hashrate, gas price, syncing status)
     * 
     * @return array
     */
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

    /**
     * Get congestion status of the network (Safe, Propose, Fast gas price)
     * 
     * @return array
     */
    public function getMonitorCongestion() {
        $status = [];
        // mainnet
        // $url = "https://api.polygonscan.com/api?module=gastracker&action=gasoracle&apikey={$this->apiKey}";
        // testnet
        $url = "https://api-testnet.polygonscan.com/api?module=gastracker&action=gasoracle&apikey={$this->apiKey}";
        $gasData = file_get_contents($url);
        if ($gasData !== false) {
            $gasData = json_decode($gasData, true);
            if ($gasData['status'] == '1' && isset($gasData['result'])) {
                $status['Safe'] = (float)$gasData['result']['SafeGasPrice'];
                $status['Propose'] = (float)$gasData['result']['ProposeGasPrice'];
                $status['Fast'] = (float)$gasData['result']['FastGasPrice'];
            } else {
                $status['error'] = "Could not retrieve gas price data";
            }
        } else {
            $status['error'] = "Failed to connect to Polygonscan API";
        }
        return $status;
    }
}