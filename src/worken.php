<?php

namespace Worken;

use Worken\Services\WalletService;
// use Worken\Services\TransactionService;
// use Worken\Services\ContractService;
// use Worken\Services\NetworkService;
use Web3\Web3;

class Worken {
    public $wallet;
    public $transaction;
    public $contract;
    public $network;
    private $web3;
    private $contractAddress;
    private $nodeUrl;


    /**
     * Worken constructor
     * 
     * @param string $nodeUrl
     * @param string $contractAddress
     */
    public function __construct() {
        $this->contractAddress = "0x3AE0726b5155fCa70dd79C0839B07508Ce7F0F13";
        $this->nodeUrl = "https://rpc-mumbai.maticvigil.com/";
        $this->web3 = new Web3($this->nodeUrl);

        $this->wallet = new WalletService($this->web3, $this->contractAddress);
        // $this->transactionService = new TransactionService($this->web3);
        // $this->contractService = new ContractService($this->web3, $this->contractAddress);
        // $this->networkService = new NetworkService($this->web3);
    }
}