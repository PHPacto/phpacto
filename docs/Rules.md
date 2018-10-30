Mastering with PHPacto contract Rules
=====================================

There are many rules you can use in your contracts to make asserts.
Any time you edit a rule make sure the sample is matching its rule

and
---
Asserts that a value is matching both children rules `greater` and `lower`
```yaml
'@rule': and
rules:
  - '@rule': greater
    value: 5
  - '@rule': lower
    value: 7
sample: 6
```

boolean
---
Asserts that a value is a boolean type `True` or `False`
```yaml
'@rule': equals
sample: true
```

contains
--------
Asserts that at least one of the items of an array type value is matching the child rule
```yaml
'@rule': contains
rule:
  '@rule': equals
  sample: 5
sample:
  - 4
  - 5
  - 6
```

count
-----
Asserts that a child rule is matching the array length.
Allowed child rule types: `equals` `greater` `greaterEqual` `lower` `lowerEqual`
```yaml
'@rule': count
rule:
  '@rule': equals
  sample: 3
sample:
  - Item 1
  - Item 2
  - Item 3
```

datetime
Asserts that a string can be parsed as `date`, `time` or `datetime` with given format.
Can see valid format values [here](http://php.net/manual/en/function.date.php#refsect1-function.date-parameters) and some examples [here](https://stackoverflow.com/questions/10569053/convert-datetime-to-string-php#answer-39356556)
--------
```yaml
'@rule': datetime
format: Y-m-d
sample: '2018-10-30'
```

each
----
Asserts that each array item is matching againts the child rule or childer rules
```yaml
'@rule': each
rules:
  '@rule': equals
  sample: 3
sample:
  - 3
  - 3
  - 3
  - 3
```
```yaml
'@rule': each
rules:
  property_a:
    '@rule': stringEquals
    sample: a
  property_b:
    '@rule': stringEquals
    sample: b
sample:
  - property_a: a
    property_b: b
  - property_a: a
    property_b: b
```
    
equals
------
Asserts that a value is equal to a given value
```yaml
'@rule': equals
sample: 5
```

exists
------
Asserts that an object has a given property
```yaml
object:
  property:
    '@rule': exists
    sample: 'An optional sample value' # sample is optional here 
```

greaterEqual
------------
Asserts that a value is greater than or equal to a given value
```yaml
'@rule': greaterEqual
sample: 5
```

greater
-------
Asserts that a value is greater than a given value
```yaml
'@rule': greater
sample: 5
```

ifNotNull
---------
Will match child rules only if the value is set and different than `null`
```yaml
'@rule': ifNotNull
rule:
  '@rule': equals
  sample: 3
sample: 3
```

integer
-------
Asserts that a value is integer type *(numbers with decimals will not been accepted)*
```yaml
'@rule': number
sample: 5
```

lowerEqual
----------
Asserts that a value is lower than or equal to a given value
```yaml
'@rule': lowerEqual
sample: 5
```

lower
-----
Asserts that a value is lower than a given value
```yaml
'@rule': lower
sample: 5
```

number
------
Asserts that a value is number *(it does not distinguish between integers and decimals)*, can also be a string number
```yaml
'@rule': number
sample: 5
```

or
--
Asserts that a value is matching al least one of the children rules `greater` and `lower`
```yaml
'@rule': and
rules:
  - '@rule': greater
    value: 5
  - '@rule': lower
    value: 7
sample: 6
```

regex
-----
Asserts that a value is matching a given regular expression pattern
```yaml
'@rule': regex
pattern: ^(M|F)$
case_sensitive: false   # optional, default: True
multi_line: true        # optional, default: False
sample: M
```

string
------
Asserts that a value is string type, **any string** value will been accepted.
Ideal for user's input fields *like `description` or `title`*
```yaml
'@rule': string
sample: 'string value'
```

stringBegins
------------
Asserts that a string begins with given value
```yaml
'@rule': stringBegins
value: 'str'
sample: 'string value'
```

stringContains
--------------
Asserts that a string contains a given value
```yaml
'@rule': stringContains
value: 'ing'
sample: 'string value'
```

stringEnds
----------
Asserts that a string ends with given value
```yaml
'@rule': stringEnds
value: 'lue'
sample: 'string value'
```

stringEquals
------------
Asserts that a string equals to a given value
```yaml
'@rule': stringEquals
value: 'string value'
```

stringLength
------------
Asserts that a string length matches given child rule.
Allowed child rule types: `equals` `greater` `greaterEqual` `lower` `lowerEqual`
```yaml
'@rule': stringLength
length: 12
sample: 'string value'
```
```yaml
'@rule': stringLength
length: 12
sample: 'string value'
```

uuid
----
Asserts that a string is a UUID
```yaml
'@rule': uuid
sample: '00000000-0000-0000-0000-000000000000'
```

version
-------
