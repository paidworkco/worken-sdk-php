<?php
namespace Worken\Services;

use Web3\Contract;
use Web3\Web3;
use Worken\Utils\Converter;
use Worken\Utils\ABI;
use Worken\Utils\KeyFactory;


class WalletService {
    private $web3;
    private $contractAddress;
    private $apiKey;

    public function __construct(Web3 $web3, string $contractAddress, string $apiKey) {
        $this->web3 = $web3;
        $this->contractAddress = $contractAddress;
        $this->apiKey = $apiKey;
    }

    /**
     * Get balance of given wallet address
     * 
     * @param string $address
     * @return string
     */
    public function getBalance(string $address) {
        $contract = new Contract($this->web3->provider, ABI::ERC20Balance());
        $contract->at($this->contractAddress);

        $result = [];
        
        $contract->call('balanceOf', $address, function ($err, $balance) use (&$result) {
            if ($err !== null) {
                return $result['walletBalanceWORK']['error'] = $err->getMessage();
            }
            $result['walletBalanceWORK']['WEI'] = $balance['balance']->toString();
            $result['walletBalanceWORK']['Ether'] = Converter::convertWEItoEther($result['walletBalanceWORK']['WEI']); // Convert to Ether
            $result['walletBalanceWORK']['Hex'] = "0x" . $balance['balance']->toHex(); // 0x... hex value
        });

        return $result;
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
                $info['nonce']['error'] = $err->getMessage();
            }
            $info['nonce'] = $nonce->toString();
        });

        // TO DO - more info about wallet

        return $info;
    }

    // required gmp extension in php.ini
    public function createWallet(int $words)
    {
        $result = [];

        $seedphrase = KeyFactory::generateSeedPhrase($words);
        $result['seedphrase'] = $seedphrase->words;

        $keys = KeyFactory::generateKeysfromSeedPhrase($seedphrase->entropy);
        $result['privateKey'] = $keys->privateKey;
        $result['publicKey'] = $keys->publicKey;
        $result['publicKeyCompressed'] = $keys->publicKeyCompressed;
        $result['address'] = KeyFactory::generateAddressfromPublicKey($keys->publicKey);
        return $result;
    }

    /**
     * Get history of transactions for given address
     * 
     * @param string $address
     * @return array
     */
    public function getHistory(string $address) {
        if (empty($this->apiKey)) {
            $history['error'] = "Empty API key, please set WORKEN_POLYGONSCAN_APIKEY in your environment variables. You can get it from https://polygonscan.com/apis";
        }
    
        $url = "https://api.polygonscan.com/api?module=account&action=txlist&address={$address}&startblock=0&endblock=99999999&sort=asc&apikey={$this->apiKey}";
        $history = [];
        $response = file_get_contents($url);
        if ($response === FALSE) {
            $history['error'] = "Error while fetching data from Polygonscan.";
        }

        $result = json_decode($response, true);
    
        if ($result['status'] == '0') {
            if ($result['message'] == 'No transactions found') {
                return $history;
            }
            $result['error'] = $result['message'];
        }

        foreach ($result['result'] as $transaction) {
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
        }
    
        return $history;
    }
}