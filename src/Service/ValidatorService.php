<?php

namespace App\Service;

class ValidatorService
{
    public function validate(array $data): bool
    {
        if (isset($data['sku'])) {
            if ((empty($data['sku'])) || (empty($data['product_name']))) {
                return false;
            }
        } else {
            for ($i = 0; $i < count($data); $i++) {
                if ((empty($data[$i]['sku'])) || (empty($data[$i]['product_name']))) {
                    return false;
                }
            }
        }

        return true;
    }
}