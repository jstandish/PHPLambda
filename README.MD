# Description
Running PHP inside Lambda layers using [stackery php-lambda-layer](https://github.com/stackery/php-lambda-layer)

# Files
|file|description|
|-|-|
|src/index.php| Main file handler|
|src/json-response.php| Sets JSON header and encodes response data object|
|src/php.ini|Includes json.so to encode json response |
|template.yaml|SAM CloudFormation template which includes PHP Lambda Layer|

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
      AutoPublishAlias: live
      DeploymentPreference:
        Type: Linear10PercentEvery10Minutes
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

# Deplying
Run the following commands:

```sh
$ sam package \
    --template-file template.yaml \
    --output-template-file serverless-output.yaml \
    --s3-bucket <your SAM deployment bucket created above>

# Deploying directly using SAM. Recommendation is to use a CI/CD like AWS CodePipeline
$ sam deploy \
    --template-file serverless-output.yaml \
    --stack-name my-first-serverless-php-service \
    --capabilities CAPABILITY_IAM
```

## Packaging using AWS CodeBuild

Use the following in your `buildspec.yaml`
```yaml
version: 0.2

phases:
  install:
    runtime-versions:
       python: 3.7
    commands:
      - pip install cfn-lint
  build:
    commands:
      - cfn-lint -t template.yaml
      - aws cloudformation package --template-file template.yaml --s3-bucket ${PACKAGE_BUCKET} --output-template-file packaged-template.yaml
artifacts:
  files:
    - packaged-template.yaml
```

### Deploying using CodeDeploy
Copy and modify the `template.yaml` from https://github.com/awslabs/aws-sam-codepipeline-cd/blob/master/sam/app/template.yaml


# Additional Resources
- [AWS Lambda Custom Runtime for PHP: A Practical Example](
https://aws.amazon.com/blogs/apn/aws-lambda-custom-runtime-for-php-a-practical-example/)
- [Using AWS Lambda Layers for PHP](https://codingwar.com/using-aws-lambda-layers-php)
- [Bref Lambda Layers](https://bref.sh/docs/environment/php.html)
- [Safe Lambda deployments
](https://github.com/awslabs/serverless-application-model/blob/master/docs/safe_lambda_deployments.rst)