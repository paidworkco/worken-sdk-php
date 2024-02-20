# worken-sdk

<br />
<p align="center">
  <img src="https://zrcdn.net/images/logos/paidwork/paidwork-logo-header-mobile-bitlabs.png" alt="Paidwork" />
</p>

<h3 align="center">
  Make and receive secure transactions with Worken
</h3>
<p align="center">
  <a href="https://www.paidwork.com/?utm_source=github.com&utm_medium=referral&utm_campaign=readme">🚀 Over 15M+ Users using WORK!</a>
</p>

<p align="center">
  <a href="https://github.com/paidworkco/worken-sdk-php">
    <img alt="GitHub Repository Stars Count" src="https://img.shields.io/github/stars/paidworkco/worken-sdk-php?style=social" />
  </a>
    <a href="https://x.com/paidworkco">
        <img alt="Follow Us on X" src="https://img.shields.io/twitter/follow/paidworkco?style=social" />
    </a>
</p>
<p align="center">
    <a href="http://commitizen.github.io/cz-cli/">
        <img alt="Commitizen friendly" src="https://img.shields.io/badge/commitizen-friendly-brightgreen.svg" />
    </a>
    <a href="https://github.com/paidworkco/worken-sdk-php">
        <img alt="License" src="https://img.shields.io/github/license/paidworkco/worken-sdk-php" />
    </a>
    <a href="https://github.com/paidworkco/worken-sdk-php/pulls">
        <img alt="PRs Welcome" src="https://img.shields.io/badge/PRs-welcome-brightgreen.svg" />
    </a>
</p>

SDK library providing access to make easy and secure transactions with Worken

Feel free to try out our provided Postman collection. Simply click the button below to fork the collection and start testing.<br>

[<img src="https://run.pstmn.io/button.svg" alt="Run In Postman" style="width: 128px; height: 32px;">](https://god.gw.postman.com/run-collection/32839969-fd54da1c-0e5b-43e8-9d89-8330e9bebf17?action=collection%2Ffork&source=rip_markdown&collection-url=entityId%3D32839969-fd54da1c-0e5b-43e8-9d89-8330e9bebf17%26entityType%3Dcollection%26workspaceId%3Dbeab0417-9a12-472d-8f22-3c7c478123a9)

## Install

Via Composer (tba.)

```
$ composer require paidworkco/worken-sdk
```

## Configuration

To ensure flexibility and ease of integration, the Worken SDK allows for configuration through environmental variables. These variables can be set directly in your project's .env file. Below is a list of available configuration variables along with their descriptions:

`WORKEN_POLYGONSCAN_APIKEY`: This is your API key, which you can generate at: https://polygonscan.com/myapikey. The API key is required for accessing PolygonScan's data programmatically and is essential for querying transaction history, block information data and other blockchain-related information on the Polygon network.

## Usage

#### Initialization
```php
use Worken\Worken;
$worken = new Worken(); // Create worken object
```
### Wallet
#### Get wallet balance
```php
$worken->wallet->getBalance(string $address)
```
| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `address` | `string` | **Required**. Your wallet address |

This structure details the balance of a wallet in terms of the WORK token specified in contract, providing the balance in WEI, Ether, and Hexadecimal units.

- `WEI (string)`: The balance of the wallet expressed in WEI, which is the smallest denomination of Ether. Given its size, the balance is represented as a string to maintain precision. Example: `11820000000000000000000`
- `Ether (string)`: The balance of the wallet converted into Ether, offering a more readable and commonly used representation of the balance. This conversion is necessary for understanding the balance in terms of Ether, which is more familiar to users. Example: `11820.000000000000000000`
- `Hex (string)`: The balance of the wallet expressed as a hexadecimal value. This format is often used in lower-level blockchain operations and interactions. Example: `0x0280c373aef4bd300000`

#### Get wallet information
```php
$worken->wallet->getInformation(string $address)
```
| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `address` | `string` | **Required**. Your wallet address |

- `nonce (int)`: Number of transactions on specific address, needed for send transaction.

*TO DO: more informations if needed*

#### Get wallet transaction history
```php
$worken->wallet->getHistory(string $address)
```
| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `address` | `string` | **Required**. Your wallet address |

Output given in array, below specific variables.

- `blockNumber`: Block number in which this transaction was included. Block numbers are sequential and indicate the position of a block in the blockchain.
- `timeStamp`: Timestamp for when the block was mined, expressed in Unix epoch time.
- `hash`: This is the unique identifier for the transaction, also known as the transaction hash(txHash).
- `from`: Address of the sender of the transaction.
- `to`: Recipient's address.
- `value`: This indicates the amount of cryptocurrency (likely Ether if this is Ethereum) that was transferred in this transaction. A value of "0" suggests that no Ether was transferred as part of this transaction, which is common for contract deployments like Worken(WORK) or other non-value transactions.
- `contractAddress`:  Address of the contract that was created by this transaction. 
- `input`: This field contains the data sent along with the transaction.
- `type`: This specifies the type of transaction. 
- `gas`: This is the maximum amount of gas the sender is willing to use for this transaction. 
- `gasUsed`: This indicates the actual amount of gas that was used to execute the transaction. It's often less than the maximum gas specified.
- `traceId`: This is likely an identifier used to trace the transaction through the system or in a debugging process.
- `isError`: This field indicates whether the transaction encountered an error. A value of "0" means there was no error.
- `errCode`: Since `isError = 0`, this field is empty. If there was an error, it might contain an error code or message indicating what went wrong.

#### Create new wallet
```php
$worken->wallet->createWallet(int $words)
```
| Parameter | Type     | Description                                                                     |
| :-------- | :------- | :------------------------------------------------------------------------------ |
| `words`   | `int`    | **Required**. Count number of words in seedphrase. **Only: 12, 15, 18, 21, 24** |

This structure outlines the output of the `createWallet()` function, which generates a new Ethereum wallet. It provides essential details including the seed phrase, private key, public key (both uncompressed and compressed formats), and the Ethereum address.

- `seedphrase (array)`: An array of X words that constitute the seed phrase. This phrase is crucial for wallet recovery and should be stored securely.
- `privateKey (string)`: The private key of the wallet, expressed as a 64-character hexadecimal string. It is essential for signing transactions and should be kept confidential.
- `publicKey (string)`: The uncompressed public key associated with the wallet, expressed as a 130-character hexadecimal string. It is derived from the private key. 
- `publicKeyCompressed (string)`: The compressed version of the public key, expressed as a 66-character hexadecimal string. Useful for certain operations where space is a concern.
- `address (string)`: The Ethereum address generated from the public key, expressed as a 42-character hexadecimal string. This address is used for sending and receiving funds.

### Contract

TODO: Show contract status, show contract function

### Transactions

#### Send transaction 
```php
$worken->transaction->sendTransaction(string $privateKey, string $from, string $to, string $amount)
```
| Parameter     | Type        | Description                                                      |
| :------------ | :---------- | :--------------------------------------------------------------- |
| `privateKey`  | `string`    | **Required**. Sender wallet private key to authorize transaction |
| `from`        | `string`    | **Required**. Sender wallet address (hex)                        |
| `to`          | `string`    | **Required**. Receiver wallet address (hex)                      |
| `amount`      | `string`    | **Required**. Amount of WORK token in WEI                        |

This function sends transaction in WORK token using Web3.

- `txHash (string)`: Transaction hash

#### Show transaction status
```php
$worken->transaction->getTransactionStatus(string $txHash)
```
| Parameter  | Type     | Description                    |
| :--------- | :------- | :----------------------------- |
| `txHash`   | `string` | **Required**. Transaction hash |

**Output**

- `status (int)`: 0 - Success, 1 - Failed, 2 - Transaction not found or pending

TODO: receive transaction, show recent transaction

### Network

#### Show block information
```php
$worken->network->getBlockInformation(int $blockNumber)
```
| Parameter     | Type     | Description                   |
| :------------ | :------- | :---------------------------- |
| `blockNumber` | `int`    | **Required**. Number of block |

TO DO - info about output from Polygonscan API

#### Show estimated gas
```php
$worken->network->getEstimatedGas(string $from, string $to, string $amount)
```
| Parameter | Type     | Description                                                                  |
| :-------  | :------- | :----------------------------------------------------------------------------|
| `from`    | `string` | **Required**. Sender address in format `0x...`                               |
| `to`      | `string` | **Required**. Recipient address in format `0x...`                            |
| `amount`  | `string` | **Required**. Amount in WEI, example: 1 WEI = 0.000000000000000001 of token  |

This structure provides the estimated gas required for a transaction on the Ethereum network, represented in various units: WEI, Ether, and Hex.

- `WEI (string)`: The estimated gas required for the transaction, expressed in WEI, the smallest unit of Ether. Example: "21000"
- `Ether (string)`: The estimated gas converted into Ether, allowing for an understanding of the cost in more familiar terms. Due to the high granularity of WEI, this number is typically very small and expressed in scientific notation for readability. Example: "0.000000000000021000"
- `Hex (string)`: The estimated gas required for the transaction, expressed as a hexadecimal value. This format is often used in low-level or system-level interactions with the Ethereum blockchain. Example: "0x5208"

#### Show network status
```php
$worken->network->getNetworkStatus()
```
This function returns an array containing the following keys and values about Polygon network:

- `latestBlock (int)`: The number of the most recent block in the network.
Example: `45919144`

- `hashrate (string)`: The current network hashrate expressed as a string.
Example: `"0"` (Note: A value of "0" may indicate a lack of available data or a problem with reading the hashrate).

- `gasPrice (array)`: An array containing information about the current gas price in different units:
  - `WEI (string)`: The gas price expressed in WEI units.
    Example: `1500000015`
  - `Ether (string)`: The gas price converted to Ether.
    Example: `0.000000001500000015`
  - `Hex (string)`: The gas price expressed as a hexadecimal value.
    Example: `0x59682f0f`
- `syncStatus (bool)`: The synchronization status with the blockchain network. true indicates that the node is synchronized, whereas false indicates a lack of synchronization.
Example: `true`

#### Show monitor network congestion
```php
$worken->network->getMonitorCongestion()
```

- `Safe (float)`: The recommended gas price for transactions expected to be confirmed within a reasonable time frame without overpaying. Measured in Gwei. Example: `50.5`
- `Propose (float)`: The gas price suggested for transactions that are slightly more urgent and aim for a faster confirmation time than those marked as Safe. Measured in Gwei. Example: `57.1`
- `Fast (float)`: The highest gas price recommended for transactions that need to be confirmed as quickly as possible. Measured in Gwei. Example: `57.6`
