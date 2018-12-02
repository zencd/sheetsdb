<?php

/**
 * Абстрактный веб-сервер, раздаёт HTML-контент.
 * Для генерации контента использует внешний ContentGenerator.
 * Кэширует сгенерированный контент на сервере + использует кэширование на клиенте.
 * Серверный кэш протухает раз в N минут и тогда автообновляется.
 * Клиент может запросить форсированую регенерацию контента, через request query.
 */
class CachingWebServer
{
    /**
     * @var string файл где будет храниться серверный кэш
     */
    private $cacheFile;

    /**
     * @var ContentGenerator генератор контента
     */
    private $contentGenerator;

    /**
     * @var int таймаут обновления кэша, в секундах
     */
    private $cacheTtl;

    public function __construct(
        ContentGenerator &$contentGenerator,
        $cacheFile,
        $cacheTtl
    ) {
        $this->contentGenerator = $contentGenerator;
        $this->cacheFile        = $cacheFile;
        $this->cacheTtl         = $cacheTtl;
    }

    public function serve()
    {
        if ($this->isRefreshForced()) {
            $this->serveForcedRefresh();
        } elseif ($this->isCacheExpired()) {
            $this->serveCacheExpired();
        } elseif ($this->isClientCacheActual()) {
            $this->serveNotModified();
        } else {
            $this->serveFromCache();
        }
    }

    private function serveForcedRefresh()
    {
        $uriWithoutQuery = strtok($_SERVER["REQUEST_URI"], '?');
        $this->generateContentAndSaveToCache();
        $this->sendLastModifiedHeader();
        header("Location: $uriWithoutQuery", true, 302);
    }

    private function serveCacheExpired()
    {
        $content = $this->generateContentAndSaveToCache();
        $this->sendHeadersAndContent($content);
    }

    private function serveNotModified()
    {
        header('HTTP/1.0 304 Not Modified');
    }

    private function serveFromCache()
    {
        $content = file_get_contents($this->cacheFile);
        $this->sendHeadersAndContent($content);
    }

    private function isCacheExpired()
    {
        if ( ! file_exists($this->cacheFile)) {
            return true;
        }
        $mtime_diff = time() - filemtime($this->cacheFile); // seconds
        if ($mtime_diff > $this->cacheTtl) {
            return true;
        }

        return false;
    }

    private function sendResponseHeaders()
    {
        $this->sendLastModifiedHeader();

        // при серьёзном изменении длины сгенерированного ХТМЛ получается
        // что выдача клиенту обрезается, вероятно потому что
        // длина файла кэшируется by OS
        // так что пока закомментирую
        //header('Content-Length: '.filesize($this->cacheFile));

        // Content-Type отдаётся автоматически, по крайней мере встроенным в пхп веб-сервером
        //header('Content-Type: text/html');
        //header('Content-Type: text/html; charset=utf-8');
    }

    /**
     * Клиентская оптимизация: шлём заголовок "Last-Modified" чтобы в след. раз клиент прислал нам "If-Modified-Since".
     * Файл кэша к этому времени уже должен существовать.
     * Пример:
     * Last-Modified: Fri, 01 Jan 1990 00:00:00 GMT
     */
    private function sendLastModifiedHeader()
    {
        $mtime = filemtime($this->cacheFile);
        header("Last-Modified: " . gmdate("D, d M Y H:i:s", $mtime) . " GMT");
    }

    private function sendHeadersAndContent(&$html_content)
    {
        $this->sendResponseHeaders();
        echo $html_content;
    }

    private function isClientCacheActual()
    {
        return
            isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
            && file_exists($this->cacheFile)
            && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])
               >= filemtime($this->cacheFile);
    }

    private function isRefreshForced()
    {
        $forceKey = 'force';

        return isset($_GET[$forceKey]) && $_GET[$forceKey] == '1';
    }

    private function generateContentAndSaveToCache()
    {
        $content = $this->contentGenerator->generate();
        file_put_contents($this->cacheFile, $content);

        return $content;
    }
}
