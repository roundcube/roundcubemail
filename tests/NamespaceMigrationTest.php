<?php

namespace Roundcube\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

/**
 * Test if all PHP files are namespaced but accessible via non-namespaced original names for back compatibility.
 *
 * In next major version (Roundcube 2.0), the non-namespaced name aliases should be removed.
 */
class NamespaceMigrationTest extends TestCase
{
    /**
     * @return array<string, list<array{'abstract class'|'class'|'interface'|'trait', class-string}>>
     */
    protected function listClassesByPath(): array
    {
        $finder = new Finder();
        $finder
            ->files()
            ->in(dirname(__DIR__) . '/program')
            ->in(dirname(__DIR__) . '/plugins')
            ->name('*.php');

        $classNamesByPath = [];
        foreach ($finder as $file) {
            $contents = $file->getContents();

            $namespace = null;
            if (preg_match('~(?<=\n)namespace ([^;\s]+);~', $contents, $matches)) {
                $namespace = $matches[1];
            }

            preg_match_all('~(?<=\s)((?:abstract )?class|interface|trait) ([^;\s$"\']+)\s(?=[^;*$]*\{(?!\s*\.))~', $contents, $matchesAll, \PREG_SET_ORDER);
            foreach ($matchesAll as $matches) {
                $classNamesByPath[$file->getPathname()][] = [
                    $matches[1],
                    ($namespace === null ? '' : $namespace . '\\') . $matches[2],
                ];
            }
        }

        ksort($classNamesByPath);

        return $classNamesByPath;
    }

    public function testAllClassesAreAccesibleAsNonAliased(): void
    {
        $expectedCode = <<<'EOF'
            <?php

            namespace Roundcube\WIP {
                use Composer\Autoload\ClassLoader;

                // handle calls from https://github.com/composer/composer/blob/2.7.6/src/Composer/Autoload/ClassLoader.php#L427
                if (!isset($legacyAutoloadClassName)) {
                    $trace = debug_backtrace(0, 3);
                    if (
                        ($trace[2]['class'] ?? null) === ClassLoader::class
                        && $trace[2]['function'] === 'loadClass'
                        && is_string($trace[2]['args'][0] ?? null)
                    ) {
                        $legacyAutoloadClassName = $trace[2]['args'][0];
                    }
                }

                if (!isset($legacyAutoloadClassName)) {
                    function rcube_autoload_legacy(string $classname)
                    {
                        if (strpos($classname, '\\') === false) {
                            $fqcn = 'Roundcube\WIP\\' . $classname;

                            if (class_exists($fqcn) || interface_exists($fqcn) || trait_exists($fqcn)) {
                                $legacyAutoloadClassName = $classname;
                                require __FILE__;
                            }
                        }
                    }

                    spl_autoload_register(__NAMESPACE__ . '\rcube_autoload_legacy');
                }
            }

            namespace {
                if (isset($legacyAutoloadClassName)) {
                    switch ($legacyAutoloadClassName) {

            EOF;

        foreach ($this->listClassesByPath() as $classPairs) {
            foreach ($classPairs as [$classType, $className]) {
                $classNameShort = preg_replace('~.+\\\~', '', $className);
                if (strpos($className, 'Roundcube\\WIP\\') === 0) {
                    $expectedCode .= '            case \'' . $classNameShort . '\':' . "\n";
                    if ($classType === 'trait') {
                        $expectedCode .= '                ' . $classType . ' ' . $classNameShort . "\n"
                            . '                {' . "\n"
                            . '                    use ' . $className . ';' . "\n"
                            . '                }' . "\n\n";
                    } else {
                        $expectedCode .= '                ' . $classType . ' ' . $classNameShort . ' extends ' . $className . ' {}' . "\n\n";
                    }
                    $expectedCode .= '                break;' . "\n";
                }
            }
        }

        $expectedCode .= <<<'EOF'
                    }
                }
            }

            EOF;

        self::assertSame(
            $expectedCode,
            str_replace(
                ["\r\n", "\r"],
                ["\n", "\n"],
                file_get_contents(__DIR__ . '/../program/unnamespaced-legacy-classes.php')
            )
        );
    }
}
