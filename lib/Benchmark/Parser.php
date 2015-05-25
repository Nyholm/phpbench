<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

use PhpBench\Exception\InvalidArgumentException;

class Parser
{
    public function parseMethodDoc($methodDoc)
    {
        $lines = explode(PHP_EOL, $methodDoc);
        $meta = array();

        $meta = array(
            'beforeMethod' => array(),
            'paramProvider' => array(),
            'iterations' => array(),
            'description' => array(),
            'processIsolation' => array(),
            'revs' => array(),
        );

        foreach ($lines as $line) {
            if (!preg_match('{@([a-zA-Z0-9]+)\s+(.*)$}', $line, $matches)) {
                continue;
            }

            $annotationName = $matches[1];
            $annotationValue = $matches[2];

            if (!isset($meta[$annotationName])) {
                throw new InvalidArgumentException(sprintf(
                    'Unknown annotation "%s"',
                    $annotationName
                ));
            }

            $meta[$annotationName][] = $annotationValue;
        }

        if (count($meta['description']) > 1) {
            throw new InvalidArgumentException(
                'Method "%s" in bench case "%s" cannot have more than one description'
            );
        }

        if (count($meta['iterations']) > 1) {
            throw new InvalidArgumentException(
                'Cannot have more than one iterations declaration'
            );
        }

        if (count($meta['processIsolation']) > 1) {
            throw new InvalidArgumentException(
                'Cannot specify more than one process isolation policy'
            );
        }

        $meta['description'] = reset($meta['description']);
        $meta['processIsolation'] = reset($meta['processIsolation']);
        $iterations = $meta['iterations'];
        $meta['iterations'] = empty($iterations) ? 1 : (int) reset($iterations);
        $revs = $meta['revs'];
        $meta['revs'] = empty($revs) ? array(1) : $revs;

        if ($meta['processIsolation']) {
            Runner::validateProcessIsolation($meta['processIsolation']);
        }

        return $meta;
    }
}
