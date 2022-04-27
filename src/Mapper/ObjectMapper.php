<?php
namespace Terrazza\Component\Dto\Mapper;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class ObjectMapper implements ObjectMapperInterface {
    private LoggerInterface $logger;
    private string $argumentDelimiter               = ".";
    public function __construct(LoggerInterface $logger, string $argumentDelimiter=".") {
        $this->logger                               = $logger;
        $this->argumentDelimiter                    = $argumentDelimiter;
    }

    /**
     * @param object $source
     * @param array $mapping
     * @return array|object|null
     */
    public function map(object $source, array $mapping) {
        $cStaticValues								= 0;
        $cSetValues									= 0;
        $cElements 									= 0;
        if ($this->isAssociative($mapping)) {
            $this->logger->debug("mapping isAssociative");
            $target 								= (object)[];
            foreach ($mapping as $key => $value) {
                $cElements++;
                if (substr($key, -1, 1) === "*") {
                    $key 							= substr($key, 0, -1);
                    $this->logger->debug("use static value for target/property $key");
                    $target->{$key}					= $value;
                    $cStaticValues++;
                    continue;
                }
                if (is_array($value)) {
                    $this->logger->debug("target/property $key isArray");
                    if ($map = $this->map($source, $value)) {
                        $target->{$key}				= $map;
                        $cSetValues++;
                    } else {
                        $this->logger->debug("no value for target/property $key found");
                    }
                } elseif (is_string($value)) {
                    $this->logger->debug("target/property $key isString");
                    if ($this->hasSourceProperty($source, $value)) {
                        $this->logger->debug("value for source/property $value found");
                        $value 						= $this->getSourceValue($source, $value);
                        $target->{$key}				= $value;
                        $cSetValues++;
                    } else {
                        $this->logger->debug("no value for source/property $value found");
                    }
                }
            }
        } else {
            $this->logger->debug("mapping isSequential");
            $target									= [];
            foreach ($mapping as $value) {
                if ($map = $this->map($source, $value)) {
                    $target[]						= $map;
                }
            }
        }

        if ($cSetValues) {
            return $target;
        } elseif ($cElements === $cStaticValues) {
            return $target;
        } else {
            return null;
        }
    }

    /**
     * @param array $input
     * @return bool
     */
    private function isAssociative(array $input) : bool {
        if ([] === $input) {
            return true;
        }
        $cElements 									= count($input);
        for ($iElement = 0; $iElement < $cElements; $iElement++) {
            if(!array_key_exists($iElement, $input)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param mixed $source
     * @param string $propertyName
     * @return mixed
     */
    private function getSourceValue($source, string $propertyName) {
        $input 									    = clone $source;
        $names 										= explode($this->argumentDelimiter, $propertyName);
        foreach ($names as $name) {
            if (is_object($input)) {
                if (property_exists($input, $name)) {
                    $input 							= $input->{$name};
                } else {
                    throw new InvalidArgumentException("$propertyName does not exist in source object");
                }
            } else {
                throw new InvalidArgumentException("cannot get sourceValue from type, given ".gettype($source));
            }
        }
        return $input;
    }

    /**
     * @param $source
     * @param string $propertyName
     * @return bool
     */
    private function hasSourceProperty($source, string $propertyName) : bool {
        $input 									    = clone $source;
        $names 										= explode($this->argumentDelimiter, $propertyName);
        foreach ($names as $name) {
            if (is_object($input)) {
                if (property_exists($input, $name)) {
                    $input 							= $input->{$name};
                }
                else {
                    return false;
                }
            } else {
                throw new InvalidArgumentException("cannot get sourceProperty from type, given ".gettype($source));
            }
        }
        return true;
    }
}