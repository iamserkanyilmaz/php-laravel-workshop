<?php

if (!function_exists('change_array_keys')){
    function change_array_keys(array $data, bool $flip = false): array
    {
        $maps = [
            'start_date' => 'startDate',
            'expire_date' => 'expireDate',
            'renewal_date' => 'renewalDate',
            'status' => 'status',
            'real_status' => 'realStatus'
        ];

        if ($flip){
            $maps = array_flip($maps);
        }

        $arr = [];
        foreach ($data as $key => $value)
        {
            foreach ($maps as $k => $v){
                if ($v == $key){
                    $arr[$k] = $value;
                }
            }
        }
        return $arr;
    }
}
