<?php

declare(strict_types=1);

namespace Y2KaoZ\PhpSession;

/** 
 * @api class EphemeralStorage
 */
class EphemeralStorage extends SessionStorage
{
  /** @var array<array-key,mixed> $ephemeralValue */
  private array $ephemeralValue;
  public function __construct(null|string $namespace = null)
  {
    parent::__construct($namespace ?? self::class);
    $this->ephemeralValue = parent::getContents();
    parent::clear();
  }

  /** @return array<array-key,mixed> */
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

  #[\Override]
  public function __get(string $key): mixed
  {
    return $this->offsetGet($key);
  }

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

  /** @param array-key $key */
  #[\Override]
  public function offsetExists(mixed $key): bool
  {
    return array_key_exists($key, $this->ephemeralValue);
  }

  /** @param array-key $key */
  #[\Override]
  public function offsetGet(mixed $key): mixed
  {
    if (!$this->offsetExists($key)) {
      throw new \Exception("The variable '$key' is not in namespace: '{$this->namespace}'.");
    }
    return $this->ephemeralValue[$key];
  }

  /** @param array-key $key */
  #[\Override]
  public function offsetSet(mixed $key, mixed $value): void
  {
    parent::offsetSet($key, $value);
    $this->ephemeralValue[$key] = $value;
  }

  /** @param array-key $key */
  #[\Override]
  public function offsetUnset(mixed $key): void
  {
    parent::offsetUnset($key);
    unset($this->ephemeralValue[$key]);
  }

  /** @param null|int|string|non-empty-list<int|string> $keys */
  public function refresh(null|int|string|array $keys = null): void
  {
    if ($keys === null) {
      /** @var mixed $value */
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
        /** @var mixed $value */
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
