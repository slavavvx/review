<?php
namespace Traits;


trait DataCleaning
{
    /**
     * @param array $inputData
     * @return array
     */
    public function clearData(array $inputData) : array
    {
        $outputData = [];

        foreach ($inputData as $key => $value) {
            $value = trim($value);
            $outputData[$key] = filter_var($value, FILTER_SANITIZE_STRING);
        }

        return $outputData;
    }
}
