<?php

namespace App\Model;


use App\Services\Common\SlugCreator;


class GeneralAdmin
{
    public function prepareData($request, $entity, $properties, $property_for_slug = null)
    {
        $dateTime = new \DateTime();
        $dateProperties = [
            'create_date',
            'update_date',
            'registration_date'
        ];

        foreach ($properties as $property=>$set) {
            $property_value = $request->request->get($property);
            if ($property == 'slug' && empty($property_value)) {
                if ($property_for_slug == null) {
                    $property_value = $dateTime->getTimestamp();
                } else {
                    $slugCreator = new SlugCreator();
                    $property_value = $slugCreator->createSlug($request->request->get($property_for_slug));
                }
            } elseif (in_array($property, $dateProperties)) {
                $property_value = $dateTime;
            }

            $entity->$set($property_value);
        }

        return $entity;
    }
}