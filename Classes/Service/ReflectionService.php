<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

class ReflectionService
{
    /**
     * @param string $filePath
     * @return string|null
     */
    public function getClassNameFromFile(string $filePath): ?string
    {
        $tokens = token_get_all(file_get_contents($filePath));
        $count = count($tokens);
        $i = 0;
        $namespace = '';
        $namespaceFound = false;
        while ($i < $count) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                while (++$i < $count) {
                    if ($tokens[$i] === ';') {
                        $namespaceFound = true;
                        $namespace = trim($namespace);
                        break;
                    }
                    $namespace .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                }
                break;
            }
            $i++;
        }

        if ($namespaceFound) {
            return $namespace . '\\' . basename($filePath, '.php');
        }

        return null;
    }
}
