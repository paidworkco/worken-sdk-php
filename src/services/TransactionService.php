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

    public function __construct(Web3 $web3, WalletService $walletService, NetworkService $networkService, string $contractAddress) {
        $this->web3 = $web3;
        $this->contractAddress = $contractAddress;
        $this->walletService = $walletService;
        $this->networkService = $networkService;
        $this->contract = new Contract($this->web3->provider, ABI::ERC20());
        $this->contract->at($this->contractAddress);
    }

    public function sendTransaction(string $privateKey, string $from, string $to, string $amount) {
        $amountWEI = Utils::toWei($amount, 'ether');
        $data = '0x' . $this->contract->getData('transfer', $to, Utils::toHex($amountWEI));

        $walletinfo = $this->walletService->getInformation($from);
        if(!empty($walletinfo['nonce']['error'])) {
            return $walletinfo['nonce']['error'];
        } else {
            $nonce = $walletinfo['nonce'] + 1;
            $nonce = $this->web3->utils->toHex($nonce);
        }

        $gas = $this->networkService->getEstimatedGas($from, $to, $amountWEI);
        if(!empty($gas['error'])) {
            return $gas['error'];
        } else {
            $gas = $gas['estimatedGas']['Hex'];
        }

        $gasPrice = $this->networkService->getMonitorCongestion();
        if(!empty($gasPrice['gasPrice']['error'])) {
            return $gasPrice['gasPrice']['error'];
        } else {
            $gasPrice = (string)round($gasPrice['gasPrice']['Safe']);
            echo "gasPrice:".$gasPrice;
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

        $this->web3->eth->sendRawTransaction('0x' . $signedTransaction, function ($err, $txHash) {
            if ($err !== null) {
                return $err->getMessage();
            }
            return $txHash;
        });
    }
}