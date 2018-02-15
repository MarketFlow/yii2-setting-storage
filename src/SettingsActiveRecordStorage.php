<?php

namespace MarketFlow\SettingsStorage;

use MarketFlow\SettingsManager\interfaces\StorageInterface;
use yii\base\InvalidConfigException;
use yii\base\BaseObject;
use yii\db\ActiveRecord;

class SettingsActiveRecordStorage extends BaseObject implements StorageInterface
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

    private $cache = [];

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
        $settingId = $this->settingPrefix . $key;

        if (!isset($this->cache[$settingId])) {
            $class = $this->settingClass;
            $this->cache[$settingId] = $class::find()
                ->where([
                    $this->settingId => $settingId
                ])
                ->one();
        }


        return $this->cache[$settingId];
    }

    public function getSetting($key)
    {
        return $this->getSettingObject($key)->{$this->settingValue} ?? null;
    }

    public function setSetting($key, $value)
    {
        $setting = $this->getSettingObject($key);
        $settingId = $this->settingPrefix . $key;

        if (is_null($setting)) {
            $class = $this->settingClass;
            $setting = new $class([
                $this->settingId => $settingId
            ]);
        }

        $setting->scenario = $this->settingScenario;
        $setting->{$this->settingValue} = $value;

        if ($result = $setting->save()) {
            $this->cache[$settingId] = $setting;
        }

        return $result;
    }

    private function getApplicationSettingObect($key)
    {
        $settingId = $this->applicationPrefix . $key;

        if (!isset($this->cache[$settingId])) {
            $class = $this->applicationClass;
            $this->cache[$settingId] = $class::find()
                ->where(
                    [
                        $this->applicationId => $settingId
                    ]
                )
                ->one();
        }
        return $this->cache[$settingId];
    }

    public function getApplicationSetting($key)
    {
        return $this->getApplicationSettingObect($key)->{$this->applicationValue} ?? null;
    }

    public function setApplicationSetting($key, $value)
    {
        $setting = $this->getApplicationSettingObect($key);
        $settingId = $this->applicationPrefix . $key;

        if (is_null($setting)) {
            $class = $this->applicationClass;
            $setting = new $class([
                $this->applicationId => $settingId
            ]);
        }

        $setting->scenario = $this->applicationScenario;
        $setting->{$this->applicationValue} = $value;

        if ($result = $setting->save()) {
            $this->cache[$settingId] = $setting;
        }

        return $result;
    }

    private function getUserSettingObject($userId, $key)
    {
        $prefix = str_replace('{userId}', $userId, $this->userPrefix);
        $settingId = $prefix . $key;

        if (!isset($this->cache[$settingId])) {
            $class = $this->userClass;
            $this->cache[$settingId] = $class::find()
                ->where(
                    [
                        $this->userId => $settingId
                    ]
                )
                ->one();
        }

        return $this->cache[$settingId];
    }

    public function getUserSetting($userId, $key)
    {
        return $this->getUserSettingObject($userId, $key)->{$this->userValue} ?? null;
    }

    public function setUserSetting($userId, $key, $value)
    {
        $setting = $this->getUserSettingObject($userId, $key);
        $prefix = str_replace('{userId}', $userId, $this->userPrefix);
        $settingId = $prefix . $key;

        if (is_null($setting)) {
            $class = $this->userClass;
            $setting = new $class([
                $this->userId => $settingId
            ]);
        }

        $setting->scenario = $this->userScenario;
        $setting->{$this->userValue} = $value;

        if ($result = $setting->save()) {
            $this->cache[$settingId] = $setting;
        }

        return $result;
    }

}
