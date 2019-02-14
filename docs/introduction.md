Introduction To Sly Template Engine
===============================


The sly template engine is a very powerful template engine for PHP, it is very fast both at compile and run time.

The template engine syntax by default is a bit similar to ASP.NET razor.

The sly component is independent from the Billi tech framework which means it can be used on any project.

## Installing on separate project

The recommended way to install sly is via Composer:

```
composer require "billitech/sly:~0.1"
```

## Basic Usage

```php
//place at the top of your script
use Billitech\Sly\Sly;
use Billitech\Sly\FileLoader;
$loader = new FileLoader(['/path/to/your/templates']);
$sly = new Sly($loader, '/path/to/store/compiled_files');

echo $sly->render('index.sly', ['title' => 'Sly Template Ingine']);
```

Then Create a file with the name **index.sly** in the path you specify when creating the FileLoader instance and put the following code in it :

```sly
@( title )
```

When creating the FileLoader instance you can specify a array of paths where your templates are.

## Disable caching

To disable caching do not specify compile path while creating sly instance, for example :

```php
$sly = new Sly($loader);
```

## Other configurations

```php
$sly->setCharset('utf8'); // Set the character encoding of your templates.
$sly->enableAutoEscape(); // Enable auto escape. 
$sly->disableAutoEscape(); // Disable auto escape.
$sly->enableStrictVariables(); // Enable strict variable.
$sly->disableStrictVariables(); // Disable strict variable.
```

Auto escape is on by default.

## Syntax

The sly template engine uses th @ symbol to indicate start of a sly statement. @( is used to start a print statement and ) is used to close a print statement i.e @( abs(-1) ). while @ is used to start a control statement and ) is used to close a control statement i.e @if( true ).

### Variable

Variable passes to the the template can be access within the the template by usin the name of the variable in an expression. for example :
php file
```php
$sly->render('users.sly', ['users' => ['foo', 'bar', 'barz'], 'title' => 'My Users']);
```
users.sly
```html
<html>
    <head>
        <title>@( title )</title>
    </head>
    <body>
        <ul class="users">
        @for( user in users )
            <li>>@( user )</li>
        @end
        </ul>
    </body>
</html>
```



### Sly Expression

The sly template engine accept most valid php experessions with some few additional expressions.



#### Printing expression

By default the sly template use @(  ) syntax to print an expression. for example @(1 + 1) will be printed as 2.
 everything within the curly bracket must be a valid php expression and by default the printed expression will be automatically escaped using php htmlspecialchars function if the expression returns a string or sting convertable value, or the value will be converted to JSON using php json_encode function.

Sly template unlike other pupuler template engines, u can use any php function within an expression
