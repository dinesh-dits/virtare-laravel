<?php

namespace App\Library;
require_once('crest/src/crest.php');

class BitrixApi extends CRest
{
    function getDealById($id)
    {
        if ($id) {
            $getDeal = CRest::call(
                'crm.contact.get',
                [
                    'id' => $id,
                ]
            );
            return $getDeal;
        } else {
            return false;
        }
    }

    function getDealByName($title)
    {
        if ($title) {
            $title = explode(" ", $title);
            $array['result'] = array();
            //foreach ($title as $key => $value) {
            if (count($title) > 1) {

                $getDeal = CRest::call(
                    'crm.contact.list',
                    [
                        'filter' => array("%NAME" => $title[0], "%LAST_NAME" => $title[1])
                    ]
                );
                if (isset($getDeal['result'])) {
                    $array['result'] = array_merge($array['result'], $getDeal['result']);
                }
                $getDeal = CRest::call(
                    'crm.contact.list',
                    [
                        'filter' => array("%NAME" => $title[1], "%LAST_NAME" => $title[0])
                    ]
                );
                if (!isset($getDeal['result'])) {
                    $array['result'] = array_merge($array['result'], $getDeal['result']);
                }
            } else {
                $getDeal = CRest::call(
                    'crm.contact.list',
                    [
                        'filter' => array("%NAME" => $title[0])
                    ]
                );
                if (isset($getDeal['result'])) {
                    $array['result'] = array_merge($array['result'], $getDeal['result']);
                }
            }

            //}

            return $array;
        } else {
            return false;
        }
    }

    function getAllDeal()
    {
        $getDeal = CRest::call(
            'crm.contact.list',
            []
        );

        return $getDeal;
    }

    function searchDeals($search_obj)
    {
        if ($search_obj) {
            $getDeal = CRest::call(
                'crm.contact.list',
                [
                    'filter' => $search_obj,
                ]
            );
            return $getDeal;
        } else {
            return false;
        }
    }
}
