<?php

namespace Laminas\Validator;

use Laminas\Session\Container as SessionContainer;
use Laminas\Stdlib\ArrayUtils;
use Traversable;

use function explode;
use function is_array;
use function is_string;
use function md5;
use function random_bytes;
use function sprintf;
use function str_replace;
use function strtolower;
use function strtr;

/**
 * @deprecated This validator will be removed in version 3.0 of this component. A replacement is available in
 *             version 2.21.0 of the laminas-session component: https://docs.laminas.dev/laminas-session/
 *
 * @final
 */
class Csrf extends AbstractValidator
{
    /**
     * Error codes
     *
     * @const string
     */
    public const NOT_SAME = 'notSame';

    /**
     * Error messages
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_SAME => 'The form submitted did not originate from the expected site',
    ];

    /**
     * Actual hash used.
     *
     * @var mixed
     */
    protected $hash;

    /**
     * Static cache of the session names to generated hashes
     *
     * @todo unused, left here to avoid BC breaks
     * @var array
     */
    protected static $hashCache;

    /**
     * Name of CSRF element (used to create non-colliding hashes)
     *
     * @var string
     */
    protected $name = 'csrf';

    /**
     * Salt for CSRF token
     *
     * @var string
     */
    protected $salt = 'salt';

    /** @var SessionContainer */
    protected $session;

    /**
     * TTL for CSRF token
     *
     * @var int|null
     */
    protected $timeout = 300;

    /**
     * Constructor
     *
     * @param  array|Traversable $options
     */
    public function __construct($options = [])
    {
        parent::__construct($options);

        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (! is_array($options)) {
            $options = (array) $options;
        }

        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'name':
                    $this->setName($value);
                    break;
                case 'salt':
                    $this->setSalt($value);
                    break;
                case 'session':
                    $this->setSession($value);
                    break;
                case 'timeout':
                    $this->setTimeout($value);
                    break;
                default:
                    // ignore unknown options
                    break;
            }
        }
    }

    /**
     * Does the provided token match the one generated?
     *
     * @param mixed $value
     * @param mixed $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        if (! is_string($value)) {
            return false;
        }

        $this->setValue($value);

        $tokenId = $this->getTokenIdFromHash($value);
        $hash    = $this->getValidationToken($tokenId);

        $tokenFromValue = $this->getTokenFromHash($value);
        $tokenFromHash  = $this->getTokenFromHash($hash);

        if ($tokenFromValue === null || $tokenFromHash === null || ($tokenFromValue !== $tokenFromHash)) {
            $this->error(self::NOT_SAME);
            return false;
        }

        return true;
    }

    /**
     * Set CSRF name
     *
     * @param  string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * Get CSRF name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set session container
     *
     * @return $this
     */
    public function setSession(SessionContainer $session)
    {
        $this->session = $session;
        if ($this->hash) {
            $this->initCsrfToken();
        }
        return $this;
    }

    /**
     * Get session container
     *
     * Instantiate session container if none currently exists
     *
     * @return SessionContainer
     */
    public function getSession()
    {
        if (null === $this->session) {
            // Using fully qualified name, to ensure polyfill class alias is used
            $this->session = new SessionContainer($this->getSessionName());
        }
        return $this->session;
    }

    /**
     * Salt for CSRF token
     *
     * @param  string $salt
     * @return $this
     */
    public function setSalt($salt)
    {
        $this->salt = (string) $salt;
        return $this;
    }

    /**
     * Retrieve salt for CSRF token
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Retrieve CSRF token
     *
     * If no CSRF token currently exists, or should be regenerated,
     * generates one.
     *
     * @param  bool $regenerate    default false
     * @return string
     */
    public function getHash($regenerate = false)
    {
        if ((null === $this->hash) || $regenerate) {
            $this->generateHash();
        }
        return $this->hash;
    }

    /**
     * Get session namespace for CSRF token
     *
     * Generates a session namespace based on salt, element name, and class.
     *
     * @return string
     */
    public function getSessionName()
    {
        return str_replace('\\', '_', self::class) . '_'
            . $this->getSalt() . '_'
            . strtr($this->getName(), ['[' => '_', ']' => '']);
    }

    /**
     * Set timeout for CSRF session token
     *
     * @param  int|null $ttl
     * @return $this
     */
    public function setTimeout($ttl)
    {
        $this->timeout = $ttl !== null ? (int) $ttl : null;
        return $this;
    }

    /**
     * Get CSRF session token timeout
     *
     * @return int|null
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Initialize CSRF token in session
     *
     * @return void
     */
    protected function initCsrfToken()
    {
        $session = $this->getSession();
        $timeout = $this->getTimeout();
        if (null !== $timeout) {
            $session->setExpirationSeconds($timeout);
        }

        $hash    = $this->getHash();
        $token   = $this->getTokenFromHash($hash);
        $tokenId = $this->getTokenIdFromHash($hash);

        if (! $session->tokenList) {
            $session->tokenList = [];
        }
        $session->tokenList[$tokenId] = $token;
        $session->hash                = $hash; // @todo remove this, left for BC
    }

    /**
     * Generate CSRF token
     *
     * Generates CSRF token and stores both in {@link $hash} and element
     * value.
     *
     * @return void
     */
    protected function generateHash()
    {
        $token = md5($this->getSalt() . random_bytes(32) . $this->getName());

        $this->hash = $this->formatHash($token, $this->generateTokenId());

        $this->setValue($this->hash);
        $this->initCsrfToken();
    }

    /**
     * @return string
     */
    protected function generateTokenId()
    {
        return md5(random_bytes(32));
    }

    /**
     * Get validation token
     *
     * Retrieve token from session, if it exists.
     *
     * @param string $tokenId
     * @return null|string
     */
    protected function getValidationToken($tokenId = null)
    {
        $session = $this->getSession();

        /**
         * if no tokenId is passed we revert to the old behaviour
         *
         * @todo remove, here for BC
         */
        if ($tokenId === null && isset($session->hash)) {
            return $session->hash;
        }

        if ($tokenId !== null && isset($session->tokenList[$tokenId])) {
            return $this->formatHash($session->tokenList[$tokenId], $tokenId);
        }

        return null;
    }

    /**
     * @return string
     */
    protected function formatHash(string $token, string $tokenId)
    {
        return sprintf('%s-%s', $token, $tokenId);
    }

    protected function getTokenFromHash(?string $hash): ?string
    {
        if (null === $hash) {
            return null;
        }

        $data = explode('-', $hash);
        return $data[0] ?: null;
    }

    protected function getTokenIdFromHash(string $hash): ?string
    {
        $data = explode('-', $hash);

        if (! isset($data[1])) {
            return null;
        }

        return $data[1];
    }
}
