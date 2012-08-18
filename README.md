# Machina
===============

## REST service black-box testing framework


## Verbosity

### default (no v param)

```
Running tests for: 'fenphen'

Found 9 tests
....E...F

Failure Summary:

	[tests.fenphen.yaml#GET all robot]	"The HTTP response code for this GET request [http://localhost/fen-phen/?_wrap_array=1&__c=/api/v1/robot] should have been 200, was instead: [404]
			Response __error: Array
			(
			    [message] => Resource robot is not known
			    [type] => invalid_request
			    [validation] =>
			)
			"


Error Summary:

	[tests.fenphen.yaml#PATCH product]		"The the function name ['uniXqid'] from the php eval property value: 'name: php::uniXqid()' is unknown "

Run time: 13 seconds
Memory Usage: 2,559,656 bytes
```

### -v

```
Registering service: 'fenphen' (file: /Users/sam/Projects/deus-ex-machina/machine/conf/tests.fenphen.yaml)

Running tests for: 'fenphen'

Found 9 tests
Running GET Test: 'GET all product' (just ensure the get all URL, '/product' returns 200)
Passed
Running GET Test: 'GET product' (Create a product(POST) then assert you can GET it)
Passed
Running POST Test: 'POST product' (Test the successful POST of a product)
Passed
Running DELETE Test: 'DELETE product' (Test the successful DELETE of a product)
Passed
Running PATCH Test: 'PATCH product' (Test the successful PATCH of a product)
The the function name ['uniXqid'] from the php eval property value: 'name: php::uniXqid()' is unknown
Running PUT Test: 'PUT product' (Test the successful PUT of a product)
Passed
Running GET Test: 'GET all tag' (just ensure the get all URL, '/tag' returns 200)
Passed
Running GET Test: 'GET tag' (Create a tag(POST) then assert you can GET it)
Passed
Running GET Test: 'GET all robot' (just ensure the get all URL, '/robot' returns 200)
The HTTP response code for this GET request [http://localhost/fen-phen/?_wrap_array=1&__c=/api/v1/robot] should have been 200, was instead: [404]
Response __error: Array
(
    [message] => Resource robot is not known
    [type] => invalid_request
    [validation] =>
)


Failure Summary:

	[tests.fenphen.yaml#GET all robot]	"The HTTP response code for this GET request [http://localhost/fen-phen/?_wrap_array=1&__c=/api/v1/robot] should have been 200, was instead: [404]
			Response __error: Array
			(
			    [message] => Resource robot is not known
			    [type] => invalid_request
			    [validation] =>
			)
			"


Error Summary:

	[tests.fenphen.yaml#PATCH product]		"The the function name ['uniXqid'] from the php eval property value: 'name: php::uniXqid()' is unknown "

Run time: 20 seconds
Memory Usage: 2,566,688 bytes
```

### -vv

```
Found Manifest File: /Users/sam/Projects/deus-ex-machina/machine/conf/tests.fenphen.yaml
Registering service: 'fenphen' (file: /Users/sam/Projects/deus-ex-machina/machine/conf/tests.fenphen.yaml)
1 Manifest files to process

Running tests for: 'fenphen'

Found 9 tests
Running GET Test: 'GET all product' (just ensure the get all URL, '/product' returns 200)

 ...

Running DELETE Test: 'DELETE product' (Test the successful DELETE of a product)
Creating resource for test (/product)
Constructed full URI: http://localhost/fen-phen/?_wrap_array=1&__c=/api/v1/product
Response Body: {"id":634,"name":"502ae520ded2a","slug":"502ae520ded2a","created":{"date":"2012-08-14 16:54:09","timezone_type":3,"timezone":"America\/Los_Angeles"},"modified":{"date":"2012-08-14 16:54:09","timezone_type":3,"timezone":"America\/Los_Angeles"}}
Asserted expected Response code [201] matched recieved: 201
Asserted Expected properties [name] were found in response Resource
Resource Created (/product/634)
Constructed full URI: http://localhost/fen-phen/?_wrap_array=1&__c=/api/v1/product/634
Asserted expected Response code [204] matched recieved: 204
Asserted response body was empty
Passed
-------------
Running PATCH Test: 'PATCH product' (Test the successful PATCH of a product)
Creating resource for test (/product)
Constructed full URI: http://localhost/fen-phen/?_wrap_array=1&__c=/api/v1/product
Response Body: {"id":635,"name":"502ae5238e7a0","slug":"502ae5238e7a0","created":{"date":"2012-08-14 16:54:12","timezone_type":3,"timezone":"America\/Los_Angeles"},"modified":{"date":"2012-08-14 16:54:12","timezone_type":3,"timezone":"America\/Los_Angeles"}}
Asserted expected Response code [201] matched recieved: 201
Asserted Expected properties [name] were found in response Resource
Resource Created (/product/635)
ERROR: The the function name ['uniXqid'] from the php eval property value: 'name: php::uniXqid()' is unknown
-------------
Running PUT Test: 'PUT product' (Test the successful PUT of a product)
Creating resource for test (/product)
Constructed full URI: http://localhost/fen-phen/?_wrap_array=1&__c=/api/v1/product
Response Body: {"id":636,"name":"502ae524eb4b7","slug":"502ae524eb4b7","created":{"date":"2012-08-14 16:54:13","timezone_type":3,"timezone":"America\/Los_Angeles"},"modified":{"date":"2012-08-14 16:54:13","timezone_type":3,"timezone":"America\/Los_Angeles"}}
Asserted expected Response code [201] matched recieved: 201
Asserted Expected properties [name] were found in response Resource
Resource Created (/product/636)
Constructed full URI: http://localhost/fen-phen/?_wrap_array=1&__c=/api/v1/product/636
Asserted expected Response code [204] matched recieved: 204
Asserted response body was empty
Passed
-------------
Running GET Test: 'GET all tag' (just ensure the get all URL, '/tag' returns 200)
Constructed full URI: http://localhost/fen-phen/?_wrap_array=1&__c=/api/v1/tag
Response Body: []
Asserted expected Response code [200] matched recieved: 200
Passed
-------------
Running GET Test: 'GET tag' (Create a tag(POST) then assert you can GET it)
Constructed full URI: http://localhost/fen-phen/?_wrap_array=1&__c=/api/v1/tag
Response Body: []
Asserted expected Response code [200] matched recieved: 200
Passed
-------------
Running GET Test: 'GET all robot' (just ensure the get all URL, '/robot' returns 200)
Constructed full URI: http://localhost/fen-phen/?_wrap_array=1&__c=/api/v1/robot
Response Body: {"__error":{"message":"Resource robot is not known","type":"invalid_request","validation":null},"__dev-mode-log-stack":null,"__dev-mode-stack-trace":[{"file":"\/Users\/sam\/Projects\/fen-phen\/src\/vendor\/saccharin\/src\/run.php","line":192,"function":"response_error","class":"Saccharin\\Util\\Http","type":"::","args":[{}]},{"file":"\/x\/fen-phen\/index.php","line":82,"args":["\/Users\/sam\/Projects\/fen-phen\/src\/vendor\/saccharin\/src\/run.php"],"function":"require"}]}
FAIL: The HTTP response code for this GET request [http://localhost/fen-phen/?_wrap_array=1&__c=/api/v1/robot] should have been 200, was instead: [404]
Response __error: Array
(
    [message] => Resource robot is not known
    [type] => invalid_request
    [validation] =>
)
-------------


Failure Summary:

[tests.fenphen.yaml#GET all robot]
The HTTP response code for this GET request
[http://localhost/fen-phen/?_wrap_array=1&__c=/api/v1/robot] should have been
200, was instead: [404]
	Response __error: Array
	(
	    [message] => Resource robot is not known
	    [type] => invalid_request
	    [validation] =>
	)



Error Summary:

[tests.fenphen.yaml#PATCH product]
The the function name ['uniXqid'] from the php eval property value: 'name:
php::uniXqid()' is unknown

Run time: 12 seconds
Memory Usage: 2,690,224 bytes
```

### -q

