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
     * Get balance WORK tokens of given wallet address
     * 
     * @param string $address
     * @return string Balance in WEI, Ether and Hex value
     */
    public function getBalance(string $address) {
        $contract = new Contract($this->web3->provider, ABI::ERC20());
        $contract->at($this->contractAddress);

        $result = [];
        
        $contract->call('balanceOf', $address, function ($err, $balance) use (&$result) {
            if ($err !== null) {
                return $result['error'] = $err->getMessage();
            }
            $result['WEI'] = $balance['balance']->toString();
            $result['Ether'] = Converter::convertWEItoEther($result['WEI']); // Convert to Ether
            $result['Hex'] = "0x" . $balance['balance']->toHex(); // 0x... hex value
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

    /**
     * Create new ETH wallet
     * 
     * @param int $words Number of words for seedphrase
     * @return array Wallet information (seedphrase, private key, public key, compressed public key, address)
     */
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

        // mainnet
        // $url = "https://api.polygonscan.com/api?module=account&action=txlist&address={$address}&startblock=0&endblock=99999999&sort=asc&apikey={$this->apiKey}";
        // testnet - test endpoint
        // $url = "https://api-testnet.polygonscan.com/api?module=account&action=txlist&address={$address}&startblock=0&endblock=99999999&sort=asc&apikey={$this->apiKey}";
        // testnet 2 - internal transactions similiar 
        $url = "https://api-testnet.polygonscan.com/api?module=account&action=txlistinternal&address={$address}&startblock=0&endblock=99999999&sort=asc&apikey={$this->apiKey}";
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
            $result['error'] = $result['result'];
        }

        foreach ($result['result'] as $transaction) {
            $history[] = $transaction;
        }
    
        return $history;
    }
}