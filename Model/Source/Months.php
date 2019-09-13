<?php
namespace Dfe\PayPalPlusMx\Model\Source;

class Months implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function  toOptionArray()
    {
        return [
            ['value' => 1, 'label' => 1],
            ['value' => 3, 'label' => 3],
            ['value' => 6, 'label' => 6],
            ['value' => 9, 'label' => 9],
            ['value' => 12, 'label' => 12],
        ];
    }
}
