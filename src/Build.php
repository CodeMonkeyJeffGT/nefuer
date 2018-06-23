<?php
namespace Nefu;

/**
 * 生成参数/header数组的操作
 * 
 * @author gutian <18846938586@163.com>
 * @date 2018/06/22
 */
class Build
{
    
    /**
     * 根据参数/header配置与提供的参数/header生成参数/header数组
     * 
     * @param array $config 参数/header配置
     * @param array $array 提供的参数/header
     * @param string $method 返回结果编码方式 http、JSON、array
     * 
     * @return array $result 参数/header数组
     */
    public static function build($config, $array = array(), $method = 'array')
    {
        $result = array();
        foreach ($config as $param) {
            try {
                $name = $param['name'];
                $type = $param['type'];
                $value = isset($param['default']) ? $param['default'] : null;
                if ( ! is_null($value) && ! static::checkType($type, $value)) {
                    throw new \Exception('默认值与所定义类型不符');
                }
            } catch (\Exception $e) {
                throw new \Exception('配置文件错误：' . $e->getMessage());
            }

            if (isset($array[$name])) {
                if ( ! static::checkType($type, $array[$name])) {
                    throw new \InvalidArgumentException(sprintf('类型不符，需要 %s 类型的内容'), $type);
                }
                $value = $array[$name];
            }

            if (is_null($value)) {
                throw new \InvalidArgumentException(sprintf('缺少参数/header：%s (%s)', $name, $type));
            }

            $result[$name] = $value;
        }

        switch ($method) {
            case 'http':
                return http_build_query($result);
            case 'JSON':
                return json_encode($result);
            default:
                return $result;
        }
    }

    /**
     * 判断值是否与类型相符
     * 
     * @param string $type 类型
     * @param mixed $value 值
     * 
     * @return boolean true|false
     */
    private static function checkType($type, $value) {
        switch ($type) {
            case 'int':
                return $value == (int)$value;
            case 'string':
                return is_string($value);
            case 'number':
                return is_numeric($value);
            default:
                return true;
        }
    }

}