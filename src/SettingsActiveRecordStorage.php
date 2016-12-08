<?php

namespace MarketFlow\SettingsStorage;

use MarketFlow\SettingsManager\interfaces\StorageInterface;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\db\ActiveRecord;

class SettingsActiveRecordStorage extends Object implements StorageInterface
{
    /** @var ActiveRecord */
    public $userClass;
    public $userId;
    public $userPrefix = '';
    public $userValue;
    public $userScenario = 'default';

    /** @var ActiveRecord */
    public $settingClass;
    public $settingId;
    public $settingPrefix = '';
    public $settingValue;
    public $settingScenario = 'default';

    /** @var ActiveRecord */
    public $applicationClass;
    public $applicationId;
    public $applicationPrefix = '';
    public $applicationValue;
    public $applicationScenario = 'default';

    public function init()
    {
        parent::init();
        if (
            !isset($this->userClass, $this->settingClass, $this->applicationClass)
            || !is_subclass_of($this->userClass, ActiveRecord::class)
            || !is_subclass_of($this->settingClass, ActiveRecord::class)
            || !is_subclass_of($this->applicationClass, ActiveRecord::class)
        ) {
            throw new InvalidConfigException('userSettingClass, settingClass and applicationSettingClass must be set and must be instance of ' . ActiveRecord::class);
        }

        if (!isset($this->userId)) {
            $class = $this->userClass;
            $pk = $class::primaryKey();

            if (count($pk) != 1) {
                throw new \UnexpectedValueException('userSettingId must be set or userSettingClass must be a class with a single field primary key');
            }

            $this->userId = $pk[0];
        }

        if (!isset($this->userValue)) {
            $this->userValue = 'value';
        }

        if (!isset($this->settingId)) {
            $class = $this->settingClass;
            $pk = $class::primaryKey();

            if (count($pk) != 1) {
                throw new \UnexpectedValueException('settingClass must be set or settingClass must be a class with a single field primary key');
            }

            $this->settingId = $pk[0];
        }

        if (!isset($this->settingValue)) {
            $this->settingValue = 'value';
        }

        if (!isset($this->applicationId)) {
            $class = $this->applicationClass;
            $pk = $class::primaryKey();

            if (count($pk) != 1) {
                throw new \UnexpectedValueException('applicationSettingClass must be set or applicationSettingClass must be a class with a single field primary key');
            }

            $this->applicationId = $pk[0];
        }

        if (!isset($this->applicationValue)) {
            $this->applicationValue = 'value';
        }
    }

    private function getSettingObject($key)
    {
        $class = $this->settingClass;
        $result = $class::find()
            ->where([
                $this->settingId => $this->settingPrefix . $key
            ])
            ->one();
        return $result;
    }

    public function getSetting($key)
    {
        return $this->getSettingObject($key)->{$this->settingValue} ?? null;
    }

    public function setSetting($key, $value)
    {
        $setting = $this->getSettingObject($key);
        if (is_null($setting)) {
            $class = $this->settingClass;
            $setting = new $class([
                $this->settingId => $this->settingPrefix . $key
            ]);
        }

        $setting->scenario = $this->settingScenario;
        $setting->{$this->settingValue} = $value;

        return $setting->save();
    }

    private function getApplicationSettingObect($key)
    {
        $class = $this->applicationClass;
        $result = $class::find()
            ->where([
                $this->applicationId => $this->applicationPrefix . $key
            ])
            ->one();
        return $result;
    }

    public function getApplicationSetting($key)
    {
        return $this->getApplicationSettingObect($key)->{$this->applicationValue} ?? null;
    }

    public function setApplicationSetting($key, $value)
    {
        $setting = $this->getApplicationSettingObect($key);
        if (is_null($setting)) {
            $class = $this->applicationClass;
            $setting = new $class([
                $this->applicationId => $this->applicationPrefix . $key
            ]);
        }

        $setting->scenario = $this->applicationScenario;
        $setting->{$this->applicationValue} = $value;

        return $setting->save();
    }

    private function getUserSettingObject($userId, $key)
    {
        $prefix = str_replace('{userId}', $userId, $this->userPrefix);

        $class = $this->userClass;
        $result = $class::find()
            ->where([
                $this->userId => $prefix . $key
            ])
            ->one();
        return $result;
    }

    public function getUserSetting($userId, $key)
    {
        return $this->getUserSettingObject($userId, $key)->{$this->userValue} ?? null;
    }

    public function setUserSetting($userId, $key, $value)
    {
        $setting = $this->getUserSettingObject($userId, $key);

        if (is_null($setting)) {
            $prefix = str_replace('{userId}', $userId, $this->userPrefix);
            $class = $this->userClass;
            $setting = new $class([
                $this->userId => $prefix . $key
            ]);
        }

        $setting->scenario = $this->userScenario;
        $setting->{$this->userValue} = $value;

        return $setting->save();
    }

}