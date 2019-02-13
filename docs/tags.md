
Tags
====

## If

The if tag is used to perform conditional execution of code fragments.

### Usage

```
if( expr )
 statements
endif
```

The above will execute every statements within the `if` and `endif` tag if the `expr` within the `if` tag parenthesis evaluates to **true**.

> **Note**: `if` statements can be nested infinitely within other `if` statements, which provides you with complete  flexibility for conditional execution of the various parts of your  program. 

### Example

```
@if( a > b )
	a is bigger than b
@endif
```

## Else

`else` extends an `if` statement to execute an alternative statement  in case the expression in the `if` statement  evaluates to **false**.

### Usage

```
if( expr )
	true statements
else
	false statements
endif
```

The above will execute every statements within the `if` and `else` tag if the `expr` within the `if` tag parenthesis evaluates to **true** otherwise will execute every statements within the `else` and `endif` tag.

### Example

```
@if( a > b )
	a is bigger than b
@else
	a is smaller than b
@endif
```

## Elseif

`elseif` extends an `if` statement to execute an alternative statement  in case the expression in the `if` statement  evaluates to **false**. However, unlike `else` tag, it will execute that alternative expression only if the `elseif` conditional  expression evaluates to **true**

### Usage

```
if( expr )
	true statements
elseif( expr )
	alternative true statements
else
	false statements
endif
```

The above will execute every statements within the `if` and `else` tag if the `expr` within the `if` tag parenthesis evaluates to **true** otherwise will execute every statements within the `elseif`  and `else` tag if the expression within the `elseif` tag parenthesis evaluates to true otherwise will execute every statements within the `else` and `endif` tag.

> **Note**: There may be several `elseif` tag within the same `if`  statement.  The first `elseif` expression (if any) that evaluates to **true** would be executed.
>
> The `elseif` statement is only executed if the  preceding `if` expression and any preceding `elseif` expressions evaluated to **false**, and the current `elseif` expression evaluated to **true**. 

### Example

```
@if( a > b )
	a is bigger than b
@elseif( a > c )
	a is bigger than c but smaller than b
@else
	a is smaller than b and c
@end
```

## While

The while tag is used to executes nested statement(s) repeatedly, as long  as the `while` expression evaluates to **true**.  The value of the expression is checked each time at the beginning of the loop, so even if this value changes during the execution of the nested statement(s), execution will not stop until the end of the iteration (each time  the statements in the loop is run,  is one iteration). If the `while` expression evaluates to **false** from the very beginning, the nested statement(s) won't even be run once. 

### Usage

```
while( expr )
	statements
endwhile
```

The above will continuously executes the statements within the `while` and `endwhile` tag as long as the while expression valuates to true.

### Example

```
@while( i <= 10 )
	i is equal to @( i++ )
@endwhile
```

## For

The for tag in sly template engine has to functions which are, to iterate over arrays and to perform a conditional loop statement. 

### Usage 1

```
for( value_var in array_expr )
	statements
endfor
```

The above loop over the array given by `array_expr` and executes the statements within `for` and `endfor` tags . On each iteration, the value of the current element is assigned to `value_var` and the internal array pointer is advanced by one.

```
for( key_var, value_var in array_expr )
	statements
endfor
```

The above loop over the array given by `array_expr` and executes the statements within `for` and `endfor` tags. On each iteration, the value and key of the current element is assigned to `key_var` and `value_var` respectively  , and the internal array pointer is advanced by one.

### Usage 2

```
for( expr1; expr2; expr3 )
	statements
endfor
```

The above perform a conditional loop. `expr1` is evaluated once unconditionally at the beginning of the loop. In the beginning of each iteration, `expr2` is evaluated.  If it evaluates to **true**, the loop continues and the nested  statements within `for` and `endfor` tags are executed.  If it evaluates to **false**, the execution of the loop ends. At the end of each iteration, `expr3` is  evaluated. 

> **Note**: Each of the expressions can be empty and`expr1` and `expr2` can contain multiple expressions separated by comma.

### Examples

```
<ul class="users">
@for( user in users )
	<li>@( user )</li>
@end
</ul>
```

```
<ul class="users">
@for( username, userInfor in users )
	<li>@( username ) - @( userInfo.age )</li>
@end
</ul>
```

```
<ul class="users">
@for( i = 1; i < count(users); i++)
	<li>@( users[i] )</li>
@end
</ul>
```

```
<ul class="users">
@for( i = 1, totalUsers = count(users); i < totalUsers; i++)
	<li>@( users[i] )</li>
@end
</ul>
```

```
<ul class="users">
@for( i = 1, totalUsers = count(users); i < totalUsers; )
	<li>@( users[i++] )</li>
@end
</ul>
```

## Break

The break tag performs same function as its php counterpart. The break tag ends execution of the current `for`,   `while` or `switch` structure.  `break` accepts an optional numeric argument which tells it how many nested enclosing structures are to be broken out of. The default value is *1*, only  the immediate enclosing structure is broken out of.

> the break tag is compile exactly to its php counterparts.

### Examples

```
@for( user in users )
	@if( user == "foo" )
		@break
	@endif
@endfor
```

```
@for( i = 1; ; i++ )
	@switch( i )
		@case( 1 )
			i is equal to 1
		@break @* Exit only the switch.
		@case( 5 )
			i is equal to 5
		@break( 2 ) @* Exit both switch and while.
	@endswitch
@endfor
```

## Continue

The continue tag performs same function as its php counterpart. The continue tag is used to skip the current loop iteration statements execution of the current `for`,   `while` or `switch` structure.  `continue` also accepts an optional numeric argument which tells it how many nested enclosing structures are to be skipped out of. The default value is *1*, only  the immediate enclosing structure is skipped out of.

> the continue tag is compiled exactly to its php counterparts.

### Examples

```
@for( user in users )
	@if( user == "foo" )
		@continue
	@endif
	some other statements
@endfor
```

```
@for( i = 1; ; i++ )
	@while( i < 10 )
		@if( i == 5 )
			@continue( 2 )
		@endif
	@endwhile
@endfor
```

## Include

 The `include` tag as it name suggest is used to include the evaluated content of another template into the current template.

> If the file name is not absolute, the file will be include from the templates path set to the sly instance.
>
> If the file is not found an `\SF\Sly\Exceptions\FileNotFound` exception will be thrown.
>
> By default the included template will have access to the current template data. This means all the variables in the current template and the current scope will be available to the included template.

### Usage

```
include( template_file_expr )
```

The above will include the evaluated content of the evaluated value of `template_file_expr` into the current template.

```
include( template_file_expr with data_array_expr )
```

The above will include the template with the evaluated array value of `data_array_expr` merge with the current template data passed to the included template.

```
include( template_file_expr with data_array_expr only )
```

The above will include the template with only data of `data_array_expr` passed to the included template without the current template data.

```
include( template_file_expr only )
```

The above will include the template without any data passed to the included template.

### Examples

```
@include( 'header.sly' )
```

```
@include( 'header.sly', with ['title' => 'My Site'] )
```

```
@include( 'header.sly', with ['title' => 'My Site'] only )
```

```
@include( 'header.sly', only )
```

## extends

The `extends` tag is used for template inheritance. It extends a given template with the current template.

> The extends tag must on the first line and the first declaration of the template, else an `\SF\Sly\Exceptions\SintaxError` exception will be thrown.
>
> You can only declare one `extends` tag per template, else an `\SF\Sly\Exceptions\SintaxError` exception will be thrown.
>
> If the file name is not absolute, the file will be include from the templates path set to the sly instance.
>
> If the file is not found an `\SF\Sly\Exceptions\FileNotFound` exception will be thrown.
>
> By default the extended template will have access to the current template data. This means all the variables in the current template will be available to the included template.

### Usage

```
extends( template_file_expr )
```

The above will extend the template of the evaluated value of `template_name_expr` with the current template. 

All the blocks in the extended template will be merge with the blocks in the current template.

### Example

```
@* base.sly template.
<!DOCTYPE html>
<html>
	<head>
	@block( head )
		<link rel="stylesheet" href="style.css" />
		<title>@block( title '' )</title>
	@endBlock
	</head>
	<body>
		<div class="content">
			@block( content '')
		</div>
		<div class="footer">
			@block( footer '&copy; Copyright 2017 Silver' )
		</div>
	</body>
</html>
```

```
@* child.sly template.
@extends( 'base.sly ')
@block( title 'Site Index')
@block( content )
	<h1>Index<h1>
	<dive class="body">
		Welcome to my website
	</div>
@endblock
```
The above example will result to :

```html
<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" href="style.css" />
		<title>Site Index</title>
	</head>
	<body>
		<div class="content">
			<h1>Index<h1>
			<dive class="body">
      			Welcome to my website
      		 </div>
		</div>
		<div class="footer">
			&copy; Copyright 2017 Silver
		</div>
	</body>
</html>
```

## Block

The `block` tag is used as placeholders and replacements for template inheritance.

> Block  names should start with alphabetical characters or  underscore(`_`) , And the rest characters should consist of only alphabetical, numeric, and underscore characters

### Usage

```
block( block_name )
	statements
endblock
```

The above will replace  or serve as placeholder for block with name `block_name`

If the block is for replacing all the evaluated contents of statements within `block` and `endblock` tag will be use for the replacements.

```
block( block_name block_value_expr )
```



### Examples

See the [extends](#extends) tag for example.

## Raw

The `raw` tag is used to print an expression value without escaping it.

> **Note:** the raw tag can accept multiple expressions divided by comma(`,`), The expressions values will be concatenated and print as a single value.

### Example

```
@raw( var )
@raw( var, var2 ?: "foo", var3 )
```

## E

the `e`  tag is used to escape and print an expression value irrespective of whether auto-escape is turned off.

> **Note:** the e tag can accept multiple expressions divided by comma(`,`), The expressions values will be concatenated and print as a single value.

### Example

```
@e( var )
@e( var, var2 ?: "foo", var3 )
```

## Autoescape

The `autoescape` tag is used to turn auto escape on or off for a group of statements.

### Usage

```
autoescape( bollean )
	statements
endautoescape
```

### Example

```
@autoescape( false )
	@( var )
	@if( var2 is set)
		@( var2 )
	@endif
@endautoescape
```

The above will not escape all the print statements within the `autoescape` and the `endautoescape` tags.

## Do

The `do` tag is used to execute expression(s) without printing the expression(s) values.

> **Note:** when using multiple expressions. Expressions should be separated using comma(`,`)

### Example

```
@do( var = "foo" )
@do( var .= "bar", var2++)
```

## Filter

The `filter` tag is used to apply filter(s) on a group of statements.

### Example

```
@filter( trim )
	@( "  foo" )
	@( bar )
@endfilter
@filter( raw|trim )
	@( "  foo" )
	@( bar )
@endfilter
```

## Spaceless

The `spaceless` tag is used to remove space within a group of html tags.

### Example

```
@spaceless
	@( "  foo" )
	@( bar )
@endspaceless
```

## Set

The set tag is use to set value to a variable