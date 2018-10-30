Mastering with PHPacto contract Rules
=========

- and
  ===
    ```yaml
    '@rule': and
    rules:
      - '@rule': greaterEqual
        sample: 5
      - '@rule': lowerEqual
        sample: 7
    sample: 6
    ```

- contains
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

- count
  -----
  Asserts array length
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

- datetime
    ```yaml
    '@rule': datetime
    format: Y-m-d
    sample: 2018-10-30
    ```

- each
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
    
- equals
- exists
- greaterEqual
- greater
- ifNotNull
- integer
- lowerEqual
- lower
- number
- or
- regex
- string
- stringBegins
- stringContains
- stringEnds
- stringEquals
- stringLength
- uuid
- version