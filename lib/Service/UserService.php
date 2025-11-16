<?php
declare(strict_types=1);
namespace Beeralex\Core\Service;

use Bitrix\Main\Config\Option;

class UserService
{
    /**
     * Генерация пароля, валидного по политике безопасности Битрикс для указанных групп.
     * @param int[] $groupIds Массив ID групп пользователя
     * @param int|null $length Желаемая длина. Если не указана — возьмём минимум из политики.
     */
    public function generatePassword(array $groupIds, ?int $length = null): string
    {
        $policy = $this->getPolicyByGroups($groupIds);

        $minLen       = (int)($policy['PASSWORD_LENGTH'] ?? 6);
        $requireUpper = $this->toBool($policy['PASSWORD_UPPERCASE'] ?? false);
        $requireLower = $this->toBool($policy['PASSWORD_LOWERCASE'] ?? false);
        $requireDigits = $this->toBool($policy['PASSWORD_DIGITS'] ?? false);
        $requirePunct = $this->toBool($policy['PASSWORD_PUNCTUATION'] ?? false);

        $special = \CUser::PASSWORD_SPECIAL_CHARS;

        $lower  = 'abcdefghijklnmopqrstuvwxyz';
        $upper  = 'ABCDEFGHIJKLNMOPQRSTUVWXYZ';
        $digits = '0123456789';

        $allowedPools = [$lower, $upper, $digits];
        if ($requirePunct) {
            $allowedPools[] = $special;
        }

        $length = \max($length ?? $minLen, $minLen);

        $required = [];
        if ($requireLower) {
            $required[] = $this->pickRandomChar($lower);
        }
        if ($requireUpper) {
            $required[] = $this->pickRandomChar($upper);
        }
        if ($requireDigits) {
            $required[] = $this->pickRandomChar($digits);
        }
        if ($requirePunct) {
            $required[] = $this->pickRandomChar($special);
        }

        if ($length < \count($required)) {
            $length = \count($required);
        }

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $chars = $required;

            while (\count($chars) < $length) {
                $pool = $allowedPools[\random_int(0, \count($allowedPools) - 1)];
                $chars[] = $this->pickRandomChar($pool);
            }

            $this->secureShuffle($chars);

            $password = \implode('', $chars);
            if ($this->validateByPolicy($password, $policy, $lower, $upper, $digits, $special)) {
                return $password;
            }
        }

        // Фолбек: последняя попытка
        return \implode('', $chars);
    }

    public function generateLogin(string $email): string
    {
        $login = \mb_strstr($email, '@', true);
        $login = \mb_substr($login, 0, 47);

        while (\CUser::GetByLogin($login)->Fetch()) {
            $login = $login . \mt_rand(0, 99999);
        }

        return \str_pad($login, 3, '_');
    }

    /**
     * Получение групп по умолчанию для новых пользователей
     */
    public function getDefaultUserGroups(): array
    {
        $defaultGroups = Option::get('main', 'new_user_registration_def_group', '');
        return $defaultGroups ? \explode(',', $defaultGroups) : [];
    }

    public function generateCode(int $length = 6): string
    {
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }

        return $result;
    }

    protected function getPolicyByGroups(array $groupIds): array
    {
        /** забавный метод, принимает как user id, так и массив id групп */
        $policy = \CUser::GetGroupPolicy($groupIds);
        if (!empty($policy)) {
            return $policy;
        }

        return [
            'PASSWORD_LENGTH'      => 6,
            'PASSWORD_UPPERCASE'   => 'N',
            'PASSWORD_LOWERCASE'   => 'N',
            'PASSWORD_DIGITS'      => 'N',
            'PASSWORD_PUNCTUATION' => 'N',
        ];
    }

    protected function validateByPolicy(string $password, array $policy, string $lower, string $upper, string $digits, string $special): bool
    {
        $minLen       = (int)($policy['PASSWORD_LENGTH'] ?? 6);
        $requireUpper = $this->toBool($policy['PASSWORD_UPPERCASE'] ?? false);
        $requireLower = $this->toBool($policy['PASSWORD_LOWERCASE'] ?? false);
        $requireDigits = $this->toBool($policy['PASSWORD_DIGITS'] ?? false);
        $requirePunct = $this->toBool($policy['PASSWORD_PUNCTUATION'] ?? false);

        if (\mb_strlen($password, '8bit') < $minLen) return false;
        if ($requireLower  && !$this->containsAny($password, $lower))   return false;
        if ($requireUpper  && !$this->containsAny($password, $upper))   return false;
        if ($requireDigits && !$this->containsAny($password, $digits))  return false;
        if ($requirePunct  && !$this->containsAny($password, $special)) return false;

        return true;
    }

    protected function toBool(mixed $v): bool
    {
        if (\is_string($v)) {
            return \strtoupper($v) === 'Y';
        }
        return (bool)$v;
    }

    protected function pickRandomChar(string $alphabet): string
    {
        $i = \random_int(0, \strlen($alphabet) - 1);
        return $alphabet[$i];
    }

    protected function containsAny(string $haystack, string $alphabet): bool
    {
        $len = \strlen($alphabet);
        for ($i = 0; $i < $len; $i++) {
            if (\str_contains($haystack, $alphabet[$i])) {
                return true;
            }
        }
        return false;
    }

    protected function secureShuffle(array &$arr): void
    {
        for ($i = \count($arr) - 1; $i > 0; $i--) {
            $j = \random_int(0, $i);
            [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
        }
    }
}
