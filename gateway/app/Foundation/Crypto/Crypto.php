<?php

namespace App\Foundation\Crypto;

/**
 * Class Crypto
 */
class Crypto
{
    /**
     * @var string $salt This variable holds the salt for use in password hashing.
     */
    protected string $salt;

    /**
     * @var string $hash_algorithm This variable stores the hash algorithm used for password hashing.
     */
    protected string $hash_algorithm;

    /**
     * Constructor for initializing hash algorithm and salt.
     *
     * @param string|null $hash_algorithm The hash algorithm to use, defaults to value from environment variable HASH_ALGORITHM if not provided
     * @param string|null $salt The salt value to use, defaults to value from environment variable SHA_PHRASE if not provided
     */
    public function __construct(
        string $hash_algorithm = null,
        string $salt = null
    )
    {
        $this->hash_algorithm = $hash_algorithm ?? env('CALCULATION_HASH_ALGORITHM');
        $this->salt = $salt ?? env('CALCULATION_SHA_PHRASE');
    }

    /**
     *
     * @param string|null $hash_algorithm
     * @param string|null $salt
     * @return static
     */
    public static function build(
        string $hash_algorithm = null,
        string $salt = null
    ): Crypto
    {
        return new self($hash_algorithm, $salt);
    }

    /**
     * Set the salt for the application.
     *
     * @param string $salt The salt value to be set
     * @return $this The current object instance for method chaining
     */
    public function salt(
        string $salt
    ): static
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * Set the hash algorithm to be used for the application.
     *
     * @param string $hash_algorithm The hash algorithm to be set
     * @return $this The current object instance for method chaining
     */
    public function algorithm(
        string $hash_algorithm
    ): static
    {
        $this->hash_algorithm = $hash_algorithm;
        return $this;
    }

    /**
     * Check if the given value matches the hashed value using the specified hash algorithm.
     *
     * @param string $value The value to be checked
     * @param string|null $hashed_value The hashed value to be compared against
     * @return bool True if the value matches the hashed value, false otherwise
     */
    public function check(
        string $value,
        ?string $hashed_value
    ): bool
    {
        return $hashed_value && hash($this->hash_algorithm, "{$this->salt}_{$value}_{$this->salt}") === $hashed_value;
    }
}
