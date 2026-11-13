<?php

/**
 * Staggered dual-stack TCP connection establishment.
 *
 * DNS resolution uses the synchronous system resolver. Connection attempts are
 * interleaved by address family and raced, with one overall connection budget.
 * TLS and application authentication run only on the selected TCP connection.
 *
 * Licensed under GPL-3.0-or-later, with Roundcube's exceptions.
 */
class rcube_socket_connector
{
    private const ATTEMPT_DELAY = 0.250;

    /**
     * @return resource|false|null null means use the existing transport path
     */
    public function connect($host, $port, $timeout, array $options, &$errno, &$errstr)
    {
        $errno = 0;
        $errstr = '';
        $scheme = 'tcp';
        if (preg_match('~^([a-z0-9.]+)://(.+)$~i', $host, $matches)) {
            $scheme = strtolower($matches[1]);
            $host = $matches[2];
        }
        $host = trim($host, '[]');
        // Explicit source addresses, unusual wrappers and unavailable socket
        // facilities retain the existing implementation and its semantics.
        if (!in_array($scheme, ['tcp', 'ssl'], true) || !empty($options['socket']['bindto'])
            || !function_exists('socket_addrinfo_lookup') || !function_exists('socket_import_stream')
        ) {
            return null;
        }

        $addresses = $this->resolve($host, $port);
        if (!$addresses) {
            return null;
        }
        $timeout = $timeout > 0 ? (float) $timeout : (float) ini_get('default_socket_timeout');
        if ($timeout <= 0) {
            return null;
        }
        $options['ssl']['peer_name'] ??= $host;
        $deadline = $this->now() + $timeout;
        $stream = $this->race(self::interleave($addresses), $port, $deadline, $options, $errno, $errstr);
        if (!$stream) {
            return false;
        }

        stream_set_blocking($stream, true);
        $remaining = max(0.001, $deadline - $this->now());
        stream_set_timeout($stream, (int) $remaining, (int) (($remaining - (int) $remaining) * 1000000));
        if ($scheme === 'ssl') {
            $method = $options['ssl']['crypto_method'] ?? \STREAM_CRYPTO_METHOD_TLS_CLIENT;
            // Never select another address to work around a TLS failure.
            if (@stream_socket_enable_crypto($stream, true, $method) !== true) {
                $this->close($stream);
                $errstr = 'TLS negotiation or certificate verification failed';
                return false;
            }
        }

        $errno = 0;
        $errstr = '';
        return $stream;
    }

    protected function resolve($host, $port)
    {
        if (filter_var($host, \FILTER_VALIDATE_IP)) {
            return [$host];
        }
        $records = @socket_addrinfo_lookup($host, (string) $port, [
            'ai_socktype' => \SOCK_STREAM, 'ai_protocol' => getprotobyname('tcp'),
        ]);
        $addresses = [];
        foreach ($records ?: [] as $record) {
            $address = socket_addrinfo_explain($record)['ai_addr'];
            $ip = $address['sin6_addr'] ?? $address['sin_addr'] ?? null;
            if ($ip !== null && !in_array($ip, $addresses, true)) {
                $addresses[] = $ip;
            }
        }
        return $addresses;
    }

    /** Keep the resolver's preferred family first, then alternate families. */
    public static function interleave(array $addresses)
    {
        if (!$addresses) {
            return [];
        }
        $first = str_contains($addresses[0], ':');
        $groups = [[], []];
        foreach ($addresses as $address) {
            $groups[str_contains($address, ':') === $first ? 0 : 1][] = $address;
        }
        $ordered = [];
        while ($groups[0] || $groups[1]) {
            foreach ([0, 1] as $group) {
                if ($groups[$group]) {
                    $ordered[] = array_shift($groups[$group]);
                }
            }
        }
        return $ordered;
    }

    protected function race(array $addresses, $port, $deadline, array $options, &$errno, &$errstr)
    {
        $pending = [];
        $next = $this->now();
        try {
            while (($addresses || $pending) && $this->now() < $deadline) {
                if ($addresses && (!$pending || $this->now() >= $next)) {
                    $address = array_shift($addresses);
                    $stream = $this->start($address, $port, $options, $errno, $errstr);
                    if ($stream) {
                        $pending[(int) $stream] = $stream;
                    }
                    $next = $this->now() + self::ATTEMPT_DELAY;
                    if (!$pending) {
                        continue;
                    }
                }
                $wait = max(0, min($deadline, $addresses ? $next : $deadline) - $this->now());
                $ready = $this->ready(array_values($pending), $wait);
                if ($ready === false) {
                    $errstr = 'Unable to wait for connection attempts';
                    return false;
                }
                foreach ($ready as $stream) {
                    unset($pending[(int) $stream]);
                    if ($this->complete($stream, $errno)) {
                        return $stream;
                    }
                    $this->close($stream);
                    $next = $this->now();
                }
            }
            $errno = 110;
            $errstr = 'Connection attempts failed or timed out';
            return false;
        } finally {
            foreach ($pending as $stream) {
                $this->close($stream);
            }
        }
    }

    protected function start($address, $port, array $options, &$errno, &$errstr)
    {
        $address = str_contains($address, ':') ? '[' . $address . ']' : $address;
        $stream = @stream_socket_client('tcp://' . $address . ':' . $port, $errno, $errstr, 0,
            \STREAM_CLIENT_CONNECT | \STREAM_CLIENT_ASYNC_CONNECT, stream_context_create($options));
        if ($stream) {
            stream_set_blocking($stream, false);
        }
        return $stream;
    }

    protected function ready(array $streams, $timeout)
    {
        $read = [];
        $except = $streams;
        $seconds = (int) $timeout;
        $count = @stream_select($read, $streams, $except, $seconds, (int) (($timeout - $seconds) * 1000000));
        return $count === false ? false : array_unique(array_merge($streams, $except), \SORT_REGULAR);
    }

    protected function complete($stream, &$errno)
    {
        $socket = @socket_import_stream($stream);
        if (!$socket) {
            return false;
        }
        $errno = socket_get_option($socket, \SOL_SOCKET, \SO_ERROR);
        return $errno === 0 && @stream_socket_get_name($stream, true) !== false;
    }

    protected function now()
    {
        return hrtime(true) / 1000000000;
    }

    protected function close($stream)
    {
        fclose($stream);
    }
}
