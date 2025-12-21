<?php

declare(strict_types=1);

namespace Y2KaoZ\PhpSession;

/** 
 * @api class EphemeralStorage
 */
class EphemeralStorage extends SessionStorage
{
  /** @var array<string,null|scalar|object|array<mixed>> $ephemeralValue */
  private array $ephemeralValue;
  public function __construct(null|string $namespace = null)
  {
    parent::__construct($namespace ?? self::class);
    $this->ephemeralValue = parent::getContents();
    parent::clear();
  }

  /** @return array<string,null|scalar|object|array<mixed>> */
  #[\Override]
  public function getContents(): array
  {
    return $this->ephemeralValue;
  }

  #[\Override]
  public function clear(): void
  {
    parent::clear();
    $this->ephemeralValue = [];
  }

  /** @return null|scalar|object|array<mixed> */
  #[\Override]
  public function __get(string $key): mixed
  {
    return $this->offsetGet($key);
  }

  /** @param null|scalar|object|array<mixed> $value*/
  #[\Override]
  public function __set(string $key, mixed $value): void
  {
    $this->offsetSet($key, $value);
  }

  #[\Override]
  public function __isset(string $key): bool
  {
    return $this->offsetExists($key) && $this->ephemeralValue[$key] !== null;
  }

  #[\Override]
  public function __unset(string $key): void
  {
    $this->offsetUnset($key);
  }

  #[\Override]
  public function offsetExists(mixed $key): bool
  {
    return array_key_exists($key, $this->ephemeralValue);
  }

  #[\Override]
  public function offsetGet(mixed $key): mixed
  {
    if (!$this->offsetExists($key)) {
      throw new \Exception("The variable '$key' is not in namespace: '{$this->namespace}'.");
    }
    return $this->ephemeralValue[$key];
  }

  #[\Override]
  public function offsetSet(mixed $key, mixed $value): void
  {
    if ($key !== null) {
      parent::offsetSet($key, $value);
      $this->ephemeralValue[$key] = $value;
    }
  }

  #[\Override]
  public function offsetUnset(mixed $key): void
  {
    parent::offsetUnset($key);
    unset($this->ephemeralValue[$key]);
  }

  /** @param null|string|non-empty-list<string> $keys */
  public function refresh(null|string|array $keys = null): void
  {
    if ($keys === null) {
      foreach ($this->ephemeralValue as $key => $value) {
        parent::offsetSet($key, $value);
      }
    } elseif (is_array($keys)) {
      $old = parent::getContents();
      try {
        foreach ($keys as $key) {
          parent::offsetSet($key, $this->offsetGet($key));
        }
      } catch (\Throwable $t) {
        parent::clear();
        foreach ($old as $key => $value) {
          parent::offsetSet($key, $value);
        }
        throw $t;
      }
    } else {
      parent::offsetSet($keys, $this->offsetGet($keys));
    }
  }
}
