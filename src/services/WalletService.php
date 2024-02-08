<?php
namespace Worken\Services;

use Web3\Contract;
use Web3\Utils;

class WalletService {
    private $web3;
    private $contractAddress;
    private $EtherscanAPI = "";

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
        $info = [];
        
        // saldo
        $this->web3->eth->getBalance($address, function ($err, $balance) use (&$info) {
            if ($err !== null) {
                $info['balanceError'] = 'Nie można pobrać salda.';
                return;
            }
            $info['balance'] = Utils::toEther($balance, 'wei');
        });

        // nonce (liczby transakcji)
        $this->web3->eth->getTransactionCount($address, function ($err, $nonce) use (&$info) {
            if ($err !== null) {
                $info['nonceError'] = 'Nie można pobrać nonce.';
                return;
            }
            $info['nonce'] = $nonce->toString();
        });

        // TO DO - more info about wallet

        return $info;
    }

    public function createWallet() {
        $configargs = array(
            'private_key_bits' => 2048,
            'default_md' => "sha256",
        );
        $opensslConfigPath = getenv('WORKEN_OPENSSL_CONF') ?: __DIR__ . '/assets/openssl.cnf'; 
        if (file_exists($opensslConfigPath)) {
            $configargs['config'] = $opensslConfigPath;
        }
    
        $res = openssl_pkey_new($configargs);
    
        if (!$res) {
            return "error";
        }
        if (!openssl_pkey_export($res, $privKey, NULL, $configargs)) {
            return "error_exporting_key";
        }
        $keyDetails = openssl_pkey_get_details($res);
        if (!$keyDetails) {
            return "error_getting_details"; 
        }
        $privKey = $keyDetails['key']; 
    
        return [
            'privateKey' => $privKey,
            'publicKey' => $keyDetails['publicKey']
        ];
    }

    public function getHistory($address) {
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