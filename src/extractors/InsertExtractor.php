<?php

namespace RexShijaku\SQLToCIBuilder\extractors;
/**
 * This class extracts and compiles SQL query parts for the following Query Builder methods :
 *
 *  insert
 *
 * @author Rexhep Shijaku <rexhepshijaku@gmail.com>
 *
 */
class InsertExtractor extends AbstractExtractor implements Extractor
{
    public function extract(array $value, array $parsed = array()): array
    {
        $table = '';
        $column_list = array();
        foreach ($value as $val)
        {
            if ($val['expr_type'] == 'table')
                $table = $val['base_expr'];
            else if ($val['expr_type'] == 'column-list')
            {
                foreach ($val['sub_tree'] as $column)
                    $column_list[] = $column['base_expr'];
            }
        }

        $records = array(); // collect data so you gave only records and know about if is it batch or not
        if (isset($parsed['VALUES']))
        {
            foreach ($parsed['VALUES'] as $key => $item)
            {
                if ($item['expr_type'] == 'record')
                {
                    $data = array();
                    foreach ($item['data'] as $datum)
                        $data[] = $datum['base_expr'];
                    $records[] = $data;
                }
            }
        }
        if (isset($parsed['SET']))
        {
            $data = [];
            foreach ($parsed['SET'] as $key => $item)
            {
                if ($item['expr_type'] == 'expression')
                {
                    $set_expression = [
                        'colref' => $item['sub_tree'][0]['base_expr'],
                        'const' => $item['sub_tree'][2]['base_expr'],
                    ];
                    $data[] = $set_expression['const'];
                    $column_list[] = $set_expression['colref'];
                }
            }
            $records = [$data];
        }

        return array('table' => $table, 'columns' => $column_list, 'records' => $records);
    }

}