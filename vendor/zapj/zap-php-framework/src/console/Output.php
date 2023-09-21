<?php
namespace zap\console;

class Output {

    private $stdout;
    private $stderr;

    public function __construct()
    {
        $this->stdout = $this->openOutputStream();
        $this->stderr = $this->openErrorStream();
    }

    /**
     * @return false|resource
     */
    public function getStderr(): bool
    {
        return $this->stderr;
    }

    private function openOutputStream()
    {
        return \defined('STDOUT') ? \STDOUT : (@fopen('php://stdout', 'w') ?: fopen('php://output', 'w'));
    }


    private function openErrorStream()
    {
       return \defined('STDERR') ? \STDERR : (@fopen('php://stderr', 'w') ?: fopen('php://output', 'w'));
    }

    protected function hasColorSupport(): bool
    {
        // Follow https://no-color.org/
        if (isset($_SERVER['NO_COLOR']) || false !== getenv('NO_COLOR')) {
            return false;
        }

        if ('Hyper' === getenv('TERM_PROGRAM')) {
            return true;
        }

        if (\DIRECTORY_SEPARATOR === '\\') {
            return false !== getenv('ANSICON')
                || 'ON' === getenv('ConEmuANSI')
                || 'xterm' === getenv('TERM');
        }

        return stream_isatty($this->stdout);
    }
}