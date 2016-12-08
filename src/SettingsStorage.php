<?php

namespace MarketFlow\SettingsManager;

use Exception;
use MarketFlow\SettingsManager\interfaces\SettingInterface;
use MarketFlow\SettingsManager\interfaces\StorageInterface;
use MarketFlow\SettingsManager\interfaces\TypeInterface;
use UnexpectedValueException;

/**
 * Class SettingsManager
 * @package MarketFlow\SettingsManager
 */
class SettingsManager
{
    /** @var string */
    private $separator = '|';

    /** @var StorageInterface */
    private $storage;

    /** @var TypeInterface[] */
    private $types = [];

    /**
     * SettingsManager constructor.
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param $key
     * @param SettingInterface|null $context
     * @param SettingInterface|null $userContext
     * @return mixed
     * @throws Exception
     */
    public function get($key, SettingInterface $context = null, SettingInterface $userContext = null)
    {
        $result = [];
        $setting = $this->getType($key);

        while (isset($userContext)) {
            $result[] = $setting->unserialize($this->storage->getUserSetting($this->userKey($key, $userContext)));
            $userContext = $userContext->getSettingParent();
        }

        while (isset($context)) {
            $result[] = $setting->unserialize($this->storage->getSetting($this->contextKey($key, $context)));
            $context = $context->getSettingParent();
        }

        $result[] = $setting->unserialize($this->storage->getApplicationSetting($key));

        return $setting->merge($result);
    }

    /**
     * @param $key
     * @return TypeInterface
     * @throws Exception
     */
    public function getType($key) : TypeInterface
    {
        if (isset($this->types[$key])) {
            return $this->types[$key];
        } else {
            throw new Exception('Unknown type key');
        }
    }

    /**
     * @param $name
     * @param TypeInterface $type
     */
    public function registerType($name, TypeInterface $type)
    {
        $this->types[$name] = $type;
    }

    /**
     * @param $key
     * @param $value
     * @throws Exception
     */
    public function setApplicationSetting($key, $value)
    {
        $setting = $this->getType($key);

        if ($setting->validate($value)) {
            $this->storage->setApplicationSetting($key, $setting->serialize($value));
        } else {
            throw new UnexpectedValueException('Validation failed');
        }
    }

    /**
     * @param SettingInterface $context
     * @param $key
     * @param $value
     * @throws Exception
     */
    public function setContextSetting(SettingInterface $context, $key, $value)
    {
        $setting = $this->getType($key);

        if ($setting->validate($value)) {
            $this->storage->setSetting(
                $this->contextKey($key, $context),
                $setting->serialize($value)
            );
        } else {
            throw new UnexpectedValueException('Validation failed');
        }
    }

    /**
     * @param SettingInterface $userContext
     * @param $key
     * @param $value
     * @throws Exception
     */
    public function setUserSetting(SettingInterface $userContext, $key, $value)
    {
        $setting = $this->getType($key);

        if ($setting->validate($value)) {
            $this->storage->setUserSetting(
                $this->userKey($key, $userContext),
                $setting->serialize($value)
            );
        } else {
            throw new UnexpectedValueException('Validation failed');
        }
    }

    /**
     * @param $key
     * @param SettingInterface $userContext
     * @return string
     */
    private function userKey($key, SettingInterface $userContext) {
        return $key . $this->separator . $userContext->getSettingId();
    }

    /**
     * @param $key
     * @param SettingInterface $context
     * @return string
     */
    private function contextKey($key, SettingInterface $context) {
        return $key . $this->separator . $context->getSettingId();
    }
}