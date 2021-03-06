<?php declare(strict_types=1);
/*
 * This file is part of FlexPHP.
 *
 * (c) Freddie Gar <freddie.gar@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FlexPHP\Bundle\HelperBundle\Domain\Helper;

use FlexPHP\Messages\RequestInterface;

trait FactoryExtendedTrait
{
    public function patch(RequestInterface $request, array $data): object
    {
        $class = \get_class($request);

        return $this->make(new $class($request->id, \array_filter(\array_merge(
            \array_filter($data, function ($value): bool {
                return !\is_null($value);
            }),
            \array_filter(\get_object_vars($request), function ($value): bool {
                return !\is_null($value);
            })
        ), function ($value, string $key): bool {
            return !\is_null($value) && \strpos($key, '.') === false;
        }, \ARRAY_FILTER_USE_BOTH), $request->updatedBy));
    }

    private function getFkEntity(string $prefix, array &$data): array
    {
        $_data = [];

        \array_map(function (string $key, ?string $value) use ($prefix, &$_data): void {
            if (\strpos($key, $prefix) !== false) {
                $_data[\substr($key, \strlen($prefix))] = $value;
            }
        }, \array_keys($data), $data);

        return $_data;
    }
}
