# PHP Zip-Tax

> A PHP wrapper for the zip-tax.com API.

## Requirements

1. An account on zip-tax.com
2. PHP 5.6.0+
3. libcurl

## Usage

```
<?php
$zipTax = new supplyhog\ZipTax('YOUR_API_KEY');

try{
	$response = $zipTax->request('37402', 'Chattanooga', 'TN');
	if($response) {
		echo $response['results'][0]['taxSales'];
	}
}
catch(\Exception $e) {
	echo $e->getMessage();
}
```

## License

MIT © [Vu Tran](https://github.com/vutran/)
