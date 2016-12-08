<?php

namespace MarketFlow\SettingsStorage;

use MarketFlow\SettingsManager\interfaces\StorageInterface;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\db\ActiveRecord;

class SettingsActiveRecordStorage extends Object implements StorageInterface
{
    /** @var ActiveRecord */
    public $userSettingClass;
    public $userSettingId;
    public $userSettingIdPrefix = '';
    public $userSettingValue;

    /** @var ActiveRecord */
    public $settingClass;
    public $settingId;
    public $settingIdPrefix = '';
    public $settingValue;

    /** @var ActiveRecord */
    public $applicationSettingClass;
    public $applicationSettingId;
    public $applicationSettingIdPrefix = '';
    public $applicationSettingValue;

    public function init()
    {
        parent::init();
        if (
            !isset($this->userSettingClass, $this->settingClass, $this->applicationSettingClass)
            || !is_subclass_of($this->userSettingClass, ActiveRecord::class)
            || !is_subclass_of($this->settingClass, ActiveRecord::class)
            || !is_subclass_of($this->applicationSettingClass, ActiveRecord::class)
        ) {
            throw new InvalidConfigException('userSettingClass, settingClass and applicationSettingClass must be set and must be instance of ' . ActiveRecord::class);
        }

        if (!isset($this->userSettingId)) {
            $class = $this->userSettingClass;
            $pk = $class::primaryKey();

            if (count($pk) != 1) {
                throw new \UnexpectedValueException('userSettingId must be set or userSettingClass must be a class with a single field primary key');
            }

            $this->userSettingId = $pk[0];
        }

        if (!isset($this->userSettingValue)) {
            $this->userSettingValue = 'value';
        }

        if (!isset($this->settingClass)) {
            $class = $this->settingClass;
            $pk = $class::primaryKey();

            if (count($pk) != 1) {
                throw new \UnexpectedValueException('settingClass must be set or settingClass must be a class with a single field primary key');
            }

            $this->userSettingId = $pk[0];
        }

        if (!isset($this->settingValue)) {
            $this->settingValue = 'value';
        }

        if (!isset($this->applicationSettingClass)) {
            $class = $this->applicationSettingClass;
            $pk = $class::primaryKey();

            if (count($pk) != 1) {
                throw new \UnexpectedValueException('applicationSettingClass must be set or applicationSettingClass must be a class with a single field primary key');
            }

            $this->userSettingId = $pk[0];
        }

        if (!isset($this->applicationSettingValue)) {
            $this->applicationSettingValue = 'value';
        }
    }

    public function getSetting($key)
    {
        $class = $this->settingClass;
        return $class::find()
            ->select($this->settingValue)
            ->where([
                $this->settingId => $this->settingIdPrefix . $key
            ])
            ->one();
    }

    public function setSetting($key, $value)
    {
        // TODO: Implement setSetting() method.
    }

    public function getApplicationSetting($key)
    {
        $class = $this->applicationSettingClass;
        return $class::find()
            ->select($this->applicationSettingValue)
            ->where([
                $this->applicationSettingId => $this->applicationSettingIdPrefix . $key
            ])
            ->one();
    }

    public function setApplicationSetting($key, $value)
    {
        // TODO: Implement setApplicationSetting() method.
    }

    public function getUserSetting($userId, $key)
    {
        // TODO: Implement getUserSetting() method.
    }

    public function setUserSetting($userId, $key, $value)
    {
        // TODO: Implement setUserSetting() method.
    }

}