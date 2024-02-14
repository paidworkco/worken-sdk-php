<?php
namespace Worken\Utils;

class ABI {
    public static function ERC20Balance() {
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