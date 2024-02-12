<?php
namespace Worken\Services;

use Web3\Contract;
use Web3\Utils;
use phpseclib\Math\BigInteger;

class WalletService {
    private $web3;
    private $contractAddress;
    private $EtherscanAPI = "";

    public function __construct($web3, $contractAddress) {
        $this->web3 = $web3;
        $this->contractAddress = $contractAddress;
    }

    /**
     * Get balance of given wallet address
     * 
     * @param string $address
     * @return string
     */
    public function getBalance(string $address) {
        $contract = new Contract($this->web3->provider, $this->getERC20ABIBalance());
        $contract->at($this->contractAddress);

        $balance = null;
        
        $contract->call('balanceOf', $address, function ($err, $result) use (&$balance) {
            if ($err !== null) {
                //TO DO: error handlings
                return;
            }

            $balancestring = $result['balance']->toString();
            $balancetokens = bcdiv($balancestring, bcpow('10', '18'), 0);
            $balance = intval($balancetokens);
        });

        return $balance;
    }
    
    /**
     * Get information about wallet
     * 
     * @param string $address
     * @return array
     */
    public function getInformation(string $address) {
        $info = [];

        // nonce (liczby transakcji)
        $this->web3->eth->getTransactionCount($address, function ($err, $nonce) use (&$info) {
            if ($err !== null) {
                $info['nonceError'] = 'Error while getting nonce';
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
            'publicKey' => $keyDetails
        ];
    }

    /**
     * Get history of transactions for given address
     * 
     * @param string $address
     * @return array
     */
    public function getHistory(string $address) {
        $polygonscanAPIKey = getenv('WORKEN_POLYGONSCAN_APIKEY');
        if($polygonscanAPIKey) {
            $this->EtherscanAPI = "https://api.polygonscan.com/api?module=account&action=txlist&address={$address}&startblock=0&endblock=99999999&sort=asc&apikey={$polygonscanAPIKey}";
        } else {
            return "Empty API key, please set WORKEN_POLYGONSCAN_APIKEY in your environment variables. You can get it from https://polygonscan.com/apis";
        }
        $response = file_get_contents($this->EtherscanAPI);
        $data = json_decode($response, true);

        $history = [];
        if ($data['status'] == '1' && $data['message'] == 'OK') {
            foreach ($data['result'] as $transaction) {
                $history[] = [
                    'blockNumber' => $transaction['blockNumber'],
                    'timeStamp' => date('Y-m-d H:i:s', $transaction['timeStamp']),
                    'hash' => $transaction['hash'],
                    'nonce' => $transaction['nonce'],
                    'blockHash' => $transaction['blockHash'],
                    'transactionIndex' => $transaction['transactionIndex'],
                    'from' => $transaction['from'],
                    'to' => $transaction['to'],
                    'value' => $transaction['value'],
                    'gas' => $transaction['gas'],
                    'gasPrice' => $transaction['gasPrice'],
                    'isError' => $transaction['isError'],
                    'txreceipt_status' => $transaction['txreceipt_status'],
                ];
                return $history;
            }
        } else {
            return $data['message'];
        }
    }

    private function getERC20ABIBalance() {
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