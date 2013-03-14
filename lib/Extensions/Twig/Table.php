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
     *          'actions' => Array pointing the URL for edit and delete. Replaces '?' with PK
     *              Ex.: 'actions' => array(
     *                       'edit' => '/user/edit/?',
     *                       'delete' => '/user/delete/?'
     *                   )
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
        
        $table = '<table class="' . $class . '">';
        $table .= $this->getTableHeader($cols, count($attributes), isset($options['actions']));
        $table .= $this->getTableBody($attributes, $entities, $actions);
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
    
    private function getTableBody(array $attributes, array $entities, array $actions)
    {
        $body = '<tbody>';
        foreach ($entities as $entity)
        {
            $body .= '<tr>';
            foreach ($attributes as $attribute) {
                if (is_array($entity->$attribute)) {
                    $body .= '<td>';
                    foreach ($entity->$attribute as $value)
                        $body .= $value . '<br>';
                    $body .= '</td>';
                } else {
                    $body .= '<td>' . $entity->$attribute . '</td>';
                }
            }

            if (!empty($actions)) {
                $body .= '<td style="text-align: center;">';
                if (isset($actions['edit']))
                    $body .= '<a href="' . str_replace('?', $entity->getPKValue(), $actions['edit']) . '"><img src="/images/edit.png" /></a>&nbsp;&nbsp;&nbsp;';
                if (isset($actions['delete']))
                    $body .= '<a href="' . str_replace('?', $entity->getPKValue(), $actions['delete']) . '"><img src="/images/delete.png" /></a>';
                $body .= '</td>';
            }

            $body .= '</tr>';
        }
        $body .= '</tbody>';
        
        return $body;
    }
    
    private function getPaginator($page, $perPage, $total, $httpMethod, $route)
    {
        $route = $this->app['request']->get('_route');
        $urlPattern = $this->app['routes']->get($route)->getPattern();
        $urlNext = str_replace('{page}', ($page + 1), $urlPattern);
        $urlPrev = $page - 1 <= 0 ? str_replace('/{page}', '', $urlPattern) : str_replace('{page}', ($page - 1), $urlPattern);
        $totalPages = ceil($total / $perPage);
        
        $paginator = '<div style="text-align: center;">';
        if (strtoupper($httpMethod) === 'GET') {
            $next = $page + 1 >= $totalPages ? '&rarr;' : '<a href="' . $urlNext . '">&rarr;</a>';
            $previous = $page - 1 < 0 ? '&larr;' : '<a href="' . $urlPrev . '">&larr;</a>';
            $paginator .= $previous . ' | ' . $next;
        } elseif (strtoupper($httpMethod) === 'POST') {
            $params = $this->app['request']->request->all();
            $next = $page + 1 >= $totalPages ? '&rarr;' : '<a id="link-next" href="' . $urlNext . '">&rarr;</a>';
            $previous = $page - 1 < 0 ? '&larr;' : '<a id="link-prev" href="' . $urlPrev . '">&larr;</a>';
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