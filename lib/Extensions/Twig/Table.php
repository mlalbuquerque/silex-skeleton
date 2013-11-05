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
     *                        'delete' => '/user/delete/?',
     *                        'others' => array( // as many links as you want
     *                            array(
     *                                'title' => 'Link Title',
     *                                'url'   => '/user/something/{id}/{name},
     *                                'icon'  => 'image.png'
     *                            ),
     *                            array(
     *                                'title' => 'Other Title',
     *                                'url'   => '/user/xyz/?,
     *                                'icon'  => 'image2.png'
     *                            )
     *                        )
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
        $url = $this->app['request']->getPathInfo();
        $class = isset($options['class']) ? $options['class'] : 'ssk-table';
        if (isset($options['cols'])) {
            $cols = $options['cols'] + $attributes;
            ksort($cols);
        } else
            $cols = $attributes;
        $actions = isset($options['actions']) ? $options['actions'] : array();
        $modifiers = isset($options['modifiers']) ? $options['modifiers'] : array();
        
        $table = isset($options['sort']) || isset($options['search']) ?
                '<form id="ssk_table_modifier_form" action="' . $url . '" method="POST">' . "\n" : '';
        $table .= isset($options['sort']) ?
                $this->getSortingArea($attributes, $cols, $options['sort']) : '';
        $table .= isset($options['search']) ?
                $this->getSearchingArea($attributes, $cols, $options['search']) : '';
        $table .= isset($options['sort']) || isset($options['search']) ?
                '<a href="' . $url . '">Apagar consulta</a></form>' : '';
        $table .= '<table class="' . $class . '">';
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
                $body .= isset($actions['others']) ? $this->linkToOthers($entity, $actions['others']) . '&nbsp;' : '';
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
    
    private function linkToOthers(\Model\Entity $entity, $rules)
    {
        $link = '';
        foreach ($rules as $rule) { // $rule has indexes title, url and icon
            if (preg_match_all('/\{(\w+)\}/', $rule['url'], $parts)) {
                $link .= $this->getLink('others', $parts[0], array($entity, $parts[1]), $rule);
            } else {
                $link .= $this->getLink('others', '?', $entity->getPKValue(), $rule);
            }
            $link .= '&nbsp;';
        }
        return $link;
    }
    
    private function getLink($type, $search, $replace, $rules)
    {
        $url = is_array($rules) ? $rules['url'] : $rules;
        $title = is_array($rules) ? (isset($rules['title']) ? $rules['title'] : '') : '';
        $icon = is_array($rules) ? (isset($rules['icon']) ? $rules['icon'] : '') : '';
        
        switch ($type) {
            case 'delete':
                $linkTitle = 'Excluir';
                $linkIcon = '/images/delete.png';
                break;
            case 'edit':
                $linkTitle = 'Editar';
                $linkIcon = '/images/edit.png';
                break;
            default:
                $linkTitle = $title;
                $linkIcon = '/images/' . $icon;
        }
        
        $linkTmp = $type == 'delete' ?
                '<a title="[TITLE]" onclick="if (!confirm(\'[MSG]\')) return false;" href="[LINK]"><img src="[ICON]" /></a>' :
                '<a title="[TITLE]" href="[LINK]"><img src="[ICON]" /></a>';
        
        $link = '';
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
        
        $link = str_replace('[TITLE]', $linkTitle, $link);
        $link = strpos($link, '[ICON]') !== false ?
                str_replace('[ICON]', $linkIcon, $link) :
                $link;
        
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
        $urlFirst = str_replace('/{page}', '', $urlPattern);
        $urlLast = str_replace('{page}', ($totalPages - 1), $urlPattern);
        
        $first = '<a class="table-previous" href="' . $urlFirst . '">&laquo;</a>&nbsp;';
        $last = '&nbsp;<a class="table-next" href="' . $urlLast . '">&raquo;</a>';
        $middle = $this->getMiddlePages($page, $totalPages, $urlPattern);
        
        $paginator = '<div class="table-paginator">';
        if (strtoupper($httpMethod) === 'GET') {
            $next = '&nbsp;' . ($page + 1 >= $totalPages ? '&rarr;' : '<a class="table-next" href="' . $urlNext . '">&rarr;</a>') . '&nbsp;';
            $previous = '&nbsp;' . ($page - 1 < 0 ? '&larr;' : '<a class="table-previous" href="' . $urlPrev . '">&larr;</a>') . '&nbsp;';
            $paginator .= $this->checkOtherParameters($first . $previous . $middle . $next . $last);
        } elseif (strtoupper($httpMethod) === 'POST') {
            $params = $this->app['request']->request->all();
            $next = '&nbsp;' . ($page + 1 >= $totalPages ? '&rarr;' : '<a class="table-next" href="' . $urlNext . '">&rarr;</a>') . '&nbsp;';
            $previous = '&nbsp;' . ($page - 1 < 0 ? '&larr;' : '<a class="table-previous" href="' . $urlPrev . '">&larr;</a>') . '&nbsp;';
            $paginator .= $first . $previous . $middle . $next . $last;
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
    
    private function getMiddlePages($page, $total, $url)
    {
        $prev1 = $page - 1 < 0 ? null : $page - 1;
        $prev2 = $page - 2 < 0 ? null : $page - 2;
        $next1 = $page + 1 > $total - 1 ? null : $page + 1;
        $next2 = $page + 2 > $total - 1 ? null : $page + 2;
        
        $urlPrev1 = is_null($prev1) ? '' : '&nbsp;<a class="table-previous" href="' . str_replace('{page}', $prev1, $url) . '">' . ($prev1 + 1) . '</a>&nbsp;';
        $urlPrev2 = is_null($prev2) ? '' : '&nbsp;<a class="table-previous" href="' . str_replace('{page}', $prev2, $url) . '">' . ($prev2 + 1) . '</a>&nbsp;';
        $urlNext1 = is_null($next1) ? '' : '&nbsp;<a class="table-next" href="' . str_replace('{page}', $next1, $url) . '">' . ($next1 + 1) . '</a>&nbsp;';
        $urlNext2 = is_null($next2) ? '' : '&nbsp;<a class="table-next" href="' . str_replace('{page}', $next2, $url) . '">' . ($next2 + 1) . '</a>&nbsp;';
        $center = '&nbsp;<span class="table-actual-page">' . ($page + 1) . '</span>&nbsp;';
        
        return $urlPrev2 . $urlPrev1 . $center . $urlNext1 . $urlNext2;
    }
    
    private function getSortingArea($keys, $values, $options)
    {
        $id = isset($options['selectId']) ? $options['selectId'] : 'ssk_sort_select';
        $default = isset($options['defaultText']) ? $options['defaultText'] : '-- Choose --';
        $sortVal = $this->app['request']->get($options['paramName'], null);
        $container = isset($options['container']) ? $options['container'] : 'ssk_sort_container';
        $html = '<div id="' . $container . '">' . "\n";
        $html .= '<select id="' . $id . '" name="' . $options['paramName'] . '">' . "\n";
        $html .= '    <option value="">' . $default . '</option>' . "\n";
        foreach ($keys as $idx => $val) {
            $html .= '    <option value="' . $val . ' ASC"' . ($sortVal == $val.' ASC' ? ' selected' : '') . '>' . \Helper\Text::generateLabel($values[$idx]) . ' ascendentemente</option>' . "\n";
            $html .= '    <option value="' . $val . ' DESC"' . ($sortVal == $val.' DESC' ? ' selected' : '') . '>' . \Helper\Text::generateLabel($values[$idx]) . ' descendentemente</option>' . "\n";
        }

        $html .= '</select>' . "\n";
        $html .= '<script>' . "\n";
        $html .= '    var select = document.getElementById("' . $id . '");' . "\n";
        $html .= '    select.addEventListener("change", sskCallSort, false);' . "\n";
        $html .= '    function sskCallSort() {' . "\n";
        $html .= '        var sort = select.options[select.selectedIndex].value;' . "\n";
        $html .= '        var form = document.getElementById("ssk_table_modifier_form");' . "\n";
        $html .= '        if (sort != "")' . "\n";
        $html .= '            form.submit();' . "\n";
        $html .= '    }' . "\n";
        $html .= '</script>' . "\n";
        $html .= '</div>' . "\n";
        
        return $html;
    }
    
    private function getSearchingArea($keys, $values, $options)
    {
        $search = $this->app['request']->get($options['paramName'], array('value' => '', 'field' => ''));
        $btID = isset($options['buttonId']) ? $options['buttonId'] : 'ssk_search_input';
        $selID = isset($options['selectId']) ? $options['selectId'] : 'ssk_search_select';
        $placeholder = isset($options['placeholder']) ? $options['placeholder'] : 'Input search string';
        $default = isset($options['defaultText']) ? $options['defaultText'] : '-- Choose --';
        $text = isset($options['buttonText']) ? $options['buttonText'] : 'Search';
        $container = isset($options['container']) ? $options['container'] : 'ssk_search_container';
        $error = isset($options['error']) ? $options['error'] : 'Must choose field!';
        
        $html = '<div id="' . $container . '">' . "\n";
        $html .= '<select id="' . $selID . '" name="' . $options['paramName'] . '[field]">' . "\n";
        $html .= '    <option value="">' . $default . '</option>' . "\n";
        foreach ($keys as $idx => $val)
            $html .= '    <option value="' . $val . '"' . ($search['field'] == $val ? ' selected' : '') . '>' . \Helper\Text::generateLabel($values[$idx]) . '</option>' . "\n";
        $html .= '</select>' . "\n";
        $html .= '<input type="search" name="' . $options['paramName'] . '[value]" value="' . $search['value'] . '" placeholder="' . $placeholder . '" />' . "\n";
        $html .= '<button id="' . $btID . '">' . $text . '</button>' . "\n";
        
        $html .= '<script>' . "\n";
        $html .= '    var button = document.getElementById("' . $btID . '");' . "\n";
        $html .= '    button.addEventListener("click", sskCallSearch, false);' . "\n";
        $html .= '    function sskCallSearch(evt) {' . "\n";
        $html .= '        evt.preventDefault();' . "\n";
        $html .= '        var form = document.getElementById("ssk_table_modifier_form");' . "\n";
        $html .= '        var selectSearch = document.getElementById("' . $selID . '");' . "\n";
        $html .= '        var searchField = selectSearch.options[selectSearch.selectedIndex].value;' . "\n";
        $html .= '        if (searchField != "")' . "\n";
        $html .= '            form.submit();' . "\n";
        $html .= '        else' . "\n";
        $html .= '            alert("' . $error . '");' . "\n";
        $html .= '    }' . "\n";
        $html .= '</script>' . "\n";
        $html .= '</div>' . "\n";
        
        return $html;
    }
    
    private function checkOtherParameters($url)
    {
        $matches = array();
        preg_match_all('/{(\w+)}/', $url, $matches);
        $parameters = $matches[1];
        
        foreach ($parameters as $parameter) {
            $url = str_replace('{' . $parameter . '}', $this->app['request']->get($parameter), $url);
        }
        
        return $url;
    }
    
    private function getScript()
    {
        return <<<SCRIPT
<script>
    function sskSubmitPagination(evt) {
        evt.preventDefault();
        var form = document.getElementById("formPagination");
        form.action = this.href;
        form.submit();
        return false;
    }
    
    var links = document.querySelectorAll(".table-previous, .table-next");
    console.log(links);
    if (links) {
        for (var i = 0; i < links.length; i++) {
            links[i].addEventListener('click', sskSubmitPagination, false);
        }
    }
</script>
SCRIPT;
    }
    
}