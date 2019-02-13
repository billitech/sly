
Operators
=========

### Arithmetic operators

| Operator | Description                   | Example       | Result |
| -------- | ----------------------------- | ------------- | ------ |
| +        | For addition operation.       | `@( 2 + 1 )`  | 3      |
| _        | For subtraction operation.    | `@( 2 - 1 )`  | 1      |
| *        | For multiplication operation. | `@( 2 * 3 )`  | 6      |
| /        | For division operation.       | `@( 4 / 2 )`  | 2      |
| %        | For modulu operation.         | `@( 10 % 3 )` | 1      |
| **       | For power of, operation.      | `@( 2 ** 3 )` | 8      |

### Logical operators

| Operator | Usage             | Description                              | Example                           |
| -------- | ----------------- | ---------------------------------------- | --------------------------------- |
| \|\|     | `expr1 || expr2`  | Returns **true** if either `expr1` or `expr2` is **true**. | `if( true || true ) .... @endif`  |
| or       | `expr1 or expr2`  | Returns **true** if either `expr1` or `expr2`  is **true**. | `if( true or true ) .... @endif`  |
| &&       | `expr1 && expr2`  | Returns **true** if both `expr1` and expr2  are **true**. | `if( true && true ) .... @endif`  |
| and      | `expr1 and expr2` | Returns **true** if both `expr1` and `expr2`  are **true**. | `if( true and true ) .... @endif` |
| xor      | `expr1 xor expr2` | Returns **true** if either `expr1` or `expr2`  is **true**, but not both. | `if( true xor true ) .... @endif` |
| !        | `! expr`          | Returns **true** if the `expr`  is not **true**. | `if( ! false ) .... @endif`       |

### Comparison operators

| Operator | Usage             | Description                              | Example                           |
| :------- | ----------------- | ---------------------------------------- | --------------------------------- |
| <        | `expr1 < expr2`   | Returns **true** if `expr1` is less than `expr2`. | `@if( 19 < 20 )  ..... @endif`    |
| >        | `expr1 > expr2`   | Returns **true** if `expr1` is greater than `expr2`. | `@if( 20 > 19 )  ..... @endif`    |
| <=       | `expr1 <= expr2`  | Returns **true** if `expr1` is less than or equals `expr2`. | `@if( 20 <= 20 )  ..... @endif`   |
| >=       | `expr1 >= expr2`  | Returns **true** if `expr1` is greater than or equals `expr2`. | `@if( 20 >= 20 )  ..... @endif`   |
| ==       | `expr1 == expr2`  | Returns **true** if `expr1` equals `expr2`. | `@if( 20 == 20 )  ..... @endif`   |
| ===      | `expr1 === expr2` | Returns **true** if `expr1` is identical to `expr2`. | `@if( 20 === 20 )  ..... @endif`  |
| !=       | `expr1 != expr2`  | Returns **true** if `expr1` dose not equals `expr2`. | `@if( 20 != 19 )  ..... @endif`   |
| !==      | `expr1 != expr2`  | Returns **true** if `expr1` is not identical to `expr2`. | `@if( 20 !== '20' ) ..... @endif` |
| <>       | `expr1 <> expr2`  | Returns **true** if `expr1` dose not equals `expr2`. | `@if( 20 != 19 )  ..... @endif`   |

### Assignment operators

| Operator | Usage         | Description                              | Example         |
| -------- | ------------- | ---------------------------------------- | --------------- |
| =        | `var = expr`  | For standard assignment. Assigns value of `b` to `a`. | `@do( a = b )`  |
| +=       | `var += expr` | For addition assignment. Assigns value of `a` plus value of `b` to `a`. | `@do( a += b )` |
| -=       | `var -= expr` | For the subtraction  assignment. Assigns value of `a` minus value of `b` to `a`. | `@do( a -= b )` |
| *=       | `var* = expr` | For multiplication assignment. Assigns value of `a` times value of `b` to `a`. | `@do( a *= b )` |
| /=       | `var /= expr` | For division assignment. Assigns value of `a` divide by value of `b` to `a`. | `@do( a /= b )` |
| .=       | `var .= expr` | For concatenation assignment. Assigns value of `a` concatenate  with the value of `b` to `a`. | `@do( a .= b )` |

### Incrementing/Decrementing operators

| Operator | Usage   | Description                              | Example             |
| -------- | ------- | ---------------------------------------- | ------------------- |
| ++       | `++var` | For post-increment( use the variable and increment it ) and pre-increment( increment the variable and use it ) variable. | `@( ++var, var++ )` |
| --       | `--var` | For post-decrement( use the variable and decrement it ) and pre-decrement( decrement the variable and use it ) variable. | `@( --var, var--)`  |

### String operator

| Operator | Description              | Example              | Result   |
| -------- | ------------------------ | -------------------- | -------- |
| ~        | For string concatenation | @( 'My ' ~ 'users' ) | My users |

### Ternary operator

| Usage                   | Description                              | Example                             |
| ----------------------- | ---------------------------------------- | ----------------------------------- |
| `expr1 ? expr2 : expr3` | Returns `expr2` if` expr1`  is **true** otherwise returns `expr3`. | `@( var is set ? var : 'default' )` |
| `expr1 ?: expr2`        | Returns `expr1` if `expr1`  is **true** otherwise returns `expr2`. | `@( var ?: 'default' )`             |

### Other operators

| Operator | Description                              | Example                                  |
| -------- | ---------------------------------------- | ---------------------------------------- |
| is       | For performing test operation.           | `@if( var is set ) ..... @endif`         |
| is not   | For performing negative test operation.  | `@if( var is not set ) ..... @endif`     |
| in       | For performing containment test operation. | `@if( 'ab' in 'abcd' ) ..... @endif`     |
| not in   | For performing negative containment test operation. | `@if( 'ad' not in 'abcd' ) ..... @endif` |
| \|       | For applying filter to an expr value     | @( var\|title )                          |

### Test operator

The test operator(`is`)  is used to test an expression expression against certain condition. test can be written by writing `is` after an expression followed by the name of the test. Test by default is any php function, or any function defined to the sly instance. for example :

```
@if( var is set )
	@( var )
@end
```

### Containment operator

The containment operator(`in`) is used to test if the left expression value is present in the right expression value. the wright expression value can be of type array. for example :

```
@if( 'cd' in 'abcde' and 'c' in ['a', 'b', 'c', 'd', 'e'] )
	...
@end
```