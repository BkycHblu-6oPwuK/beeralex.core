<?
declare(strict_types=1);
namespace Beeralex\Core\Config\Module\Fields;

class Password extends Input
{
    protected function getType() : string
    {
        return 'password';
    }
}
