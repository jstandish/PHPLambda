# Description
Running PHP inside Lambda layers using [stackery php-lambda-layer](https://github.com/stackery/php-lambda-layer)

# Files
|file|description|
|-|-|
|src/index.php| Main file handler|
|src/json-response.php| Sets JSON header and encodes response data object|
|src/php.ini|Includes json.so to encode json response |

# Included Extensions
The following extensions are built into the layer:

PHP 7.3 Layer:
```
bz2.so
calendar.so
ctype.so
curl.so
dom.so
exif.so
fileinfo.so
ftp.so
gettext.so
iconv.so
json.so
mbstring.so
mysqli.so
mysqlnd.so
pdo_mysql.so
pdo_pgsql.so
pdo.so
pdo_sqlite.so
pgsql.so
phar.so
posix.so
shmop.so
simplexml.so
sockets.so
sqlite3.so
sysvmsg.so
sysvsem.so
sysvshm.so
tokenizer.so
wddx.so
xmlreader.so
xml.so
xmlwriter.so
xsl.so
```

# Using AWS SAM (Serverless Application Model)
Create a basic SAM application:

```sh
$ mkdir app
$ cd app
```

Create a template.yaml file with the following SAM infrastructure:

```yaml
AWSTemplateFormatVersion: 2010-09-09
Description: My PHP Application
Transform: AWS::Serverless-2016-10-31
Resources:
  phpserver:
    Type: AWS::Serverless::Function
    Properties:
      FunctionName: !Sub ${AWS::StackName}-phpserver
      Description: PHP Webserver
      CodeUri: src
      Runtime: provided
      Handler: index.php
      MemorySize: 3008
      Timeout: 30
      Tracing: Active
      Layers:
        - !Sub arn:aws:lambda:${AWS::Region}:887080169480:layer:php73:3
      Events:
        api:
          Type: Api
          Properties:
            Path: /{proxy+}
            Method: ANY
```

Last create `index.php` in the src directory
```php
<?
include 'json-response.php';

// get the JSON request and serialize it
$data = json_decode(file_get_contents('php://input'), true);

// create response object
$response = [ 'context' => 'custom-runtime-example', 'age' => $data ];

// return response
send_json_response($response);
?>
```
