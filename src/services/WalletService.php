<?php
namespace Worken\Services;

use Web3\Contract;

class WalletService {
    private $web3;
    private $contractAddress;

    public function __construct($web3, $contractAddress) {
        $this->web3 = $web3;
        $this->contractAddress = $contractAddress;
    }

    public function getBalance($address) {
        $contract = new Contract($this->web3->provider, $this->getERC20ABI());
        $contract->at($this->contractAddress);

        $balance = null;
        $contract->call('balanceOf', $address, function ($err, $result) use (&$balance) {
            if ($err !== null) {
                //TO DO: error handlings
                return;
            }
            $balance = bcdiv($result['balance'], bcpow('10', '18'), 18);
        });

        return $balance;
    }

    public function getInformation($address) {
        return null;
    }

    public function getHistory($address) {
        return null;
    }

    public function create() {
        return null;
    }

    private function getERC20ABI() {
        return '[
            {
              "constant": true,
              "inputs": [{"name": "_owner", "type": "address"}],
              "name": "balanceOf",
              "outputs": [{"name": "balance", "type": "uint256"}],
              "type": "function"
            }
          ]';
    }
}