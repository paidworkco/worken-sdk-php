<?php

namespace Worken\Utils;

use FurqanSiddiqui\BIP39\BIP39;
use BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory;
use BitWasp\Buffertools\Buffer;
use kornrunner\Keccak;
use BitWasp\Bitcoin\Key\PrivateKeyFactory;

class KeyFactory {
    public static function generateSeedPhrase(int $words) {
        $response = new \stdClass();
        if (!in_array($words, [12, 15, 18, 21, 24])) {
            throw new \InvalidArgumentException("Invalid number of words for a mnemonic. Allowed values are 12, 15, 18, 21, 24.");
        }
        $mnemonic = BIP39::Generate($words);
        $response->words = $mnemonic->words; //Words in array
        $response->wordsString = implode(" ", $mnemonic->words); //Words in string imploded by space
        $response->entropy = $mnemonic->entropy; // Entropy of mnemonic, ready for generating private key
        return $response;
    }

    public static function generateKeysfromSeedPhrase(string $entropy) {
        $response = new \stdClass();

        $seedBuffer = Buffer::hex($entropy); // Entropy mnemonic
        $master = HierarchicalKeyFactory::fromEntropy($seedBuffer);
        // Ethereum path
        $child = $master->derivePath("44'/60'/0'/0/0");
        // Export private key
        $response->privateKey = $child->getPrivateKey()->getHex();
        $response->publicKey = PrivateKeyFactory::fromHex($response->privateKey)->getPublicKey()->getHex();
        $response->publicKeyCompressed = $child->getPublicKey()->getHex();
        return $response;
    }

    public static function generateAddressfromPublicKey(string $publicKey) {
        $publicKey = str_replace('0x', '', $publicKey);

        $hash = Keccak::hash(hex2bin(substr($publicKey, 2)), 256);
        $address = '0x' . substr($hash, -40);
        return $address;
    }
}