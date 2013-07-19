<?php
namespace Oxygen\WeezeventBundle\Form\Type;

use Oxygen\WeezeventBundle\API\WeezeventAPI;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\AbstractType;

/**
 * Base class for form based on weezevent data
 * 
 * @author lolozere
 *
 */
abstract class WeezeventFormType extends AbstractType {
	/**
	 * 
	 * @var WeezeventAPI
	 */
	protected $weezeventApi;
	/**
	 * 
	 * @param WeezeventAPI $weezeventApi
	 */
	public function __construct($weezeventApi) {
		$this->weezeventApi = $weezeventApi;
	}

}