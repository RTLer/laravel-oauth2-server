<?php

namespace RTLer\Oauth2\Models;

class ModelResolver
{
    protected $options;
    protected $customModels;

    /**
     * ModelResolver constructor.
     *
     * @param string $type         (mysql|mongo)
     * @param array  $customModels
     */
    public function __construct($type = null, $customModels = [])
    {
        if (is_null($type)) {
            $type = 'mysql';
        }

        $this->type = $type;
        $this->customModels = $customModels;
    }

    /**
     * get Model class address.
     *
     * @param $modelName
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getModel($modelName)
    {
        if ($this->type == 'custom') {
            if (in_array($modelName, $this->customModels)
                && class_exists($this->customModels[$modelName])
            ) {
                return $this->customModels[$modelName];
            }

            throw new \Exception('custom model '.$modelName.' not found.');
        }

        return 'RTLer\Oauth2\Models\\'
        .studly_case($this->type).'\\'
        .studly_case($modelName);
    }
}
