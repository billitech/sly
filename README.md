<h1>Introduction To Sly Template Engine</h1>


The sly template engine is a very powerful template engine for PHP, it is very fast both at compile and run time.

The template engine syntax by default is a bit similar to ASP.NET razor.

The sly component is independent from the Billi tech framework which means it can be used on any project.

<h1>Installing on separate project</h1>

The recommended way to install sly is via Composer:

<pre>composer require "billitech/sly:~0.1"</pre>

<h1>Basic Usage</h1>

<pre>
//place at the top of your script
use Billitech\Sly\Sly;
use Billitech\Sly\FileLoader;
$loader = new FileLoader(['/path/to/your/templates']);
$sly = new Sly($loader, '/path/to/store/compiled_files');

echo $sly->render('index.razor', ['title' => 'Sly Template Ingine']);
</pre>

Then Create a file with the name <b>index.razor</b> in the path you specify when creating the FileLoader instance and put the following code in it :

<pre>
@( title )
</pre>

When creating the FileLoader instance you can specify a array of paths where your templates are.

<h1>Disable caching</h1>

To disable caching do not specify compile path while creating sly instance, for example :

<pre>
$sly = new Sly($loader);
</pre>

<h1>Other configurations</h1> 

<pre>
$sly->setCharset('utf8'); // Set the character encoding of your templates.
$sly->enableAutoEscape(); // Enable auto escape. 
$sly->disableAutoEscape(); // Disable auto escape.
$sly->enableStrictVariables(); // Enable strict variable.
$sly->disableStrictVariables(); // Disable strict variable.
</pre>

Auto escape is on by default.

<h1>Syntax</h1>

The sly template engine uses th @ symbol to indicate start of a sly statement. @( is used to start a print statement and ) is used to close a print statement i.e @( abs(-1) ). while @ is used to start a control statement and ) is used to close a control statement i.e @if( true ).

<h1>Variable</h1>

Variable passes to the the template can be access within the the template by usin the name of the variable in an expression. for example :
php file
<pre>
$sly->render('users.sly', ['users' => ['foo', 'bar', 'barz'], 'title' => 'My Users']);
</pre>
users.sly
<pre>
<!DOCTYPE html>
<html>
    <head>
        <title>@( title )</title>
    </head>
    <body>
        <ul class="users">
        @for( user in users )
            <li>>@( user )</a></li>
        @end
        </ul>
    </body>
</html>
</pre>

<h1>Sly Expression<h1>

The sly template engine accept most valid php experessions with some few additional expressions.



<h1>Printing expression</h1>

By default the sly template use @(  ) syntax to print an expression. for example @(1 + 1) will be printed as 2.
 everything within the curly bracket must be a valid php expression and by default the printed expression will be automatically escaped using php htmlspecialchars function if the expression returns a string or sting convertable value, or the value will be converted to JSON using php json_encode function.

Sly template unlike other pupuler template engines, u can use any php function within an expression
