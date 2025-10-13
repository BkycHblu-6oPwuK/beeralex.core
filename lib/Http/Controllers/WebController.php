<?php
namespace Beeralex\Core\Http\Controllers;

use Bitrix\Main\Engine\Controller;

abstract class WebController extends Controller
{
    const VIEWS_PATH = '/local/views/';

    protected function getDefaultPreFilters()
    {
        return [];
    }

    /**
     * Перехватываем любые Throwable, которые ловит битриксовый run()
     * Показываем HTML-страницу ошибки для обычных браузерных запросов
     */
    protected function runProcessingThrowable(\Throwable $throwable)
    {
        while (ob_get_level()) {
            @ob_end_clean();
        }

        global $APPLICATION;
        if (isset($APPLICATION)) {
            $APPLICATION->RestartBuffer();
        }

        $template = $_SERVER['DOCUMENT_ROOT'] . static::VIEWS_PATH . 'errors/exception.php';

        $errorData = [
            'message' => $throwable->getMessage(),
            'file'    => $throwable->getFile(),
            'line'    => $throwable->getLine(),
            'trace'   => $throwable->getTrace(),
            'traceString' => $throwable->getTraceAsString(),
        ];

        if (file_exists($template)) {
            extract($errorData);
            include $template;
        } else {
            echo '<h1 style="color:#b00;">Ошибка</h1><p>' . htmlspecialchars($message) . '</p>';
        }

        die();
    }

    protected function runProcessingException(\Exception $e)
    {
        $this->runProcessingThrowable($e);
    }

    protected function runProcessingError(\Error $e)
    {
        $this->runProcessingThrowable($e);
    }

    /**
     * @param string $path относительно константы VIEWS_PATH
     * @see Bitrix\Main\Engine\Controller::renderView
     * 
     ```php
        public function fooAction()
		{
			return $this->view('catalog.index', withSiteTemplate: false);
		}
     ```
     */
    protected function view(string $path, array $params = [], bool $withSiteTemplate = true)
    {
        $filePath = static::VIEWS_PATH . str_replace('.', '/', $path) . '.php';
        return $this->renderView($filePath, $params, $withSiteTemplate);
    }
}
