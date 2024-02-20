<?php
namespace Worken\Services;

use Web3\Web3;
use Web3\Utils;
use Web3\Contract;
use Web3p\EthereumTx\Transaction;
use Worken\Services\WalletService;
use Worken\Services\NetworkService;
use Worken\Utils\ABI;

class TransactionService {
    private $web3;
    private $contractAddress;
    private $walletService;
    private $networkService;
    private $contract;
    private $apiKey;

    public function __construct(Web3 $web3, WalletService $walletService, NetworkService $networkService, string $contractAddress, string $apiKey) {
        $this->web3 = $web3;
        $this->contractAddress = $contractAddress;
        $this->walletService = $walletService;
        $this->networkService = $networkService;
        $this->contract = new Contract($this->web3->provider, ABI::ERC20());
        $this->contract->at($this->contractAddress);
        $this->apiKey = $apiKey;
    }

    /**
     * Send transaction
     * 
     * @param string $privateKey Sender private key
     * @param string $from Sender address in Hex
     * @param string $to Receiver address in Hex
     * @param string $amount Amount to send in WEI
     * @return array
     */
    public function sendTransaction(string $privateKey, string $from, string $to, string $amount) {
        $status = [];
        $data = '0x' . $this->contract->getData('transfer', $to, Utils::toHex($amount));

        $walletinfo = $this->walletService->getInformation($from);
        if(!empty($walletinfo['nonce']['error'])) {
            return $walletinfo['nonce'];
        } else {
            $nonce = $walletinfo['nonce'] + 1;
            $nonce = $this->web3->utils->toHex($nonce);
        }

        $gas = $this->networkService->getEstimatedGas($from, $to, $amount);
        if(!empty($gas['error'])) {
            return $gas;
        } else {
            $gas = $gas['Hex'];
        }

        $gasPrice = $this->networkService->getMonitorCongestion();
        if(!empty($gasPrice['error'])) {
            return $gasPrice;
        } else {
            $gasPrice = (string)round($gasPrice['Safe']);
        }

        $transaction = new Transaction([
            'nonce' => $nonce,
            'from' => $from,
            'to' => $this->contractAddress,
            'gas' => $gas,
            'gasPrice' => Utils::toHex(Utils::toWei($gasPrice, 'gwei'), true),
            'value' => '0x0', 
            'chainId' => 80001, // 80001 testnet, 137 mainnet
            'data' => $data
        ]);

        $signedTransaction = $transaction->sign($privateKey);

        $this->web3->eth->sendRawTransaction('0x' . $signedTransaction, function ($err, $txHash) use (&$status) {
            if ($err !== null) {
                $status['error'] = $err->getMessage();
            }
            $status['txHash'] = $txHash;
        });
    }

    /**
     * Get transaction status
     * 
     * @param string $txHash Transaction hash
     * @return int 0 - success, 1 - fail, 2 - pending or not found
     */
    public function getTransactionStatus(string $txHash) {
        $status = "";
        $this->web3->eth->getTransactionReceipt($txHash, function ($err, $receipt) use (&$status) {
            if ($err !== null) {
                echo "Error: " . $err->getMessage();
                return;
            }
            if ($receipt !== null) {
                if ($receipt->status == '0x1') {
                    $status = 0; // success
                } else {
                    $status = 1; // fail
                }
            } else {
                $status = 2; // pending or not found
            }
        });
        return $status;
    }
}