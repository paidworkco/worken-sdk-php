<?php
namespace Worken\Services;

use Web3\Contract;
use Web3\Web3;
use Web3\Utils;
use Worken\Utils\ABI;

class ContractService {
    private $web3;
    private $contractAddress;
    private $contract;

    public function __construct(Web3 $web3, string $contractAddress) {
        $this->web3 = $web3;
        $this->contractAddress = $contractAddress;
        $this->contract = new Contract($this->web3->provider, ABI::ERC20());
        $this->contract->at($this->contractAddress);
    }

    // public function showContractStatus($callback) { // TO DO
    //     $this->web3->eth->getBalance($this->contractAddress, function ($err, $balance) use ($callback) {
    //         if ($err !== null) {
    //             $callback('Error: ' . $err->getMessage(), null);
    //             return;
    //         }
    //         $balanceInEther = Utils::fromWei($balance, 'ether');
    //         $callback(null, ['BalanceWEI' => $balance->toString(), 'BalanceEther' => $balanceInEther]);
    //     });
    // }

    // not finished
    public function showContractFunction() {
        $abi = $this->contract->getAbi();
        $functions = [];
        foreach ($abi as $item) {
            if ($item['type'] === 'function') {
                array_push($functions, $item['name']);
            }
        }
        return $functions;
    }
}