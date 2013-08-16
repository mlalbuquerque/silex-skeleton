<?php

namespace Extensions\Twig;

class Table extends \Twig_Extension
{
    
    public $app;
    
    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
    }
    
    /**
     * 
     * @param array $entities Array of \Model\Entity
     * @param array $attributes Array of \Model\Entity attributes to use
     * @param type $total Entities total
     * @param type $page Actual page
     * @param array $options You can set:
     *          'class' => CSS class to use in table
     *          'cols' => Array with the column's labels
     *          'modifiers' => Array with callbacks to modify the return value of some column.
     *              Ex.: 'modifiers' => array(
     *                       2 => function ($value, $entity) { // you can use the column 'value' and you get the 'entity' if you want to use it
     *                           return md5($value); // must return scalar value
     *                       }
     *                   )
     *          'actions' => Array pointing the URL for edit and delete. You can mix the examples
     *              Ex.1: 'actions' => array(
     *                        'edit' => '/user/edit/?', // It will replace '?' by PK value
     *                        'delete' => '/user/delete/?'
     *                    )
     *              Ex.2: 'actions' => array(
     *                        'edit' => '/link/edit/{id}', // It will use entity's 'id' attribute to replace
     *                        'delete' => '/link/delete/{id}/{name}' // Can use as many attributes as you want
     *                    )
     *              Ex.3: 'actions' => array(
     *                        'edit' => array(
     *                            'url' => '/link/edit/{id}'
     *                        ),
     *                        'delete' => array(
     *                            'url' => '/link/delete/{id}/{name}',
     *                            'msg' => 'Are you sure you want to delete this item?'
     *                        )
     *                    )
     * @return string HTML for Table with Pagination
     */
    public function create(array $entities, array $attributes, $total, $page = 0, array $options = array())
    {
        $httpMethod = $this->app['request']->getMethod();
        $route = $this->app['request']->getRequestUri();
        $class = isset($options['class']) ? $options['class'] : 'ssk-table';
        if (isset($options['cols'])) {
            $cols = $options['cols'] + $attributes;
            ksort($cols);
        } else
            $cols = $attributes;
        $actions = isset($options['actions']) ? $options['actions'] : array();
        $modifiers = isset($options['modifiers']) ? $options['modifiers'] : array();
        
        $table = '<table class="' . $class . '">';
        $table .= $this->getTableHeader($cols, count($attributes), isset($options['actions']));
        $table .= $this->getTableBody($attributes, $entities, $actions, $modifiers);
        $table .= '</table>';
        
        $perPage = empty($options['perPage']) ? PER_PAGE : $options['perPage'];
        $table .= $this->getPaginator($page, $perPage, $total, $httpMethod, $route);
        
        return $table;
    }
    
    public function getName()
    {
        return 'twig_table_extension';
    }
    
    
    private function getTableHeader(array $cols, $numCols, $hasActions)
    {
        $header = '<thead><tr>';
        foreach ($cols as $col) {
            $header .= '<th>' . \Helper\Text::generateLabel($col) . '</th>';
        }
        if ($hasActions) {
            $header .= $numCols < count($cols) ? '' : '<th>Ações</th>';
        }
        $header .= '</tr></thead>';
        
        return $header;
    }
    
    private function getTableBody(array $attributes, array $entities, array $actions, array $modifiers)
    {
        $body = '<tbody>';
        foreach ($entities as $entity)
        {
            $body .= '<tr>';
            for($i = 0; $i < count($attributes); $i++) {
                $attribute = $attributes[$i];
                $value = $this->checkType($entity->$attribute);
                if (isset($modifiers[$i])) {
                    $function = $modifiers[$i];
                    $value = $function($value, $entity);
                }
                $body .= '<td>' . $value . '</td>';
            }

            if (!empty($actions)) {
                $body .= '<td class="table-actions">';
                $body .= isset($actions['edit']) ? $this->linkToEdit($entity, $actions['edit']) . '&nbsp;' : '';
                $body .= isset($actions['delete']) ? $this->linkToDelete($entity, $actions['delete']) . '&nbsp;' : '';
                $body .= '</td>';
            }

            $body .= '</tr>';
        }
        $body .= '</tbody>';
        
        return $body;
    }
    
    private function linkToEdit(\Model\Entity $entity, $rules)
    {
        $link = '';
        $parts = array();
        $href = is_array($rules) ? $rules['url'] : $rules;
        if (preg_match_all('/\{(\w+)\}/', $href, $parts)) {
            $link = $this->getLink('edit', $parts[0], array($entity, $parts[1]), $rules);
        } else {
            $link = $this->getLink('edit', '?', $entity->getPKValue(), $rules);
        }
        return $link;
    }
    
    private function linkToDelete(\Model\Entity $entity, $rules)
    {
        $link = '';
        $parts = array();
        $href = is_array($rules) ? $rules['url'] : $rules;
        if (preg_match_all('/\{(\w+)\}/', $href, $parts)) {
            $link = $this->getLink('delete', $parts[0], array($entity, $parts[1]), $rules);
        } else {
            $link = $this->getLink('delete', '?', $entity->getPKValue(), $rules);
        }
        return $link;
    }
    
    private function getLink($type, $search, $replace, $rules)
    {
        $linkTmp = $type == 'edit' ?
                '<a title="Editar" href="[LINK]"><img src="/images/edit.png" /></a>' :
                '<a title="Excluir" onclick="if (!confirm(\'[MSG]\')) return false;" href="[LINK]"><img src="/images/delete.png" /></a>';
        $link = '';
        $url = is_array($rules) ? $rules['url'] : $rules;
        if (is_array($search)) {
            $entity = $replace[0];
            for ($i = 0; $i < count($search); $i++) {
                $method = $replace[1][$i];
                $url = str_replace($search[$i], $entity->$method, $url);
            }
            $link = str_replace('[LINK]', $url, $linkTmp);
        } else {
            $href = str_replace($search, $replace, $url);
            $link = str_replace('[LINK]', $href, $linkTmp);
        }
        
        return ($type == 'delete' && is_array($rules)) ? 
                str_replace('[MSG]', $rules['msg'], $link) :
                str_replace('[MSG]', 'Tem certeza que deseja excluir este item?', $link);
    }
    
    private function checkType($attribute)
    {
        $text = '';
        if (is_array($attribute)) {
            foreach ($attribute as $value)
                $text .= $value . '<br>';
        } elseif (is_bool($attribute)) {
            $text .= ($attribute) ? 'Sim' : 'Não';
        } else {
            $text .= $attribute;
        }
        return $text;
    }
    
    private function getPaginator($page, $perPage, $total, $httpMethod, $route)
    {
        $route = $this->app['request']->get('_route');
        $urlPattern = $this->app['routes']->get($route)->getPattern();
        $urlNext = str_replace('{page}', ($page + 1), $urlPattern);
        $urlPrev = $page - 1 <= 0 ? str_replace('/{page}', '', $urlPattern) : str_replace('{page}', ($page - 1), $urlPattern);
        $totalPages = ceil($total / $perPage);
        
        $paginator = '<div class="table-paginator">';
        if (strtoupper($httpMethod) === 'GET') {
            $next = $page + 1 >= $totalPages ? '&rarr;' : '<a class="table-next" href="' . $urlNext . '">&rarr;</a>';
            $previous = $page - 1 < 0 ? '&larr;' : '<a class="table-previous" href="' . $urlPrev . '">&larr;</a>';
            $paginator .= $previous . ' | ' . $next;
        } elseif (strtoupper($httpMethod) === 'POST') {
            $params = $this->app['request']->request->all();
            $next = $page + 1 >= $totalPages ? '&rarr;' : '<a class="table-next" id="link-next" href="' . $urlNext . '">&rarr;</a>';
            $previous = $page - 1 < 0 ? '&larr;' : '<a class="table-previous" id="link-prev" href="' . $urlPrev . '">&larr;</a>';
            $paginator .= $previous . ' | ' . $next;
            $paginator .= '<form id="formPagination" action="" method="post">';
            foreach ($params as $param => $value) {
                if (is_array($value))
                    foreach ($value as $key => $val)
                        $paginator .= '<input type="hidden" name="' . $param . '[' . $key . ']" value="' . $val . '" />';
                else
                    $paginator .= '<input type="hidden" name="' . $param . '" value="' . $value . '" />';
            }
            $paginator .= '</form>';
            $paginator .= $this->getScript();
        }
        $paginator .= '</div>';
        
        return $paginator;
    }
    
    private function getScript()
    {
        return <<<SCRIPT
<script>
    function submit(evt) {
        evt.preventDefault();
        var form = document.getElementById("formPagination");
        form.action = this.href;
        form.submit();
        return false;
    }
    
    if (document.getElementById("link-next"))
        document.getElementById("link-next").addEventListener(
            'click', submit, false
        );
    
    if (document.getElementById("link-prev"))
        document.getElementById("link-prev").addEventListener(
            'click', submit, false
        );
</script>
SCRIPT;
    }
    
}