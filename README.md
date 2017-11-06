# Simple PHP Client for Bitcoinaverage

Use the [BitcoinAverage](https://bitcoinaverage.com/) API with PHP.

Currently only ticker and exchange is supported.

## Usage

```PHP
require_once('./Bitcoinaverage.php');

$btcavg = new Bitcoinaverage(
	<public key>,
	<secret key>
);

try {
	$ticker = $btcavg->ticker('EUR,USD');
} catch (Exception $e) {
    // Error, something went wrong...
}
```
