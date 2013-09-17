<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class ChoiceFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('type', $data) || !array_key_exists('value', $data)) {
            return;
        }

        $values = (array) $data['value'];
        $type = $data['type'];

        // if values not set or "all" sepcified, do not do this filter
        if (!$values || in_array('all', $values, true)) {
            return;
        }

        // clean values
        foreach ($values as $key => $value) {
            $value = trim($value);
            if (!$value) {
                unset($values[$key]);
            } else {
                $values[$key] = $value;
            }
        }

        $andX = $this->getWhere($proxyQuery)->andX();

        if ($type == ChoiceType::TYPE_NOT_CONTAINS) {
            foreach ($values as $value) {
                $andX->not()->like()->field('a.'.$field)->literal('%'.$value.'%');
            }
        } elseif ($type == ChoiceType::TYPE_CONTAINS) {
            foreach ($values as $value) {
                $andX->like('a.'.$field)->literal('%'.$value.'%');
            }
        } else {
            foreach ($values as $value) {
                $andX->like('a.'.$field)->literal($value);
            }
        }

        // filter is active as we have now modified the query
        $this->active = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return array('sonata_type_filter_default', array(
            'operator_type' => 'sonata_type_equal',
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label'         => $this->getLabel()
        ));
    }
}
