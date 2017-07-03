# Request deserializer

Package for providing JSON schema validation and request deserialization for API's built on top of [Dingo API](https://github.com/dingo/api).


## Installation

Since this package is built on top [Dingo API](https://github.com/dingo/api) it has to be installed first, see its installation instructions for details.

Now you can run

```
composer require realshadow/request-deserializer
```

Add package provider to list of providers in `app.php` configuration file

```
\Realshadow\RequestDeserializer\Providers\LaravelServiceProvider::class
``` 

and add the request deserialization middleware to Kernel

```
Realshadow\RequestDeserializer\Http\Middleware\RequestDeserializationMiddleware
```

And for the last step publish configuration files with

```
php artisan vendor:publish --provider="Realshadow\RequestDeserializer\Providers\LaravelServiceProvider"
```

I should note that this package relies on [Purifier](https://github.com/mewebstudio/Purifier) package and if you are using it, you will have to update its configuration accordingly or use
the `--force` option for publishing.

## Usage

After you complete the installation steps you can add the `DeserializesRequests` trait to your controller. 
Now you can create a new schema and request object pair (see how it works section for more details). 


## How it works

This package is built on top of [Dingo API](https://github.com/dingo/api) and combines two 
great packages - [JMS serializer](https://github.com/schmittjoh/serializer) and 
[JSON schema validator](https://github.com/justinrainbow/json-schema) to create a powerful validation and deserialization 
layer for all incoming requests.

Note: I will assume you are familiar with at least one of the required packages.

### Now for the actual magic

Middleware will catch every request and looks if the called method in controller expects any request object in its arguments. If its found the
request will run trough 3 steps:

 - data will be sanitized (cleaned by [HTML Purifier](https://github.com/mewebstudio/Purifier) and all special characters will be converted via **htmlspecialchars** method
 - validated against provided JSON schema
 - validated data will be deserialized into a request object
 
Basic request object looks like this

```
class PaginatedRequest implements RequestInterface
{

    /**
     * @var int $page
     *
     * @JMS\Since("1.x")
     * @JMS\Type("integer")
     * @JMS\SerializedName("page")
     */
    private $page;

    // If the request should be validated against schema or not
    public static function shouldValidate()
    {
        return true;
    }

    // Request version - this allows to use the same request for multiple API versions
    public static function getVersionConstraint()
    {
        return '1.0';
    }

    // Most important method - returns path to JSON schema this request should validate against
    public function getSchema()
    {
        return resource_path('schemas/foo.json');
    }
    
    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }
    
}
```

Request object **must** implement `RequestInterface`. This interface contains three helper methods that will describe
the request for us (see phpdoc in code above) and list of properties and their respective *getter* methods.

When the validation of a request fails, it will throw **HTTP 422 Validation Exception** with list of violated constraints.

### GET requests

PHP will handle every parameter in query string as a *string*. In this case the validation process will use **type coercion**. Furthermore all passed data is deserialized
to the correct type defined in request object.

### Caveats

Since all properties are *always* present in request objects (or any object for that matter) it is impossible to distinguish between properties that should have *null values* and properties
that are not present in request body as they will always be null. You can get around it by using Laravels `Input` facade or any other viable method that works with the original request
directly.

## Configuration

Package contains two configuration files: 
 
 - modified configuration file for HTML purifier
 - package configuration file
 
### Package configuration

Package configuration is merged so if you want to extend it just copy the required options from package configuration. 

Cache directory for serializer can be configured in *.env* by setting *SERIALIZER_STORAGE_PATH* key.

## Helper command

Since creation of bigger request objects can become tedious this package includes a helper command that will generate a new request object from provided JSON schema. 
Usage is very simple

```
php artisan schema:convert {path to schema} {reqeust object}

// e.g.

php artisan schema:convert pagination.json PaginationRequest.php
```

JSON schema and created request are always relative to directories set in configuration including the namespace the request object will belong to.

## Examples

In the example folder you can find two directories with request objects and their respecitive schemas - a simple request and a more complicated request with nested schema. These should work out of the box so it should
be enough just to copy them to their respective folders (dont forget to add methods in routes!)

Furthermore you can [import a Postman collection](https://documenter.getpostman.com/view/273296/request-deserializer/6fVVjhS) with two example requests that will for with example schemas.
