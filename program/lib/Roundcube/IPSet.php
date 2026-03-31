<?php
/**
 * Copyright 2014, 2015 Brandon Black <blblack@gmail.com>
 *
 * @license GPL-2.0-or-later
 * @file
 * @author Brandon Black <blblack@gmail.com>
 */
namespace Wikimedia;

use JsonSerializable;
use function ord;
use function strlen;

/**
 * Matches IP addresses against a set of CIDR specifications
 *
 * Usage:
 *
 *     use Wikimedia\IPSet;
 *     // At startup, calculate the optimized data structure for the set:
 *     $ipset = new IPSet( [
 *         '208.80.154.0/26',
 *         '2620:0:861:1::/64',
 *         '10.64.0.0/22',
 *     ] );
 *
 *     // Runtime check against cached set (returns bool):
 *     $allowme = $ipset->match( $ip );
 *
 * In rough benchmarking, this takes about 80% more time than
 * in_array() checks on a short (a couple hundred at most) array
 * of addresses.  It's fast either way at those levels, though,
 * and IPSet would scale better than in_array if the array were
 * much larger.
 *
 * For mixed-family CIDR sets, however, this code gives well over
 * 100x speedup vs iterating Wikimedia\IPUtils::isInRange() over an array
 * of CIDR specs.
 *
 * The basic implementation is two separate binary trees
 * (IPv4 and IPv6) as nested php arrays with keys named 0 and 1.
 * The values false and true are terminal match-fail and match-success,
 * otherwise the value is a deeper node in the tree.
 *
 * A simple depth-compression scheme is also implemented: whole-byte
 * tree compression at whole-byte boundaries only, where no branching
 * occurs during that whole byte of depth.  A compressed node has
 * keys 'comp' (the byte to compare) and 'next' (the next node to
 * recurse into if 'comp' matched successfully).
 *
 * For example, given these inputs:
 *
 *     25.0.0.0/9
 *     25.192.0.0/10
 *
 * The v4 tree would look like:
 *
 *     root4 => [
 *         'comp' => 25,
 *         'next' => [
 *             0 => true,
 *             1 => [
 *                 0 => false,
 *                 1 => true,
 *             ],
 *         ],
 *     ];
 *
 * (multi-byte compression nodes were attempted as well, but were
 * a net loss in my test scenarios due to additional match complexity)
 */
class IPSet implements JsonSerializable {
	/** @var array|bool The root of the IPv4 matching tree */
	private array|bool $root4 = false;

	/** @var array|bool The root of the IPv6 matching tree */
	private array|bool $root6 = false;

	/**
	 * Instantiate the object from an array of CIDR specs
	 *
	 * Invalid input network/mask values in $cfg will result in issuing
	 * E_WARNING and/or E_USER_WARNING and the bad values being ignored.
	 *
	 * @param array $cfg Array of IPv[46] CIDR specs as strings
	 */
	public function __construct( array $cfg ) {
		foreach ( $cfg as $cidr ) {
			$this->addCidr( $cidr );
		}
	}

	/**
	 * Add a single CIDR spec to the internal matching trees
	 *
	 * @param string $cidr String CIDR spec, IPv[46], optional /mask (def all-1's)
	 * @return bool Returns true on success, false on failure
	 */
	private function addCidr( string $cidr ): bool {
		// v4 or v6 check
		if ( !str_contains( $cidr, ':' ) ) {
			$node =& $this->root4;
			$defMask = '32';
		} else {
			$node =& $this->root6;
			$defMask = '128';
		}

		// Default to all-1's mask if no netmask in the input
		if ( !str_contains( $cidr, '/' ) ) {
			$net = $cidr;
			$mask = $defMask;
		} else {
			[ $net, $mask ] = explode( '/', $cidr, 2 );
			if ( (int)$mask > $defMask || !ctype_digit( $mask ) ) {
				trigger_error( "IPSet: Bad mask '$mask' from '$cidr', ignored", E_USER_WARNING );
				return false;
			}
		}
		// explicit integer convert, checked above
		$mask = (int)$mask;

		// phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
		$raw = @inet_pton( $net );
		if ( $raw === false ) {
			return false;
		}

		// iterate the bits of the address while walking the tree structure for inserts
		// at the end, $snode will point to the highest node that could only lead to a
		// successful match (and thus can be set to true)
		$snode =& $node;
		$curBit = 0;
		$lastByteIndex = -1;
		$byteOrd = 0;
		while ( 1 ) {
			if ( $node === true ) {
				// already added a larger supernet, no need to go deeper
				return true;
			}

			if ( $curBit === $mask ) {
				// this may wipe out deeper subnets from earlier
				$snode = true;
				return true;
			}

			$byteIndex = $curBit >> 3;
			if ( $byteIndex !== $lastByteIndex ) {
				$byteOrd = ord( $raw[$byteIndex] );
				$lastByteIndex = $byteIndex;
			}

			if ( $node === false ) {
				// create new subarray to go deeper
				if ( !( $curBit & 7 ) && $curBit <= $mask - 8 ) {
					$node = [ 'comp' => $byteOrd, 'next' => false ];
				} else {
					$node = [ false, false ];
				}
			}

			if ( isset( $node['comp'] ) ) {
				$comp = $node['comp'];
				if ( $byteOrd === $comp && $curBit <= $mask - 8 ) {
					// whole byte matches, skip over the compressed node
					$node =& $node['next'];
					$snode =& $node;
					$curBit += 8;
					continue;
				}

				// have to decompress the node and check individual bits
				$unode = $node['next'];
				for ( $i = 0; $i < 8; ++$i ) {
					$unode = ( $comp & ( 1 << $i ) )
						? [ false, $unode ]
						: [ $unode, false ];
				}
				$node = $unode;
			}

			$maskShift = 7 - ( $curBit & 7 );
			$index = ( $byteOrd >> $maskShift ) & 1;
			if ( $node[$index ^ 1] !== true ) {
				// no adjacent subnet, can't form a supernet at this level
				$snode =& $node[$index];
			}
			$node =& $node[$index];
			++$curBit;
		}
	}

	/**
	 * Match an IP address against the set
	 *
	 * If $ip is unparseable, inet_pton may issue an E_WARNING to that effect
	 *
	 * @param string $ip string IPv[46] address
	 * @return bool True is match success, false is match failure
	 */
	public function match( string $ip ): bool {
		// phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
		$raw = @inet_pton( $ip );
		if ( $raw === false ) {
			return false;
		}

		if ( strlen( $raw ) === 4 ) {
			$node =& $this->root4;
		} else {
			$node =& $this->root6;
		}

		$curBit = 0;
		$lastByteIndex = -1;
		$byteOrd = 0;
		while ( $node !== true && $node !== false ) {
			$byteIndex = $curBit >> 3;
			if ( $byteIndex !== $lastByteIndex ) {
				$byteOrd = ord( $raw[$byteIndex] );
				$lastByteIndex = $byteIndex;
			}
			if ( isset( $node['comp'] ) ) {
				// compressed node, matches 1 whole byte on a byte boundary
				if ( $byteOrd !== $node['comp'] ) {
					return false;
				}
				$curBit += 8;
				$node =& $node['next'];
			} else {
				// uncompressed node, walk in the correct direction for the current bit-value
				$maskShift = 7 - ( $curBit & 7 );
				$node =& $node[( $byteOrd >> $maskShift ) & 1];
				++$curBit;
			}
		}

		return $node;
	}

	public static function newFromJson( string $jsonState ): IPSet {
		$ipset = new IPSet( [] );
		$state = json_decode( $jsonState, true );
		$ipset->root4 = $state['ipv4'] ?? false;
		$ipset->root6 = $state['ipv6'] ?? false;

		return $ipset;
	}

	public function jsonSerialize(): array {
		return [
			'ipv4' => $this->root4,
			'ipv6' => $this->root6,
		];
	}
}
