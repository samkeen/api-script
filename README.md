## REST service blackbox testing framework

Enables you to write terse tests (BDD like) for REST based web services.

Currently hard coded to only work with JSON data transfer to and from the target Web services.

## Verbosity

### default (no v param)

```
Running tests for: 'fenphen'

Found 9 tests
....E...F
FAILURES (Tests: 9, Passes: 7, Fails: 1, Errors: 1)


Failure Detail:

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



Error Detail:

[tests.fenphen.yaml#PATCH product]
The the function name ['uniXqid'] from the php eval property value: 'name:
php::uniXqid()' is unknown

Run time: 9 seconds
Memory Usage: 2,864,104 bytes
```

### -v

```
Registering service: 'fenphen' (file: /Users/sam/Projects/deus-ex-machina/machine/conf/tests.fenphen.yaml)

Running tests for: 'fenphen'

Found 9 tests
Running GET Test: 'GET all product' (just ensure the get all URL, '/product' returns 200)
Passed
Running GET Test: 'GET a single product' (Create a product(POST) then assert you can GET it)
Passed
Running POST Test: 'POST product' (Test the successful POST of a product)
Passed
Running DELETE Test: 'DELETE product' (Test the successful DELETE of a product)
Passed
Running PATCH Test: 'PATCH product' (Test the successful PATCH of a product)
ERROR: The the function name ['uniXqid'] from the php eval property value: 'name: php::uniXqid()' is unknown
Running PUT Test: 'PUT product' (Test the successful PUT of a product)
Passed
Running GET Test: 'GET all tag' (just ensure the get all URL, '/tag' returns 200)
Passed
Running GET Test: 'GET tag' (Create a tag(POST) then assert you can GET it)
Passed
Running GET Test: 'GET all robot' (just ensure the get all URL, '/robot' returns 200)
FAIL: The HTTP response code for this GET request [http://localhost/fen-phen/?_wrap_array=1&__c=/api/v1/robot] should have been 200, was instead: [404]
Response __error: Array
(
    [message] => Resource robot is not known
    [type] => invalid_request
    [validation] =>
)

FAILURES (Tests: 9, Passes: 7, Fails: 1, Errors: 1)


Failure Detail:

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



Error Detail:

[tests.fenphen.yaml#PATCH product]
The the function name ['uniXqid'] from the php eval property value: 'name:
php::uniXqid()' is unknown

Run time: 8 seconds
Memory Usage: 2,858,344 bytes
```

### -vv

```
   lots of output
```

### -q