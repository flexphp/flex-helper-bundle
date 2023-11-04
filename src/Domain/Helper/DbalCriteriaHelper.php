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

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

class DbalCriteriaHelper
{
    use DateTimeTrait;

    public const OP_EQUALS = '=';

    public const OP_START = 'START';

    public const OP_END = 'END';

    public const OP_CONTAINS = 'CONTAINS';

    public const OP_SEARCH = 'SEARCH';

    private $query;

    private $offset;

    public function __construct(QueryBuilder &$query, int $offset = 0)
    {
        $this->query = $query;
        $this->offset = $this->getOffsetInverted($offset);
    }

    public function getCriteria(string $alias, string $column, $value, string $operator = self::OP_EQUALS): void
    {
        if (\in_array($column, ['_page', '_offset', '_limit']) || \is_null($value) || $value === '') {
            return;
        }

        if (\is_array($value) && \count($value) === 2) {
            [$dateStart, $dateEnd] = $value;

            if ($dateStart && $dateEnd) {
                $this->query->andWhere("{$alias}.{$column} BETWEEN :{$column}Start AND :{$column}End");
                $this->query->setParameter(":{$column}Start", $this->getStartDay($dateStart . ' 00:00:00', $this->offset));
                $this->query->setParameter(":{$column}End", $this->getEndDay($dateEnd . ' 23:59:59', $this->offset));
            } else {
                if ($dateStart) {
                    $this->query->andWhere("{$alias}.{$column} >= :{$column}");
                    $this->query->setParameter(":{$column}", $this->getStartDay($dateStart . ' 00:00:00', $this->offset));
                } elseif ($dateEnd) {
                    $this->query->andWhere("{$alias}.{$column} <= :{$column}");
                    $this->query->setParameter(":{$column}", $this->getEndDay($dateEnd . ' 23:59:59', $this->offset));
                }
            }
        } elseif ($operator === self::OP_START) {
            $this->query->andWhere("{$alias}.{$column} LIKE :{$column}");
            $this->query->setParameter(":{$column}", "{$value}%");
        } elseif ($operator === self::OP_END) {
            $this->query->andWhere("{$alias}.{$column} LIKE :{$column}");
            $this->query->setParameter(":{$column}", "%{$value}");
        } elseif ($operator === self::OP_CONTAINS) {
            $this->query->andWhere("{$alias}.{$column} LIKE :{$column}");
            $this->query->setParameter(":{$column}", "%{$value}%");
        } elseif ($operator === self::OP_SEARCH) {
            $value = \preg_replace('/(\s)+/', '%', \trim($value));

            $this->query->andWhere("{$alias}.{$column} LIKE :{$column}");
            $this->query->setParameter(":{$column}", "%{$value}%");
        } else {
            $this->query->andWhere("{$alias}.{$column} {$operator} :{$column}");
            $this->query->setParameter(":{$column}", $value, is_int($value) ? ParameterType::INTEGER : ParameterType::STRING);
        }
    }
}
