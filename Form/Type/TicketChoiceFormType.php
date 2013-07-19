<?php
namespace Oxygen\WeezeventBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\AbstractType;

/**
 * Choice ticket from Weezevent
 * 
 * @author lolozere
 *
 */
class TicketChoiceFormType extends WeezeventFormType {

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$tickets = $this->weezeventApi->getAllTickets();
		$choices = array();
		foreach($tickets as $ticket) {
			$choices[$ticket['id']] = $ticket['event']['name'] . ' > ' . $ticket['name'];
		}
		$resolver->setDefaults(array(
				'choices' => $choices
		));
	}
	
	public function getParent()
	{
		return 'choice';
	}
	
	public function getName()
	{
		return 'oxygen_weezevent_tickets_type';
	}
	
}