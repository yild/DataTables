<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DataTables\View\Helper;

use Cake\View\Helper;

/**
 * CakePHP DataTablesHelper
 * @author allan
 */
class DataTablesHelper extends Helper
{

    public $helpers     = ['Html'];
    public $wasRendered = [];
    public $json        = [];

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->json['data'] = [];
    }
    
    public function render($name, array $options = [])
    {
        if (empty($this->_View->viewVars["DataTables"][$name]))
        {
            throw new \Cake\Error\FatalErrorException(__d('datatables', 'The requested DataTables config was not configured or set to view in controller'));
        }
        $config = $this->_View->viewVars["DataTables"][$name];

        $this->wasRendered[] = $name;

        $options['id'] = $config['id'];

        $options += [
            'width'       => '100%',
            'cellspacing' => 0,
            'class'       => 'display'
        ];


        foreach ($config['columns'] as $item)
        {
            $cols[] = $item['label'];
        }


        $html = $this->Html->tag('table', $this->Html->tag('thead', $this->Html->tableHeaders($cols)), $options);


        return $html;
    }

    public function prepareData(array $data)
    {

        $this->json['data'][] = $data;
    }

    public function response()
    {

        $data = $this->json['data'];
        
        

        $this->json         = $this->_View->viewVars['resultInfo'];
        $this->json['data'] = $data;

        echo json_encode($this->json, JSON_PRETTY_PRINT);
    }

    public function setJs()
    {

        if (!empty($this->_View->viewVars["DataTables"]))
        {
            $html = "<script>$(document).ready(function() {";
            foreach ($this->wasRendered as $item)
            {

                $config = $this->_View->viewVars["DataTables"][$item];



                if (!empty($config['options']))
                {
                    $options = $config['options'];
                } else
                {
                    $options = [];
                }

                $columnCount = 0;
                $order       = [];
                $columnDefs  = [];
                foreach ($config['columns'] as $column)
                {
                    if (!empty($column['order']))
                    {
                        $order[] = [$columnCount, $column['order']];
                    }



                    $columnDefs[] = [
                        'targets'        => $columnCount,
                        'searchable'     => $column['searchable'],
                        'orderable'      => $column['orderable'],
                        'className'      => $column['className'],
                        'name'           => $column['name'],
                        'orderDataType'  => $column['orderDataType'],
                        'type'           => $column['type'],
                        'title'          => $column['label'],
                        'visible'        => $column['visible'],
                        'width'          => $column['width'],
                        'defaultContent' => $column['defaultContent'],
                        'contentPadding' => $column['contentPadding'],
                        'cellType'       => $column['cellType']
                    ];

                    $columnCount++;
                }



                $options['processing'] = true;
                $options['serverSide'] = true;
                $options['ajax']       = \Cake\Routing\Router::url(['controller' => $this->request->params['controller'], 'action' => 'getDataTablesContent', $item]);
                $options['order']      = $order;
                $options['columnDefs'] = $columnDefs;



                $html .= "$('#" . $config['id'] . "').DataTable(";


                $html .= json_encode($options);



                $html .= ");";
            }
            $html .= '} );</script>';



            return $html;
        }
    }

}
