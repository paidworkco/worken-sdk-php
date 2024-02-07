<?php

namespace Worken;

use Worken\Services\WalletService;
// use Worken\Services\TransactionService;
// use Worken\Services\ContractService;
// use Worken\Services\NetworkService;
use Web3\Web3;

class Worken {
    private $walletService;
    private $transactionService;
    private $contractService;
    private $networkService;
    private $web3;
    private $contractAddress;
    private $nodeUrl;

    public function __construct($nodeUrl, $contractAddress) {
        $this->web3 = new Web3($nodeUrl);
        $this->contractAddress = $contractAddress;
        $this->nodeUrl = $nodeUrl;

        $this->walletService = new WalletService($this->web3, $this->contractAddress);
        // $this->transactionService = new TransactionService($this->web3);
        // $this->contractService = new ContractService($this->web3, $this->contractAddress);
        // $this->networkService = new NetworkService($this->web3);
    }

    //Wallet Service
    public function getBalance($address) {
        return $this->walletService->getBalance($address);
    }

    // Metody delegujące do odpowiednich usług
}