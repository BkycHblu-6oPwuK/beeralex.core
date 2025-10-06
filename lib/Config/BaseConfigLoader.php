<?php

namespace Beeralex\Core\Config;

/**
 * Базовый загрузчик файлов из local/config
 */
abstract class BaseConfigLoader
{
    private string $configDir;
    private array $loadedFiles = [];

    public function __construct(?string $configDir = null)
    {
        $this->configDir = $configDir ?? rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/local/config';
    }

    /**
     * Загружает файл конфигурации
     *
     * @param string $fileName Имя файла
     * @return mixed Загруженные данные или null если файл не существует
     */
    public function load(string $fileName)
    {
        if (isset($this->loadedFiles[$fileName])) {
            return $this->loadedFiles[$fileName];
        }

        $filePath = $this->getPath($fileName);

        if (!$this->exists($fileName)) {
            throw new \Exception("file {$fileName}");
        }

        $data = $this->loadFile($filePath);
        $validatedData = $this->validate($data, $fileName);

        return $this->loadedFiles[$fileName] = $validatedData;
    }

    /**
     * Загружает файл конфигурации и при ошибке возвращает значение по умолчанию
     */
    public function tryLoad(string $fileName)
    {
        try {
            return $this->load($fileName);
        } catch (\Throwable $e) {
            $this->handleLoadError($fileName, $e);
            return $this->defaultValue();
        }
    }

    /**
     * Обрабатывает ошибку загрузки (можно переопределить в наследниках)
     */
    protected function handleLoadError(string $fileName, \Throwable $e): void
    {
        error_log(sprintf(
            'Config load failed: %s (%s)',
            $fileName,
            $e->getMessage()
        ));
    }

    /**
     * Загружает содержимое файла
     */
    protected function loadFile(string $filePath)
    {
        return require $filePath;
    }

    /**
     * Валидирует загруженные данные
     */
    abstract protected function validate($data, string $fileName);

    /**
     * Обрабатывает ситуацию когда файл не найден
     */
    abstract protected function defaultValue() : mixed;

    /**
     * Проверяет наличие конфигурационного файла
     */
    public function exists(string $fileName): bool
    {
        $filePath = $this->configDir . '/' . ltrim($fileName, '/');
        return is_file($filePath);
    }

    /**
     * Возвращает абсолютный путь до конфиг-файла
     */
    public function getPath(string $fileName): string
    {
        return $this->configDir . '/' . ltrim($fileName, '/');
    }

    /**
     * Получить директорию конфигов
     */
    public function getConfigDir(): string
    {
        return $this->configDir;
    }

    /**
     * Установить директорию конфигов
     */
    public function setConfigDir(string $configDir): self
    {
        $this->configDir = rtrim($configDir, '/');
        $this->clearCache(); // Очищаем кеш при смене директории
        return $this;
    }

    /**
     * Очищает кеш загруженных файлов
     */
    public function clearCache(): void
    {
        $this->loadedFiles = [];
    }

    /**
     * Получить список всех загруженных файлов
     */
    public function getLoadedFiles(): array
    {
        return array_keys($this->loadedFiles);
    }

    /**
     * Проверяет, загружен ли файл в кеш
     */
    public function isLoaded(string $fileName): bool
    {
        return isset($this->loadedFiles[$fileName]);
    }
}
