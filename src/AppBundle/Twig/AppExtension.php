<?php
/**
 * Created by PhpStorm.
 * User: desaroger
 * Date: 7/11/16
 * Time: 19:54
 */

namespace AppBundle\Twig;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('publisherName', array($this, 'publisherNameFilter')),
        );
    }

    public function publisherNameFilter($publisherCode)
    {
        $valueMap = [
            'elpais' => 'El País',
            'elmundo' => 'El Mundo',
            'elconfidencial' => 'El Confidencial',
            'larazon' => 'La Razón',
            'elperiodico' => 'El Periódico'
        ];
        return $valueMap[$publisherCode];
    }

    public function getName()
    {
        return 'app_extension';
    }
}