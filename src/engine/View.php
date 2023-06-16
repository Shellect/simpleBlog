<?php

namespace engine;

use RuntimeException;

class View
{
    public function __construct()
    {
        self::$cache_path = App::$config['template_cache_path'] ?: 'cache/';
        self::$cache_enabled = App::$config['template_cache_enabled'] ?: false;
    }

    private static array $blocks = [];
    private static string $cache_path;
    private static bool $cache_enabled;

    private string $code = '';

    public function render($file, $data = []): false|string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require $this->cache($file);
        return ob_get_flush();
    }

    private function cache($file): string
    {
        if (!file_exists(self::$cache_path)
            && !mkdir($concurrentDirectory = self::$cache_path, 0744)
            && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        $cached_file = self::$cache_path . str_replace(['/', '.html'], ['_', ''], $file . '.php');
        if (!self::$cache_enabled || !file_exists($cached_file) || filemtime($cached_file) < filemtime($file)) {
            $this->code = $this->includeFiles($file);
            $this->compileCode();
            file_put_contents($cached_file, $this->code);
        }
        return $cached_file;
    }

    public static function clearCache(): void
    {
        foreach (glob(self::$cache_path . '*') as $file) {
            unlink($file);
        }
    }

    private function compileCode(): void
    {
        $this->compileStatic()
            ->compileBlock()
            ->compileYield()
            ->compileEscapedEchos()
            ->compileEchos()
            ->compilePHP();
    }

    private function includeFiles($file): array|string|null
    {
        $file = App::$config['template_dir'] . $file . '.html';
        if (!file_exists($file)) {
            throw new RuntimeException(sprintf('Template "%s" was not found!', $file));
        }
        $code = file_get_contents($file);
        $pattern = '/{%\s*(extends|include)\s*\'(.*?)\'\s*%}/i';
        preg_match_all($pattern, $code, $matches, PREG_SET_ORDER);
        foreach ($matches as $value) {
            $code = str_replace($value[0], self::includeFiles($value[2]), $code);
        }
        return preg_replace($pattern, '', $code);
    }

    private function compilePHP(): void
    {
        $this->code = preg_replace('~\{%\s*(.+?)\s*%}~s', '<?php $1 ?>', $this->code);
    }

    private function compileEchos(): static
    {
        $this->code = preg_replace('~\{{\s*(.+?)\s*}}~s', '<?php echo $1 ?>', $this->code);
        return $this;
    }

    private function compileEscapedEchos(): static
    {
        $this->code = preg_replace('~\{{{\s*(.+?)\s*}}}~s', '<?php echo htmlentities($1, ENT_QUOTES, \'UTF-8\') ?>', $this->code);
        return $this;
    }

    private function compileBlock(): static
    {
        preg_match_all('~{%\s*block (.*?)\s*%}(.*?){%\s*endblock\s*%}~is', $this->code, $matches, PREG_SET_ORDER);
        foreach ($matches as $value) {
            [$block, $block_name, $block_content] = $value;
            if (!array_key_exists($block_name, self::$blocks)) {
                self::$blocks[$block_name] = '';
            }
            if (str_contains($block_content, '@parent')) {
                self::$blocks[$block_name] = str_replace('@parent', self::$blocks[$block_name], $block_content);
            } else {
                self::$blocks[$block_name] = $block_content;
            }
            $this->code = str_replace($block, '', $this->code);
        }
        return $this;
    }

    private function compileYield(): static
    {
        foreach (self::$blocks as $block => $value) {
            $this->code = preg_replace("~{%\s*yield $block\s*%}~", $value, $this->code);
        }
        $this->code = preg_replace('/{%\s*yield (.*?)\s*%}/i', '', $this->code);
        return $this;
    }

    private function compileStatic(): static
    {
        preg_match_all('~{%\s*static \'?(.*?)\'?\s*%}~is', $this->code, $matches, PREG_SET_ORDER);
        foreach ($matches as $value) {
            [$pattern, $url] = $value;
            $this->code = str_replace($pattern, App::$config['static_path'] . $url, $this->code);
        }
        return $this;
    }
}