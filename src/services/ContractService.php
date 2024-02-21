<?php
namespace Worken\Services;

use Web3\Web3;

class ContractService {
    private $web3;
    private $contractAddress;
    private $contract;
    private $apiKey;

    public function __construct(Web3 $web3, string $contractAddress, string $apiKey) {
        $this->web3 = $web3;
        $this->contractAddress = $contractAddress;
        $this->apiKey = $apiKey;
    }

    /**
     * Get contract status
     * 
     * @return array
     */
    public function getContractStatus() { 
        $result = [];
        $this->web3->eth->getCode($this->contractAddress, function ($err, $code) use (&$result) {
            if ($err !== null) {
                $result['error'] = $err->getMessage();
                return;
            }
            if ($code != '0x') { // Kontrakt istnieje
                $result['status'] = true;
            } else { // Kontrakt nie istnieje lub został zniszczony
                $result['status'] = false;
            }
        });
        return $result;
    }

    /**
     * Get contract ABI functions / contract must be verified on Polygonscan
     * 
     * @return string ABI 
     */
    public function getContractFunction() {
        // testnet
        $url = "https://api-testnet.polygonscan.com/api?module=contract&action=getsourcecode&address={$this->contractAddress}&apikey={$this->apiKey}";
        // mainnet 
        // $url = "https://api.polygonscan.com/api?module=contract&action=getsourcecode&address={$this->contractAddress}&apikey={$this->apiKey}";
        $abi = "";
        $response = file_get_contents($url);
        if ($response === FALSE) {
            $abi = "Error while fetching data from Polygonscan.";
        }

        $result = json_decode($response, true);
        if ($result['status'] == '1') {
            $abi = $result['result'][0]['ABI'];
        }
        return $abi;
    }
}