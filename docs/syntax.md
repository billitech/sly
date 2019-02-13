
Syntax
======

The sly template engine syntax by default is a bit similar to ASP.NET razor.  
The sly template engine uses the @ symbol to indicate start of a sly statement.  
`@(` is used to start a print statement and `)` to close the print statement, for example `@( abs(-1) )`.  
While `@` is used to start a control statement and `)` to close the control statement, for example `@if( true )`.

## Variable

Variable passed to template or set within the template can be access in the template by using the name of the variable in an expression, for example :
```php
    //php file
    $sly->render('users.sly', ['users' => ['foo', 'bar', 'baz'], 'title' => 'My Users']);
```
Then in your template file :
```html
<!DOCTYPE html>
<html>
    <head>
        <title>@( title )</title>
    </head>
    <body>
        <ul class="users">
        @for( user in users )
            <li>@( user )</li>
        @end
        </ul>
    </body>
</html>
```
Which will produce :
```html
<!DOCTYPE html>
<html>
    <head>
        <title>My Users</title>
    </head>
    <body>
        <ul class="users">
            <li>foo</li>
            <li>bar</li>
            <li>baz</li>
        </ul>
    </body>
</html>
```
**Note** : If the variable is not defined and `useStrictVariable` configuration option is set to true a `\SF\Sly\Exceptions\RuntimeError` will be thrown or null value will be return if `useStrictVariable` configuration option is set to false.

### Accessing Variable Attributes

Variable attributes (Object properties, Array items), can be access using `.` notation or using the subscript syntax `[``]` just like in php, for example :
```php
    //php file
    $sly->render('users.sly', ['users' => [['name' => 'foo', 'gender' => 'male'], ['name' => 'bar', 'gender' => 'male'], ['name' => 'baz', 'gender' => 'male']], 'title' => 'My Users']);
```
Then in your template file :
```html
<!DOCTYPE html>
<html>
    <head>
        <title>@( title )</title>
    </head>
    <body>
        <ul class="users">
        @for( user in users )
            <li>@( user.name ) - @( user['gender'] )</li>
        @end
        </ul>
    </body>
</html>
```
Which will produce :
```html
<!DOCTYPE html>
<html>
    <head>
        <title>My Users</title>
    </head>
    <body>
        <ul class="users">
            <li>foo - male</li>
            <li>bar - male</li>
            <li>baz - male</li>
        </ul>
    </body>
</html>
```
**Note** : If the attribute dose not exists an `\SF\Sly\Exceptions\RuntimeError` will be thrown.

### Accessing Object Variable method

Object variable method can be access using `.` notation and parenthesis after the method name and optionally with arguments separated with comma(`,`), for example :
```php
    //php file
    $sly->render('users.sly', ['users' => [['name' => 'foo', 'gender' => 'male'], ['name' => 'bar', 'gender' => 'male'], ['name' => 'baz', 'gender' => 'male']], 'title' => $titleObject]);
```
Then in your template file :
```html
<!DOCTYPE html>
<html>
    <head>
        <title>@( title.getTitle() )</title>
    </head>
    <body>
        <ul class="users">
        @for( user in users )
            <li>@( user.name ) - @( user['gender'] )</li>
        @end
        </ul>
    </body>
</html>
```
If the titleObject getTitle method returns **My Active Users**, Then the output will be :
```html
<!DOCTYPE html>
<html>
    <head>
        <title>My Active Users</title>
    </head>
    <body>
        <ul class="users">
            <li>foo - male</li>
            <li>bar - male</li>
            <li>baz - male</li>
        </ul>
    </body>
</html>
```
**Note** : If the method dose not exists and the object class dose not have `__call` method an `\SF\Sly\Exceptions\RuntimeError` will be thrown.

Below are more complex examples :

```
    @( foo.bar.baz )
    @( foo.['bar'].baz )
    @( foo[5].baz )
    @( foo.bar.baz[4] )
    @( foo[ bar.baz ] )
    @( foo[5] )
    @( foo.5 )
    @( foo.bar )
    @( foo['bar'] )
    @( foo["bar"] )
    @( foo[bar] )
    @( foo.bar.baz[ bar.getBaz("foo") ] )
    @( foo.bar(5).baz )
```
**Note** : `this` variable holds the current template object instance, for example `@( this->getTemplateName() )` will return the current template name.

## Defining Or Setting Variables

Variables can be defined or set using using assignment operators in an expression, for example :
```
    @do( foo = 'bar' )
    @( foo .= ' baz' )
    @do( bar = 1 )
    @( ++bar )
    @( bar++ )
    @do( bar -= --bar )
    @do( bar -= bar-- )
    @do( bar += 2 )
    @do( bar *= 2 )
    @do( bar += 2 )
    @do( bar /= 2 )
    @do( foo.['bar'].baz. = get_foo_bar_baz([foo, 'bar', baz]) )
```
Or you can use the set tag to set variable, for example :
```
    @set( foo = 'foo' )
    @set( foo = (foo .= ' bar') )
    @set( foo.bar = get_foo_bar([foo, 'bar']) )
```
**Note** the value cam be any valid expression
## Scalar values

### Strings

A string literal just like in php is everything between single quotes(`'`) or double quotes(`'`), for example :
```
    @( 'string' )
    @( func("string") )
```

To specify a literal for both single and double quote within string, just place a backslash (`\`) before the quote,
To specify a literal backslash, double it (`\\`), for example :
```
    @( 'foo\'s' ) and @( "foo\\" )
```
will return **foo's and foo\**

### Integers And floats

Integers and floating point numbers are specify by just writing the number. If the number as a dot, the number will be consider a float otherwise be consider an integer. for example :
```
    @( 123 ) @* will return 123
    @( 12.3 ) @* will return 12.3
    @( 12.3 + 123) ) @* will return 135.3
```

### Arrays

Arrays are defined with expression value within squared brackets separated by comma, for example :

```
    ['foo', bar, [baz.bazz, foo(), foos.foo()]]
```
**Note** the array values can be any valid expression, which means it can be an array can be nested.

### Associative Arrays Or Hash

Associative arrays are defined by peers of list of keys and values within squared brackets separated by comma, for example :

```
    ['foo' => bar, foo => [baz.bazz, foo(), foos.foo()], 'baz' => 'bar', 1 + 2 => 3, get_foo() => get_foo()]
```
**Note** the keys can be any valid expression that return string or integer value. while the values can be any valid expression, which means associative array values can be nested.

### Booleans

A boolean is a representation of true or false value which is defined using `true` constant for true and `false` constant for false. for example :
```
    @if( var !== true and var !== false )
        @( var ~ 'is not boolean' )
    @end
```

### NULL

The null value represents a variable with no value or an expression that returns an empty or void value, It can be defined using `null` constant. for example :
```
    @if( var === null or get_foo(null) == null )
        empty
    @end
```

## Filters

Filters are expression modifiers that can be applied to any valid expression. Filters can be applied by pacifying a pipe(`|`) symbol after an expression follow by the filter name. Filters can optionally receive arguments just like functions by pacifying arguments separated by comma(`,`) within a parentheses(`(` `)`). Filters by sly design is any php function or functions defined to the sly instance. for example :
```
    @( foo|title )
    @( 'foo'|title )
    @( ['foo', 'bar', baz,]|join(', ') )
    @( 'bar'|title|upper )
    @( get_title()|title )
    @( ('foo')|title )
```

## Functions Call

Functions are set of reusable code defined with name and acceptable arguments or parameters. Php Functions or functions defined to the sly instance can be called within the template by writing the name of the function followed by parentheses(`(` `)`) and optionally with arguments separated with comma(`,`) within the parentheses. for example :
```
    @( title('foo') )
    @( date() )
    @( join(['foo', 'bar', baz], ',') )
    @( title(get_title()) )
```

### Test

The test opeartor(`is`)  is used to test an expression expression against certain condition. test can be written by writing `is` after an expression followed by the name of the test. Test by default is any php function, or any function defined to the sly instance. for example :

```
@if( var is set )
	@( var )
@end
```

### Containment Test

Containment test is used to test if the left expression value is present in the right expression value. the wright expression value can be of type array. for example :

```
@if( 'cd' in 'abcde' and 'c' in ['a', 'b', 'c', 'd', 'e'] )
	...
@end
```
## Tags

Tags in sly template by default starts we with `@` followed by the tag name and optionally with parentheses(`(` `)`) for tags that requires arguments. Tag arguments are defined within the parentheses. for example :

```
	@if(true)
	@endif
```

See [List of built in tags](tags.md) 

### Control Structure

A control structure are dose tags that control the flow of the template.

Sly template engine supports conditional control structures (`if`, `elseif`, `else` and `switch`), loop control structures (`for` and `while`) and blocks control structures.

#### If Statements

The if statement is use to test if an expression meets certain conditions.

The if statement can be define using `if` `elseif` `else` `endif` `end` tags by the following way :

```
	@if( users|lenth > 1 )
		You have more than one users.
	@elseif( users|lenght == 1 )
		You have only one users.
	@else
		You have zero users
	@end
	
```

The above example first check if the users are more than one then print **You have more than one users** if the if expression is true, else check if the users is only one and  print **You have only one users** if  true or print **You have no users ** if the expression is false. 

#### Loops

```
	<ul class="users">
	@for( user in users )
		<li>@(user.name)</li>
	@end
	</ul>
```

The above example  will loop through the users array and print each user name.

Go to the [Tags](tags.md) page to learn more about all the available built-in tags.

## Comments

You can defined  a single line comments or multiple line comments in sly templates by using `@*-` ... `-*` for multiple line comments and `@*` to new line  for single line comments. for example :

```
	@*-
		Display users names.
	-*
	
	@* Loop through the users array and print each user name.
	@for( user in users )
		@( user.name )
	@end
```

## Operators