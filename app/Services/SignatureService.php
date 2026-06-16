<?php

namespace App\Services;

class SignatureService
{
    /**
     * Генерирует параметры с подписью s (точная копия JavaScript DJB2 хеша)
     *
     * @param array $params Ассоциативный массив параметров
     * @return array Массив параметров с добавленной подписью s
     */
    public function generateQueryWithSignature(array $params): array
    {
        // 1. Сортировка ключей case-insensitive
        $keys = array_keys($params);

        usort($keys, function ($a, $b) {
            return strcasecmp($a, $b);
        });

        // 2. Сборка строки параметров (точное соответствие qs.stringify)
        $queryParts = [];
        foreach ($keys as $key) {
            $value = (string) $params[$key];
            // Используем rawurlencode для точного соответствия encodeURIComponent
            $queryParts[] = rawurlencode($key) . '=' . rawurlencode($value);
        }
        $queryString = implode('&', $queryParts);

        // 3. DJB2 хеш точно как в JavaScript
        $hash = $this->djb2HashJavaScript($queryString);

        // 4. Добавляем подпись
        return array_merge($params, ['s' => $hash]);
    }

    /**
     * Точная копия JavaScript DJB2 хеш-функции с >>> 0
     */
    private function djb2HashJavaScript(string $string): string
    {
        $hash = 5381;
        $length = strlen($string);

        for ($i = 0; $i < $length; $i++) {
            // В JavaScript: hash = ((hash << 5) + hash) ^ charCode;
            // Это эквивалентно hash * 33 ^ charCode
            $hash = (($hash << 5) + $hash) ^ ord($string[$i]);

            // Эмулируем 32-битное целое со знаком (как в JavaScript)
            $hash = $this->toInt32($hash);
        }

        // Эмулируем >>> 0 (беззнаковый сдвиг вправо на 0)
        return (string) $this->unsignedRightShift($hash, 0);
    }

    /**
     * Конвертирует в 32-битное целое со знаком (как в JS)
     */
    private function toInt32($value): int
    {
        $value = $value & 0xFFFFFFFF;

        if ($value & 0x80000000) {
            return -((~$value + 1) & 0xFFFFFFFF);
        }

        return $value;
    }

    /**
     * Эмуляция >>> (unsigned right shift) из JavaScript
     */
    private function unsignedRightShift($value, $shift): int
    {
        // Конвертируем в 32-битное беззнаковое
        $value = $value & 0xFFFFFFFF;
        $shift = $shift & 0x1F;

        // Сдвигаем
        return ($value >> $shift) & 0xFFFFFFFF;
    }

    /**
     * Упрощенная версия, если предыдущая не работает
     */
    public function generateQueryWithSignatureSimple(array $params): array
    {
        // 1. Сортировка ключей
        $keys = array_keys($params);
        usort($keys, 'strcasecmp');

        // 2. Сборка строки
        $pairs = [];
        foreach ($keys as $key) {
            $pairs[] = rawurlencode($key) . '=' . rawurlencode($params[$key]);
        }
        $str = implode('&', $pairs);

        // 3. DJB2 хеш с использованием 32-битной арифметики
        $hash = 5381;
        for ($i = 0; $i < strlen($str); $i++) {
            // Используем intval для принудительного целочисленного типа
            $hash = intval(($hash * 33) ^ ord($str[$i]));
            // Ограничиваем 32 битами
            $hash = $hash & 0xFFFFFFFF;
        }

        // Конвертируем в беззнаковое
        if ($hash < 0) {
            $hash += 4294967296; // 2^32
        }

        return array_merge($params, ['s' => (string) $hash]);
    }
}
