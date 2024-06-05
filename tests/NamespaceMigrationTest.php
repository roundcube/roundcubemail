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
     * @return array<string, list<class-string>>
     */
    protected function listClassNamesByPath(): array
    {
        $finder = new Finder();
        $finder
            ->files()
            ->in(dirname(__DIR__) . '/program')
            ->name('*.php');

        $classNamesByPath = [];
        foreach ($finder as $file) {
            $contents = $file->getContents();

            $namespace = null;
            if (preg_match('~(?<=\n)namespace ([^;\s]+);~', $contents, $matches)) {
                $namespace = $matches[1];
            }

            preg_match_all('~(?<=\s)class ([^;\s]+)\s(?=[^;*$]*\{)~', $contents, $matchesAll, \PREG_SET_ORDER);
            foreach ($matchesAll as $matches) {
                $classNamesByPath[$file->getPathname()][] = ($namespace === null ? '' : $namespace . '\\')
                    . $matches[1];
            }
        }

        return $classNamesByPath;
    }

    public function testAllClassesAreAccesibleAsNonAliased(): void
    {
        $expectedCode = '<?php' . "\n\n";
        foreach ($this->listClassNamesByPath() as $path => $classNames) {
            foreach ($classNames as $className) {
                $classNameShort = preg_replace('~.+\\\~', '', $className);
                if ($classNameShort !== $className) {
                    $expectedCode .= 'class ' . $classNameShort . ' extends \\' . $className . ' {}' . "\n";
                }
            }
        }

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
