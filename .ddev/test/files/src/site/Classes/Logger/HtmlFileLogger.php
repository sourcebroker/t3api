<?php

namespace V\Site\Logger;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\AbstractLogger;
use Stringable;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class HtmlFileLogger extends AbstractLogger
{
    private const LOGS_DIRECTORY = 'logs';
    private static int $logCalls = 0;
    private static ?string $logPath;

    public function __construct()
    {
        self::$logPath = self::$logPath ?? Environment::getPublicPath() . '/' . self::LOGS_DIRECTORY;
        if (!is_dir(self::$logPath)) {
            GeneralUtility::mkdir_deep(self::$logPath);
        }
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        self::$logCalls++;
        $logEntry = '<h3><strong>Message:</strong></h3>';
        $logEntry .= '<p>' . $message . '</p>';
        $logEntry .= '<h3>Data from caller:</h3>';
        $logEntry .= '<hr>';
        if (self::$logCalls > 1) {
            $logEntry .= $this->getDebuggerUtilityCss();
        }

        $logEntry .= DebuggerUtility::var_dump($context, null, 20, false, false, true);

        $logEntry .= '<h3>Data from logger:</h3>';
        $logEntry .= '<hr>';
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $request = $this->getRequest();
        $fileName = $this->normalizeStringForFilename(
            implode('_', [
                sprintf('%.5f', microtime(true)),
                (string)$request->getUri(),
                $request->getMethod(),
                $backtrace[2]['class']
            ])
        );

        GeneralUtility::writeFile(
            self::$logPath . '/' . $fileName . '.html',
            $logEntry
        );
    }

    protected function normalizeStringForFilename(string $uri): string
    {
        $uri = explode('?', $uri)[0];
        $uri = str_replace(array('/', '\\'), '_', $uri);
        return preg_replace('/[^A-Za-z0-9_\-\.]/', '', $uri);
    }

    private function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }

    /*
     * DebuggerUtility::var_dump does add css ony once on first call. But we need to add it every time because we store output in separate files.
     */
    protected function getDebuggerUtilityCss(): string
    {
        return '
				<style>
					.extbase-debugger-tree{position:relative}
					.extbase-debugger-tree input{position:absolute !important;float: none !important;top:0;left:0;height:14px;width:14px;margin:0 !important;cursor:pointer;opacity:0;z-index:2}
					.extbase-debugger-tree input~.extbase-debug-content{display:none}
					.extbase-debugger-tree .extbase-debug-header:before{position:relative;top:3px;content:"";padding:0;line-height:10px;height:12px;width:12px;text-align:center;margin:0 3px 0 0;background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkViZW5lXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgMTIgMTIiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDEyIDEyOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+PHN0eWxlIHR5cGU9InRleHQvY3NzIj4uc3Qwe2ZpbGw6Izg4ODg4ODt9PC9zdHlsZT48cGF0aCBpZD0iQm9yZGVyIiBjbGFzcz0ic3QwIiBkPSJNMTEsMTFIMFYwaDExVjExeiBNMTAsMUgxdjloOVYxeiIvPjxnIGlkPSJJbm5lciI+PHJlY3QgeD0iMiIgeT0iNSIgY2xhc3M9InN0MCIgd2lkdGg9IjciIGhlaWdodD0iMSIvPjxyZWN0IHg9IjUiIHk9IjIiIGNsYXNzPSJzdDAiIHdpZHRoPSIxIiBoZWlnaHQ9IjciLz48L2c+PC9zdmc+);display:inline-block}
					.extbase-debugger-tree input:checked~.extbase-debug-content{display:inline}
					.extbase-debugger-tree input:checked~.extbase-debug-header:before{background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkViZW5lXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgMTIgMTIiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDEyIDEyOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+PHN0eWxlIHR5cGU9InRleHQvY3NzIj4uc3Qwe2ZpbGw6Izg4ODg4ODt9PC9zdHlsZT48cGF0aCBpZD0iQm9yZGVyIiBjbGFzcz0ic3QwIiBkPSJNMTEsMTFIMFYwaDExVjExeiBNMTAsMUgxdjloOVYxeiIvPjxnIGlkPSJJbm5lciI+PHJlY3QgeD0iMiIgeT0iNSIgY2xhc3M9InN0MCIgd2lkdGg9IjciIGhlaWdodD0iMSIvPjwvZz48L3N2Zz4=)}
					.extbase-debugger{display:block;text-align:left;background:#2a2a2a;border:1px solid #2a2a2a;box-shadow:0 3px 0 rgba(0,0,0,.5);color:#000;margin:20px;overflow:hidden;border-radius:4px}
					.extbase-debugger-floating{position:relative;z-index:99990}
					.extbase-debugger-top{background:#444;font-size:12px;font-family:monospace;color:#f1f1f1;padding:6px 15px}
					.extbase-debugger-center{padding:0 15px;margin:15px 0;background-image:repeating-linear-gradient(to bottom,transparent 0,transparent 20px,#252525 20px,#252525 40px)}
					.extbase-debugger-center,.extbase-debugger-center .extbase-debug-string,.extbase-debugger-center a,.extbase-debugger-center p,.extbase-debugger-center pre,.extbase-debugger-center strong{font-size:12px;font-weight:400;font-family:monospace;line-height:20px;color:#f1f1f1}
					.extbase-debugger-center pre{background-color:transparent;margin:0;padding:0;border:0;word-wrap:break-word;color:#999}
					.extbase-debugger-center .extbase-debug-string{color:#ce9178;white-space:normal}
					.extbase-debugger-center .extbase-debug-type{color:#569CD6;padding-right:4px}
					.extbase-debugger-center .extbase-debug-unregistered{background-color:#dce1e8}
					.extbase-debugger-center .extbase-debug-filtered,.extbase-debugger-center .extbase-debug-proxy,.extbase-debugger-center .extbase-debug-ptype,.extbase-debugger-center .extbase-debug-visibility,.extbase-debugger-center .extbase-debug-uninitialized,.extbase-debugger-center .extbase-debug-scope{color:#fff;font-size:10px;line-height:12px;padding:2px 4px;margin-right:2px;position:relative;top:-1px}
					.extbase-debugger-center .extbase-debug-scope{background-color:#497AA2}
					.extbase-debugger-center .extbase-debug-ptype{background-color:#698747}
					.extbase-debugger-center .extbase-debug-visibility{background-color:#6c0787}
					.extbase-debugger-center .extbase-debug-uninitialized{background-color:#698747}
					.extbase-debugger-center .extbase-debug-dirty{background-color:#FFFFB6}
					.extbase-debugger-center .extbase-debug-filtered{background-color:#4F4F4F}
					.extbase-debugger-center .extbase-debug-seeabove{text-decoration:none;font-style:italic}
					.extbase-debugger-center .extbase-debug-property{color:#f1f1f1}
					.extbase-debugger-center .extbase-debug-closure{color:#9BA223;}
				</style>';
    }
}
