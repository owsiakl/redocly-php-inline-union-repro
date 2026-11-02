# PHP client passes an array to a union-typed property

`Pet.owner` is an inline `oneOf` of two object schemas, with no discriminator:

```yaml
Pet:
  type: object
  required: [name]
  properties:
    name:
      type: string
    owner:
      type: object
      oneOf:
        - $ref: '#/components/schemas/PersonOwner'   # { kind: person, name }
        - $ref: '#/components/schemas/ShelterOwner'  # { kind: shelter, shelterId }
```

`generate-client` prints no warning. The generated `Pet` types `$owner` as the
union of the two classes, but `fromArray()` passes the decoded array on
unchanged:

```php
final class Pet
{
    public function __construct(
        public string $name,
        public PersonOwner|ShelterOwner|null $owner = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            owner: $data['owner'] ?? null,
        );
    }
```

So any response with `owner` set throws:

```
TypeError: InlineUnionRepro\Pet::__construct(): Argument #2 ($owner) must be of type InlineUnionRepro\PersonOwner|InlineUnionRepro\ShelterOwner|null, array given
```

## Reproduce

Requires Node.js and PHP 8.1 or later.

```sh
./run.sh            # latest @redocly/cli
./run.sh 2.66.1     # a specific version
```

The script generates a client from `openapi.yaml` and starts a local PHP
server that echoes the request body back. It then calls `postPet()` once
without an owner and once with one:

```
--- Pet::$owner type ---
14:        public PersonOwner|ShelterOwner|null $owner = null,

--- Pet::fromArray() owner argument ---
22:            owner: $data['owner'] ?? null,

--- Calling postPet() ---
without owner: OK, got InlineUnionRepro\Pet
with owner: TypeError: InlineUnionRepro\Pet::__construct(): Argument #2 ($owner) must be of type InlineUnionRepro\PersonOwner|InlineUnionRepro\ShelterOwner|null, array given, called in .../out/client.php on line 20
```

Confirmed with `@redocly/cli` 2.66.1 and 2.71.0 on PHP 8.5.10.

| File | Purpose |
| --- | --- |
| `openapi.yaml`, `redocly.yaml` | Minimal API and generator config |
| `server.php` | Local server that echoes the request body with status 201 |
| `request.php` | Calls `postPet()` with a pet without an owner and one with an owner |
