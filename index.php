<?php

/**
 * Copyright (c) 2022 Jeff Harris <jeff@jeffharris.us>.
 * This work is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 */
declare(strict_types = 1);

const ARRAY_ITERATIONS = 100000;
const BENCHMARKS = [
    'nonStaticNonNamespace',
    'staticNonNamespace',
    'nonStaticNamespace',
    'staticNamespace',
];
const TESTS = 70;

function lotsOfBenchmarks(): array
{
    $foo = createRandomArray(ARRAY_ITERATIONS);
    $benchmarks = [];
    foreach (BENCHMARKS as $index => $benchmark
    ) {
        $benchmarks += array_fill($index * TESTS, TESTS, $benchmark);
    }
    shuffle($benchmarks);

    return array_map(fn($function) => [$function, benchmark($function, $foo)], $benchmarks);
}

function benchmark(string $function, array $foo): float
{
    shuffle($foo);
    $start = microtime(true);
    $function($foo);
    $end = microtime(true);

    echo '.';

    return $end - $start;
}

function createRandomArray(int $iterations): array
{
    $foo = [];
    for ($i = $iterations; $i--;) {
        $foo[$i] = bin2hex(\random_bytes(6));
        if ($i % 25000 === 0) {
            echo $i . PHP_EOL;
        }
    }
    echo 'Created array of ' . $iterations . ' elements.' . PHP_EOL;

    return $foo;
}

function nonStaticNonNamespace(array $foo)
{
    $nonStaticGate = array_map(fn($hex) => hexdec($hex) % 2 ? true : null, $foo);
    $bar = array_map(fn($hex) => hexdec($hex) % 100, $foo);
    $bas = array_filter($foo, fn($hex) => hexdec($hex) % 100 === 0);
}

function staticNonNamespace(array $foo)
{
    $staticGate = array_map(static fn($hex) => hexdec($hex) % 2 ? true : null, $foo);
    $bat = array_map(static fn($hex) => hexdec($hex) % 100, $foo);
    $bau = array_filter($foo, static fn($hex) => hexdec($hex) % 100 === 0);
}

function nonStaticNamespace(array $foo)
{
    $staticGate = \array_map(fn($hex) => \hexdec($hex) % 2 ? \true : \null, $foo);
    $bat = \array_map(fn($hex) => \hexdec($hex) % 100, $foo);
    $bau = \array_filter($foo, fn($hex) => \hexdec($hex) % 100 === 0);
}

function staticNamespace(array $foo)
{
    $staticGate = \array_map(static fn($hex) => \hexdec($hex) % 2 ? \true : \null, $foo);
    $bat = \array_map(static fn($hex) => \hexdec($hex) % 100, $foo);
    $bau = \array_filter($foo, static fn($hex) => \hexdec($hex) % 100 === 0);
}

echo 'Running benchmarks...' . PHP_EOL;
$timings = lotsOfBenchmarks();
echo PHP_EOL;
echo PHP_EOL;
$benchmarks = [];
foreach ($timings as $timing) {
    $benchmarks[$timing[0]][] = $timing[1];
}
echo <<< PYTHON
# Beginning of plot.py

import pandas as pd
import matplotlib.pyplot as plt

plt.close("all")

d = {

PYTHON;

foreach (BENCHMARKS as $function) {
    echo "'" . $function . "': pd.Series([" . PHP_EOL . wordwrap(
            implode(', ', $benchmarks[$function]),
            80,
            PHP_EOL,
            false
        ) . ']),' . PHP_EOL;
}

echo <<< PYTHON
}
df = pd.DataFrame(d)

print(df.describe())
plt.figure(num=1, figsize=(8, 6), dpi=100)
df.boxplot()
plt.show()

### End
PYTHON;
