# Machina
===============

## REST service black-box testing framework


## Verbosity

### default (no v param)
......F..
Total Tests Run: 9
Tests Pass: 8
Tests Fail: 1
Run time: 13 seconds
Memory Usage: 2,273,096 bytes

### -v

```
Registering service: 'fenphen' (file: /Users/sam/Projects/deus-ex-machina/machine/conf/tests.fenphen.yaml)
Running tests for: 'fenphen'
Found 9 tests
Running GET Test: 'GET all product' (just ensure the get all URL, '/product' returns 200)
Running GET Test: 'GET product' (Create a product(POST) then assert you can GET it)
Running POST Test: 'POST product' (Test the successful POST of a product)
Running DELETE Test: 'DELETE product' (Test the successful DELETE of a product)
Running PATCH Test: 'PATCH product' (Test the successful PATCH of a product)
Running PUT Test: 'PUT product' (Test the successful PUT of a product)
Running GET Test: 'GET all tag' (just ensure the get all URL, '/tag' returns 200)
Running GET Test: 'GET tag' (Create a tag(POST) then assert you can GET it)
Running GET Test: 'GET all robot' (just ensure the get all URL, '/robot' returns 200)
  The HTTP response code for this GET request [http://localhost/fen-phen/?_wrap_array=1&__c=/api/v1/robot] should have been 200, was instead: [404]
  Response:
      [__error] => Array
      (
          [message] => Resource 'robot' is not known
          [type] => invalid_request
          [validation] =>
      )

Total Tests Run: 9
Tests Pass: 8
Tests Fail: 1
Run time: 13 seconds
Memory Usage: 2,273,096 bytes
```

### -vv

### -q
