<?php

declare(strict_types=1);

namespace Y2KaoZ\PhpSession;

/** 
 * @api class SessionStorage
 * @template-implements \ArrayAccess<string,null|scalar|object|array<mixed>> 
 */
class SessionStorage implements \ArrayAccess
{
  public function __construct(
    private(set) string $namespace = ""
  ) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      if (!session_start()) {
        throw new \Exception("Unable to start php session.");
      }
      if (!isset($_SESSION[$this->namespace])) {
        $_SESSION[$this->namespace] = [];
      }
    }
  }

  /** @return array<string,null|scalar|object|array<mixed>> */
  public function getContents(): array
  {
    assert(isset($_SESSION[$this->namespace]) && is_array($_SESSION[$this->namespace]));
    /** @var array<string,null|scalar|object|array<mixed>> $namespaceContent */
    $namespaceContent = $_SESSION[$this->namespace];
    return $namespaceContent;
  }

  public function clear(): void
  {
    $_SESSION[$this->namespace] = [];
  }

  /** @return null|scalar|object|array<mixed> */
  public function __get(string $key): mixed
  {
    return $this->offsetGet($key);
  }

  /** @param null|scalar|object|array<mixed> $value*/
  public function __set(string $key, mixed $value): void
  {
    $this->offsetSet($key, $value);
  }

  public function __isset(string $key): bool
  {
    assert(isset($_SESSION[$this->namespace]) && is_array($_SESSION[$this->namespace]));
    return $this->offsetExists($key) && $_SESSION[$this->namespace][$key] !== null;
  }

  public function __unset(string $key): void
  {
    $this->offsetUnset($key);
  }

  #[\Override]
  public function offsetExists(mixed $key): bool
  {
    assert(isset($_SESSION[$this->namespace]) && is_array($_SESSION[$this->namespace]));
    return array_key_exists($key, $_SESSION[$this->namespace]);
  }

  #[\Override]
  public function offsetGet(mixed $key): mixed
  {
    assert(isset($_SESSION[$this->namespace]) && is_array($_SESSION[$this->namespace]));
    if (!$this->offsetExists($key)) {
      throw new \Exception("The variable '$key' is not in namespace: '{$this->namespace}'.");
    }
    $value = $_SESSION[$this->namespace][$key];
    assert(is_null($value) || is_scalar($value) || is_object($value) || is_array($value));
    return $value;
  }

  #[\Override]
  public function offsetSet(mixed $key, mixed $value): void
  {
    assert(isset($_SESSION[$this->namespace]) && is_array($_SESSION[$this->namespace]) && is_string($key));
    $_SESSION[$this->namespace][$key] = $value;
  }

  #[\Override]
  public function offsetUnset(mixed $key): void
  {
    assert(isset($_SESSION[$this->namespace]) && is_array($_SESSION[$this->namespace]));
    unset($_SESSION[$this->namespace][$key]);
  }
}
