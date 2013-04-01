<?php

namespace Helper;

class Text
{
    
    const EOL = "\r\n";
    const DOUBLE_EOL = "\r\n\r\n";

    public static function classNameOnly($class)
    {
        if (($pos = strrpos($class, "\\")) !== false)
            $class = substr($class, $pos+1);
        
        return $class;
    }
    
    public static function namespaceNameOnly($class)
    {
        if (($pos = strrpos($class, "\\")) !== false)
            $class = substr($class, 0, $pos);
        
        return $class;
    }
    
    public static function generateLabel($text)
    {
        return strtr(ucwords(str_replace('_', ' ', $text)), array(
            ' De ' => ' de ',
            ' Da ' => ' da ',
            ' Do ' => ' do ',
            ' Das ' => ' das ',
            ' Dos ' => ' dos ',
        ));
    }
    
    public static function generateAttribute($text)
    {
        return strtr(strtolower(str_replace(' ', '_', $text)), array(
            '!' => '',
            '?' => '',
            '@' => '',
            '%' => ''
        ));
    }
    
    public static function sanitizeAttributeName($text)
    {
        return str_replace('-', '_', $text);
    }
    
    public static function MongoStringConnection(array $params)
    {
        $hosts = array();
        $host = 'localhost';
        $port = 27017;
        for ($i = 0; $i < count($params['hosts']); $i++)
        {
            if (!empty($params['hosts']) && !empty($params['hosts'][$i]))
                $host = $params['hosts'][$i] . ':';

            if (!empty($params['ports']) && !empty($params['ports'][$i]))
            {
                $host .= $params['ports'][$i];
                $port = $params['ports'][$i];
            }
            else
                $host .= $port;

            $hosts[] = $host;
        }
        
        $user = '';
        if (!empty($params['password'])) $user .= ':' . $params['password'];
        if (!empty($params['username'])) $user = $params['username'] . $user . '@';
        
        $db = '';
        if (!empty($params['database'])) $db .= '/' . $params['database'];
        
        return 'mongodb://' . $user . implode(',', $hosts) . $db;
    }
    
}
